<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

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

    /**
     * Check a module permission for the logged-in admin.
     */
    // public function hasModuleAccess(string $module, string $access = 'view'): bool
    // {
    //     // The super admin must always be able to manage permissions and recover access.
    //     if ($this->type === 'superadmin') {
    //         return true;
    //     }

    //     $role = $this->relationLoaded('roles')
    //         ? $this->roles->firstWhere('module', $module)
    //         : $this->roles()->where('module', $module)->first();

    //     if (! $role || $role->no_access) {
    //         return false;
    //     }

    //     return match ($access) {
    //         'add' => (bool) $role->add_access,
    //         'edit' => (bool) $role->edit_access,
    //         'delete' => (bool) $role->delete_access,
    //         'full' => (bool) ($role->view_access && $role->add_access && $role->edit_access && $role->delete_access),
    //         'view' => (bool) $role->view_access,
    //         default => false,
    //     };
    // }

    public function hasModuleAccess(string $module, string $access = 'view'): bool
    {
        if ($this->type === 'superadmin') {
            return true;
        }
        $role = $this->roles()->where('module', $module)->first();
        if (!$role || $role->no_access == 1) {
            return false;
        }
        if ($access === 'add') {
            return (bool) $role->add_access;
        }

        if ($access === 'edit') {
            return (bool) $role->edit_access;
        }

        if ($access === 'delete') {
            return (bool) $role->delete_access;
        }

        if ($access === 'full') {
            return $role->view_access && $role->add_access && $role->edit_access && $role->delete_access;
        }
        return (bool) $role->view_access;
    }
}
