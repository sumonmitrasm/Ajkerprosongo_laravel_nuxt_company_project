<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $module
 * @property int $view_access
 * @property int $add_access
 * @property int $edit_access
 * @property int $delete_access
 * @property int $no_access
 */
class AdminRole extends Model
{
    protected $table = 'admin_roles';

    protected $fillable = [
        'admin_id',
        'module',
        'view_access',
        'edit_access',
        'add_access',
        'delete_access',
        'no_access',
    ];
}
