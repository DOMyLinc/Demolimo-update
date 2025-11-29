<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpgradePrompt extends Model
{
    use HasFactory;

    protected $fillable = [
        'trigger',
        'title',
        'message',
        'cta_text',
        'cta_link',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function getPromptFor($trigger)
    {
        return static::where('trigger', $trigger)
            ->where('is_active', true)
            ->orderBy('priority')
            ->first();
    }

    public static function triggers()
    {
        return [
            'upload_limit' => 'Upload Limit Reached',
            'storage_limit' => 'Storage Limit Reached',
            'track_limit' => 'Track Limit Reached',
            'feature_access' => 'Feature Access Denied',
            'sell_tracks' => 'Sell Tracks Attempt',
            'create_event' => 'Create Event Attempt',
            'monetization' => 'Monetization Request',
            'analytics' => 'Advanced Analytics',
            'video_upload' => 'Video Upload Attempt',
        ];
    }
}
