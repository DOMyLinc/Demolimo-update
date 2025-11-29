<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    protected $fillable = [
        'name',
        'code',
        'flag',
        'is_rtl',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_rtl' => 'boolean',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class);
    }
}
