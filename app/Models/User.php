<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // admin, artist, user
        'avatar',
        'cover',
        'points',
        'is_verified',
        'is_banned',
        'storage_limit',
        'used_storage',
        'banned_until',
        'max_uploads',
        'username',
        'permissions',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_verified' => 'boolean',
        'is_banned' => 'boolean',
        'banned_until' => 'datetime',
        'max_uploads' => 'integer',
        'permissions' => 'array',
    ];

    /**
     * Check if user has a specific permission
     */
    public function hasPermission($permission)
    {
        if ($this->role === 'admin' && $this->id === 1) {
            return true; // Super Admin has all permissions
        }

        if ($this->role === 'admin' && empty($this->permissions)) {
            return true; // Legacy admins might have all permissions by default, or restrict this if needed
        }

        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Check if user is Super Admin
     */
    public function isSuperAdmin()
    {
        return $this->id === 1; // Assuming ID 1 is Super Admin
    }

    public function tracks()
    {
        return $this->hasMany(Track::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id');
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id');
    }

    public function zipcode()
    {
        return $this->belongsTo(Zipcode::class);
    }

    public function ownedZipcodes()
    {
        return $this->hasMany(Zipcode::class, 'owner_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function ticketPurchases()
    {
        return $this->hasMany(TicketPurchase::class);
    }

    public function boosts()
    {
        return $this->hasMany(Boost::class);
    }

    public function performingAt()
    {
        return $this->belongsToMany(Event::class, 'event_performers')
            ->withPivot('order', 'performance_time')
            ->withTimestamps();
    }

    public function featureAccess()
    {
        return $this->hasMany(UserFeatureAccess::class);
    }

    // Two‑factor authentication relationship
    public function twoFactor()
    {
        return $this->hasOne(UserTwoFactor::class);
    }

    // Helper to quickly check if 2FA is active for this user
    public function hasTwoFactorEnabled(): bool
    {
        return $this->twoFactor ? $this->twoFactor->enabled : false;
    }

    public function subscription()
    {
        return $this->hasOne(UserSubscription::class)->latest();
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function donationSettings()
    {
        return $this->hasOne(ArtistDonationSettings::class);
    }

    public function receivedDonations()
    {
        return $this->hasMany(ArtistDonation::class, 'artist_id');
    }

    public function sentDonations()
    {
        return $this->hasMany(ArtistDonation::class, 'donor_id');
    }

    public function receivedTips()
    {
        return $this->hasMany(ArtistTip::class, 'artist_id');
    }

    public function sentTips()
    {
        return $this->hasMany(ArtistTip::class, 'tipper_id');
    }

    public function receivedGifts()
    {
        return $this->hasMany(ArtistGift::class, 'recipient_id');
    }

    public function sentGifts()
    {
        return $this->hasMany(ArtistGift::class, 'sender_id');
    }

    public function songBattleRewards()
    {
        return $this->hasMany(SongBattleReward::class, 'winner_user_id');
    }

    public function radioStations()
    {
        return $this->hasMany(RadioStation::class);
    }

    public function podcasts()
    {
        return $this->hasMany(Podcast::class);
    }

    public function subscribedPodcasts()
    {
        return $this->belongsToMany(Podcast::class, 'podcast_subscribers')
            ->withTimestamps();
    }

    public function flashAlbums()
    {
        return $this->hasMany(FlashAlbum::class);
    }

    public function flashAlbumOrders()
    {
        return $this->hasMany(FlashAlbumOrder::class);
    }

    public function extendPremium($days)
    {
        $subscription = $this->subscription;

        if (!$subscription || !$subscription->is_active) {
            // Create new subscription
            UserSubscription::create([
                'user_id' => $this->id,
                'plan_name' => 'Premium',
                'expires_at' => now()->addDays($days),
                'is_active' => true,
            ]);
        } else {
            // Extend existing subscription
            $subscription->expires_at = $subscription->expires_at->addDays($days);
            $subscription->save();
        }

        return $this;
    }

    /**
     * Check if user is a Pro subscriber
     */
    public function isPro(): bool
    {
        $subscription = $this->subscription;

        if (!$subscription) {
            return false;
        }

        return $subscription->is_active &&
            $subscription->expires_at &&
            $subscription->expires_at->isFuture();
    }

    /**
     * Check if user has access to a feature
     */
    public function hasFeatureAccess(string $featureKey): bool
    {
        return FeatureAccess::userHasAccess($this, $featureKey);
    }

    /**
     * Check if user has reached limit for a feature
     */
    public function hasReachedFeatureLimit(string $featureKey): bool
    {
        return FeatureAccess::userHasReachedLimit($this, $featureKey);
    }

    /**
     * Get remaining quota for a feature
     */
    public function getFeatureQuota(string $featureKey): ?int
    {
        return FeatureAccess::getRemainingQuota($this, $featureKey);
    }

    /**
     * Check if user can create more of a feature
     */
    public function canCreate(string $featureKey): bool
    {
        return $this->hasFeatureAccess($featureKey) &&
            !$this->hasReachedFeatureLimit($featureKey);
    }
}
