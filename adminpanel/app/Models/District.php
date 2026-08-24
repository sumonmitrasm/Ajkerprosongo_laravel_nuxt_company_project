<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    /**
     * Fields that can be saved through create() or update().
     */
    protected $fillable = [
        'division_id',
        'name',
        'bn_name',
        'lat',
        'lon',
        'url',
    ];

    /**
     * A district belongs to one division.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * A district contains many upazilas.
     */
    public function upazilas(): HasMany
    {
        return $this->hasMany(Upazila::class);
    }
}