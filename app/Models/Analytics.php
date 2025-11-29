<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Analytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', // Optional, if logged in
        'track_id',
        'album_id',
        'ip_address',
        'country',
        'city',
        'device',
        'browser',
        'referrer',
    ];

    public function track()
    {
        return $this->belongsTo(Track::class);
    }
}
