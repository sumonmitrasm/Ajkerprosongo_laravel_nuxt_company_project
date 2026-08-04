<?php

namespace App\Http\Controllers;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

abstract class Controller
{
    /** Cache one small page of primitive records, never Eloquent objects. */
    protected function cachedPage(Request $request, string $prefix, Closure $query, Closure $map): LengthAwarePaginator
    {
        $perPage = 25;
        $page = max(1, (int) $request->query('page', 1));
        $version = Cache::get($prefix . '.version', '1');
        $key = $prefix . '.' . $version . '.page.' . $page;
        $payload = Cache::get($key);

        if (! is_array($payload) || ! isset($payload['items'], $payload['total']) || ! is_array($payload['items'])) {
            Cache::forget($key);
            $paginator = $query()->paginate($perPage, ['*'], 'page', $page);
            $payload = [
                'items' => $paginator->getCollection()->map($map)->all(),
                'total' => $paginator->total(),
            ];
            Cache::put($key, $payload, now()->addHours(6));
        }

        return new LengthAwarePaginator($payload['items'], (int) $payload['total'], $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    }

    protected function invalidateCachedPages(string $prefix): void
    {
        Cache::forever($prefix . '.version', (string) Str::uuid());
    }
}
