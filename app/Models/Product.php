<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_category_id',
        'name',
        'slug',
        'description',
        'price',
        'sale_price',
        'sku',
        'stock_quantity',
        'track_inventory',
        'images',
        'status',
        'is_featured',
        'views',
        'sales_count',
        'total_revenue',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'track_inventory' => 'boolean',
        'is_featured' => 'boolean',
        'images' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (!$product->slug) {
                $product->slug = Str::slug($product->name) . '-' . Str::random(6);
            }
            if (!$product->sku) {
                $product->sku = 'PRD-' . strtoupper(Str::random(8));
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

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function orderItems()
    {
        return $this->hasMany(ProductOrderItem::class);
    }

    /**
     * Scopes
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where('track_inventory', false)
                ->orWhere('stock_quantity', '>', 0);
        });
    }

    /**
     * Accessors
     */
    public function getActivePriceAttribute()
    {
        return $this->sale_price ?? $this->price;
    }

    public function getIsOnSaleAttribute()
    {
        return $this->sale_price && $this->sale_price < $this->price;
    }

    public function getDiscountPercentageAttribute()
    {
        if (!$this->is_on_sale) {
            return 0;
        }

        return round((($this->price - $this->sale_price) / $this->price) * 100);
    }

    public function getIsInStockAttribute()
    {
        if (!$this->track_inventory) {
            return true;
        }

        return $this->stock_quantity > 0;
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }

    public function getMainImageAttribute()
    {
        return $this->images[0] ?? null;
    }

    /**
     * Methods
     */
    public function incrementViews()
    {
        $this->increment('views');
    }

    public function decrementStock($quantity = 1)
    {
        if ($this->track_inventory) {
            $this->decrement('stock_quantity', $quantity);

            if ($this->stock_quantity <= 0) {
                $this->update(['status' => 'out_of_stock']);
            }
        }
    }

    public function incrementStock($quantity = 1)
    {
        if ($this->track_inventory) {
            $this->increment('stock_quantity', $quantity);

            if ($this->status === 'out_of_stock' && $this->stock_quantity > 0) {
                $this->update(['status' => 'published']);
            }
        }
    }

    public function recordSale($quantity, $price)
    {
        $this->increment('sales_count', $quantity);
        $this->increment('total_revenue', $price * $quantity);
        $this->decrementStock($quantity);
    }
}
