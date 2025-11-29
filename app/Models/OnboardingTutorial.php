<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnboardingTutorial extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'target_element',
        'position',
        'step_order',
        'user_type',
        'is_required',
        'is_active',
        'icon',
        'video_url',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function userProgress()
    {
        return $this->hasMany(UserTutorialProgress::class, 'tutorial_id');
    }

    public function isCompletedBy($userId)
    {
        return $this->userProgress()
            ->where('user_id', $userId)
            ->where('is_completed', true)
            ->exists();
    }

    public static function getForUser($user)
    {
        $query = static::where('is_active', true);

        if ($user->user_type !== 'all') {
            $query->whereIn('user_type', ['all', $user->user_type]);
        }

        return $query->orderBy('step_order')->get();
    }
}
