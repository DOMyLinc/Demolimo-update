<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'commentable_type',
        'commentable_id',
        'parent_id',
        'content',
        'likes',
        'is_pinned',
        'is_edited',
        'edited_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function reactions()
    {
        return $this->hasMany(CommentReaction::class);
    }

    /**
     * Update reaction counts
     */
    public function updateReactionCounts()
    {
        $this->update([
            'reactions_count' => $this->reactions()->count(),
        ]);
    }

    /**
     * Update replies count
     */
    public function updateRepliesCount()
    {
        $this->update([
            'replies_count' => $this->replies()->count(),
        ]);
    }

    /**
     * Check if user has reacted to this comment
     */
    public function hasReaction($userId, $reactionType = null)
    {
        $query = $this->reactions()->where('user_id', $userId);

        if ($reactionType) {
            $query->where('reaction_type', $reactionType);
        }

        return $query->exists();
    }

    /**
     * Get user's reaction to this comment
     */
    public function getUserReaction($userId)
    {
        return $this->reactions()->where('user_id', $userId)->first();
    }
}
