<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'venue',
        'address',
        'city',
        'country',
        'latitude',
        'longitude',
        'start_date',
        'end_date',
        'cover_image',
        'event_type',
        'status',
        'is_featured',
        'is_online',
        'stream_url',
        'capacity',
        'tickets_sold',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_featured' => 'boolean',
        'is_online' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class);
    }

    public function purchases()
    {
        return $this->hasMany(TicketPurchase::class);
    }

    public function performers()
    {
        return $this->belongsToMany(User::class, 'event_performers')
            ->withPivot('order', 'performance_time')
            ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    /**
     * Accessors & Mutators
     */
    public function getIsUpcomingAttribute()
    {
        return $this->start_date > now();
    }

    public function getIsOngoingAttribute()
    {
        return $this->start_date <= now() && $this->end_date >= now();
    }

    public function getIsPastAttribute()
    {
        return $this->end_date < now();
    }

    public function getAvailableTicketsAttribute()
    {
        if (!$this->capacity) {
            return null; // Unlimited
        }
        return $this->capacity - $this->tickets_sold;
    }

    public function getIsSoldOutAttribute()
    {
        if (!$this->capacity) {
            return false;
        }
        return $this->tickets_sold >= $this->capacity;
    }

    public function getTotalRevenueAttribute()
    {
        return $this->purchases()
            ->where('payment_status', 'completed')
            ->sum('price_paid');
    }

    /**
     * Methods
     */
    public function publish()
    {
        $this->update(['status' => 'published']);
        return $this;
    }

    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
        return $this;
    }

    public function complete()
    {
        $this->update(['status' => 'completed']);
        return $this;
    }

    public function canPurchaseTickets()
    {
        return $this->status === 'published'
            && $this->is_upcoming
            && !$this->is_sold_out;
    }

    public function incrementTicketsSold($quantity = 1)
    {
        $this->increment('tickets_sold', $quantity);
    }

    public function decrementTicketsSold($quantity = 1)
    {
        $this->decrement('tickets_sold', $quantity);
    }
}
