<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'flag',
        'is_rtl',
        'is_active',
        'is_default',
        'order',
    ];

    protected $casts = [
        'is_rtl' => 'boolean',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function translations()
    {
        return $this->hasMany(Translation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}

class Translation extends Model
{
    use HasFactory;

    protected $fillable = [
        'language_id',
        'group',
        'key',
        'value',
        'is_auto_translated',
    ];

    protected $casts = [
        'is_auto_translated' => 'boolean',
    ];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}

class CmsPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'status',
        'show_in_footer',
        'show_in_header',
        'footer_order',
        'header_order',
    ];

    protected $casts = [
        'show_in_footer' => 'boolean',
        'show_in_header' => 'boolean',
    ];

    public function translations()
    {
        return $this->hasMany(CmsPageTranslation::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeInFooter($query)
    {
        return $query->where('show_in_footer', true)->orderBy('footer_order');
    }

    public function scopeInHeader($query)
    {
        return $query->where('show_in_header', true)->orderBy('header_order');
    }

    /**
     * Get translated content
     */
    public function getTranslation($languageCode = null)
    {
        if (!$languageCode) {
            $languageCode = app()->getLocale();
        }

        $language = Language::where('code', $languageCode)->first();

        if (!$language) {
            return $this;
        }

        $translation = $this->translations()
            ->where('language_id', $language->id)
            ->first();

        if ($translation) {
            $this->title = $translation->title;
            $this->content = $translation->content;
            $this->meta_title = $translation->meta_title;
            $this->meta_description = $translation->meta_description;
        }

        return $this;
    }
}

class CmsPageTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'cms_page_id',
        'language_id',
        'title',
        'content',
        'meta_title',
        'meta_description',
    ];

    public function page()
    {
        return $this->belongsTo(CmsPage::class, 'cms_page_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}

class FooterSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value)
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
