<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadioSchedule extends Model
{
    protected $fillable = [
        'radio_station_id',
        'day_of_week',
        'start_time',
        'end_time',
        'show_name',
        'host_name',
        'description',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(RadioStation::class, 'radio_station_id');
    }
}
