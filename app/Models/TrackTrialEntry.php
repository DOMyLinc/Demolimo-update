<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackTrialEntry extends Model
{
    protected $fillable = [
        'track_trial_id',
        'user_id',
        'track_title',
        'audio_path',
        'cover_image',
        'votes',
        'plays',
    ];

    public function trial(): BelongsTo
    {
        return $this->belongsTo(TrackTrial::class, 'track_trial_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
