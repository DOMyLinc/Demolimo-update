<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lyric extends Model
{
    use HasFactory;

    protected $fillable = [
        'track_id',
        'content',
        'synced_content',
        'language',
        'is_synced',
        'contributor_id',
        'is_verified',
    ];

    protected $casts = [
        'is_synced' => 'boolean',
        'is_verified' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function contributor()
    {
        return $this->belongsTo(User::class, 'contributor_id');
    }

    /**
     * Scopes
     */
    public function scopeSynced($query)
    {
        return $query->where('is_synced', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Methods
     */
    public function getSyncedLyrics()
    {
        if (!$this->is_synced || !$this->synced_content) {
            return null;
        }

        return $this->parseLRC($this->synced_content);
    }

    protected function parseLRC($lrcContent)
    {
        $lines = explode("\n", $lrcContent);
        $lyrics = [];

        foreach ($lines as $line) {
            // Parse LRC format: [mm:ss.xx]lyrics
            if (preg_match('/\[(\d+):(\d+)\.(\d+)\](.*)/', $line, $matches)) {
                $minutes = (int) $matches[1];
                $seconds = (int) $matches[2];
                $milliseconds = (int) $matches[3];
                $text = trim($matches[4]);

                $timeInMs = ($minutes * 60 * 1000) + ($seconds * 1000) + $milliseconds;

                $lyrics[] = [
                    'time' => $timeInMs,
                    'text' => $text,
                ];
            }
        }

        return $lyrics;
    }

    public function canAccess($user)
    {
        // Free users get static lyrics only
        if (!$user->isPro() && $this->is_synced) {
            return false;
        }

        return true;
    }

    public static function createFromText($trackId, $content, $userId = null)
    {
        return static::create([
            'track_id' => $trackId,
            'content' => $content,
            'contributor_id' => $userId,
            'is_synced' => false,
        ]);
    }

    public static function createSynced($trackId, $content, $syncedContent, $userId = null)
    {
        return static::create([
            'track_id' => $trackId,
            'content' => $content,
            'synced_content' => $syncedContent,
            'contributor_id' => $userId,
            'is_synced' => true,
        ]);
    }
}
