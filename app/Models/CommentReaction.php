<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentReaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'comment_id',
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

    public function comment()
    {
        return $this->belongsTo(Comment::class);
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
            $reaction->comment->updateReactionCounts();
            $reaction->notifyCommentOwner();
        });

        static::updated(function ($reaction) {
            $reaction->comment->updateReactionCounts();
        });

        static::deleted(function ($reaction) {
            $reaction->comment->updateReactionCounts();
        });
    }

    protected function notifyCommentOwner()
    {
        if ($this->comment->user_id !== $this->user_id) {
            ReactionNotification::create([
                'user_id' => $this->comment->user_id,
                'reactor_id' => $this->user_id,
                'reactable_type' => Comment::class,
                'reactable_id' => $this->comment_id,
                'reaction_type' => $this->reaction_type,
            ]);
        }
    }
}
