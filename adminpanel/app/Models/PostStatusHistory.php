<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostStatusHistory extends Model
{
    public $timestamps=false;
    protected $fillable=['post_id','changed_by','from_status','to_status','note','created_at'];
    protected $casts=['created_at'=>'datetime'];
    public function post(): BelongsTo { return $this->belongsTo(Post::class); }
    public function changedBy(): BelongsTo { return $this->belongsTo(Admin::class,'changed_by'); }
}