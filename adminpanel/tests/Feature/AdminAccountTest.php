<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AdminLoginActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccountTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $type = 'reporter'): Admin
    {
        return Admin::create([
            'name' => 'Newsroom User',
            'email' => uniqid().'@example.test',
            'password' => 'current-password-123',
            'type' => $type,
            'status' => true,
        ]);
    }

    public function test_admin_can_view_and_update_own_account(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'admin');

        $this->get('/admin/my-account')->assertOk()->assertSee('My Account');
        $this->putJson('/admin/my-account', [
            'name' => 'Updated Byline',
            'mobile' => '01700000000',
            'current_password' => 'current-password-123',
            'password' => 'replacement-password-123',
            'password_confirmation' => 'replacement-password-123',
        ])->assertOk()->assertJsonPath('user.name', 'Updated Byline');

        $admin->refresh();
        $this->assertSame('Updated Byline', $admin->name);
        $this->assertTrue(Hash::check('replacement-password-123', $admin->password));
    }

    public function test_successful_admin_login_is_recorded(): void
    {
        $admin = $this->admin();

        $this->postJson('/admin/login', [
            'email' => $admin->email,
            'password' => 'current-password-123',
        ], ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0) Chrome/140.0'])
            ->assertOk()
            ->assertJsonPath('status', true);

        $activity = AdminLoginActivity::firstOrFail();
        $this->assertSame($admin->id, $activity->admin_id);
        $this->assertSame('Desktop', $activity->device);
        $this->assertSame('Google Chrome', $activity->browser);
        $this->assertSame('Windows', $activity->platform);
    }

    public function test_regular_admin_only_sees_their_own_login_activity(): void
    {
        $admin = $this->admin();
        $other = $this->admin();
        AdminLoginActivity::create(['admin_id' => $admin->id, 'device' => 'Desktop', 'logged_in_at' => now()]);
        AdminLoginActivity::create(['admin_id' => $other->id, 'device' => 'Mobile', 'logged_in_at' => now()]);

        $this->actingAs($admin, 'admin')
            ->get('/admin/login-activity')
            ->assertOk()
            ->assertSee($admin->email)
            ->assertDontSee($other->email);
    }

    public function test_login_activity_cleanup_respects_account_level(): void
    {
        $admin = $this->admin('admin');
        $old = AdminLoginActivity::create([
            'admin_id' => $admin->id, 'device' => 'Desktop', 'logged_in_at' => now()->subDays(100),
        ]);
        $recent = AdminLoginActivity::create([
            'admin_id' => $admin->id, 'device' => 'Desktop', 'logged_in_at' => now(),
        ]);

        $this->actingAs($admin, 'admin')
            ->deleteJson('/admin/login-activity/older')
            ->assertOk();
        $this->assertDatabaseMissing('admin_login_activities', ['id' => $old->id]);
        $this->assertDatabaseHas('admin_login_activities', ['id' => $recent->id]);
        $this->deleteJson('/admin/login-activity')->assertForbidden();

        $superadmin = $this->admin('superadmin');
        $this->actingAs($superadmin, 'admin')
            ->deleteJson('/admin/login-activity')
            ->assertOk();
        $this->assertDatabaseCount('admin_login_activities', 0);
    }
}
