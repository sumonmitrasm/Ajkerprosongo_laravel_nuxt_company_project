<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'name', 'slug', 'image', 'title', 'description', 'meta_title',
        'meta_description', 'url_structure', 'heading_tag', 'schema_markup',
        'meta_data', 'meta_robot', 'meta_keywords', 'canonical_tag', 'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
