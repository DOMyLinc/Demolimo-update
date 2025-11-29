<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'content',
        'recipient_count',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Check if newsletter has been sent
     */
    public function isSent()
    {
        return !is_null($this->sent_at);
    }

    /**
     * Scope to get only sent newsletters
     */
    public function scopeSent($query)
    {
        return $query->whereNotNull('sent_at');
    }

    /**
     * Scope to get only draft newsletters
     */
    public function scopeDraft($query)
    {
        return $query->whereNull('sent_at');
    }
}
