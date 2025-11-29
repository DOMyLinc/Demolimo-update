<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Podcast extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'cover_image',
        'category',
        'is_explicit',
        'language',
        'author_name',
        'author_email',
        'website_url',
        'rss_feed_url',
        'social_links',
        'is_active',
        'requires_approval',
        'approved_at',
        'total_episodes',
        'total_plays',
        'subscribers_count',
    ];

    protected $casts = [
        'is_explicit' => 'boolean',
        'is_active' => 'boolean',
        'requires_approval' => 'boolean',
        'approved_at' => 'datetime',
        'social_links' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($podcast) {
            if (empty($podcast->slug)) {
                $podcast->slug = Str::slug($podcast->title);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(PodcastEpisode::class)->orderBy('published_at', 'desc');
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'podcast_subscribers')
            ->withTimestamps();
    }

    public function isSubscribedBy(User $user): bool
    {
        return $this->subscribers()->where('user_id', $user->id)->exists();
    }

    public function generateRSSFeed(): string
    {
        return route('podcast.rss', $this->slug);
    }
}
