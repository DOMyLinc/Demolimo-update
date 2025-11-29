<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AudioAd extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'audio_file',
        'duration',
        'target_url',
        'target_genre',
        'budget',
        'cpc_rate',
        'max_plays',
        'total_plays',
        'total_clicks',
        'total_spent',
        'status',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'cpc_rate' => 'decimal:2',
        'total_spent' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function analytics()
    {
        return $this->morphMany(AdAnalytics::class, 'ad');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Accessors
     */
    public function getRemainingBudgetAttribute()
    {
        return $this->budget - $this->total_spent;
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'active' && $this->remaining_budget > 0;
    }

    /**
     * Methods
     */
    public function recordPlay($userId = null, $trackId = null)
    {
        $this->increment('total_plays');

        AdAnalytics::create([
            'ad_type' => 'App\Models\AudioAd',
            'ad_id' => $this->id,
            'user_id' => $userId,
            'track_id' => $trackId,
            'event_type' => 'play',
            'cost' => 0, // Plays are free, only clicks cost
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Check if max plays reached
        if ($this->max_plays && $this->total_plays >= $this->max_plays) {
            $this->update(['status' => 'completed']);
        }
    }

    public function recordClick($userId = null)
    {
        $cost = $this->cpc_rate;

        $this->increment('total_clicks');
        $this->increment('total_spent', $cost);

        AdAnalytics::create([
            'ad_type' => 'App\Models\AudioAd',
            'ad_id' => $this->id,
            'user_id' => $userId,
            'event_type' => 'click',
            'cost' => $cost,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Deduct from wallet
        $wallet = $this->user->wallet;
        if ($wallet) {
            $wallet->deductBalance($cost, "Audio ad click: {$this->title}", 'ad_click', $this);
        }

        // Check if budget exhausted
        if ($this->remaining_budget <= 0) {
            $this->update(['status' => 'completed']);
        }
    }

    public function pause()
    {
        $this->update(['status' => 'paused']);
    }

    public function resume()
    {
        if ($this->remaining_budget > 0) {
            $this->update(['status' => 'active']);
        }
    }

    public static function getRandomAd($genre = null)
    {
        $query = static::active();

        if ($genre) {
            $query->where(function ($q) use ($genre) {
                $q->where('target_genre', $genre)
                    ->orWhereNull('target_genre');
            });
        }

        return $query->inRandomOrder()->first();
    }
}
