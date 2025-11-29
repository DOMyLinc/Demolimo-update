<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtistDonationSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'donations_enabled',
        'tips_enabled',
        'minimum_donation',
        'minimum_tip',
        'suggested_amounts',
        'donation_message',
        'paypal_email',
        'stripe_account_id',
        'platform_fee_percentage',
    ];

    protected $casts = [
        'donations_enabled' => 'boolean',
        'tips_enabled' => 'boolean',
        'minimum_donation' => 'decimal:2',
        'minimum_tip' => 'decimal:2',
        'suggested_amounts' => 'array',
        'platform_fee_percentage' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getSuggestedAmountsArrayAttribute()
    {
        return $this->suggested_amounts ?? [5, 10, 20, 50];
    }
}
