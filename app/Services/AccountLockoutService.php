<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AccountLockoutService
{
    protected int $maxAttempts;
    protected int $lockoutDuration; // in minutes

    public function __construct()
    {
        $this->maxAttempts = config('security.lockout.max_attempts', 5);
        $this->lockoutDuration = config('security.lockout.duration', 30);
    }

    /**
     * Record a failed login attempt
     */
    public function recordFailedAttempt(string $email): void
    {
        $key = $this->getAttemptKey($email);
        $attempts = Cache::get($key, 0);
        $attempts++;

        Cache::put($key, $attempts, now()->addMinutes($this->lockoutDuration));

        // Lock account if max attempts reached
        if ($attempts >= $this->maxAttempts) {
            $this->lockAccount($email);
        }
    }

    /**
     * Lock an account
     */
    public function lockAccount(string $email): void
    {
        $lockKey = $this->getLockKey($email);
        $unlockAt = now()->addMinutes($this->lockoutDuration);

        Cache::put($lockKey, $unlockAt, $unlockAt);

        // Log security event
        app(SecurityLogger::class)->logAccountLockout($email, $unlockAt);

        // Send email notification
        $user = User::where('email', $email)->first();
        if ($user) {
            $this->sendLockoutNotification($user, $unlockAt);
        }
    }

    /**
     * Check if account is locked
     */
    public function isLocked(string $email): bool
    {
        $lockKey = $this->getLockKey($email);
        return Cache::has($lockKey);
    }

    /**
     * Get remaining lockout time in minutes
     */
    public function getRemainingLockoutTime(string $email): ?int
    {
        $lockKey = $this->getLockKey($email);
        $unlockAt = Cache::get($lockKey);

        if (!$unlockAt) {
            return null;
        }

        return max(0, now()->diffInMinutes($unlockAt, false));
    }

    /**
     * Unlock an account (admin action)
     */
    public function unlockAccount(string $email): void
    {
        Cache::forget($this->getLockKey($email));
        Cache::forget($this->getAttemptKey($email));

        app(SecurityLogger::class)->logAccountUnlock($email);
    }

    /**
     * Reset failed attempts on successful login
     */
    public function resetAttempts(string $email): void
    {
        Cache::forget($this->getAttemptKey($email));
    }

    /**
     * Get failed attempts count
     */
    public function getAttempts(string $email): int
    {
        return Cache::get($this->getAttemptKey($email), 0);
    }

    /**
     * Get attempt cache key
     */
    protected function getAttemptKey(string $email): string
    {
        return 'login_attempts:' . sha1($email);
    }

    /**
     * Get lock cache key
     */
    protected function getLockKey(string $email): string
    {
        return 'account_locked:' . sha1($email);
    }

    /**
     * Send lockout notification email
     */
    protected function sendLockoutNotification(User $user, Carbon $unlockAt): void
    {
        try {
            Mail::send('emails.account_locked', [
                'user' => $user,
                'unlockAt' => $unlockAt,
                'duration' => $this->lockoutDuration,
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Account Temporarily Locked - Security Alert');
            });
        } catch (\Exception $e) {
            \Log::error('Failed to send lockout email: ' . $e->getMessage());
        }
    }
}
