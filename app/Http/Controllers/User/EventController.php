<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\TicketPurchase;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index()
    {
        $upcomingEvents = Event::published()
            ->upcoming()
            ->with(['user', 'ticketTypes'])
            ->orderBy('start_date')
            ->paginate(12);

        $featuredEvents = Event::published()
            ->featured()
            ->upcoming()
            ->with(['user', 'ticketTypes'])
            ->limit(3)
            ->get();

        return view('user.events.index', compact('upcomingEvents', 'featuredEvents'));
    }

    public function show(Event $event)
    {
        $event->load(['user', 'ticketTypes', 'performers']);

        $relatedEvents = Event::published()
            ->upcoming()
            ->where('id', '!=', $event->id)
            ->where(function ($query) use ($event) {
                $query->where('city', $event->city)
                    ->orWhere('event_type', $event->event_type);
            })
            ->limit(4)
            ->get();

        return view('user.events.show', compact('event', 'relatedEvents'));
    }

    public function myEvents()
    {
        $myEvents = Event::where('user_id', Auth::id())
            ->with(['ticketTypes', 'purchases'])
            ->latest()
            ->paginate(10);

        return view('user.events.my-events', compact('myEvents'));
    }

    public function myTickets()
    {
        $tickets = TicketPurchase::where('user_id', Auth::id())
            ->with(['event', 'ticketType'])
            ->latest()
            ->paginate(10);

        return view('user.events.my-tickets', compact('tickets'));
    }

    public function create()
    {
        // Check if user can create events (Pro/Artist only)
        if (!Auth::user()->isPro() && !Auth::user()->is_verified) {
            return redirect()->back()->with('error', 'Only Pro users and verified artists can create events.');
        }

        return view('user.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'venue' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
            'cover_image' => 'nullable|image|max:5120',
            'event_type' => 'required|in:concert,festival,workshop,meetup',
            'is_online' => 'boolean',
            'stream_url' => 'nullable|url',
            'capacity' => 'nullable|integer|min:1',
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('events', 'public');
        }

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'draft';

        $event = Event::create($validated);

        return redirect()->route('user.events.edit', $event)
            ->with('success', 'Event created! Now add ticket types.');
    }

    public function edit(Event $event)
    {
        $this->authorize('update', $event);

        $event->load('ticketTypes');

        return view('user.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'venue' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'cover_image' => 'nullable|image|max:5120',
            'event_type' => 'required|in:concert,festival,workshop,meetup',
            'is_online' => 'boolean',
            'stream_url' => 'nullable|url',
            'capacity' => 'nullable|integer|min:1',
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('events', 'public');
        }

        $event->update($validated);

        return back()->with('success', 'Event updated successfully!');
    }

    public function addTicketType(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'sale_start' => 'nullable|date',
            'sale_end' => 'nullable|date|after:sale_start',
        ]);

        $event->ticketTypes()->create($validated);

        return back()->with('success', 'Ticket type added successfully!');
    }

    public function purchaseTicket(Request $request, Event $event, TicketType $ticketType)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        $quantity = $validated['quantity'];

        // Check if tickets are available
        if (!$ticketType->canPurchase($quantity)) {
            return back()->with('error', 'Tickets not available for purchase.');
        }

        $totalPrice = $ticketType->price * $quantity;

        // Check wallet balance
        $wallet = Auth::user()->wallet ?? Wallet::create(['user_id' => Auth::id()]);

        if ($wallet->available_balance < $totalPrice) {
            return back()->with('error', 'Insufficient wallet balance. Please add funds.');
        }

        // Create tickets
        $tickets = [];
        for ($i = 0; $i < $quantity; $i++) {
            $ticket = TicketPurchase::create([
                'user_id' => Auth::id(),
                'event_id' => $event->id,
                'ticket_type_id' => $ticketType->id,
                'price_paid' => $ticketType->price,
                'payment_status' => 'pending',
            ]);

            $tickets[] = $ticket;
        }

        // Deduct from wallet
        $wallet->deductBalance(
            $totalPrice,
            "Purchased {$quantity} ticket(s) for {$event->title}",
            'ticket_purchase'
        );

        // Complete all tickets
        foreach ($tickets as $ticket) {
            $ticket->complete('wallet', 'WALLET-' . time());
        }

        return redirect()->route('user.events.my-tickets')
            ->with('success', "Successfully purchased {$quantity} ticket(s)!");
    }

    public function downloadTicket(TicketPurchase $ticket)
    {
        $this->authorize('view', $ticket);

        // Generate PDF or return view for printing
        return view('user.events.ticket-download', compact('ticket'));
    }
}
