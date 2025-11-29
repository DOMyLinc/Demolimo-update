<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'platform_fee_percentage',
        'platform_fee_fixed',
        'min_platform_fee',
        'max_platform_fee',
        'is_active',
        'description',
    ];

    protected $casts = [
        'platform_fee_percentage' => 'decimal:2',
        'platform_fee_fixed' => 'decimal:2',
        'min_platform_fee' => 'decimal:2',
        'max_platform_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public static function calculatePlatformFee($type, $amount)
    {
        $setting = self::where('type', $type)->where('is_active', true)->first();

        if (!$setting) {
            // Default 10% if no setting found
            return round($amount * 0.10, 2);
        }

        $percentageFee = ($amount * $setting->platform_fee_percentage) / 100;
        $totalFee = $setting->platform_fee_fixed + $percentageFee;

        // Apply min/max constraints
        if ($setting->min_platform_fee && $totalFee < $setting->min_platform_fee) {
            $totalFee = $setting->min_platform_fee;
        }

        if ($setting->max_platform_fee && $totalFee > $setting->max_platform_fee) {
            $totalFee = $setting->max_platform_fee;
        }

        return round($totalFee, 2);
    }

    public static function getArtistAmount($type, $amount, $gatewayFee = 0)
    {
        $platformFee = self::calculatePlatformFee($type, $amount);
        $artistAmount = $amount - $platformFee - $gatewayFee;

        return max(0, round($artistAmount, 2));
    }

    public static function getFeeBreakdown($type, $amount, $gatewayFee = 0)
    {
        $platformFee = self::calculatePlatformFee($type, $amount);
        $artistAmount = self::getArtistAmount($type, $amount, $gatewayFee);

        return [
            'amount' => round($amount, 2),
            'platform_fee' => $platformFee,
            'gateway_fee' => round($gatewayFee, 2),
            'artist_amount' => $artistAmount,
            'total_fees' => round($platformFee + $gatewayFee, 2),
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
