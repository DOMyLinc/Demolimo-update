<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtistTip extends Model
{
    use HasFactory;

    protected $fillable = [
        'artist_id',
        'tipper_id',
        'tipper_name',
        'amount',
        'tippable_type',
        'tippable_id',
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

    public function tipper()
    {
        return $this->belongsTo(User::class, 'tipper_id');
    }

    public function tippable()
    {
        return $this->morphTo();
    }

    public function process()
    {
        if ($this->status !== 'pending') {
            throw new \Exception('Only pending tips can be processed');
        }

        $settings = $this->artist->donationSettings;
        $platformFee = $this->amount * ($settings->platform_fee_percentage / 100);
        $artistAmount = $this->amount - $platformFee;

        // Add to artist wallet
        $this->artist->wallet->addBalance(
            $artistAmount,
            "Tip from " . ($this->is_anonymous ? 'Anonymous' : $this->getTipperName()) . " on " . $this->getTippableDescription(),
            'tip',
            $this
        );

        $this->status = 'completed';
        $this->processed_at = now();
        $this->save();

        // Send notification to artist
        $this->artist->notify(new \App\Notifications\TipReceived($this));

        return $this;
    }

    public function getTipperName()
    {
        if ($this->is_anonymous) {
            return 'Anonymous';
        }

        return $this->tipper ? $this->tipper->name : $this->tipper_name;
    }

    public function getTippableDescription()
    {
        if (!$this->tippable) {
            return 'Unknown';
        }

        $type = class_basename($this->tippable_type);
        $name = $this->tippable->title ?? $this->tippable->name ?? 'Unknown';

        return "{$type}: {$name}";
    }
}
