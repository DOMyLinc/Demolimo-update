<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'ad_type',
        'ad_id',
        'user_id',
        'event_type',
        'ip_address',
        'user_agent',
        'country',
        'city',
        'track_id',
        'cost',
    ];

    protected $casts = [
        'cost' => 'decimal:4',
    ];

    /**
     * Relationships
     */
    public function ad()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    /**
     * Scopes
     */
    public function scopeImpressions($query)
    {
        return $query->where('event_type', 'impression');
    }

    public function scopeClicks($query)
    {
        return $query->where('event_type', 'click');
    }

    public function scopePlays($query)
    {
        return $query->where('event_type', 'play');
    }

    /**
     * Static Methods
     */
    public static function recordImpression($ad, $userId = null)
    {
        return static::create([
            'ad_type' => get_class($ad),
            'ad_id' => $ad->id,
            'user_id' => $userId,
            'event_type' => 'impression',
            'cost' => $ad->pricing_model === 'cpm' ? ($ad->cpm_rate / 1000) : 0,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public static function getStats($adType, $adId)
    {
        $analytics = static::where('ad_type', $adType)
            ->where('ad_id', $adId);

        return [
            'total_impressions' => (clone $analytics)->impressions()->count(),
            'total_clicks' => (clone $analytics)->clicks()->count(),
            'total_plays' => (clone $analytics)->plays()->count(),
            'total_cost' => (clone $analytics)->sum('cost'),
            'ctr' => static::calculateCTR($adType, $adId),
        ];
    }

    public static function calculateCTR($adType, $adId)
    {
        $impressions = static::where('ad_type', $adType)
            ->where('ad_id', $adId)
            ->impressions()
            ->count();

        $clicks = static::where('ad_type', $adType)
            ->where('ad_id', $adId)
            ->clicks()
            ->count();

        if ($impressions == 0) {
            return 0;
        }

        return round(($clicks / $impressions) * 100, 2);
    }
}
