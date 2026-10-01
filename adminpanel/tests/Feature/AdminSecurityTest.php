<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $type = 'superadmin'): Admin
    {
        return Admin::create([
            'name' => 'Test Admin',
            'email' => uniqid().'@example.test',
            'password' => 'a-long-test-password-for-admin-security',
            'type' => $type,
            'status' => true,
        ]);
    }

    public function test_public_cache_clear_url_is_removed(): void
    {
        Cache::put('security-test', 'keep');
        $this->get('/clear-cache')->assertNotFound();
        $this->assertSame('keep', Cache::get('security-test'));
    }

    public function test_delegated_admin_cannot_escalate_or_grant_permissions(): void
    {
        $actor = $this->admin('admin');
        $actor->roles()->create([
            'module' => 'admin', 'view_access' => 1, 'add_access' => 1,
            'edit_access' => 1, 'delete_access' => 1, 'no_access' => 0,
        ]);
        $target = $this->admin();
        $this->actingAs($actor, 'admin');

        $this->postJson('/admin/users', ['type' => 'superadmin'])->assertForbidden();
        $this->putJson('/admin/users/'.$actor->id, ['type' => 'superadmin'])->assertForbidden();
        $this->putJson('/admin/users/'.$target->id, ['password' => 'replacement-password'])->assertForbidden();
        $this->patchJson('/admin/users/'.$target->id.'/status')->assertForbidden();
        $this->deleteJson('/admin/users/'.$target->id)->assertForbidden();
        $this->postJson('/admin/admin/users/'.$actor->id.'/permission', [])->assertForbidden();
        $this->getJson('/admin/users/'.$target->id)->assertOk();
        $this->assertSame('admin', $actor->fresh()->type);
        $this->assertTrue($target->fresh()->status);
    }

    public function test_superadmin_can_create_and_update_a_regular_account(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $data = [
            'name' => 'Reporter', 'email' => 'reporter@example.test',
            'password' => 'a-long-test-password', 'type' => 'reporter', 'status' => 1,
        ];
        $this->postJson('/admin/users', $data)->assertCreated();
        $target = Admin::where('email', $data['email'])->firstOrFail();
        $data['name'] = 'Updated Reporter';
        $this->putJson('/admin/users/'.$target->id, $data)->assertOk();
        $this->assertSame('Updated Reporter', $target->fresh()->name);
    }

    public function test_disabled_account_is_logged_out_on_its_next_request(): void
    {
        $actor = $this->admin();
        $this->actingAs($actor, 'admin');
        $actor->update(['status' => false]);
        $this->getJson('/admin/users/'.$actor->id)->assertForbidden();
        $this->assertGuest('admin');
    }

    public function test_superadmin_cannot_disable_or_demote_their_own_account(): void
    {
        $actor = $this->admin();
        $this->actingAs($actor, 'admin');
        $this->patchJson('/admin/users/'.$actor->id.'/status')->assertUnprocessable();
        $this->putJson('/admin/users/'.$actor->id, [
            'name' => $actor->name, 'email' => $actor->email,
            'type' => 'reporter', 'status' => 1,
        ])->assertUnprocessable();
        $this->assertSame('superadmin', $actor->fresh()->type);
        $this->assertTrue($actor->fresh()->status);
    }

    public function test_other_module_permissions_still_work(): void
    {
        $actor = $this->admin('reporter');
        $actor->roles()->create([
            'module' => 'post', 'view_access' => 1, 'add_access' => 1,
            'edit_access' => 0, 'delete_access' => 0, 'no_access' => 0,
        ]);
        $this->assertTrue($actor->hasModuleAccess('post', 'add'));
        $this->assertFalse($actor->hasModuleAccess('post', 'edit'));
    }

    public function test_admin_pages_render_with_local_security_assets(): void
    {
        $this->get('/admin/login')->assertOk();
        $this->actingAs($this->admin(), 'admin');
        $response = $this->get('/admin/dashboard')->assertOk()
            ->assertSee('admin/assets/plugins/sweetalert2/sweetalert2.all.min.js')
            ->assertSee('admin/assets/js/editor-security.js')
            ->assertDontSee('cdn.jsdelivr.net');
        preg_match_all('/(?:src|href)="([^"]+)"/', $response->getContent(), $assets);
        foreach (array_unique($assets[1]) as $url) {
            $path = parse_url(html_entity_decode($url), PHP_URL_PATH);
            $position = strpos($path ?? '', 'admin/assets/');
            if ($position !== false) {
                $this->assertFileExists(public_path(substr($path, $position)));
            }
        }
    }

    public function test_long_password_can_be_used_to_log_in(): void
    {
        $actor = $this->admin();
        $this->postJson('/admin/login', [
            'email' => $actor->email,
            'password' => 'a-long-test-password-for-admin-security',
        ])->assertOk()->assertJson(['status' => true]);
        $this->assertAuthenticatedAs($actor, 'admin');
    }

    public function test_delegated_account_management_still_works(): void
    {
        $actor = $this->admin('admin');
        $actor->roles()->create([
            'module' => 'admin', 'view_access' => 1, 'add_access' => 1,
            'edit_access' => 1, 'delete_access' => 1, 'no_access' => 0,
        ]);
        $this->actingAs($actor, 'admin');
        $data = [
            'name' => 'Reporter', 'email' => 'reporter@example.test',
            'password' => 'long-reporter-password', 'type' => 'reporter', 'status' => 1,
        ];
        $this->postJson('/admin/users', $data)->assertCreated();
        $target = Admin::where('email', $data['email'])->firstOrFail();
        $this->get('/admin/users')->assertOk()->assertSee('data-crud-create', false)
            ->assertSee(route('admin-user.update', $target->id), false)
            ->assertDontSee('<option value="superadmin">', false);
        $data['name'] = 'Edited Reporter';
        $this->putJson('/admin/users/'.$target->id, $data)->assertOk();
        $this->patchJson('/admin/users/'.$target->id.'/status')->assertOk();
        $this->assertFalse($target->fresh()->status);
        $this->deleteJson('/admin/users/'.$target->id)->assertOk();
        $this->assertDatabaseMissing('admins', ['id' => $target->id]);
        $this->putJson('/admin/users/'.$actor->id, [
            'name' => 'My Name', 'email' => $actor->email, 'type' => 'admin', 'status' => 1,
        ])->assertOk();
    }

    public function test_delegated_admin_cannot_take_over_more_powerful_accounts(): void
    {
        $actor = $this->admin('admin');
        $actor->roles()->create([
            'module' => 'admin', 'view_access' => 1, 'add_access' => 1,
            'edit_access' => 1, 'delete_access' => 1, 'no_access' => 0,
        ]);
        $target = $this->admin('manager');
        $target->roles()->create([
            'module' => 'post', 'view_access' => 1, 'add_access' => 1,
            'edit_access' => 1, 'delete_access' => 1, 'no_access' => 0,
        ]);
        $this->actingAs($actor, 'admin');
        foreach ([true, false] as $status) {
            $target->update(['status' => $status]);
            $this->putJson('/admin/users/'.$target->id, ['password' => 'new-password'])->assertForbidden();
            $this->patchJson('/admin/users/'.$target->id.'/status')->assertForbidden();
            $this->deleteJson('/admin/users/'.$target->id)->assertForbidden();
        }
        $this->get('/admin/users')->assertOk()
            ->assertDontSee('data-update-url="'.route('admin-user.update', $target->id).'"', false);
    }

    public function test_account_changes_require_the_matching_module_permission(): void
    {
        $this->actingAs($this->admin('reporter'), 'admin');
        $target = $this->admin('reporter');
        $this->postJson('/admin/users', [])->assertForbidden();
        $this->putJson('/admin/users/'.$target->id, [])->assertForbidden();
        $this->patchJson('/admin/users/'.$target->id.'/status')->assertForbidden();
        $this->deleteJson('/admin/users/'.$target->id)->assertForbidden();
    }

    public function test_superadmin_pages_and_permission_controls_are_present(): void
    {
        $actor = $this->admin();
        $target = $this->admin('reporter');
        $this->actingAs($actor, 'admin');
        foreach (['users', 'section', 'category', 'settings', 'tags', 'polls'] as $page) {
            $this->get('/admin/'.$page)->assertOk();
        }
        // Location lookup tables are imported separately in this project.
        // Exercise the cached lookup path without touching the real database.
        Cache::put('admin.post-form-lookups.v1', array_fill_keys([
            'sections', 'categories', 'tags', 'admins', 'reviewers',
            'divisions', 'districts', 'upazilas',
        ], []));
        foreach (['posts', 'posts/mine', 'posts/review-queue'] as $page) {
            $this->get('/admin/'.$page)->assertOk()->assertSee('post-form', false);
        }
        $this->get('/admin/admin/users/'.$target->id.'/permission')->assertOk()
            ->assertSee('permissions[admin][add_access]', false)
            ->assertSee('permissions[admin][edit_access]', false)
            ->assertSee('permissions[admin][delete_access]', false);
        $this->postJson('/admin/admin/users/'.$target->id.'/permission', [
            'permissions' => ['admin' => ['full_access' => 1]],
        ])->assertOk();
        $this->assertTrue($target->fresh()->hasModuleAccess('admin', 'add'));
        $this->assertFalse($target->fresh()->hasModuleAccess('admin', 'full'));
    }
}
