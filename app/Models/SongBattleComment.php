<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SongBattleComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'song_battle_version_id',
        'user_id',
        'content',
    ];

    public function version()
    {
        return $this->belongsTo(SongBattleVersion::class, 'song_battle_version_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
