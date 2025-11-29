<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SecurityLogger
{
    /**
     * Log failed login attempt
     */
    public function logFailedLogin(string $email, string $ip): void
    {
        $this->log('failed_login', [
            'email' => $email,
            'ip' => $ip,
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log account lockout
     */
    public function logAccountLockout(string $email, Carbon $unlockAt): void
    {
        $this->log('account_lockout', [
            'email' => $email,
            'unlock_at' => $unlockAt->toDateTimeString(),
            'ip' => request()->ip(),
        ], 'warning');
    }

    /**
     * Log account unlock
     */
    public function logAccountUnlock(string $email): void
    {
        $this->log('account_unlock', [
            'email' => $email,
            'unlocked_by' => auth()->user()?->email ?? 'system',
        ]);
    }

    /**
     * Log suspicious file upload
     */
    public function logSuspiciousFileUpload(string $filename, string $reason): void
    {
        $this->log('suspicious_file_upload', [
            'filename' => $filename,
            'reason' => $reason,
            'user' => auth()->user()?->email ?? 'guest',
            'ip' => request()->ip(),
        ], 'warning');
    }

    /**
     * Log SQL injection attempt
     */
    public function logSqlInjectionAttempt(string $input, string $field = null): void
    {
        $this->log('sql_injection_attempt', [
            'input' => substr($input, 0, 200),
            'field' => $field,
            'user' => auth()->user()?->email ?? 'guest',
            'ip' => request()->ip(),
            'url' => request()->fullUrl(),
        ], 'critical');
    }

    /**
     * Log XSS attempt
     */
    public function logXssAttempt(string $input, string $field = null): void
    {
        $this->log('xss_attempt', [
            'input' => substr($input, 0, 200),
            'field' => $field,
            'user' => auth()->user()?->email ?? 'guest',
            'ip' => request()->ip(),
            'url' => request()->fullUrl(),
        ], 'critical');
    }

    /**
     * Log password change
     */
    public function logPasswordChange(string $email): void
    {
        $this->log('password_change', [
            'email' => $email,
            'ip' => request()->ip(),
        ]);
    }

    /**
     * Log 2FA enabled
     */
    public function logTwoFactorEnabled(string $email): void
    {
        $this->log('two_factor_enabled', [
            'email' => $email,
            'ip' => request()->ip(),
        ]);
    }

    /**
     * Log 2FA disabled
     */
    public function logTwoFactorDisabled(string $email): void
    {
        $this->log('two_factor_disabled', [
            'email' => $email,
            'ip' => request()->ip(),
        ], 'warning');
    }

    /**
     * Log API abuse
     */
    public function logApiAbuse(string $endpoint, int $attempts): void
    {
        $this->log('api_abuse', [
            'endpoint' => $endpoint,
            'attempts' => $attempts,
            'ip' => request()->ip(),
        ], 'warning');
    }

    /**
     * Generic security log method
     */
    protected function log(string $event, array $data, string $level = 'info'): void
    {
        $logData = array_merge([
            'event' => $event,
            'timestamp' => now()->toDateTimeString(),
        ], $data);

        Log::channel('security')->{$level}($event, $logData);

        // Also store in database if table exists
        $this->storeInDatabase($event, $logData, $level);
    }

    /**
     * Store security event in database
     */
    protected function storeInDatabase(string $event, array $data, string $level): void
    {
        try {
            \DB::table('security_logs')->insert([
                'event' => $event,
                'level' => $level,
                'data' => json_encode($data),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id' => auth()->id(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Table might not exist yet, silently fail
        }
    }
}
