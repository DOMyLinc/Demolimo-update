<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WelcomeMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'user_type',
        'display_type',
        'show_once',
        'is_active',
        'priority',
        'button_text',
        'button_link',
    ];

    protected $casts = [
        'show_once' => 'boolean',
        'is_active' => 'boolean',
    ];

    public static function getForUser($user)
    {
        $query = static::where('is_active', true);

        if ($user->user_type !== 'all') {
            $query->whereIn('user_type', ['all', $user->user_type]);
        }

        return $query->orderBy('priority')->get();
    }
}
