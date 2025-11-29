<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PodcastEpisode extends Model
{
    protected $fillable = [
        'podcast_id',
        'title',
        'slug',
        'description',
        'cover_image',
        'audio_file',
        'duration',
        'file_size',
        'episode_number',
        'season_number',
        'is_explicit',
        'published_at',
        'play_count',
        'download_count',
    ];

    protected $casts = [
        'is_explicit' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($episode) {
            if (empty($episode->slug)) {
                $episode->slug = Str::slug($episode->title);
            }
        });
    }

    public function podcast(): BelongsTo
    {
        return $this->belongsTo(Podcast::class);
    }

    public function incrementPlayCount()
    {
        $this->increment('play_count');
        $this->podcast->increment('total_plays');
    }

    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }
}
