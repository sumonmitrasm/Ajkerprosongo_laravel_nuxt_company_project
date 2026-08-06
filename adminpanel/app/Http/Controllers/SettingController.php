<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
class SettingController extends Controller
{
    public function settings(Request $request)
    {
        $title = 'Setting Page';
        $search = trim((string) $request->query('search', ''));
        $getSettings = Setting::select('id', 'side_name', 'email', 'phone', 'perronal_phone', 'status')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('side_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('perronal_phone', 'like', "%{$search}%");
            }))
            ->latest('id')
            ->cursorPaginate($this->perPage($request))
            ->withQueryString()
            ->through(fn (Setting $setting) => $setting->only(['id', 'side_name', 'email', 'phone', 'perronal_phone', 'status']));
        return view('admin.setting.setting', compact('getSettings', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Setting::create($this->validatedData($request));
        $this->clearSettingCache();
        return response()->json(['message' => 'Setting saved successfully.'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Setting $setting)
    {
        return response()->json([
            'record' => $setting,
            'image_url' => $this->imageUrl($setting->image),
            'favicon_url' => $this->imageUrl($setting->favicon),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Setting $setting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Setting $setting)
    {
        $setting->update($this->validatedData($request, $setting));
        $this->clearSettingCache();
        return response()->json(['message' => 'Setting updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Setting $setting)
    {
        $this->deleteImage($setting->image);
        $this->deleteImage($setting->favicon);
        $setting->delete();
        $this->clearSettingCache();
        return response()->json(['message' => 'Setting deleted successfully.']);
    }

    public function updateStatus(Request $request, Setting $setting){
        $setting->update(['status' => ! $setting->status]);
        $this->clearSettingCache();
        return response()->json(['message' => 'Setting status updated successfully.']);
    }

    private function validatedData(Request $request, ?Setting $setting = null): array
    {
        $data = $request->validate([
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp,ico', 'max:1024'],
            'perronal_phone' => ['required', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'side_name' => ['required', 'string', 'max:255'],
            'developed_year' => ['required', 'string', 'max:20'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
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
        ]);

        foreach (['image', 'favicon'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $this->storeImage($request->file($field), $field);
                if ($setting) $this->deleteImage($setting->{$field});
            }
        }
        return $data;
    }

    private function storeImage($file, string $prefix): string
    {
        $directory = public_path('admin/site_settings');
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $name = $prefix . '_' . now()->format('YmdHis') . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $name);
        return $name;
    }

    private function deleteImage(?string $path): void
    {
        if ($path && file_exists($file = public_path('admin/site_settings/' . basename($path)))) {
            @unlink($file);
        }
    }

    private function imageUrl(?string $path): ?string
    {
        return $path ? asset('admin/site_settings/' . basename($path)) : null;
    }

    private function clearSettingCache(): void
    {
        Cache::forget('general_setting');
    }

}
