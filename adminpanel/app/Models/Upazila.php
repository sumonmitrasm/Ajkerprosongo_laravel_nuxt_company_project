<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Upazila extends Model
{
    /**
     * Fields that can be saved through create() or update().
     */
    protected $fillable = [
        'district_id',
        'name',
        'bn_name',
        'url',
    ];

    /**
     * An upazila belongs to one district.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}