<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashAlbumOrder extends Model
{
    protected $fillable = [
        'flash_album_id',
        'user_id',
        'order_number',
        'quantity',
        'unit_price',
        'shipping_cost',
        'tax',
        'total_amount',
        'shipping_name',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_postal_code',
        'shipping_country',
        'shipping_phone',
        'status',
        'payment_status',
        'payment_method',
        'transaction_id',
        'tracking_number',
        'shipping_carrier',
        'shipped_at',
        'delivered_at',
        'download_code',
        'download_code_expires_at',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'download_code_expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            // Generate unique order number
            $order->order_number = 'FA-' . strtoupper(uniqid());
        });
    }

    public function flashAlbum(): BelongsTo
    {
        return $this->belongsTo(FlashAlbum::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark order as shipped
     */
    public function markAsShipped(string $trackingNumber, string $carrier)
    {
        $this->update([
            'status' => 'shipped',
            'tracking_number' => $trackingNumber,
            'shipping_carrier' => $carrier,
            'shipped_at' => now(),
        ]);
    }

    /**
     * Mark order as delivered
     */
    public function markAsDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Get full shipping address
     */
    public function getFullShippingAddressAttribute()
    {
        return implode(', ', array_filter([
            $this->shipping_address,
            $this->shipping_city,
            $this->shipping_state,
            $this->shipping_postal_code,
            $this->shipping_country,
        ]));
    }
}
