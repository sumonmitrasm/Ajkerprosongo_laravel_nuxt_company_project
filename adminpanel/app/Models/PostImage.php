<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostImage extends Model
{
    protected $fillable=['post_id','image','caption','alt_text','source','photographer','position','is_featured'];
    protected $casts=['position'=>'integer','is_featured'=>'boolean'];
    public function post(): BelongsTo { return $this->belongsTo(Post::class); }
}