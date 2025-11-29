<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'is_active',
        'description',
        'logo',
        'credentials',
        'settings',
        'fixed_fee',
        'percentage_fee',
        'min_amount',
        'max_amount',
        'supported_currencies',
        'instructions',
        'processing_time',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credentials' => 'array',
        'settings' => 'array',
        'supported_currencies' => 'array',
        'fixed_fee' => 'decimal:2',
        'percentage_fee' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
    ];

    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function calculateFee($amount)
    {
        $percentageFee = ($amount * $this->percentage_fee) / 100;
        $totalFee = $this->fixed_fee + $percentageFee;

        return round($totalFee, 2);
    }

    public function getTotalAmount($amount)
    {
        return $amount + $this->calculateFee($amount);
    }

    public function isManual()
    {
        return $this->type === 'manual';
    }

    public function isAutomatic()
    {
        return $this->type === 'automatic';
    }

    public function supportsAmount($amount)
    {
        if ($amount < $this->min_amount) {
            return false;
        }

        if ($this->max_amount && $amount > $this->max_amount) {
            return false;
        }

        return true;
    }

    public function supportsCurrency($currency)
    {
        if (!$this->supported_currencies) {
            return true; // Supports all currencies
        }

        return in_array($currency, $this->supported_currencies);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeManual($query)
    {
        return $query->where('type', 'manual');
    }

    public function scopeAutomatic($query)
    {
        return $query->where('type', 'automatic');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }
}
