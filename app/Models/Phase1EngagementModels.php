<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    /**
     * Get setting value
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value
     */
    public static function set($key, $value, $type = 'text', $group = 'general')
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
            ]
        );
    }
}

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'icon',
        'action_url',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Create notification
     */
    public static function createNotification($userId, $type, $title, $message, $data = [], $actionUrl = null)
    {
        return static::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'icon' => static::getIconForType($type),
            'action_url' => $actionUrl,
        ]);
    }

    /**
     * Get icon for notification type
     */
    protected static function getIconForType($type)
    {
        return match ($type) {
            'new_follower' => '👤',
            'new_comment' => '💬',
            'new_like' => '❤️',
            'track_uploaded' => '🎵',
            'new_message' => '✉️',
            'event_reminder' => '📅',
            'payment_received' => '💰',
            'subscription_renewed' => '🔄',
            default => '🔔',
        };
    }
}

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_new_follower',
        'email_new_comment',
        'email_new_like',
        'email_track_uploaded',
        'email_new_message',
        'email_event_reminder',
        'email_weekly_digest',
        'push_new_follower',
        'push_new_comment',
        'push_new_like',
        'push_new_message',
        'app_new_follower',
        'app_new_comment',
        'app_new_like',
        'app_new_message',
    ];

    protected $casts = [
        'email_new_follower' => 'boolean',
        'email_new_comment' => 'boolean',
        'email_new_like' => 'boolean',
        'email_track_uploaded' => 'boolean',
        'email_new_message' => 'boolean',
        'email_event_reminder' => 'boolean',
        'email_weekly_digest' => 'boolean',
        'push_new_follower' => 'boolean',
        'push_new_comment' => 'boolean',
        'push_new_like' => 'boolean',
        'push_new_message' => 'boolean',
        'app_new_follower' => 'boolean',
        'app_new_comment' => 'boolean',
        'app_new_like' => 'boolean',
        'app_new_message' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

class UserPrivacySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_visibility',
        'show_email',
        'show_listening_activity',
        'show_playlists',
        'show_followers',
        'hide_explicit_content',
        'safe_mode',
        'blocked_users',
        'muted_words',
        'allow_personalized_ads',
        'allow_analytics',
        'allow_third_party_sharing',
    ];

    protected $casts = [
        'show_email' => 'boolean',
        'show_listening_activity' => 'boolean',
        'show_playlists' => 'boolean',
        'show_followers' => 'boolean',
        'hide_explicit_content' => 'boolean',
        'safe_mode' => 'boolean',
        'blocked_users' => 'array',
        'muted_words' => 'array',
        'allow_personalized_ads' => 'boolean',
        'allow_analytics' => 'boolean',
        'allow_third_party_sharing' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'referred_id',
        'referral_code',
        'email',
        'status',
        'reward_amount',
        'reward_claimed',
        'registered_at',
        'completed_at',
    ];

    protected $casts = [
        'reward_amount' => 'decimal:2',
        'reward_claimed' => 'boolean',
        'registered_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    public static function generateCode()
    {
        return strtoupper(\Illuminate\Support\Str::random(8));
    }
}

class SocialAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
