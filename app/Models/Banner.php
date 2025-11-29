<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'content',
        'link',
        'placement_zones',
        'target_audience',
        'status',
        'start_date',
        'end_date',
        'priority',
        'impressions',
        'clicks',
        'is_active',
    ];

    protected $casts = [
        'placement_zones' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function impressions()
    {
        return $this->hasMany(BannerImpression::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeForZone($query, string $zone)
    {
        return $query->whereJsonContains('placement_zones', $zone);
    }

    public function scopeForAudience($query, string $audienceType)
    {
        return $query->where(function ($q) use ($audienceType) {
            $q->where('target_audience', 'all')
                ->orWhere('target_audience', $audienceType);
        });
    }

    public function scopeScheduled($query)
    {
        $now = now();

        return $query->where(function ($q) use ($now) {
            $q->whereNull('start_date')
                ->orWhere('start_date', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>=', $now);
        });
    }

    /**
     * Methods
     */
    public function incrementImpressions(): void
    {
        $this->increment('impressions');
        // Clear cache for all zones this banner appears in
        foreach ($this->placement_zones as $zone) {
            Cache::forget("banners_zone_{$zone}");
        }
    }

    public function incrementClicks(): void
    {
        $this->increment('clicks');
        // Clear cache for all zones this banner appears in
        foreach ($this->placement_zones as $zone) {
            Cache::forget("banners_zone_{$zone}");
        }
    }

    public function isExpired(): bool
    {
        if (!$this->end_date) {
            return false;
        }

        return $this->end_date->isPast();
    }

    public function canShow(?User $user = null): bool
    {
        // Check if active
        if (!$this->is_active) {
            return false;
        }

        // Check if published
        if ($this->status !== 'published') {
            return false;
        }

        // Check if expired
        if ($this->isExpired()) {
            return false;
        }

        // Check if started
        if ($this->start_date && $this->start_date->isFuture()) {
            return false;
        }

        // Check audience targeting
        if ($user && $this->target_audience !== 'all') {
            $isPro = $user->hasActiveSubscription(); // Assuming this method exists

            if ($this->target_audience === 'pro' && !$isPro) {
                return false;
            }

            if ($this->target_audience === 'free' && $isPro) {
                return false;
            }
        }

        return true;
    }

    public function getCTR(): float
    {
        if ($this->impressions === 0) {
            return 0;
        }

        return round(($this->clicks / $this->impressions) * 100, 2);
    }
}
