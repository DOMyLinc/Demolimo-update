<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SongBattleShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'song_battle_id',
        'user_id',
        'share_type',
        'zipcode_id',
        'views',
        'clicks',
    ];

    /**
     * Relationships
     */
    public function songBattle()
    {
        return $this->belongsTo(SongBattle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function zipcode()
    {
        return $this->belongsTo(Zipcode::class);
    }

    /**
     * Methods
     */
    public function incrementViews()
    {
        $this->increment('views');
    }

    public function incrementClicks()
    {
        $this->increment('clicks');
    }
}
