<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Revenue extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'commission',
        'source_type',
        'source_id',
        'currency',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Polymorphic relationship to the source of revenue
    public function source()
    {
        return $this->morphTo();
    }

    // Scope for available balance
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    // Scope for pending balance
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
