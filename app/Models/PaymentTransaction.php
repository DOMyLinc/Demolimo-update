<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'payment_gateway_id',
        'payable_type',
        'payable_id',
        'type',
        'amount',
        'gateway_fee',
        'platform_fee',
        'artist_amount',
        'currency',
        'status',
        'payment_details',
        'manual_payment_proof',
        'notes',
        'completed_at',
        'failed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_fee' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'artist_amount' => 'decimal:2',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (!$transaction->transaction_id) {
                $transaction->transaction_id = 'TXN-' . strtoupper(Str::random(12));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gateway()
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }

    public function payable()
    {
        return $this->morphTo();
    }

    public function manualVerification()
    {
        return $this->hasOne(ManualPaymentVerification::class);
    }

    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();

        return $this;
    }

    public function markAsFailed($reason = null)
    {
        $this->status = 'failed';
        $this->failed_at = now();
        if ($reason) {
            $this->notes = $reason;
        }
        $this->save();

        return $this;
    }

    public function refund()
    {
        if ($this->status !== 'completed') {
            throw new \Exception('Only completed transactions can be refunded');
        }

        $this->status = 'refunded';
        $this->save();

        // Reverse the payment (deduct from artist, add to buyer)
        // This logic depends on the payable type

        return $this;
    }

    public function getTotalAmountAttribute()
    {
        return $this->amount + $this->gateway_fee;
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
