<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Zipcode Models
class ZipcodeOwner extends Model
{
    use HasFactory;

    protected $fillable = [
        'zipcode',
        'country_code',
        'owner_id',
        'city',
        'state',
        'country',
        'latitude',
        'longitude',
        'is_active',
        'is_verified',
        'claimed_at',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'claimed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->hasMany(ZipcodeMember::class);
    }

    public function posts()
    {
        return $this->hasMany(ZipcodePost::class);
    }

    public function events()
    {
        return $this->hasMany(ZipcodeEvent::class);
    }

    public function settings()
    {
        return $this->hasOne(ZipcodeSetting::class);
    }
}

class ZipcodeMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'zipcode_owner_id',
        'user_id',
        'role',
        'is_approved',
        'joined_at',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'joined_at' => 'datetime',
    ];

    public function zipcode()
    {
        return $this->belongsTo(ZipcodeOwner::class, 'zipcode_owner_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

class ZipcodePost extends Model
{
    use HasFactory;

    protected $fillable = [
        'zipcode_owner_id',
        'user_id',
        'content',
        'media',
        'track_id',
        'is_pinned',
    ];

    protected $casts = [
        'media' => 'array',
        'is_pinned' => 'boolean',
    ];

    public function zipcode()
    {
        return $this->belongsTo(ZipcodeOwner::class, 'zipcode_owner_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function track()
    {
        return $this->belongsTo(Track::class);
    }
}

class ZipcodeEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'zipcode_owner_id',
        'user_id',
        'title',
        'description',
        'event_date',
        'location',
        'max_attendees',
    ];

    protected $casts = [
        'event_date' => 'datetime',
    ];

    public function zipcode()
    {
        return $this->belongsTo(ZipcodeOwner::class, 'zipcode_owner_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

class ZipcodeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'zipcode_owner_id',
        'allow_posts',
        'require_approval',
        'allow_events',
        'theme_color',
        'description',
        'cover_image',
    ];

    protected $casts = [
        'allow_posts' => 'boolean',
        'require_approval' => 'boolean',
        'allow_events' => 'boolean',
    ];

    public function zipcode()
    {
        return $this->belongsTo(ZipcodeOwner::class, 'zipcode_owner_id');
    }
}

// Studio Models
class StudioProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'bpm',
        'key',
        'time_signature',
        'project_data',
        'thumbnail',
        'last_opened_at',
    ];

    protected $casts = [
        'project_data' => 'array',
        'last_opened_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function patterns()
    {
        return $this->hasMany(StudioPattern::class, 'project_id');
    }

    public function tracks()
    {
        return $this->hasMany(StudioTrack::class, 'project_id');
    }
}

class StudioPattern extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'color',
        'notes',
        'length',
    ];

    protected $casts = [
        'notes' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(StudioProject::class, 'project_id');
    }
}

class StudioTrack extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'type',
        'volume',
        'pan',
        'muted',
        'solo',
        'effects',
        'color',
        'order',
    ];

    protected $casts = [
        'muted' => 'boolean',
        'solo' => 'boolean',
        'effects' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(StudioProject::class, 'project_id');
    }

    public function audioClips()
    {
        return $this->hasMany(StudioAudioClip::class, 'track_id');
    }
}

class StudioAudioClip extends Model
{
    use HasFactory;

    protected $fillable = [
        'track_id',
        'audio_file',
        'start_time',
        'duration',
        'offset',
        'pitch',
        'speed',
    ];

    public function track()
    {
        return $this->belongsTo(StudioTrack::class, 'track_id');
    }
}

class StudioInstrument extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'icon',
        'presets',
        'is_active',
    ];

    protected $casts = [
        'presets' => 'array',
        'is_active' => 'boolean',
    ];
}

class StudioEffect extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'parameters',
        'is_active',
    ];

    protected $casts = [
        'parameters' => 'array',
        'is_active' => 'boolean',
    ];
}
