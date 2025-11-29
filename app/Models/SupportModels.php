<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'order',
        'is_active',
        'requires_login',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_login' => 'boolean',
    ];

    public function tickets()
    {
        return $this->hasMany(SupportTicket::class, 'topic_id');
    }

    public function assignedStaff()
    {
        return $this->belongsToMany(User::class, 'support_topic_staff')
            ->withPivot('can_assign', 'auto_assign')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }
}

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'topic_id',
        'name',
        'email',
        'subject',
        'message',
        'priority',
        'status',
        'assigned_to',
        'assigned_at',
        'first_response_at',
        'resolved_at',
        'closed_at',
        'last_reply_at',
        'ip_address',
        'user_agent',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
        'assigned_at' => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'last_reply_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            $ticket->ticket_number = static::generateTicketNumber();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function topic()
    {
        return $this->belongsTo(SupportTopic::class, 'topic_id');
    }

    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies()
    {
        return $this->hasMany(SupportTicketReply::class, 'ticket_id');
    }

    public function rating()
    {
        return $this->hasOne(TicketRating::class, 'ticket_id');
    }

    public static function generateTicketNumber()
    {
        $year = date('Y');
        $lastTicket = static::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastTicket ? (int) substr($lastTicket->ticket_number, -5) + 1 : 1;

        return 'TICKET-' . $year . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    public function assignTo($staffId)
    {
        $this->update([
            'assigned_to' => $staffId,
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);
    }

    public function markAsResolved()
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    public function markAsClosed()
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }

    public function reopen()
    {
        $this->update([
            'status' => 'open',
            'resolved_at' => null,
            'closed_at' => null,
        ]);
    }

    public function getSubmitterNameAttribute()
    {
        return $this->user ? $this->user->name : $this->name;
    }

    public function getSubmitterEmailAttribute()
    {
        return $this->user ? $this->user->email : $this->email;
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'assigned', 'in_progress', 'waiting_user']);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }
}

class SupportTicketReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'is_staff_reply',
        'is_internal_note',
        'attachments',
    ];

    protected $casts = [
        'is_staff_reply' => 'boolean',
        'is_internal_note' => 'boolean',
        'attachments' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($reply) {
            $ticket = $reply->ticket;

            // Update last reply time
            $ticket->update(['last_reply_at' => now()]);

            // Update first response time if this is first staff reply
            if ($reply->is_staff_reply && !$ticket->first_response_at) {
                $ticket->update(['first_response_at' => now()]);
            }

            // Update status if user replied
            if (!$reply->is_staff_reply && $ticket->status === 'waiting_user') {
                $ticket->update(['status' => 'in_progress']);
            }
        });
    }

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

class CannedResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'available_for_topics',
        'is_global',
        'usage_count',
    ];

    protected $casts = [
        'available_for_topics' => 'array',
        'is_global' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function incrementUsage()
    {
        $this->increment('usage_count');
    }
}

class TicketRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'rating',
        'feedback',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }
}
