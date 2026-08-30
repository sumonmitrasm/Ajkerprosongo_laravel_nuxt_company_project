<?php

namespace App\Support;

use App\Models\{Admin, Category, District, Division, Section, Tag, Upazila};
use Illuminate\Support\Facades\Cache;
use Throwable;

final class PostFormLookups
{
    private const CACHE_KEY = 'admin.post-form-lookups.v1';

    /**
     * Return the reusable Post editor options from one cache record.
     * Database fallback keeps the newsroom available if the cache store fails.
     */
    public static function get(): array
    {
        try {
            $payload = Cache::remember(
                self::CACHE_KEY,
                now()->addHours(6),
                fn () => self::payload(),
            );
        } catch (Throwable $exception) {
            report($exception);
            $payload = self::payload();
        }

        return collect($payload)->map(
            fn (array $records) => collect($records)->map(fn (array $record) => (object) $record),
        )->all();
    }

    /** Remove stale options after a related newsroom record changes. */
    public static function forget(): void
    {
        try {
            Cache::forget(self::CACHE_KEY);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private static function payload(): array
    {
        return [
            'sections' => Section::where('status', true)->orderBy('name')->get(['id', 'name'])->toArray(),
            'categories' => Category::where('status', true)->orderBy('category_name')
                ->get(['id', 'section_id', 'category_name'])->toArray(),
            'tags' => Tag::where('status', true)->orderBy('name')->get(['id', 'name'])->toArray(),
            'admins' => Admin::where('status', true)->orderBy('name')
                ->get(['id', 'name', 'type', 'rank', 'position'])->toArray(),
            'reviewers' => Admin::where('status', true)
                ->where(fn ($query) => $query->where('type', 'superadmin')
                    ->orWhereHas('roles', fn ($role) => $role->where('module', 'post')
                        ->where('edit_access', true)->where('no_access', false)))
                ->orderBy('name')->get(['id', 'name', 'type', 'rank', 'position'])->toArray(),
            'divisions' => Division::orderBy('name')->get(['id', 'name', 'bn_name'])->toArray(),
            'districts' => District::orderBy('name')->get(['id', 'division_id', 'name', 'bn_name'])->toArray(),
            'upazilas' => Upazila::orderBy('name')->get(['id', 'district_id', 'name', 'bn_name'])->toArray(),
        ];
    }
}
