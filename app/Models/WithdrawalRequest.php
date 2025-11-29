<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'method',
        'payment_details',
        'status',
        'admin_notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approve($adminId, $notes = null)
    {
        $this->update([
            'status' => 'completed',
            'processed_by' => $adminId,
            'processed_at' => now(),
            'admin_notes' => $notes,
        ]);

        // Update user wallet
        $wallet = $this->user->wallet;
        $wallet->pending_balance -= $this->amount;
        $wallet->total_withdrawn += $this->amount;
        $wallet->save();
    }

    public function reject($adminId, $notes)
    {
        $this->update([
            'status' => 'rejected',
            'processed_by' => $adminId,
            'processed_at' => now(),
            'admin_notes' => $notes,
        ]);

        // Return pending balance
        $wallet = $this->user->wallet;
        $wallet->pending_balance -= $this->amount;
        $wallet->balance += $this->amount;
        $wallet->save();
    }
}
