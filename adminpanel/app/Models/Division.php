<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    /**
     * Fields that can be saved through create() or update().
     */
    protected $fillable = [
        'name',
        'bn_name',
        'url',
    ];

    /**
     * A division contains many districts.
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }
}