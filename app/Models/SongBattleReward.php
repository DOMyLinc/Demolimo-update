<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SongBattleReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'song_battle_id',
        'winner_version_id',
        'winner_user_id',
        'reward_type',
        'cash_amount',
        'points_amount',
        'premium_days',
        'custom_reward_description',
        'status',
        'awarded_by',
        'awarded_at',
        'claimed_at',
        'notes',
    ];

    protected $casts = [
        'cash_amount' => 'decimal:2',
        'awarded_at' => 'datetime',
        'claimed_at' => 'datetime',
    ];

    public function battle()
    {
        return $this->belongsTo(SongBattle::class, 'song_battle_id');
    }

    public function winnerVersion()
    {
        return $this->belongsTo(SongBattleVersion::class, 'winner_version_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    public function awardedBy()
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }

    public function award()
    {
        $this->status = 'awarded';
        $this->awarded_at = now();
        $this->save();

        // Send notification to winner
        $this->winner->notify(new \App\Notifications\SongBattleRewardAwarded($this));

        return $this;
    }

    public function claim()
    {
        if ($this->status !== 'awarded') {
            throw new \Exception('Reward must be awarded before it can be claimed');
        }

        $wallet = $this->winner->wallet;

        switch ($this->reward_type) {
            case 'cash':
                $wallet->addBalance(
                    $this->cash_amount,
                    "Song Battle Reward: {$this->battle->title}",
                    'reward',
                    $this
                );
                break;

            case 'points':
                $wallet->addPoints(
                    $this->points_amount,
                    "Song Battle Reward: {$this->battle->title}",
                    'reward',
                    $this
                );
                break;

            case 'premium_subscription':
                $this->winner->extendPremium($this->premium_days);
                break;
        }

        $this->status = 'claimed';
        $this->claimed_at = now();
        $this->save();

        return $this;
    }

    public function getRewardDescriptionAttribute()
    {
        switch ($this->reward_type) {
            case 'cash':
                return '$' . number_format($this->cash_amount, 2);
            case 'points':
                return number_format($this->points_amount) . ' Points';
            case 'premium_subscription':
                return $this->premium_days . ' Days Premium';
            case 'custom':
                return $this->custom_reward_description;
            default:
                return 'Unknown Reward';
        }
    }
}
