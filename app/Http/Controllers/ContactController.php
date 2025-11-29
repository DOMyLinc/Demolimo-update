<?php

namespace App\Http\Controllers;

use App\Models\SupportTopic;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Show contact form (landing page)
     */
    public function index()
    {
        $topics = SupportTopic::active()->get();
        return view('contact.index', compact('topics'));
    }

    /**
     * Submit contact form
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'topic_id' => 'required|exists:support_topics,id',
            'name' => 'required_without:user_id|string|max:255',
            'email' => 'required_without:user_id|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
        ]);

        // Create ticket
        $ticket = SupportTicket::create([
            'user_id' => auth()->id(),
            'topic_id' => $validated['topic_id'],
            'name' => $validated['name'] ?? null,
            'email' => $validated['email'] ?? null,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'priority' => 'normal',
            'status' => 'open',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Handle attachments
        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('support/attachments', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                ];
            }
            $ticket->update(['attachments' => $attachments]);
        }

        // Auto-assign to staff if configured
        $this->autoAssignTicket($ticket);

        // Send confirmation email to user
        $this->sendConfirmationEmail($ticket);

        // Notify assigned staff
        if ($ticket->assigned_to) {
            $this->notifyStaff($ticket);
        }

        return back()->with('success', "Thank you! Your ticket #{$ticket->ticket_number} has been submitted. We'll get back to you soon!");
    }

    /**
     * View ticket status (for guests with ticket number and email)
     */
    public function viewTicket(Request $request)
    {
        $validated = $request->validate([
            'ticket_number' => 'required|string',
            'email' => 'required|email',
        ]);

        $ticket = SupportTicket::where('ticket_number', $validated['ticket_number'])
            ->where('email', $validated['email'])
            ->with([
                'topic',
                'replies' => function ($q) {
                    $q->where('is_internal_note', false)->orderBy('created_at');
                }
            ])
            ->first();

        if (!$ticket) {
            return back()->with('error', 'Ticket not found. Please check your ticket number and email.');
        }

        return view('contact.view-ticket', compact('ticket'));
    }

    /**
     * Reply to ticket (guest)
     */
    public function replyToTicket(Request $request, SupportTicket $ticket)
    {
        // Verify ownership
        if ($ticket->email !== $request->input('email')) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'required|string|min:10',
            'email' => 'required|email',
        ]);

        $reply = SupportTicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $ticket->user_id,
            'message' => $validated['message'],
            'is_staff_reply' => false,
        ]);

        // Notify assigned staff
        if ($ticket->assigned_to) {
            $this->notifyStaffOfReply($ticket, $reply);
        }

        return back()->with('success', 'Your reply has been submitted!');
    }

    /**
     * Auto-assign ticket to staff
     */
    protected function autoAssignTicket($ticket)
    {
        $topic = $ticket->topic;

        // Get staff with auto-assign enabled for this topic
        $staff = $topic->assignedStaff()
            ->wherePivot('auto_assign', true)
            ->inRandomOrder()
            ->first();

        if ($staff) {
            $ticket->assignTo($staff->id);
        }
    }

    /**
     * Send confirmation email
     */
    protected function sendConfirmationEmail($ticket)
    {
        // Mail::to($ticket->submitter_email)->send(new TicketSubmittedMail($ticket));
    }

    /**
     * Notify staff of new ticket
     */
    protected function notifyStaff($ticket)
    {
        // Mail::to($ticket->assignedStaff->email)->send(new NewTicketAssignedMail($ticket));
    }

    /**
     * Notify staff of new reply
     */
    protected function notifyStaffOfReply($ticket, $reply)
    {
        // Mail::to($ticket->assignedStaff->email)->send(new TicketReplyMail($ticket, $reply));
    }
}
