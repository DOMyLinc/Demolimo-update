<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReactionNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reactor_id',
        'reactable_type',
        'reactable_id',
        'reaction_type',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reactor()
    {
        return $this->belongsTo(User::class, 'reactor_id');
    }

    public function reactable()
    {
        return $this->morphTo();
    }

    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function getMessageAttribute()
    {
        $emoji = PostReaction::getEmoji($this->reaction_type);
        $type = $this->reactable_type === Post::class ? 'post' : 'comment';

        return "{$this->reactor->name} reacted {$emoji} to your {$type}";
    }
}
