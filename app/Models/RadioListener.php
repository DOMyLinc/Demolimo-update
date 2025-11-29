<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadioListener extends Model
{
    protected $fillable = [
        'radio_station_id',
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'connected_at',
        'disconnected_at',
    ];

    protected $casts = [
        'connected_at' => 'datetime',
        'disconnected_at' => 'datetime',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(RadioStation::class, 'radio_station_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
