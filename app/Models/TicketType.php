<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'price',
        'quantity',
        'quantity_sold',
        'sale_start',
        'sale_end',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_start' => 'datetime',
        'sale_end' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function purchases()
    {
        return $this->hasMany(TicketPurchase::class);
    }

    /**
     * Accessors
     */
    public function getAvailableQuantityAttribute()
    {
        return $this->quantity - $this->quantity_sold;
    }

    public function getIsSoldOutAttribute()
    {
        return $this->quantity_sold >= $this->quantity;
    }

    public function getIsOnSaleAttribute()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->sale_start && $now < $this->sale_start) {
            return false;
        }

        if ($this->sale_end && $now > $this->sale_end) {
            return false;
        }

        return true;
    }

    /**
     * Methods
     */
    public function canPurchase($quantity = 1)
    {
        return $this->is_on_sale
            && $this->available_quantity >= $quantity
            && $this->event->canPurchaseTickets();
    }

    public function incrementSold($quantity = 1)
    {
        $this->increment('quantity_sold', $quantity);
        $this->event->incrementTicketsSold($quantity);
    }

    public function decrementSold($quantity = 1)
    {
        $this->decrement('quantity_sold', $quantity);
        $this->event->decrementTicketsSold($quantity);
    }
}
