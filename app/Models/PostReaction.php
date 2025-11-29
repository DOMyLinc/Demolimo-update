<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostReaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'reaction_type',
    ];

    const REACTION_TYPES = [
        'like' => '👍',
        'love' => '❤️',
        'haha' => '😂',
        'wow' => '😮',
        'sad' => '😢',
        'angry' => '😠',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getEmoji($type)
    {
        return self::REACTION_TYPES[$type] ?? '👍';
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($reaction) {
            $reaction->post->updateReactionCounts();
            $reaction->notifyPostOwner();
        });

        static::updated(function ($reaction) {
            $reaction->post->updateReactionCounts();
        });

        static::deleted(function ($reaction) {
            $reaction->post->updateReactionCounts();
        });
    }

    protected function notifyPostOwner()
    {
        if ($this->post->user_id !== $this->user_id) {
            ReactionNotification::create([
                'user_id' => $this->post->user_id,
                'reactor_id' => $this->user_id,
                'reactable_type' => Post::class,
                'reactable_id' => $this->post_id,
                'reaction_type' => $this->reaction_type,
            ]);
        }
    }
}
