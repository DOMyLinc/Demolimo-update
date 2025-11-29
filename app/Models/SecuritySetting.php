<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SecuritySetting extends Model
{
    protected $fillable = [
        'max_login_attempts',
        'lockout_duration',
        'enable_login_lockout',
        'min_password_length',
        'require_uppercase',
        'require_lowercase',
        'require_numbers',
        'require_special_chars',
        'password_expiry_days',
        'session_timeout',
        'force_logout_on_password_change',
        'allow_concurrent_sessions',
        'max_concurrent_sessions',
        'enable_2fa',
        'force_2fa_for_admins',
        'recaptcha_enabled',
        'recaptcha_version',
        'recaptcha_site_key',
        'recaptcha_secret_key',
        'recaptcha_score_threshold',
        'recaptcha_on_login',
        'recaptcha_on_register',
        'recaptcha_on_forgot_password',
        'enable_ip_whitelist',
        'ip_whitelist',
        'enable_ip_blacklist',
        'ip_blacklist',
    ];

    protected $casts = [
        'enable_login_lockout' => 'boolean',
        'require_uppercase' => 'boolean',
        'require_lowercase' => 'boolean',
        'require_numbers' => 'boolean',
        'require_special_chars' => 'boolean',
        'force_logout_on_password_change' => 'boolean',
        'allow_concurrent_sessions' => 'boolean',
        'enable_2fa' => 'boolean',
        'force_2fa_for_admins' => 'boolean',
        'recaptcha_enabled' => 'boolean',
        'recaptcha_on_login' => 'boolean',
        'recaptcha_on_register' => 'boolean',
        'recaptcha_on_forgot_password' => 'boolean',
        'enable_ip_whitelist' => 'boolean',
        'ip_whitelist' => 'array',
        'enable_ip_blacklist' => 'boolean',
        'ip_blacklist' => 'array',
    ];

    /**
     * Get cached security settings
     */
    public static function getCached()
    {
        return Cache::remember('security_settings', 3600, function () {
            return self::first() ?? self::create([]);
        });
    }

    /**
     * Clear cache when updated
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('security_settings');
        });
    }

    /**
     * Validate password against policies
     */
    public function validatePassword(string $password): array
    {
        $errors = [];

        if (strlen($password) < $this->min_password_length) {
            $errors[] = "Password must be at least {$this->min_password_length} characters.";
        }

        if ($this->require_uppercase && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter.";
        }

        if ($this->require_lowercase && !preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter.";
        }

        if ($this->require_numbers && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number.";
        }

        if ($this->require_special_chars && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character.";
        }

        return $errors;
    }

    /**
     * Check if IP is allowed
     */
    public function isIpAllowed(string $ip): bool
    {
        // Check blacklist first
        if ($this->enable_ip_blacklist && in_array($ip, $this->ip_blacklist ?? [])) {
            return false;
        }

        // If whitelist is enabled, IP must be in whitelist
        if ($this->enable_ip_whitelist) {
            return in_array($ip, $this->ip_whitelist ?? []);
        }

        return true;
    }
}
