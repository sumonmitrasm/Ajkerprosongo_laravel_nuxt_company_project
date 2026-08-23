<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollComment extends Model
{
    protected $fillable = [
        'poll_id',
        'user_id',
        'visitor_name',
        'comment',
        'status',
        'ip_hash',
    ];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
