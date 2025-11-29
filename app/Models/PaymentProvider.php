<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', // stripe, paypal, coinbase, binance, esewa, khalti, imepay
        'public_key',
        'secret_key',
        'merchant_id',
        'is_active',
        'is_test_mode',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_test_mode' => 'boolean',
        'secret_key' => 'encrypted',
    ];
}
