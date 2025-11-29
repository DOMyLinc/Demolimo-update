<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTopic;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\CannedResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SupportManagementController extends Controller
{
    /**
     * Support Topics Management
     */
    public function topics()
    {
        $topics = SupportTopic::withCount(['tickets', 'assignedStaff'])
            ->orderBy('order')
            ->get();

        return view('admin.support.topics', compact('topics'));
    }

    /**
     * Create topic
     */
    public function createTopic()
    {
        return view('admin.support.create-topic');
    }

    /**
     * Store topic
     */
    public function storeTopic(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'order' => 'integer|min:0',
            'requires_login' => 'boolean',
        ]);

        $topic = SupportTopic::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'icon' => $validated['icon'] ?? '📧',
            'order' => $validated['order'] ?? 0,
            'requires_login' => $validated['requires_login'] ?? false,
            'is_active' => true,
        ]);

        return redirect()->route('admin.support.topics')
            ->with('success', 'Topic created successfully!');
    }

    /**
     * Edit topic
     */
    public function editTopic(SupportTopic $topic)
    {
        $staff = User::whereIn('role', ['admin', 'moderator', 'support'])
            ->get();

        $assignedStaff = $topic->assignedStaff()->pluck('users.id')->toArray();

        return view('admin.support.edit-topic', compact('topic', 'staff', 'assignedStaff'));
    }

    /**
     * Update topic
     */
    public function updateTopic(Request $request, SupportTopic $topic)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
            'requires_login' => 'boolean',
        ]);

        $topic->update($validated);

        return back()->with('success', 'Topic updated successfully!');
    }

    /**
     * Assign staff to topic
     */
    public function assignStaffToTopic(Request $request, SupportTopic $topic)
    {
        $validated = $request->validate([
            'staff_ids' => 'required|array',
            'staff_ids.*' => 'exists:users,id',
            'auto_assign' => 'array',
        ]);

        // Sync staff
        $syncData = [];
        foreach ($validated['staff_ids'] as $staffId) {
            $syncData[$staffId] = [
                'can_assign' => true,
                'auto_assign' => in_array($staffId, $validated['auto_assign'] ?? []),
            ];
        }

        $topic->assignedStaff()->sync($syncData);

        return back()->with('success', 'Staff assigned successfully!');
    }

    /**
     * Delete topic
     */
    public function deleteTopic(SupportTopic $topic)
    {
        if ($topic->tickets()->count() > 0) {
            return back()->with('error', 'Cannot delete topic with existing tickets.');
        }

        $topic->delete();

        return redirect()->route('admin.support.topics')
            ->with('success', 'Topic deleted successfully!');
    }

    /**
     * Tickets Dashboard
     */
    public function tickets(Request $request)
    {
        $query = SupportTicket::with(['user', 'topic', 'assignedStaff']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('topic')) {
            $query->where('topic_id', $request->topic);
        }

        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tickets = $query->latest()->paginate(50);

        $stats = [
            'open' => SupportTicket::where('status', 'open')->count(),
            'assigned' => SupportTicket::where('status', 'assigned')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'waiting_user' => SupportTicket::where('status', 'waiting_user')->count(),
            'resolved' => SupportTicket::where('status', 'resolved')->count(),
            'unassigned' => SupportTicket::whereNull('assigned_to')->count(),
        ];

        $topics = SupportTopic::active()->get();
        $staff = User::whereIn('role', ['admin', 'moderator', 'support'])->get();

        return view('admin.support.tickets', compact('tickets', 'stats', 'topics', 'staff'));
    }

    /**
     * View ticket details
     */
    public function viewTicket(SupportTicket $ticket)
    {
        $ticket->load([
            'user',
            'topic',
            'assignedStaff',
            'replies' => function ($q) {
                $q->with('user')->orderBy('created_at');
            },
            'rating'
        ]);

        $staff = User::whereIn('role', ['admin', 'moderator', 'support'])->get();
        $cannedResponses = CannedResponse::where('is_global', true)
            ->orWhere('user_id', auth()->id())
            ->get();

        return view('admin.support.ticket-details', compact('ticket', 'staff', 'cannedResponses'));
    }

    /**
     * Assign ticket to staff
     */
    public function assignTicket(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:users,id',
        ]);

        $ticket->assignTo($validated['staff_id']);

        return back()->with('success', 'Ticket assigned successfully!');
    }

    /**
     * Reply to ticket
     */
    public function replyToTicket(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string|min:10',
            'is_internal_note' => 'boolean',
            'change_status' => 'nullable|in:open,in_progress,waiting_user,resolved,closed',
        ]);

        $reply = SupportTicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'is_staff_reply' => true,
            'is_internal_note' => $validated['is_internal_note'] ?? false,
        ]);

        // Update ticket status if requested
        if ($request->filled('change_status')) {
            $ticket->update(['status' => $validated['change_status']]);

            if ($validated['change_status'] === 'resolved') {
                $ticket->markAsResolved();
            } elseif ($validated['change_status'] === 'closed') {
                $ticket->markAsClosed();
            }
        }

        // Send email to user if not internal note
        if (!$reply->is_internal_note) {
            $this->notifyUserOfReply($ticket, $reply);
        }

        return back()->with('success', 'Reply sent successfully!');
    }

    /**
     * Update ticket status
     */
    public function updateTicketStatus(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,assigned,in_progress,waiting_user,resolved,closed',
        ]);

        $ticket->update(['status' => $validated['status']]);

        if ($validated['status'] === 'resolved') {
            $ticket->markAsResolved();
        } elseif ($validated['status'] === 'closed') {
            $ticket->markAsClosed();
        }

        return back()->with('success', 'Ticket status updated!');
    }

    /**
     * Update ticket priority
     */
    public function updateTicketPriority(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'priority' => 'required|in:low,normal,high,urgent',
        ]);

        $ticket->update(['priority' => $validated['priority']]);

        return back()->with('success', 'Priority updated!');
    }

    /**
     * Canned Responses Management
     */
    public function cannedResponses()
    {
        $responses = CannedResponse::with('user')
            ->latest()
            ->paginate(20);

        return view('admin.support.canned-responses', compact('responses'));
    }

    /**
     * Create canned response
     */
    public function storeCannedResponse(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_global' => 'boolean',
            'available_for_topics' => 'nullable|array',
        ]);

        CannedResponse::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_global' => $validated['is_global'] ?? false,
            'available_for_topics' => $validated['available_for_topics'],
        ]);

        return back()->with('success', 'Canned response created!');
    }

    /**
     * Support Statistics
     */
    public function statistics()
    {
        $stats = [
            'total_tickets' => SupportTicket::count(),
            'open_tickets' => SupportTicket::open()->count(),
            'resolved_today' => SupportTicket::whereDate('resolved_at', today())->count(),
            'average_response_time' => $this->calculateAverageResponseTime(),
            'average_resolution_time' => $this->calculateAverageResolutionTime(),
            'satisfaction_rating' => $this->calculateSatisfactionRating(),
        ];

        $ticketsByTopic = SupportTicket::select('topic_id', DB::raw('COUNT(*) as count'))
            ->groupBy('topic_id')
            ->with('topic')
            ->get();

        $ticketsByStatus = SupportTicket::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        $staffPerformance = $this->getStaffPerformance();

        return view('admin.support.statistics', compact('stats', 'ticketsByTopic', 'ticketsByStatus', 'staffPerformance'));
    }

    /**
     * Calculate average response time
     */
    protected function calculateAverageResponseTime()
    {
        $avg = SupportTicket::whereNotNull('first_response_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, first_response_at)) as avg_minutes')
            ->value('avg_minutes');

        return round($avg ?? 0, 2);
    }

    /**
     * Calculate average resolution time
     */
    protected function calculateAverageResolutionTime()
    {
        $avg = SupportTicket::whereNotNull('resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
            ->value('avg_hours');

        return round($avg ?? 0, 2);
    }

    /**
     * Calculate satisfaction rating
     */
    protected function calculateSatisfactionRating()
    {
        return DB::table('ticket_ratings')->avg('rating') ?? 0;
    }

    /**
     * Get staff performance
     */
    protected function getStaffPerformance()
    {
        return User::whereIn('role', ['admin', 'moderator', 'support'])
            ->withCount([
                'assignedTickets as total_assigned',
                'assignedTickets as resolved' => function ($q) {
                    $q->where('status', 'resolved');
                },
            ])
            ->get();
    }

    /**
     * Notify user of reply
     */
    protected function notifyUserOfReply($ticket, $reply)
    {
        // Mail::to($ticket->submitter_email)->send(new StaffRepliedMail($ticket, $reply));
    }
}
