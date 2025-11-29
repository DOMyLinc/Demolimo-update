<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'content',
        'media_type',
        'media_url',
        'share_token',
        'likes_count',
        'reactions_count',
        'shares_count',
        'comments_count',
        'views_count',
        'reaction_summary',
        'is_pinned',
        'visibility',
    ];

    protected $casts = [
        'reaction_summary' => 'array',
        'is_pinned' => 'boolean',
    ];

    /**
     * Get the user that owns the post
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all reactions for the post
     */
    public function reactions()
    {
        return $this->hasMany(PostReaction::class);
    }

    /**
     * Get all comments for the post
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'commentable_id')
            ->where('commentable_type', self::class)
            ->whereNull('parent_id');
    }

    /**
     * Get all shares for the post
     */
    public function shares()
    {
        return $this->hasMany(PostShare::class);
    }

    /**
     * Get all views for the post
     */
    public function views()
    {
        return $this->hasMany(PostView::class);
    }

    /**
     * Update reaction counts and summary
     */
    public function updateReactionCounts()
    {
        $reactions = $this->reactions()
            ->selectRaw('reaction_type, COUNT(*) as count')
            ->groupBy('reaction_type')
            ->pluck('count', 'reaction_type')
            ->toArray();

        $this->update([
            'reactions_count' => array_sum($reactions),
            'reaction_summary' => $reactions,
        ]);
    }

    /**
     * Update comments count
     */
    public function updateCommentsCount()
    {
        $this->update([
            'comments_count' => $this->comments()->count(),
        ]);
    }

    /**
     * Update shares count
     */
    public function updateSharesCount()
    {
        $this->update([
            'shares_count' => $this->shares()->count(),
        ]);
    }

    /**
     * Update views count
     */
    public function updateViewsCount()
    {
        $this->update([
            'views_count' => $this->views()->count(),
        ]);
    }

    /**
     * Check if user has reacted to this post
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
     * Get user's reaction to this post
     */
    public function getUserReaction($userId)
    {
        return $this->reactions()->where('user_id', $userId)->first();
    }

    /**
     * Scope for public posts
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    /**
     * Scope for pinned posts
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }
}
