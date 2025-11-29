<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class FlashAlbum extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'cover_image',
        'label_design',
        'packaging_design',
        'color_scheme',
        'flash_drive_capacity',
        'flash_drive_type',
        'base_price',
        'production_cost',
        'artist_profit',
        'track_ids',
        'bonus_content',
        'include_digital_copy',
        'stock_quantity',
        'units_sold',
        'is_available',
        'is_pre_order',
        'release_date',
        'pre_order_end_date',
        'free_shipping',
        'shipping_cost',
        'shipping_regions',
        'is_featured',
        'requires_approval',
        'approved_at',
    ];

    protected $casts = [
        'color_scheme' => 'array',
        'track_ids' => 'array',
        'bonus_content' => 'array',
        'shipping_regions' => 'array',
        'include_digital_copy' => 'boolean',
        'is_available' => 'boolean',
        'is_pre_order' => 'boolean',
        'free_shipping' => 'boolean',
        'is_featured' => 'boolean',
        'requires_approval' => 'boolean',
        'release_date' => 'datetime',
        'pre_order_end_date' => 'datetime',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($album) {
            if (empty($album->slug)) {
                $album->slug = Str::slug($album->title);
            }

            // Calculate artist profit
            $album->artist_profit = $album->base_price - $album->production_cost;
        });

        static::updating(function ($album) {
            // Recalculate profit if prices change
            $album->artist_profit = $album->base_price - $album->production_cost;
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(FlashAlbumOrder::class);
    }

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'flash_album_tracks')
            ->withTimestamps();
    }

    public function boosts()
    {
        return $this->morphMany(Boost::class, 'boostable');
    }

    public function activeBoost()
    {
        return $this->morphOne(Boost::class, 'boostable')
            ->where('is_active', true)
            ->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    /**
     * Get tracks from track_ids JSON
     */
    public function getTracksAttribute()
    {
        if (empty($this->track_ids)) {
            return collect([]);
        }

        return Track::whereIn('id', $this->track_ids)->get();
    }

    /**
     * Check if album is in stock
     */
    public function isInStock(): bool
    {
        return $this->is_available && ($this->stock_quantity > 0 || $this->is_pre_order);
    }

    /**
     * Decrement stock after purchase
     */
    public function decrementStock(int $quantity = 1)
    {
        if (!$this->is_pre_order) {
            $this->decrement('stock_quantity', $quantity);
        }
        $this->increment('units_sold', $quantity);
    }

    /**
     * Calculate total revenue
     */
    public function getTotalRevenueAttribute()
    {
        return $this->orders()
            ->where('payment_status', 'paid')
            ->sum('total_amount');
    }

    /**
     * Calculate artist earnings
     */
    public function getArtistEarningsAttribute()
    {
        return $this->units_sold * $this->artist_profit;
    }

    /**
     * Generate download code for digital copy
     */
    public function generateDownloadCode(): string
    {
        return strtoupper(Str::random(16));
    }

    /**
     * Check if flash album is currently boosted
     */
    public function isBoosted(): bool
    {
        return $this->activeBoost()->exists();
    }
}
