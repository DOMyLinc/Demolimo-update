<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadioPlaylist extends Model
{
    protected $fillable = [
        'radio_station_id',
        'track_id',
        'position',
        'play_count',
        'last_played_at',
    ];

    protected $casts = [
        'last_played_at' => 'datetime',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(RadioStation::class, 'radio_station_id');
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }
}
