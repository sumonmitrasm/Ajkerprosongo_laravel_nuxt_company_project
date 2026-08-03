<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
