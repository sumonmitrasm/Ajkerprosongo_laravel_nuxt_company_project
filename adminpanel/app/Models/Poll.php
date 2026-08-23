<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Poll extends Model
{
    protected $fillable = [
        'admin_id',
        'title',
        'slug',
        'image',
        'description',
        'poll_type',
        'correct_option_id',
        'result_visibility',
        'allow_comments',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'allow_comments' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class)->orderBy('position');
    }

    public function correctOption(): BelongsTo
    {
        return $this->belongsTo(PollOption::class,'correct_option_id');
    }
    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }
    public function comments(): HasMany
    {
        return $this->hasMany(PollComment::class);
    }
}
