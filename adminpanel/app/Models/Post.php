<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo,HasMany,BelongsToMany};

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'section_id','category_id','division_id','district_id','upazila_id','author_id',
        'created_by','updated_by','reviewed_by','published_by','deleted_by','post_type',
        'title','slug','special_title','summary','description','source','featured_image',
        'image_caption','image_alt','video_url','video_type','status','scheduled_at',
        'reviewed_at','published_at','last_edited_at','is_breaking','is_featured',
        'is_pinned','allow_comments','featured_positions','meta_title','meta_description',
        'meta_keywords','meta_image','meta_robots','canonical_url','schema_markup',
        'views_count','reading_time',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at'=>'datetime','reviewed_at'=>'datetime','published_at'=>'datetime',
            'last_edited_at'=>'datetime','is_breaking'=>'boolean','is_featured'=>'boolean',
            'is_pinned'=>'boolean','allow_comments'=>'boolean','featured_positions'=>'array',
            'schema_markup'=>'array',
        ];
    }

    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function division(): BelongsTo { return $this->belongsTo(Division::class); }
    public function district(): BelongsTo { return $this->belongsTo(District::class); }
    public function upazila(): BelongsTo { return $this->belongsTo(Upazila::class); }
    public function author(): BelongsTo { return $this->belongsTo(Admin::class, 'author_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(Admin::class, 'created_by'); }
    public function updater(): BelongsTo { return $this->belongsTo(Admin::class, 'updated_by'); }
    public function publisher(): BelongsTo { return $this->belongsTo(Admin::class, 'published_by'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(Admin::class, 'reviewed_by'); }
    public function images(): HasMany { return $this->hasMany(PostImage::class)->orderBy('position'); }
    public function tags(): BelongsToMany { return $this->belongsToMany(Tag::class)->withTimestamps(); }
    public function revisions(): HasMany { return $this->hasMany(PostRevision::class); }
    public function statusHistories(): HasMany
    {
        return $this->hasMany(PostStatusHistory::class)
            ->orderBy('created_at')
            ->orderBy('id');
    }
}
