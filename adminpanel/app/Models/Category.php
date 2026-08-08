<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'section_id',
        'category_name',
        'description',
        'image',
        'position',
        'url',
        'url_structure',
        'heading_tag',
        'schema_markup',
        'meta_title',
        'meta_data',
        'meta_description',
        'meta_keywords',
        'meta_robot',
        'status',
    ];
    protected $casts = [
        'status' => 'boolean',
        'parent_id' => 'integer',
        'section_id' => 'integer',
        'position' => 'integer',
    ];
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id')->select('id','name');
    }
    public function parentcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id')->withDefault(['category_name' => 'Root / Main Category']);
    }
    public function subcategories(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->where('status', 1)
            ->orderBy('position')
            ->orderBy('category_name');
    }
    // public static function categories()
    // {
    //     $getCategory = Category::with('subcategories')->where(['parent_id'=>0,'status'=>1])->orderBy('position')->get()->toArray();
    //     return $getCategory;
    // }
}
