<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackInvestment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'track_id',
        'invested_amount',
        'purchase_value',
        'shares',
        'current_value',
        'profit_loss',
        'is_active',
        'sold_at',
    ];

    protected $casts = [
        'invested_amount' => 'decimal:2',
        'purchase_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'profit_loss' => 'decimal:2',
        'is_active' => 'boolean',
        'sold_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function updateCurrentValue($newTrackValue)
    {
        $this->current_value = $newTrackValue * $this->shares;
        $this->profit_loss = $this->current_value - $this->invested_amount;
        $this->save();
    }

    public function getProfitPercentageAttribute()
    {
        if ($this->invested_amount == 0)
            return 0;
        return ($this->profit_loss / $this->invested_amount) * 100;
    }
}
