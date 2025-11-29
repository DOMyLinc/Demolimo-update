<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackValuation extends Model
{
    use HasFactory;

    protected $fillable = [
        'track_id',
        'current_value',
        'initial_value',
        'peak_value',
        'lowest_value',
        'total_views',
        'total_plays',
        'total_likes',
        'total_shares',
        'total_downloads',
        'engagement_score',
        'trending_score',
        'last_calculated_at',
    ];

    protected $casts = [
        'current_value' => 'decimal:2',
        'initial_value' => 'decimal:2',
        'peak_value' => 'decimal:2',
        'lowest_value' => 'decimal:2',
        'engagement_score' => 'decimal:4',
        'trending_score' => 'decimal:4',
        'last_calculated_at' => 'datetime',
    ];

    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function history()
    {
        return $this->hasMany(ValuationHistory::class, 'track_id', 'track_id');
    }

    public function investments()
    {
        return $this->hasMany(TrackInvestment::class, 'track_id', 'track_id');
    }

    public function getChangePercentageAttribute()
    {
        if ($this->initial_value == 0)
            return 0;
        return (($this->current_value - $this->initial_value) / $this->initial_value) * 100;
    }

    public function isRising()
    {
        return $this->change_percentage > 0;
    }

    public function isFalling()
    {
        return $this->change_percentage < 0;
    }
}
