<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminLoginActivity extends Model
{
    protected $fillable = [
        'admin_id',
        'ip_address',
        'device',
        'browser',
        'platform',
        'user_agent',
        'logged_in_at',
    ];

    protected function casts(): array
    {
        return ['logged_in_at' => 'datetime'];
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
