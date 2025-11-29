<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketPurchase;
use Illuminate\Http\Request;

class EventManagementController extends Controller
{
    public function index()
    {
        $events = Event::with(['user', 'ticketTypes'])
            ->latest()
            ->paginate(20);

        $stats = [
            'total_events' => Event::count(),
            'published_events' => Event::where('status', 'published')->count(),
            'upcoming_events' => Event::upcoming()->count(),
            'total_tickets_sold' => Event::sum('tickets_sold'),
            'total_revenue' => TicketPurchase::completed()->sum('price_paid'),
        ];

        return view('admin.events.index', compact('events', 'stats'));
    }

    public function show(Event $event)
    {
        $event->load(['user', 'ticketTypes.purchases', 'performers']);

        $stats = [
            'total_tickets_sold' => $event->tickets_sold,
            'total_revenue' => $event->total_revenue,
            'checked_in' => $event->purchases()->checkedIn()->count(),
            'pending_payments' => $event->purchases()->pending()->count(),
        ];

        return view('admin.events.show', compact('event', 'stats'));
    }

    public function approve(Event $event)
    {
        $event->publish();

        return back()->with('success', 'Event published successfully!');
    }

    public function reject(Event $event)
    {
        $event->update(['status' => 'rejected']);

        return back()->with('success', 'Event rejected.');
    }

    public function cancel(Event $event)
    {
        $event->cancel();

        // Refund all tickets
        $tickets = $event->purchases()->completed()->get();

        foreach ($tickets as $ticket) {
            try {
                $ticket->refund();
            } catch (\Exception $e) {
                // Log error but continue
            }
        }

        return back()->with('success', 'Event cancelled and all tickets refunded.');
    }

    public function feature(Event $event)
    {
        $event->update(['is_featured' => !$event->is_featured]);

        $message = $event->is_featured ? 'Event featured!' : 'Event unfeatured.';

        return back()->with('success', $message);
    }

    public function tickets()
    {
        $tickets = TicketPurchase::with(['user', 'event', 'ticketType'])
            ->latest()
            ->paginate(50);

        $stats = [
            'total_tickets' => TicketPurchase::count(),
            'completed' => TicketPurchase::completed()->count(),
            'pending' => TicketPurchase::pending()->count(),
            'checked_in' => TicketPurchase::checkedIn()->count(),
        ];

        return view('admin.events.tickets', compact('tickets', 'stats'));
    }

    public function checkInTicket(TicketPurchase $ticket)
    {
        try {
            $ticket->checkIn();
            return back()->with('success', 'Ticket checked in successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function refundTicket(TicketPurchase $ticket)
    {
        try {
            $ticket->refund();
            return back()->with('success', 'Ticket refunded successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function analytics()
    {
        $monthlyRevenue = TicketPurchase::completed()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(price_paid) as total, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        $topEvents = Event::withCount([
            'purchases as tickets_sold' => function ($query) {
                $query->where('payment_status', 'completed');
            }
        ])
            ->withSum([
                'purchases as revenue' => function ($query) {
                    $query->where('payment_status', 'completed');
                }
            ], 'price_paid')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $eventsByType = Event::selectRaw('event_type, COUNT(*) as count')
            ->groupBy('event_type')
            ->get();

        return view('admin.events.analytics', compact('monthlyRevenue', 'topEvents', 'eventsByType'));
    }

    public function settings()
    {
        return view('admin.events.settings');
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'require_approval' => 'boolean',
            'allow_refunds' => 'boolean',
            'refund_deadline_hours' => 'required|integer|min:0',
        ]);

        foreach ($validated as $key => $value) {
            config(['events.' . $key => $value]);
            // Save to database or config file
        }

        return back()->with('success', 'Event settings updated!');
    }
}
