<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $fillable = ['name', 'status'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    /** Active root categories belonging to this section. */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class)
            ->where('parent_id', 0)
            ->where('status', true)
            ->orderBy('position')
            ->orderBy('category_name');
    }
}
