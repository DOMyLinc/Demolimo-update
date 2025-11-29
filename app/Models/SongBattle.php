<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SongBattle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function versions()
    {
        return $this->hasMany(SongBattleVersion::class);
    }

    public function reward()
    {
        return $this->hasOne(SongBattleReward::class);
    }

    public function getWinningVersionAttribute()
    {
        return $this->versions()
            ->withCount('votes')
            ->orderByDesc('votes_count')
            ->first();
    }
}
