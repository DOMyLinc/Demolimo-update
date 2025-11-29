<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'subtotal',
        'platform_fee',
        'total',
        'status',
        'payment_status',
        'payment_method',
        'transaction_id',
        'shipping_address',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = 'ORD-' . strtoupper(uniqid());
            }
        });
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(ProductOrderItem::class);
    }

    /**
     * Scopes
     */
    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Methods
     */
    public function complete($paymentMethod = null, $transactionId = null)
    {
        $this->update([
            'payment_status' => 'completed',
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
            'status' => 'processing',
        ]);

        // Distribute earnings to sellers
        foreach ($this->items as $item) {
            $sellerWallet = $item->seller->wallet ?? Wallet::create(['user_id' => $item->seller_id]);
            $sellerWallet->addBalance(
                $item->seller_amount,
                "Product sale: {$item->product->name}",
                'product_sale',
                $item
            );

            // Update product sales
            $item->product->recordSale($item->quantity, $item->price);
        }

        return $this;
    }
}

class ProductOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_order_id',
        'product_id',
        'seller_id',
        'quantity',
        'price',
        'seller_amount',
        'platform_fee',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'seller_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function order()
    {
        return $this->belongsTo(ProductOrder::class, 'product_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
