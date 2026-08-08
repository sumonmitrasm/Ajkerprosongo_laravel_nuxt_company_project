<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Tags';
        $search = trim((string) $request->query('search', ''));

        $getTags = Tag::query()
            ->select('id', 'name', 'slug', 'image', 'title', 'status')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            }))
            ->latest('id')
            ->cursorPaginate($this->perPage($request))
            ->withQueryString();

        return view('admin.tag.tag', compact('getTags', 'title'));
    }

    public function store(Request $request)
    {
        Tag::create($this->validatedData($request));

        return response()->json(['message' => 'Tag created successfully.'], 201);
    }

    public function show(Tag $tag)
    {
        return response()->json([
            'record' => $tag,
            'image_url' => $tag->image ? asset('admin/tagimage/' . $tag->image) : null,
        ]);
    }

    public function update(Request $request, Tag $tag)
    {
        $tag->update($this->validatedData($request, $tag));

        return response()->json(['message' => 'Tag updated successfully.']);
    }

    public function updateStatus(Tag $tag)
    {
        $tag->update(['status' => ! $tag->status]);

        return response()->json(['message' => 'Tag status updated successfully.']);
    }

    public function destroy(Tag $tag)
    {
        $this->deleteImage($tag->image);
        $tag->delete();

        return response()->json(['message' => 'Tag deleted successfully.']);
    }

    private function validatedData(Request $request, ?Tag $tag = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('tags', 'name')->ignore($tag)],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'url_structure' => ['nullable', 'string', 'max:255'],
            'heading_tag' => ['nullable', 'string', 'max:255'],
            'schema_markup' => ['nullable', 'string'],
            'meta_data' => ['nullable', 'string'],
            'meta_robot' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'canonical_tag' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'boolean'],
        ], ['name.unique' => 'This tag name already exists.']);

        $data['slug'] = $this->uniqueSlug($data['name'], $tag);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request->file('image'));
            if ($tag) {
                $this->deleteImage($tag->image);
            }
        }

        return $data;
    }

    private function uniqueSlug(string $name, ?Tag $tag = null): string
    {
        $base = Str::slug($name) ?: 'tag';
        $slug = $base;
        $number = 2;

        while (Tag::where('slug', $slug)->when($tag, fn ($query) => $query->whereKeyNot($tag->id))->exists()) {
            $slug = "{$base}-{$number}";
            $number++;
        }

        return $slug;
    }

    private function storeImage($file): string
    {
        $directory = public_path('admin/tagimage');
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $name = now()->format('YmdHis') . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $name);

        return $name;
    }

    private function deleteImage(?string $image): void
    {
        if ($image && file_exists($path = public_path('admin/tagimage/' . basename($image)))) {
            @unlink($path);
        }
    }
}
