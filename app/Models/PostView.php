<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostView extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'ip_address',
        'user_agent',
        'referer',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public $timestamps = false;

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function trackView($postId, $userId = null)
    {
        // Prevent duplicate views within 1 hour
        $recentView = self::where('post_id', $postId)
            ->where(function ($query) use ($userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('ip_address', request()->ip());
                }
            })
            ->where('viewed_at', '>', now()->subHour())
            ->exists();

        if (!$recentView) {
            return self::create([
                'post_id' => $postId,
                'user_id' => $userId,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'referer' => request()->header('referer'),
                'viewed_at' => now(),
            ]);
        }

        return null;
    }
}
