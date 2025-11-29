<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PostShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'share_platform',
        'share_message',
        'ip_address',
        'user_agent',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($share) {
            $share->post->increment('shares_count');
        });

        static::deleted(function ($share) {
            $share->post->decrement('shares_count');
        });
    }

    public static function trackShare($postId, $userId = null, $platform = 'internal', $message = null)
    {
        return self::create([
            'post_id' => $postId,
            'user_id' => $userId,
            'share_platform' => $platform,
            'share_message' => $message,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
