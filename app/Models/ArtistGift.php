<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtistGift extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_id',
        'sender_id',
        'gift_type',
        'cash_amount',
        'points_amount',
        'premium_days',
        'custom_gift_description',
        'message',
        'status',
        'sent_at',
        'claimed_at',
    ];

    protected $casts = [
        'cash_amount' => 'decimal:2',
        'sent_at' => 'datetime',
        'claimed_at' => 'datetime',
    ];

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function send()
    {
        $this->status = 'sent';
        $this->sent_at = now();
        $this->save();

        // Send notification to recipient
        $this->recipient->notify(new \App\Notifications\GiftReceived($this));

        return $this;
    }

    public function claim()
    {
        if ($this->status !== 'sent') {
            throw new \Exception('Gift must be sent before it can be claimed');
        }

        $wallet = $this->recipient->wallet;

        switch ($this->gift_type) {
            case 'cash':
                $wallet->addBalance(
                    $this->cash_amount,
                    "Gift from " . ($this->sender ? $this->sender->name : 'Admin'),
                    'gift',
                    $this
                );
                break;

            case 'points':
                $wallet->addPoints(
                    $this->points_amount,
                    "Gift from " . ($this->sender ? $this->sender->name : 'Admin'),
                    'gift',
                    $this
                );
                break;

            case 'premium_subscription':
                $this->recipient->extendPremium($this->premium_days);
                break;
        }

        $this->status = 'claimed';
        $this->claimed_at = now();
        $this->save();

        return $this;
    }

    public function getGiftDescriptionAttribute()
    {
        switch ($this->gift_type) {
            case 'cash':
                return '$' . number_format($this->cash_amount, 2);
            case 'points':
                return number_format($this->points_amount) . ' Points';
            case 'premium_subscription':
                return $this->premium_days . ' Days Premium';
            case 'custom':
                return $this->custom_gift_description;
            default:
                return 'Unknown Gift';
        }
    }
}
