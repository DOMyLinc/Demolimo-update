<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtistDonation extends Model
{
    use HasFactory;

    protected $fillable = [
        'artist_id',
        'donor_id',
        'donor_name',
        'donor_email',
        'amount',
        'type',
        'message',
        'is_anonymous',
        'status',
        'payment_method',
        'transaction_id',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_anonymous' => 'boolean',
        'processed_at' => 'datetime',
    ];

    public function artist()
    {
        return $this->belongsTo(User::class, 'artist_id');
    }

    public function donor()
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    public function process()
    {
        if ($this->status !== 'pending') {
            throw new \Exception('Only pending donations can be processed');
        }

        $settings = $this->artist->donationSettings;
        $platformFee = $this->amount * ($settings->platform_fee_percentage / 100);
        $artistAmount = $this->amount - $platformFee;

        // Add to artist wallet
        $this->artist->wallet->addBalance(
            $artistAmount,
            "Donation from " . ($this->is_anonymous ? 'Anonymous' : $this->getDonorName()),
            'donation',
            $this
        );

        $this->status = 'completed';
        $this->processed_at = now();
        $this->save();

        // Send notification to artist
        $this->artist->notify(new \App\Notifications\DonationReceived($this));

        return $this;
    }

    public function getDonorName()
    {
        if ($this->is_anonymous) {
            return 'Anonymous';
        }

        return $this->donor ? $this->donor->name : $this->donor_name;
    }
}
