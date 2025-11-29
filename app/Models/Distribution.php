<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distribution extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_DISTRIBUTED = 'distributed';
    const STATUS_FAILED = 'failed';

    // Platform constants
    const PLATFORM_SPOTIFY = 'spotify';
    const PLATFORM_APPLE_MUSIC = 'apple_music';
    const PLATFORM_YOUTUBE_MUSIC = 'youtube_music';
    const PLATFORM_AMAZON_MUSIC = 'amazon_music';
    const PLATFORM_TIDAL = 'tidal';
    const PLATFORM_DEEZER = 'deezer';
    const PLATFORM_SOUNDCLOUD = 'soundcloud';
    const PLATFORM_BANDCAMP = 'bandcamp';

    protected $fillable = [
        'user_id',
        'track_id',
        'album_id',
        'platform', // spotify, apple_music, etc.
        'status', // pending, approved, rejected, distributed
        'release_date',
        'upc',
        'isrc',
        'earnings',
    ];

    protected $casts = [
        'release_date' => 'date',
        'earnings' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isDistributed()
    {
        return $this->status === self::STATUS_DISTRIBUTED;
    }

    public function getContentTitleAttribute()
    {
        if ($this->track) {
            return $this->track->title;
        }
        if ($this->album) {
            return $this->album->title;
        }
        return 'Unknown';
    }

    public function getContentTypeAttribute()
    {
        return $this->track_id ? 'Track' : 'Album';
    }

    public function getPlatformNameAttribute()
    {
        $platforms = [
            self::PLATFORM_SPOTIFY => 'Spotify',
            self::PLATFORM_APPLE_MUSIC => 'Apple Music',
            self::PLATFORM_YOUTUBE_MUSIC => 'YouTube Music',
            self::PLATFORM_AMAZON_MUSIC => 'Amazon Music',
            self::PLATFORM_TIDAL => 'Tidal',
            self::PLATFORM_DEEZER => 'Deezer',
            self::PLATFORM_SOUNDCLOUD => 'SoundCloud',
            self::PLATFORM_BANDCAMP => 'Bandcamp',
        ];

        return $platforms[$this->platform] ?? ucfirst(str_replace('_', ' ', $this->platform));
    }

    public function getStatusBadgeColorAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'info',
            self::STATUS_DISTRIBUTED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_FAILED => 'danger',
            default => 'secondary',
        };
    }
}
