<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\PostFormLookups;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,DB};
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    /** Show the searchable post list and all form lookup data. */
    public function index(Request $request)
    {
        $admin=Auth::guard('admin')->user();
        $adminId=$admin->id;
        $routeName=$request->route()?->getName();
        $scope=match($routeName){'posts.review'=>'review','posts.mine'=>'mine',default=>'all'};
        $canManageAll=$admin->type==='superadmin';

        // Direct access to All News never exposes the full newsroom to a reporter.
        if($scope==='all'&&!$canManageAll)$scope='mine';
        if($scope==='review'&&!$admin->hasModuleAccess('post','edit')) abort(403,'You do not have permission to review posts.');

        $visiblePosts=Post::query();
        if($scope==='mine'){
            $visiblePosts->where(fn($q)=>$q->where('created_by',$adminId)->orWhere('author_id',$adminId));
        }elseif($scope==='review'){
            $visiblePosts->where('reviewed_by',$adminId)->where('status','review');
        }

        $search=trim((string)$request->search);
        $status=$request->status;
        $categoryId=$request->category_id;
        $posts=(clone $visiblePosts)
            ->select(['id','title','slug','featured_image','category_id','author_id','created_by','reviewed_by','status','published_at','created_at'])
            ->with(['category:id,category_name','author:id,name'])
            ->when($search,fn($q)=>$q->where('title','like',"%{$search}%"))
            ->when($status,fn($q)=>$q->where('status',$status))
            ->when($categoryId,fn($q)=>$q->where('category_id',$categoryId))
            ->latest()->paginate(10)->withQueryString();

        // One aggregate scan replaces four independent COUNT queries.
        $counts=(clone $visiblePosts)->selectRaw(
            "COUNT(*) AS total,
             COALESCE(SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END), 0) AS published,
             COALESCE(SUM(CASE WHEN status IN ('draft', 'review') THEN 1 ELSE 0 END), 0) AS draft_review,
             COALESCE(SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END), 0) AS scheduled"
        )->first();
        $summary=collect(['total','published','draft_review','scheduled'])
            ->mapWithKeys(fn($key)=>[$key=>(int)$counts->{$key}])->all();
        [$pageTitle,$pageSubtitle,$indexRoute]=match($scope){
            'mine'=>['My Posts','Stories created by or credited to you.','posts.mine'],
            'review'=>['Review Queue','Stories assigned to you for editorial review.','posts.review'],
            default=>['All News','Manage the complete newsroom workflow.','posts'],
        };

        return view('admin.post.post',[
            'posts'=>$posts,'summary'=>$summary,'search'=>$search,'status'=>$status,
            'categoryId'=>$categoryId,'scope'=>$scope,'pageTitle'=>$pageTitle,
            'pageSubtitle'=>$pageSubtitle,'indexRoute'=>$indexRoute,'canManageAll'=>$canManageAll,
            ...PostFormLookups::get(),
        ]);
    }
    /** Create one post, tags, gallery and initial status history atomically. */
    public function store(Request $request)
    {
        $data=$this->validatePost($request);
        $featured=$this->upload($request->file('featured_image'));
        $meta=$this->upload($request->file('meta_image'));
        $gallery=[];

        try {
            foreach($request->file('gallery_images',[]) as $index=>$file){
                if($file) $gallery[$index]=$this->upload($file,'gallery');
            }

            $post=DB::transaction(function() use($data,$featured,$meta,$gallery){
                $post=Post::create($this->postData($data,$featured,$meta));
                $post->tags()->sync($data['tag_ids']??[]);
                $this->saveGallery($post,$gallery,$data['gallery_captions']??[]);
                $post->statusHistories()->create([
                    'changed_by'=>Auth::guard('admin')->id(),'from_status'=>null,
                    'to_status'=>$post->status,'note'=>'Post created.','created_at'=>now(),
                ]);
                return $post;
            });
        } catch(\Throwable $e) {
            $this->deleteUploads(array_merge([$featured,$meta],array_values($gallery)));
            throw $e;
        }

        return response()->json(['message'=>'Post created successfully.','post'=>$post],201);
    }

    /** Return one post and its relations for the edit modal. */
    public function show(Post $post)
    {
        $this->authorizePost($post,'view');
        $post->load(['tags:id,name','images','statusHistories.changedBy:id,name']);
        // Store timestamps normally, but present editorial history in Bangladesh local time.
        $auditTime=fn($value)=>$value?->timezone('Asia/Dhaka')->format('d M Y, h:i A');
        return response()->json(['post'=>[
            // Only attributes are spread; relations are returned once in their UI-specific shape.
            ...$post->attributesToArray(),
            'tag_ids'=>$post->tags->pluck('id')->values(),
            'featured_image_url'=>$this->url($post->featured_image),
            'meta_image_url'=>$this->url($post->meta_image),
            'scheduled_at_input'=>$post->scheduled_at?->format('Y-m-d\TH:i'),
            'editorial_history'=>$post->statusHistories->map(fn($history)=>[
                'id'=>$history->id,
                'changed_by'=>$history->changedBy?->name ?? 'Deleted or unknown user',
                'from_status'=>$history->from_status,
                'to_status'=>$history->to_status,
                'note'=>$history->note,
                'changed_at'=>$auditTime($history->created_at),
            ])->values(),
            'images'=>$post->images->map(fn($image)=>[
                ...$image->toArray(),'image_url'=>$this->url($image->image),
            ]),
        ]]);
    }

    /** Update content, create a revision and keep status history. */
    public function update(Request $request, Post $post)
    {
        $this->authorizePost($post,'edit');
        $data=$this->validatePost($request,$post);
        $this->authorizePost($post,'status',$data['status']);
        $admin=Auth::guard('admin')->user();
        if(in_array($data['status'],['scheduled','published'],true)&&!empty($data['reviewed_by'])&&(int)$data['reviewed_by']!==$admin->id&&$admin->type!=='superadmin'){
            return response()->json(['message'=>'Only the assigned reviewer can approve or publish this story.'],403);
        }
        $oldFeatured=$post->featured_image;
        $oldMeta=$post->meta_image;
        $featured=$this->upload($request->file('featured_image'));
        $meta=$this->upload($request->file('meta_image'));
        $gallery=[];

        try {
            foreach($request->file('gallery_images',[]) as $index=>$file){
                if($file) $gallery[$index]=$this->upload($file,'gallery');
            }

            $removedImages=DB::transaction(function() use($post,$data,$featured,$meta,$gallery){
                $oldStatus=$post->status;
                $this->saveRevision($post,$data['change_note']??null);
                $post->update($this->postData($data,$featured?:$post->featured_image,$meta?:$post->meta_image,$post));
                $post->tags()->sync($data['tag_ids']??[]);

                $removeIds=collect($data['remove_image_ids']??[])->map(fn($id)=>(int)$id);
                $removed=$post->images()->whereIn('id',$removeIds)->pluck('image')->all();
                $post->images()->whereIn('id',$removeIds)->delete();
                $this->saveGallery($post,$gallery,$data['gallery_captions']??[]);

                if($oldStatus!==$post->status){
                    $post->statusHistories()->create([
                        'changed_by'=>Auth::guard('admin')->id(),'from_status'=>$oldStatus,
                        'to_status'=>$post->status,'note'=>$data['change_note']??null,'created_at'=>now(),
                    ]);
                }
                return $removed;
            });
        } catch(\Throwable $e) {
            $this->deleteUploads(array_merge([$featured,$meta],array_values($gallery)));
            throw $e;
        }

        if($featured) $this->deleteUploads([$oldFeatured]);
        if($meta) $this->deleteUploads([$oldMeta]);
        $this->deleteUploads($removedImages);

        return response()->json(['message'=>'Post updated successfully.']);
    }

    /** Change only the workflow status from the list. */
    public function updateStatus(Request $request, Post $post)
    {
        $data=$request->validate(['status'=>['required',Rule::in(['draft','review','scheduled','published','archived'])]]);
        $this->authorizePost($post,'status',$data['status']);
        $old=$post->status;
        $adminId=Auth::guard('admin')->id();
        if($data['status']==='review'&&!$post->reviewed_by){
            return response()->json(['message'=>'Open Edit Post and choose who should review this story first.'],422);
        }
        if(in_array($data['status'],['scheduled','published'],true)&&$post->reviewed_by&&$post->reviewed_by!==$adminId&&Auth::guard('admin')->user()?->type!=='superadmin'){
            return response()->json(['message'=>'This story is assigned to another reviewer.'],403);
        }
        $values=['status'=>$data['status'],'updated_by'=>$adminId,'last_edited_at'=>now()];
        // reviewed_at stays empty while waiting; approval records the logged-in reviewer and time.
        if($data['status']==='review'&&$old!=='review'){$values['reviewed_at']=null;}
        if(in_array($data['status'],['scheduled','published'],true)){
            $values['reviewed_by']=$adminId; $values['reviewed_at']=now();
        }
        if($data['status']==='published'&&!$post->published_at){
            $values['published_at']=now(); $values['published_by']=$adminId;
        }
        $post->update($values);
        if($old!==$post->status) $post->statusHistories()->create([
            'changed_by'=>Auth::guard('admin')->id(),'from_status'=>$old,'to_status'=>$post->status,
            'note'=>'Status changed from post list.','created_at'=>now(),
        ]);
        return response()->json(['message'=>'Post status updated successfully.']);
    }

    /** Soft-delete the post and retain files for possible restoration. */
    public function destroy(Post $post)
    {
        $this->authorizePost($post,'delete');
        $post->update(['deleted_by'=>Auth::guard('admin')->id()]);
        $post->delete();
        return response()->json(['message'=>'Post moved to trash successfully.']);
    }

    /** Validate create and update using the same readable rules. */
    private function validatePost(Request $request,?Post $post=null): array
    {
        return $request->validate([
            'section_id'=>['nullable','integer','exists:sections,id'],
            'category_id'=>['nullable','integer','exists:categories,id'],
            'division_id'=>['nullable','integer','exists:divisions,id'],
            'district_id'=>['nullable','integer','exists:districts,id'],
            'upazila_id'=>['nullable','integer','exists:upazilas,id'],
            'author_id'=>['nullable','integer','exists:admins,id'],
            'reviewed_by'=>['nullable','integer','required_if:status,review',Rule::exists('admins','id')->where(fn($q)=>$q->where('status',1))],
            'post_type'=>['required',Rule::in(['news','article','opinion','video','photo_story'])],
            'title'=>['required','string','max:255'],
            'slug'=>['nullable','string','max:255',Rule::unique('posts','slug')->ignore($post?->id)],
            'special_title'=>['nullable','string','max:255'],
            'summary'=>['nullable','string','max:1000'],
            'description'=>['required','string'],
            'source'=>['nullable','string','max:255'],
            'featured_image'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
            'image_caption'=>['nullable','string','max:2000'],
            'image_alt'=>['nullable','string','max:255'],
            'video_url'=>['nullable','url','max:2000'],
            'video_type'=>['nullable',Rule::in(['youtube','facebook','vimeo','upload'])],
            'status'=>['required',Rule::in(['draft','review','scheduled','published','archived'])],
            'scheduled_at'=>['nullable','date'],
            'is_breaking'=>['required','boolean'],'is_featured'=>['required','boolean'],
            'is_pinned'=>['required','boolean'],'allow_comments'=>['required','boolean'],
            'featured_positions'=>['nullable','array'],
            'featured_positions.*'=>[Rule::in(['home_top','home_middle','category_top','sidebar'])],
            'meta_title'=>['nullable','string','max:255'],'meta_description'=>['nullable','string','max:1000'],
            'meta_keywords'=>['nullable','string'],'meta_image'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
            'meta_robots'=>['required',Rule::in(['index,follow','noindex,follow','index,nofollow','noindex,nofollow'])],
            'canonical_url'=>['nullable','url','max:2000'],'schema_markup'=>['nullable','json'],
            'tag_ids'=>['nullable','array'],'tag_ids.*'=>['integer','exists:tags,id'],
            'gallery_images'=>['nullable','array','max:20'],'gallery_images.*'=>['image','mimes:jpg,jpeg,png,webp','max:5120'],
            'gallery_captions'=>['nullable','array'],'gallery_captions.*'=>['nullable','string','max:2000'],
            'remove_image_ids'=>['nullable','array'],'remove_image_ids.*'=>['integer','exists:post_images,id'],
            'change_note'=>['nullable','string','max:1000'],
        ]);
    }

    /** Convert validated input into the posts table values. */
    private function postData(array $data,?string $featured,?string $meta,?Post $post=null): array
    {
        $adminId=Auth::guard('admin')->id();
        $status=$data['status'];
        $publishedAt=$post?->published_at;
        $publishedBy=$post?->published_by;
        // reviewed_by is the selected editor; reviewed_at distinguishes pending from completed review.
        $reviewedBy=!empty($data['reviewed_by'])?(int)$data['reviewed_by']:null;
        $reviewedAt=($post&&$post->reviewed_by===$reviewedBy)?$post->reviewed_at:null;
        if($status==='review'){$reviewedAt=null;}
        if(in_array($status,['scheduled','published'],true)){$reviewedBy=$adminId;$reviewedAt=now();}
        if($status==='published'&&!$publishedAt){$publishedAt=now();$publishedBy=$adminId;}

        return [
            'section_id'=>$data['section_id']??null,'category_id'=>$data['category_id']??null,
            'division_id'=>$data['division_id']??null,'district_id'=>$data['district_id']??null,
            'upazila_id'=>$data['upazila_id']??null,'author_id'=>$data['author_id']??$adminId,
            'created_by'=>$post?->created_by?:$adminId,'updated_by'=>$adminId,
            'reviewed_by'=>$reviewedBy,'published_by'=>$publishedBy,
            'post_type'=>$data['post_type'],'title'=>trim($data['title']),
            'slug'=>$this->uniqueSlug(($data['slug'] ?? null) ?: $data['title'],$post),
            'special_title'=>$data['special_title']??null,'summary'=>$data['summary']??null,
            'description'=>$data['description'],'source'=>$data['source']??null,
            'featured_image'=>$featured,'image_caption'=>$data['image_caption']??null,
            'image_alt'=>$data['image_alt']??null,'video_url'=>$data['video_url']??null,
            'video_type'=>$data['video_type']??null,'status'=>$status,
            'scheduled_at'=>$data['scheduled_at']??null,
            'reviewed_at'=>$reviewedAt,
            'published_at'=>$publishedAt,'last_edited_at'=>now(),
            'is_breaking'=>(bool)$data['is_breaking'],'is_featured'=>(bool)$data['is_featured'],
            'is_pinned'=>(bool)$data['is_pinned'],'allow_comments'=>(bool)$data['allow_comments'],
            'featured_positions'=>$data['featured_positions']??null,
            'meta_title'=>$data['meta_title']??null,'meta_description'=>$data['meta_description']??null,
            'meta_keywords'=>$data['meta_keywords']??null,'meta_image'=>$meta,
            'meta_robots'=>$data['meta_robots'],'canonical_url'=>$data['canonical_url']??null,
            'schema_markup'=>!empty($data['schema_markup'])?json_decode($data['schema_markup'],true):null,
            'reading_time'=>max(1,(int)ceil(str_word_count(strip_tags($data['description']))/220)),
        ];
    }

    /** Enforce record ownership and editorial responsibility after module permission checks. */
    private function authorizePost(Post $post,string $action,?string $nextStatus=null): void
    {
        $admin=Auth::guard('admin')->user();
        $owns=(int)$post->created_by===$admin->id||(int)$post->author_id===$admin->id;
        $assigned=(int)$post->reviewed_by===$admin->id;
        $managesAll=$admin->type==='superadmin';

        $allowed=match($action){
            'view'=>$managesAll||$owns||$assigned,
            'edit'=>$managesAll||$owns||$assigned,
            'delete'=>$managesAll||$owns,
            'status'=>$managesAll||$assigned||($owns&&in_array($nextStatus,['draft','review'],true)),
            default=>false,
        };

        abort_unless($allowed,403,'You cannot manage this newsroom story.');
    }
    private function uniqueSlug(string $value,?Post $post=null): string
    {
        $base=Str::slug($value)?:'post'; $slug=$base; $i=2;
        while(Post::where('slug',$slug)->when($post,fn($q)=>$q->where('id','!=',$post->id))->exists()) $slug=$base.'-'.$i++;
        return $slug;
    }

    private function saveGallery(Post $post,array $files,array $captions): void
    {
        // Read the current position once instead of once for every uploaded image.
        $position=(int)$post->images()->max('position');
        foreach($files as $index=>$file) $post->images()->create([
            'image'=>$file,'caption'=>$captions[$index]??null,
            'position'=>++$position,
        ]);
    }

    private function saveRevision(Post $post,?string $note): void
    {
        $post->revisions()->create([
            'editor_id'=>Auth::guard('admin')->id(),
            'version'=>(int)$post->revisions()->max('version')+1,
            'title'=>$post->title,'special_title'=>$post->special_title,'summary'=>$post->summary,
            'description'=>$post->description,'featured_image'=>$post->featured_image,
            'image_caption'=>$post->image_caption,'change_note'=>$note,'created_at'=>now(),
        ]);
    }

    private function upload($file,string $folder='main'): ?string
    {
        if(!$file) return null;
        $directory=public_path('admin/postimages/'.$folder);
        if(!is_dir($directory)) mkdir($directory,0755,true);
        $name=now()->format('YmdHis').'-'.Str::random(12).'.'.$file->extension();
        $file->move($directory,$name);
        return $folder.'/'.$name;
    }

    private function url(?string $path): ?string
    {
        return $path?asset('admin/postimages/'.ltrim($path,'/')):null;
    }

    private function deleteUploads(array $paths): void
    {
        foreach(array_filter($paths) as $path){
            $full=public_path('admin/postimages/'.ltrim(basename(dirname($path)).'/'.basename($path),'/'));
            if(is_file($full)) unlink($full);
        }
    }
}
