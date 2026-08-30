<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostRevision extends Model
{
    public $timestamps=false;
    protected $fillable=['post_id','editor_id','version','title','special_title','summary','description','featured_image','image_caption','change_note','created_at'];
    protected $casts=['created_at'=>'datetime'];
    public function post(): BelongsTo { return $this->belongsTo(Post::class); }
    public function editor(): BelongsTo { return $this->belongsTo(Admin::class,'editor_id'); }
}