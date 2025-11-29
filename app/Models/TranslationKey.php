<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TranslationKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'group',
        'description',
    ];

    /**
     * Get all translations for this key
     */
    public function translations(): HasMany
    {
        return $this->hasMany(TranslationValue::class);
    }

    /**
     * Get translation for specific locale
     */
    public function translation(?string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations()->where('locale', $locale)->first();
    }

    /**
     * Get translated value for specific locale
     */
    public function getValue(?string $locale = null): string
    {
        $translation = $this->translation($locale);
        return $translation ? $translation->value : $this->key;
    }

    /**
     * Scope: Filter by group
     */
    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope: Search by key or description
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('key', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
