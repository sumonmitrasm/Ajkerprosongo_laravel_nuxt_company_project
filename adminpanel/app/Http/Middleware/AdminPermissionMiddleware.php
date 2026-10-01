<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminPermissionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $permission = $this->permissionForRoute($request->route()?->getName());

        if (! $permission) {
            return $next($request);
        }

        [$module, $access] = $permission;
        $admin = Auth::guard('admin')->user();

        if (! ($admin instanceof Admin) || ! $admin->hasModuleAccess($module, $access)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        $target = $request->route('user');

        if ($module === 'admin' && $access !== 'view'
            && $target instanceof Admin && ! $admin->canManageAccount($target)) {
            abort(403, 'You cannot change this account.');
        }

        return $next($request);
    }

    private function permissionForRoute(?string $routeName): ?array
    {
        return match ($routeName) {
            'admin-user', 'admin-user.show' => ['admin', 'view'],
            'admin-user.store' => ['admin', 'add'],
            'admin-user.update', 'admin-user.status' => ['admin', 'edit'],
            'admin-user.delete' => ['admin', 'delete'],
            'admin-user.permission', 'admin-user.permission.update' => ['admin', 'full'],

            'section', 'admin-section.show' => ['section', 'view'],
            'admin-section.store' => ['section', 'add'],
            'admin-section.update', 'admin-section.status' => ['section', 'edit'],
            'admin-section.delete' => ['section', 'delete'],

            'category', 'admin-category.show' => ['category', 'view'],
            'admin-category.store' => ['category', 'add'],
            'admin-category.update', 'admin-category.status' => ['category', 'edit'],
            'admin-category.delete' => ['category', 'delete'],

            'settings', 'admin-setting.show' => ['setting', 'view'],
            'admin-setting.store' => ['setting', 'add'],
            'admin-setting.update', 'admin-setting.status' => ['setting', 'edit'],
            'admin-setting.delete' => ['setting', 'delete'],

            'tags', 'admin-tag.show' => ['tag', 'view'],
            'admin-tag.store' => ['tag', 'add'],
            'admin-tag.update', 'admin-tag.status' => ['tag', 'edit'],
            'admin-tag.delete' => ['tag', 'delete'],

            // Protect every poll action even when a user enters the URL directly.
            'polls', 'admin-poll.show' => ['poll', 'view'],
            'admin-poll.store' => ['poll', 'add'],
            'admin-poll.update', 'admin-poll.status' => ['poll', 'edit'],
            'admin-poll.delete' => ['poll', 'delete'],

            // Protect every news post action and its editorial workflow.
            'posts', 'posts.mine', 'posts.review', 'admin-post.show' => ['post', 'view'],
            'admin-post.store' => ['post', 'add'],
            'admin-post.update', 'admin-post.status' => ['post', 'edit'],
            'admin-post.delete' => ['post', 'delete'],
            default => null,
        };
    }
}
