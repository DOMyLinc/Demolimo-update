<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Boost extends Model
{
    protected $fillable = [
        'user_id',
        'boostable_id',
        'boostable_type',
        'package',
        'budget',
        'cost',
        'target_views',
        'current_views',
        'target_impressions',
        'current_impressions',
        'status',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function boostable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    public function scopeForFlashAlbums($query)
    {
        return $query->where('boostable_type', FlashAlbum::class);
    }

    public function scopeForTracks($query)
    {
        return $query->where('boostable_type', Track::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Methods
     */
    public function incrementViews(): void
    {
        $this->increment('current_views');
    }

    public function incrementImpressions(): void
    {
        $this->increment('current_impressions');
    }

    public function isExpired(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    public function getRemainingDays(): int
    {
        if (!$this->ends_at) {
            return 0;
        }

        return max(0, now()->diffInDays($this->ends_at, false));
    }

    public function getProgressPercentage(): float
    {
        if ($this->target_views == 0) {
            return 0;
        }

        return min(100, round(($this->current_views / $this->target_views) * 100, 2));
    }

    public function approve(): void
    {
        $this->update([
            'status' => 'active',
            'starts_at' => now(),
        ]);
    }

    public function reject(): void
    {
        $this->update(['status' => 'rejected']);
    }

    public function pause(): void
    {
        $this->update(['is_active' => false]);
    }

    public function resume(): void
    {
        $this->update(['is_active' => true]);
    }
}
