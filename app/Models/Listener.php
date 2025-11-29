<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listener extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'listenable_type',
        'listenable_id',
        'session_id',
        'ip_address',
        'user_agent',
        'country',
        'city',
        'started_at',
        'ended_at',
        'duration',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function listenable()
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereNull('ended_at')
            ->where('started_at', '>', now()->subMinutes(5));
    }

    public function scopeForTrack($query, $trackId)
    {
        return $query->where('listenable_type', 'App\Models\Track')
            ->where('listenable_id', $trackId);
    }

    /**
     * Methods
     */
    public function endSession()
    {
        $this->ended_at = now();
        $this->duration = $this->started_at->diffInSeconds($this->ended_at);
        $this->save();
    }

    public static function startSession($listenableType, $listenableId, $userId = null)
    {
        return static::create([
            'user_id' => $userId,
            'listenable_type' => $listenableType,
            'listenable_id' => $listenableId,
            'session_id' => \Illuminate\Support\Str::uuid()->toString(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'started_at' => now(),
        ]);
    }

    public static function getActiveListeners($listenableType, $listenableId)
    {
        return static::where('listenable_type', $listenableType)
            ->where('listenable_id', $listenableId)
            ->active()
            ->count();
    }

    public static function getListenerStats($userId, $startDate = null, $endDate = null)
    {
        $query = static::where(function ($q) use ($userId) {
            $q->whereHasMorph('listenable', ['App\Models\Track', 'App\Models\Album'], function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        });

        if ($startDate) {
            $query->where('started_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('started_at', '<=', $endDate);
        }

        return [
            'total_listeners' => $query->count(),
            'unique_listeners' => $query->distinct('user_id')->count('user_id'),
            'total_duration' => $query->sum('duration'),
            'average_duration' => $query->avg('duration'),
            'by_country' => $query->selectRaw('country, COUNT(*) as count')
                ->groupBy('country')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
        ];
    }
}
