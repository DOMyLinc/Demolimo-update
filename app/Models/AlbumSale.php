<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AlbumSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'album_id',
        'buyer_id',
        'seller_id',
        'payment_transaction_id',
        'price',
        'platform_fee',
        'gateway_fee',
        'seller_earnings',
        'license_type',
        'license_terms',
        'download_token',
        'download_count',
        'max_downloads',
        'expires_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'gateway_fee' => 'decimal:2',
        'seller_earnings' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (!$sale->download_token) {
                $sale->download_token = Str::random(32);
            }
        });
    }

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function transaction()
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }

    public function canDownload()
    {
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->download_count >= $this->max_downloads) {
            return false;
        }

        return true;
    }

    public function incrementDownload()
    {
        $this->increment('download_count');
    }

    public function getDownloadUrl()
    {
        return route('user.purchases.download.album', $this->download_token);
    }
}
