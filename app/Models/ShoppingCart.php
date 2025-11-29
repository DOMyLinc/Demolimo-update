<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingCart extends Model
{
    use HasFactory;

    protected $table = 'shopping_cart';

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Accessors
     */
    public function getSubtotalAttribute()
    {
        return $this->product->active_price * $this->quantity;
    }

    /**
     * Static Methods
     */
    public static function addItem($userId, $productId, $quantity = 1)
    {
        $cart = static::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($cart) {
            $cart->increment('quantity', $quantity);
            return $cart;
        }

        return static::create([
            'user_id' => $userId,
            'product_id' => $productId,
            'quantity' => $quantity,
        ]);
    }

    public static function getTotal($userId)
    {
        return static::where('user_id', $userId)
            ->with('product')
            ->get()
            ->sum('subtotal');
    }

    public static function getItemCount($userId)
    {
        return static::where('user_id', $userId)->sum('quantity');
    }

    public static function clearCart($userId)
    {
        return static::where('user_id', $userId)->delete();
    }
}
