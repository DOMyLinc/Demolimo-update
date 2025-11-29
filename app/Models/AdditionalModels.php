<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MusicVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'track_id',
        'video_file',
        'thumbnail',
        'duration',
        'file_size',
        'quality',
        'views',
    ];

    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function incrementViews()
    {
        $this->increment('views');
    }
}

class ExclusiveContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'fan_club_id',
        'title',
        'description',
        'type',
        'content_type',
        'content_id',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function fanClub()
    {
        return $this->belongsTo(FanClub::class);
    }

    public function content()
    {
        return $this->morphTo();
    }
}

class SearchHistory extends Model
{
    use HasFactory;

    protected $table = 'search_history';

    protected $fillable = [
        'user_id',
        'query',
        'filters',
        'results_count',
        'ip_address',
    ];

    protected $casts = [
        'filters' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

class PlaylistFolder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'color',
        'order',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function playlists()
    {
        return $this->hasMany(Playlist::class, 'folder_id');
    }
}

class SmartPlaylist extends Model
{
    use HasFactory;

    protected $fillable = [
        'playlist_id',
        'rules',
        'max_tracks',
        'sort_by',
        'sort_direction',
        'auto_update',
        'last_updated_at',
    ];

    protected $casts = [
        'rules' => 'array',
        'auto_update' => 'boolean',
        'last_updated_at' => 'datetime',
    ];

    public function playlist()
    {
        return $this->belongsTo(Playlist::class);
    }
}

class ContentReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'reportable_type',
        'reportable_id',
        'reason',
        'description',
        'status',
        'reviewed_by',
        'admin_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportable()
    {
        return $this->morphTo();
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}

class DmcaTakedown extends Model
{
    use HasFactory;

    protected $fillable = [
        'claimant_name',
        'claimant_email',
        'claimant_company',
        'content_type',
        'content_id',
        'original_work_description',
        'infringement_description',
        'signature',
        'good_faith_statement',
        'accuracy_statement',
        'status',
        'processed_by',
        'processed_at',
        'admin_notes',
    ];

    protected $casts = [
        'good_faith_statement' => 'boolean',
        'accuracy_statement' => 'boolean',
        'processed_at' => 'datetime',
    ];

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}

class ArtistAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'plays',
        'unique_listeners',
        'new_followers',
        'playlist_adds',
        'revenue',
        'top_countries',
        'top_tracks',
        'demographics',
    ];

    protected $casts = [
        'date' => 'date',
        'revenue' => 'decimal:2',
        'top_countries' => 'array',
        'top_tracks' => 'array',
        'demographics' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

class AudioFingerprint extends Model
{
    use HasFactory;

    protected $fillable = [
        'track_id',
        'fingerprint',
        'duration_ms',
        'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function track()
    {
        return $this->belongsTo(Track::class);
    }
}

class FriendActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity_type',
        'activity_subject_type',
        'activity_subject_id',
        'activity_at',
    ];

    protected $casts = [
        'activity_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activitySubject()
    {
        return $this->morphTo();
    }
}

class GroupSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id',
        'session_code',
        'name',
        'current_track_id',
        'current_position',
        'is_active',
        'max_participants',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function currentTrack()
    {
        return $this->belongsTo(Track::class, 'current_track_id');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'group_session_participants', 'session_id')
            ->withPivot('joined_at', 'left_at')
            ->withTimestamps();
    }
}
