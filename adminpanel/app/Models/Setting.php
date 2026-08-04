<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Setting extends Model
{
    use HasFactory;
    protected $table = 'settings';
    protected $fillable = [
    'logo', 'favicon', 'perronal_phone', 'phone', 'email', 'address',
    'description', 'side_name', 'developed_year', 'facebook_url',
    'twitter_url', 'linkedin_url', 'instagram_url','youtube_url','meta_title',
    'meta_description', 'meta_image', 'url_structure', 'heading_tag',
    'schema_markup', 'meta_data', 'meta_robot', 'meta_keywords',
    'image', 'canonical_tag', 'status'
    ];
}
