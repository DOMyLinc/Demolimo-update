<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftTransaction extends Model
{
    use HasFactory;

    const PLATFORM_FEE_PERCENTAGE = 20; // 20% platform fee
    const ARTIST_EARNING_PERCENTAGE = 80; // 80% to artist

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'track_id',
        'gift_id',
        'quantity',
        'total_amount',
        'platform_fee',
        'artist_earning',
        'message',
        'is_anonymous',
        'payment_status',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'total_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'artist_earning' => 'decimal:2',
        'is_anonymous' => 'boolean',
    ];

    /**
     * Get the sender (user who sent the gift)
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the receiver (artist receiving the gift)
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the track associated with this gift
     */
    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    /**
     * Get the gift type
     */
    public function gift()
    {
        return $this->belongsTo(Gift::class);
    }

    /**
     * Calculate and set fees before saving
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (!$transaction->platform_fee && !$transaction->artist_earning) {
                $transaction->platform_fee = $transaction->total_amount * (self::PLATFORM_FEE_PERCENTAGE / 100);
                $transaction->artist_earning = $transaction->total_amount * (self::ARTIST_EARNING_PERCENTAGE / 100);
            }
        });
    }

    /**
     * Scope to get completed transactions
     */
    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'completed');
    }

    /**
     * Get sender display name (handle anonymous)
     */
    public function getSenderNameAttribute()
    {
        return $this->is_anonymous ? 'Anonymous' : $this->sender->name;
    }
}
