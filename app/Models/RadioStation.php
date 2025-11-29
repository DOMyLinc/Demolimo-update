<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RadioStation extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'cover_image',
        'type',
        'is_active',
        'is_featured',
        'is_user_created',
        'requires_approval',
        'approved_at',
        'genre',
        'mood',
        'shuffle_interval',
        'stream_url',
        'stream_type',
        'bitrate',
        'social_links',
        'website_url',
        'embed_code',
        'dj_name',
        'dj_avatar',
        'dj_bio',
        'listeners_count',
        'total_plays',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_user_created' => 'boolean',
        'requires_approval' => 'boolean',
        'approved_at' => 'datetime',
        'social_links' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function playlist(): HasMany
    {
        return $this->hasMany(RadioPlaylist::class)->orderBy('position');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(RadioSchedule::class);
    }

    public function listeners(): HasMany
    {
        return $this->hasMany(RadioListener::class);
    }

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'radio_playlists')
            ->withPivot('position', 'play_count', 'last_played_at')
            ->orderBy('position');
    }

    /**
     * Get current playing track
     */
    public function getCurrentTrack()
    {
        if ($this->type === 'auto') {
            return $this->getAutoDJTrack();
        }

        return null;
    }

    /**
     * Auto-DJ logic to get next track
     */
    protected function getAutoDJTrack()
    {
        $playlist = $this->playlist()->with('track')->get();

        if ($playlist->isEmpty()) {
            return null;
        }

        // Find least recently played track
        $nextTrack = $playlist->sortBy('last_played_at')->first();

        // Update play count and last played time
        $nextTrack->update([
            'play_count' => $nextTrack->play_count + 1,
            'last_played_at' => now(),
        ]);

        return $nextTrack->track;
    }

    /**
     * Increment listeners count
     */
    public function incrementListeners()
    {
        $this->increment('listeners_count');
    }

    /**
     * Decrement listeners count
     */
    public function decrementListeners()
    {
        $this->decrement('listeners_count');
    }

    /**
     * Get active listeners
     */
    public function getActiveListeners()
    {
        return $this->listeners()
            ->whereNull('disconnected_at')
            ->where('connected_at', '>', now()->subMinutes(5))
            ->count();
    }

    /**
     * Generate embed code
     */
    public function generateEmbedCode()
    {
        $url = route('radio.embed', $this->slug);
        return '<iframe src="' . $url . '" width="100%" height="400" frameborder="0" allowfullscreen></iframe>';
    }
}
