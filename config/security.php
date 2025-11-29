<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'login' => env('RATE_LIMIT_LOGIN', 5),
        'register' => env('RATE_LIMIT_REGISTER', 3),
        'password_reset' => env('RATE_LIMIT_PASSWORD_RESET', 3),
        'api' => env('API_RATE_LIMIT', 60),
        'api_guest' => env('API_RATE_LIMIT_GUEST', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Account Lockout
    |--------------------------------------------------------------------------
    */
    'lockout' => [
        'max_attempts' => env('ACCOUNT_LOCKOUT_ATTEMPTS', 5),
        'duration' => env('ACCOUNT_LOCKOUT_DURATION', 30), // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Requirements
    |--------------------------------------------------------------------------
    */
    'password' => [
        'min_length' => env('PASSWORD_MIN_LENGTH', 8),
        'require_uppercase' => env('PASSWORD_REQUIRE_UPPERCASE', true),
        'require_lowercase' => env('PASSWORD_REQUIRE_LOWERCASE', true),
        'require_numbers' => env('PASSWORD_REQUIRE_NUMBERS', true),
        'require_special_chars' => env('PASSWORD_REQUIRE_SPECIAL_CHARS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication
    |--------------------------------------------------------------------------
    */
    'two_factor' => [
        'enabled' => env('TWO_FACTOR_ENABLED', false),
        'issuer' => env('TWO_FACTOR_ISSUER', config('app.name')),
        'backup_codes_count' => 8,
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    */
    'session' => [
        'timeout' => env('SESSION_TIMEOUT', 120), // minutes
        'regenerate_on_login' => true,
        'strict_mode' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Logging
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => env('SECURITY_LOGGING_ENABLED', true),
        'log_failed_logins' => true,
        'log_password_changes' => true,
        'log_suspicious_activity' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Intrusion Detection
    |--------------------------------------------------------------------------
    */
    'intrusion_detection' => [
        'enabled' => env('INTRUSION_DETECTION_ENABLED', true),
        'auto_block' => env('AUTO_BLOCK_SUSPICIOUS_IPS', true),
        'block_duration' => 60, // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Management
    |--------------------------------------------------------------------------
    */
    'ip' => [
        'whitelist' => explode(',', env('IP_WHITELIST', '')),
        'blacklist' => explode(',', env('IP_BLACKLIST', '')),
    ],
];
