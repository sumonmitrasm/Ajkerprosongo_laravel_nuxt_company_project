<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

class CategoryController extends Controller
{
    public function category(Request $request)
    {
        $title = 'Category Page';
        $search = trim((string) $request->query('search', ''));
        $categories = Category::select('id', 'parent_id', 'section_id', 'category_name', 'status')
            ->with('section:id,name', 'parentcategory:id,category_name')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('category_name', 'like', "%{$search}%")
                    ->orWhereHas('section', fn ($section) => $section->where('name', 'like', "%{$search}%"));
            }))
            ->latest('id')
            ->cursorPaginate($this->perPage($request))
            ->withQueryString()
            ->through(fn (Category $category) => [
                'id' => $category->id,
                'parent_category_name' => $category->parentcategory?->category_name,
                'section_name' => $category->section?->name,
                'category_name' => $category->category_name,
                'status' => $category->status,
            ]);

        $getSection = Section::select('id', 'name')->orderBy('name')->get();
        $getCategories = Category::with('subcategories')->where('parent_id', 0)->orderBy('category_name')->get();

        return view('admin.category.category', compact('categories', 'title', 'getSection', 'getCategories'));
    }

    public function store(Request $request)
    {
        Category::create($this->validatedData($request));
        $this->clearCategoryCache();

        return response()->json(['message' => 'Category saved successfully.'], 201);
    }

    public function show(Category $category)
    {
        return response()->json([
            'record' => $category,
            'image_url' => $category->image ? asset('admin/categoryimage/' . $category->image) : null,
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validatedData($request, $category));
        $this->clearCategoryCache();

        return response()->json(['message' => 'Category updated successfully.']);
    }

    public function destroy(Category $category)
    {
        if (Category::where('parent_id', $category->id)->exists()) {
            return response()->json(['message' => 'This category has subcategories. Delete or move them first.'], 422);
        }

        $this->deleteOldImage($category->image);
        $category->delete();
        $this->clearCategoryCache();

        return response()->json(['message' => 'Category deleted successfully.']);
    }

    public function updateStatus(Category $category)
    {
        $category->update(['status' => ! $category->status]);
        $this->clearCategoryCache();

        return response()->json(['message' => 'Category status updated successfully.']);
    }

    private function validatedData(Request $request, ?Category $category = null): array
    {
        $data = $request->validate([
            'parent_id' => ['required', 'integer', 'min:0'],
            'section_id' => ['required', 'exists:sections,id'],
            'category_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'position' => ['nullable', 'integer', 'min:0'],
            'url' => ['nullable', 'string', 'max:255'],
            'url_structure' => ['nullable', 'string', 'max:255'],
            'heading_tag' => ['nullable', 'string', 'max:255'],
            'schema_markup' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_data' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'meta_robot' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'boolean'],
        ]);

        if (($data['parent_id'] ?? 0) && ! Category::whereKey($data['parent_id'])->where('section_id', $data['section_id'])->exists()) {
            abort(422, 'The parent category must belong to the selected section.');
        }

        if ($category && (int) $data['parent_id'] === $category->id) {
            abort(422, 'A category cannot be its own parent.');
        }

        if ($category && $data['parent_id'] && $this->isDescendant((int) $data['parent_id'], $category->id)) {
            abort(422, 'A subcategory cannot be selected as this category\'s parent.');
        }

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadImage($request->file('image'));
            if ($category) {
                $this->deleteOldImage($category->image);
            }
        } else {
            unset($data['image']);
        }

        return $data;
    }

    private function uploadImage($file): string
    {
        $destinationPath = public_path('admin/categoryimage');
        if (! file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        $imageName = time() . '_' . Str::random(10) . '.webp';
        $manager = ImageManager::usingDriver(GdDriver::class);
        $image = $manager->decodePath($file->getRealPath());
        $image->scaleDown(width: 1200, height: 1200);
        $image->encodeUsingFormat(Format::WEBP, quality: 75)->save($destinationPath . '/' . $imageName);

        return $imageName;
    }

    private function deleteOldImage(?string $imageName): void
    {
        if ($imageName && file_exists($path = public_path('admin/categoryimage/' . $imageName))) {
            @unlink($path);
        }
    }

    /**
     * Clear all cached category data (Permanent fix)
     */
    private function clearCategoryCache(): void
    {
        Cache::forget('api.sections-with-categories.v1');
        Cache::forget('admin.category-form.sections.v3');
        Cache::forget('admin.category-form.parents.v3');
    }

    private function isArrayList(mixed $value): bool
    {
        return is_array($value) && array_is_list($value) && collect($value)->every(fn ($record) => is_array($record));
    }


    private function isDescendant(int $candidateParentId, int $categoryId): bool
    {
        $currentParentId = $candidateParentId;

        while ($currentParentId) {
            if ($currentParentId === $categoryId) {
                return true;
            }

            $currentParentId = (int) Category::whereKey($currentParentId)->value('parent_id');
        }

        return false;
    }
}
