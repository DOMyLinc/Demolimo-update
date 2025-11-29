<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SocialProvider extends Model
{
    protected $fillable = [
        'provider',
        'name',
        'enabled',
        'client_id',
        'client_secret',
        'redirect_url',
        'scopes',
        'additional_config',
        'icon',
        'button_color',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'scopes' => 'array',
        'additional_config' => 'array',
    ];

    /**
     * Encrypt client_id when setting
     */
    public function setClientIdAttribute($value)
    {
        $this->attributes['client_id'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Decrypt client_id when getting
     */
    public function getClientIdAttribute($value)
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Encrypt client_secret when setting
     */
    public function setClientSecretAttribute($value)
    {
        $this->attributes['client_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Decrypt client_secret when getting
     */
    public function getClientSecretAttribute($value)
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get all enabled providers
     */
    public static function getEnabled()
    {
        return self::where('enabled', true)->get();
    }

    /**
     * Check if provider is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->client_id) && !empty($this->client_secret);
    }

    /**
     * Get redirect URL
     */
    public function getRedirectUrlAttribute($value)
    {
        return $value ?? url("/auth/{$this->provider}/callback");
    }
}
