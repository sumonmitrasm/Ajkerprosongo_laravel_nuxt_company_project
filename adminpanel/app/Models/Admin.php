<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property int $id
 * @property int|null $ap_id
 * @property string|null $name
 * @property string $email
 * @property string|null $image
 * @property string|null $mobile
 * @property string|null $type
 * @property bool $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AdminRole> $roles
 */
class Admin extends Authenticatable
{
    use HasFactory;

    protected $guard = 'admin';

    protected $fillable = ['ap_id', 'image', 'name', 'type', 'mobile', 'email', 'password', 'status'];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'status' => 'boolean'];
    }

    public function roles()
    {
        return $this->hasMany(AdminRole::class, 'admin_id');
    }

    public function hasModuleAccess(string $module, string $access = 'view'): bool
    {
        if (! $this->status) {
            return false;
        }

        // Superadmins always retain recovery access to every admin module.
        if ($this->type === 'superadmin') {
            return true;
        }

        // Granting permissions remains restricted to superadmins.
        if ($module === 'admin' && $access === 'full') {
            return false;
        }

        $this->loadMissing('roles');
        $role = $this->roles->firstWhere('module', $module);

        if (! $role || $role->no_access) {
            return false;
        }

        // Map each route action to its matching permission column.
        return match ($access) {
            'add' => (bool) $role->add_access,
            'edit' => (bool) $role->edit_access,
            'delete' => (bool) $role->delete_access,
            'full' => (bool) ($role->view_access && $role->add_access && $role->edit_access && $role->delete_access),
            'view' => (bool) $role->view_access,
            default => false,
        };
    }

    // Can this admin change the target account without gaining more permissions?
    public function canManageAccount(Admin $target): bool
    {
        if (! $this->status) {
            return false;
        }
        if ($this->type === 'superadmin') {
            return true;
        }
        if ($target->type === 'superadmin') {
            return false;
        }

        $this->loadMissing('roles');
        $target->loadMissing('roles');

        // Password/status changes must not give access to a more powerful account.
        // Check stored permissions even when the target account is disabled.
        foreach ($target->roles as $role) {
            if ($role->no_access) {
                continue;
            }
            if (! $role->view_access && ! $role->add_access && ! $role->edit_access && ! $role->delete_access) {
                continue;
            }

            $myRole = $this->roles->firstWhere('module', $role->module);
            if (! $myRole || $myRole->no_access) {
                return false;
            }

            if (($role->view_access && ! $myRole->view_access)
                || ($role->add_access && ! $myRole->add_access)
                || ($role->edit_access && ! $myRole->edit_access)
                || ($role->delete_access && ! $myRole->delete_access)) {
                return false;
            }
        }

        return true;
    }
}
