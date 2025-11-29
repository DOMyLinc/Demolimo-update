<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTasteProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'favorite_genres',
        'favorite_artists',
        'listening_patterns',
        'mood_preferences',
        'diversity_score',
        'last_calculated_at',
    ];

    protected $casts = [
        'favorite_genres' => 'array',
        'favorite_artists' => 'array',
        'listening_patterns' => 'array',
        'mood_preferences' => 'array',
        'diversity_score' => 'decimal:2',
        'last_calculated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

class Recommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'track_ids',
        'algorithm',
        'confidence_score',
        'generated_at',
        'expires_at',
        'plays_count',
        'likes_count',
    ];

    protected $casts = [
        'track_ids' => 'array',
        'confidence_score' => 'decimal:2',
        'generated_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get tracks
     */
    public function getTracks()
    {
        return Track::whereIn('id', $this->track_ids ?? [])->get();
    }

    /**
     * Check if expired
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}

class PlaylistCollaborator extends Model
{
    use HasFactory;

    protected $fillable = [
        'playlist_id',
        'user_id',
        'role',
        'can_add',
        'can_remove',
        'can_reorder',
        'invited_at',
        'accepted_at',
    ];

    protected $casts = [
        'can_add' => 'boolean',
        'can_remove' => 'boolean',
        'can_reorder' => 'boolean',
        'invited_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function playlist()
    {
        return $this->belongsTo(Playlist::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accept invitation
     */
    public function accept()
    {
        $this->update(['accepted_at' => now()]);
    }

    /**
     * Check permissions
     */
    public function canAdd()
    {
        return $this->can_add && $this->accepted_at;
    }

    public function canRemove()
    {
        return $this->can_remove && $this->accepted_at;
    }

    public function canReorder()
    {
        return $this->can_reorder && $this->accepted_at;
    }
}

class DirectMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'message',
        'attachable_type',
        'attachable_id',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function attachable()
    {
        return $this->morphTo();
    }

    /**
     * Mark as read
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Check if user can send message (Pro feature for unlimited)
     */
    public static function canSendMessage($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return false;
        }

        // Pro users have unlimited messaging
        if ($user->isPro()) {
            return true;
        }

        // Free users limited to 10 messages per day
        $todayCount = static::where('sender_id', $userId)
            ->whereDate('created_at', today())
            ->count();

        return $todayCount < 10;
    }
}
