<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualPaymentVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_transaction_id',
        'verified_by',
        'status',
        'proof_image',
        'proof_document',
        'transaction_reference',
        'admin_notes',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function transaction()
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approve($adminId, $notes = null)
    {
        $this->status = 'approved';
        $this->verified_by = $adminId;
        $this->verified_at = now();
        $this->admin_notes = $notes;
        $this->save();

        // Mark transaction as completed
        $this->transaction->markAsCompleted();

        return $this;
    }

    public function reject($adminId, $notes)
    {
        $this->status = 'rejected';
        $this->verified_by = $adminId;
        $this->verified_at = now();
        $this->admin_notes = $notes;
        $this->save();

        // Mark transaction as failed
        $this->transaction->markAsFailed('Manual payment rejected: ' . $notes);

        return $this;
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
