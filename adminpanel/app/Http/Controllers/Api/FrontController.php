<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Support\Facades\Cache;
use Throwable;

class FrontController extends Controller
{
    public function index()
    {
        //
    }

    /**
     * Active sections with their active categories, ready for nested Nuxt loops.
     */
    public function sections()
    {
        try {
            $sections = Cache::remember(
                'api.sections-with-categories.v1',
                now()->addHours(6),
                fn () => $this->sectionPayload(),
            );
        } catch (Throwable $exception) {
            // A failed cache service must never take down the public API.
            report($exception);
            $sections = $this->sectionPayload();
        }

        return response()
            ->json(['data' => $sections])
            ->header('Cache-Control', 'public, max-age=300, stale-while-revalidate=60');
    }

    private function sectionPayload()
    {
        return Section::query()
                ->select('id', 'name')
                ->where('status', true)
                ->with([
                    'categories:id,section_id,parent_id,category_name,url,image,position',
                    'categories.subcategories:id,section_id,parent_id,category_name,url,image,position',
                ])
                ->orderBy('name')
                ->get()
                ->map(fn (Section $section) => [
                    'id' => $section->id,
                    'name' => $section->name,
                    'categories' => $section->categories->map(fn ($category) => [
                        'id' => $category->id,
                        'name' => $category->category_name,
                        'url' => $category->url,
                        'image' => $category->image ? asset('admin/categoryimage/' . $category->image) : null,
                        'subcategories' => $category->subcategories->map(fn ($subcategory) => [
                            'id' => $subcategory->id,
                            'name' => $subcategory->category_name,
                            'url' => $subcategory->url,
                            'image' => $subcategory->image ? asset('admin/categoryimage/' . $subcategory->image) : null,
                    ])->values()->all(),
                ])->values()->all(),
            ])->values()->all();
    }

}
