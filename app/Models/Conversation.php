<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'type', // 'direct' or 'group'
        'name',
        'avatar',
    ];

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_user')
            ->withTimestamps()
            ->withPivot('last_read_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function getUnreadCountForUser($userId)
    {
        $participant = $this->participants()->where('user_id', $userId)->first();
        $lastReadAt = $participant?->pivot->last_read_at;

        return $this->messages()
            ->where('user_id', '!=', $userId)
            ->when($lastReadAt, fn($q) => $q->where('created_at', '>', $lastReadAt))
            ->count();
    }
}
