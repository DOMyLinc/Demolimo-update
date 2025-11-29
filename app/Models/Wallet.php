<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'points',
        'pending_balance',
        'total_earned',
        'total_withdrawn',
        'total_spent',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'total_spent' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class, 'user_id', 'user_id');
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class, 'user_id', 'user_id');
    }

    public function getAvailableBalanceAttribute()
    {
        return $this->balance - $this->pending_balance;
    }

    public function canWithdraw($amount)
    {
        return $this->available_balance >= $amount && $amount >= $this->user->minimum_withdrawal;
    }

    public function addBalance($amount, $description, $type = 'credit', $reference = null)
    {
        $balanceBefore = $this->balance;
        $this->balance += $amount;
        $this->total_earned += $amount;
        $this->save();

        WalletTransaction::create([
            'user_id' => $this->user_id,
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'description' => $description,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'status' => 'completed',
        ]);

        return $this;
    }

    public function deductBalance($amount, $description, $type = 'debit', $reference = null)
    {
        if ($this->available_balance < $amount) {
            throw new \Exception('Insufficient balance');
        }

        $balanceBefore = $this->balance;
        $this->balance -= $amount;
        $this->total_spent += $amount;
        $this->save();

        WalletTransaction::create([
            'user_id' => $this->user_id,
            'type' => $type,
            'amount' => -$amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'description' => $description,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'status' => 'completed',
        ]);

        return $this;
    }

    public function addPoints($points, $description, $type = 'earn', $reference = null)
    {
        $balanceBefore = $this->points;
        $this->points += $points;
        $this->save();

        PointTransaction::create([
            'user_id' => $this->user_id,
            'type' => $type,
            'points' => $points,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->points,
            'description' => $description,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
        ]);

        return $this;
    }

    public function deductPoints($points, $description, $type = 'spend', $reference = null)
    {
        if ($this->points < $points) {
            throw new \Exception('Insufficient points');
        }

        $balanceBefore = $this->points;
        $this->points -= $points;
        $this->save();

        PointTransaction::create([
            'user_id' => $this->user_id,
            'type' => $type,
            'points' => -$points,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->points,
            'description' => $description,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
        ]);

        return $this;
    }

    public function convertPointsToCash($points, $conversionRate = 0.01)
    {
        if ($this->points < $points) {
            throw new \Exception('Insufficient points');
        }

        $cashAmount = $points * $conversionRate;

        $this->deductPoints($points, "Converted {$points} points to \${$cashAmount}", 'conversion');
        $this->addBalance($cashAmount, "Converted from {$points} points", 'conversion');

        return $cashAmount;
    }
}
