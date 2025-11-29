<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Podcast extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'cover_image',
        'category',
        'tags',
        'language',
        'is_explicit',
        'rss_feed_url',
        'total_episodes',
        'total_plays',
        'subscribers',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_explicit' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function episodes()
    {
        return $this->hasMany(PodcastEpisode::class);
    }

    public function subscribers()
    {
        return $this->belongsToMany(User::class, 'podcast_subscriptions');
    }
}

class PodcastEpisode extends Model
{
    use HasFactory;

    protected $fillable = [
        'podcast_id',
        'title',
        'description',
        'audio_file',
        'duration',
        'file_size',
        'episode_number',
        'season_number',
        'chapters',
        'transcript_file',
        'plays',
        'published_at',
    ];

    protected $casts = [
        'chapters' => 'array',
        'published_at' => 'datetime',
    ];

    public function podcast()
    {
        return $this->belongsTo(Podcast::class);
    }
}

class LiveStream extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'thumbnail',
        'stream_key',
        'stream_url',
        'status',
        'current_viewers',
        'peak_viewers',
        'total_views',
        'enable_chat',
        'enable_donations',
        'scheduled_at',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'enable_chat' => 'boolean',
        'enable_donations' => 'boolean',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(LiveStreamMessage::class, 'stream_id');
    }

    public function start()
    {
        $this->update([
            'status' => 'live',
            'started_at' => now(),
        ]);
    }

    public function end()
    {
        $this->update([
            'status' => 'ended',
            'ended_at' => now(),
        ]);
    }

    public function canStream($user)
    {
        return $user->isPro() && $user->can_livestream;
    }
}

class LiveStreamMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'stream_id',
        'user_id',
        'message',
        'is_pinned',
        'is_deleted',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    public function stream()
    {
        return $this->belongsTo(LiveStream::class, 'stream_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

class FanClub extends Model
{
    use HasFactory;

    protected $fillable = [
        'artist_id',
        'name',
        'description',
        'cover_image',
        'monthly_price',
        'benefits',
        'members_count',
        'is_active',
    ];

    protected $casts = [
        'benefits' => 'array',
        'monthly_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function artist()
    {
        return $this->belongsTo(User::class, 'artist_id');
    }

    public function memberships()
    {
        return $this->hasMany(FanClubMembership::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'fan_club_memberships')
            ->wherePivot('status', 'active');
    }

    public function exclusiveContent()
    {
        return $this->hasMany(ExclusiveContent::class);
    }
}

class FanClubMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'fan_club_id',
        'user_id',
        'status',
        'started_at',
        'expires_at',
        'cancelled_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function fanClub()
    {
        return $this->belongsTo(FanClub::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive()
    {
        return $this->status === 'active' &&
            (!$this->expires_at || $this->expires_at->isFuture());
    }
}

class PresaveCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'releasable_type',
        'releasable_id',
        'title',
        'description',
        'cover_image',
        'release_date',
        'presaves_count',
        'is_active',
    ];

    protected $casts = [
        'release_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function releasable()
    {
        return $this->morphTo();
    }

    public function presaves()
    {
        return $this->belongsToMany(User::class, 'presave_users', 'campaign_id');
    }
}
