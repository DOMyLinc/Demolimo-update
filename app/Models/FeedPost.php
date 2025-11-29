<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'postable_type',
        'postable_id',
        'content',
        'type',
        'is_pinned',
        'likes_count',
        'comments_count',
        'shares_count',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function postable()
    {
        return $this->morphTo();
    }

    public function likes()
    {
        return $this->hasMany(FeedLike::class);
    }

    public function comments()
    {
        return $this->hasMany(FeedComment::class);
    }

    /**
     * Scopes
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeRecent($query)
    {
        return $query->latest();
    }

    /**
     * Methods
     */
    public function toggleLike($userId)
    {
        $like = $this->likes()->where('user_id', $userId)->first();

        if ($like) {
            $like->delete();
            $this->decrement('likes_count');
            return false;
        } else {
            $this->likes()->create(['user_id' => $userId]);
            $this->increment('likes_count');
            return true;
        }
    }

    public function isLikedBy($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    public static function createFromSongBattle($songBattle, $userId, $content = null)
    {
        return static::create([
            'user_id' => $userId,
            'postable_type' => 'App\Models\SongBattle',
            'postable_id' => $songBattle->id,
            'content' => $content ?? "Check out this epic song battle!",
            'type' => 'share',
        ]);
    }
}

class FeedLike extends Model
{
    use HasFactory;

    protected $fillable = ['feed_post_id', 'user_id'];

    public function post()
    {
        return $this->belongsTo(FeedPost::class, 'feed_post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

class FeedComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'feed_post_id',
        'user_id',
        'comment',
        'likes_count',
    ];

    public function post()
    {
        return $this->belongsTo(FeedPost::class, 'feed_post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
