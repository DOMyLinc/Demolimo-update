<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SongBattleVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'song_battle_id',
        'version_number',
        'file_path',
        'style_name',
        'play_count',
    ];

    public function battle()
    {
        return $this->belongsTo(SongBattle::class, 'song_battle_id');
    }

    public function votes()
    {
        return $this->hasMany(SongBattleVote::class);
    }

    public function comments()
    {
        return $this->hasMany(SongBattleComment::class);
    }

    public function getVotesCountAttribute()
    {
        return $this->votes()->count();
    }
}
