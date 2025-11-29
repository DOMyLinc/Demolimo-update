<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfflineDownload extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'downloadable_type',
        'downloadable_id',
        'quality',
        'file_path',
        'file_size',
        'downloaded_at',
        'expires_at',
        'last_accessed_at',
    ];

    protected $casts = [
        'downloaded_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_accessed_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function downloadable()
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Methods
     */
    public function updateAccess()
    {
        $this->update(['last_accessed_at' => now()]);
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public static function canDownload($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return false;
        }

        // Pro users have unlimited downloads
        if ($user->isPro()) {
            return true;
        }

        // Free users cannot download
        return false;
    }

    public static function createDownload($userId, $downloadable, $quality = '320kbps')
    {
        $user = User::find($userId);

        if (!static::canDownload($userId)) {
            throw new \Exception('Offline downloads require Pro subscription.');
        }

        // Check if already downloaded
        $existing = static::where('user_id', $userId)
            ->where('downloadable_type', get_class($downloadable))
            ->where('downloadable_id', $downloadable->id)
            ->where('quality', $quality)
            ->active()
            ->first();

        if ($existing) {
            $existing->updateAccess();
            return $existing;
        }

        // Generate offline file
        $filePath = static::generateOfflineFile($downloadable, $quality);

        return static::create([
            'user_id' => $userId,
            'downloadable_type' => get_class($downloadable),
            'downloadable_id' => $downloadable->id,
            'quality' => $quality,
            'file_path' => $filePath,
            'file_size' => filesize(storage_path('app/' . $filePath)),
            'downloaded_at' => now(),
            'expires_at' => $user->isPro() ? null : now()->addDays(30),
        ]);
    }

    protected static function generateOfflineFile($downloadable, $quality)
    {
        // This would convert/copy the file to the appropriate quality
        // For now, just copy the original file
        $originalPath = $downloadable->audio_file;
        $offlinePath = 'offline/' . md5($downloadable->id . $quality . time()) . '.mp3';

        \Storage::copy('public/' . $originalPath, $offlinePath);

        return $offlinePath;
    }
}
