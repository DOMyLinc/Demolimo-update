<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'car_mode_enabled',
        'sleep_timer_minutes',
        'data_saver_mode',
        'audio_quality_mobile',
        'download_over_wifi_only',
        'shake_to_skip',
        'shake_sensitivity',
    ];

    protected $casts = [
        'car_mode_enabled' => 'boolean',
        'data_saver_mode' => 'boolean',
        'download_over_wifi_only' => 'boolean',
        'shake_to_skip' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'category_id',
        'tags',
        'views',
        'reading_time',
        'meta_title',
        'meta_description',
        'published_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'published_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function comments()
    {
        return $this->hasMany(BlogComment::class, 'post_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function incrementViews()
    {
        $this->increment('views');
    }
}

class BlogCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'order',
    ];

    public function posts()
    {
        return $this->hasMany(BlogPost::class, 'category_id');
    }
}

class BlogComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'author_name',
        'author_email',
        'content',
        'is_approved',
        'parent_id',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    public function post()
    {
        return $this->belongsTo(BlogPost::class, 'post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(BlogComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(BlogComment::class, 'parent_id');
    }
}

class ApiApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'client_id',
        'client_secret',
        'redirect_uris',
        'website_url',
        'is_approved',
        'is_active',
        'rate_limit',
    ];

    protected $casts = [
        'redirect_uris' => 'array',
        'is_approved' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'client_secret',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateCredentials()
    {
        return [
            'client_id' => 'app_' . \Illuminate\Support\Str::random(32),
            'client_secret' => \Illuminate\Support\Str::random(64),
        ];
    }
}

class Webhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'url',
        'secret',
        'events',
        'is_active',
        'failed_attempts',
        'last_triggered_at',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->hasMany(WebhookLog::class);
    }
}

class WebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'webhook_id',
        'event',
        'payload',
        'response_code',
        'response_body',
        'success',
    ];

    protected $casts = [
        'success' => 'boolean',
    ];

    public function webhook()
    {
        return $this->belongsTo(Webhook::class);
    }
}

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public static function log($action, $description, $subject = null, $properties = [])
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
