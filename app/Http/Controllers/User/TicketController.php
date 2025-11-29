<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\TicketPurchase;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function purchase(Request $request, Event $event, TicketType $ticketType)
    {
        // Validate ticket availability
        if (!$ticketType->isAvailable()) {
            return back()->with('error', 'This ticket type is no longer available');
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
            'payment_method' => 'required|in:stripe,paypal,wallet',
        ]);

        // Check if enough tickets available
        if ($ticketType->remainingTickets() < $validated['quantity']) {
            return back()->with('error', 'Not enough tickets available');
        }

        $totalAmount = $ticketType->price * $validated['quantity'];

        // Process payment
        try {
            $paymentResult = $this->paymentService->processPayment([
                'amount' => $totalAmount,
                'method' => $validated['payment_method'],
                'description' => "Ticket purchase for {$event->title}",
                'user_id' => auth()->id(),
            ]);

            if ($paymentResult['status'] === 'success') {
                // Create ticket purchases
                for ($i = 0; $i < $validated['quantity']; $i++) {
                    TicketPurchase::create([
                        'user_id' => auth()->id(),
                        'event_id' => $event->id,
                        'ticket_type_id' => $ticketType->id,
                        'price_paid' => $ticketType->price,
                        'payment_status' => 'completed',
                        'payment_method' => $validated['payment_method'],
                        'transaction_id' => $paymentResult['transaction_id'],
                    ]);
                }

                // Update ticket counts
                $ticketType->increment('quantity_sold', $validated['quantity']);
                $event->increment('tickets_sold', $validated['quantity']);

                return redirect()->route('user.events.my-tickets')
                    ->with('success', 'Tickets purchased successfully! Check your email for confirmation.');
            }

            return back()->with('error', 'Payment failed. Please try again.');

        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred during payment: ' . $e->getMessage());
        }
    }

    public function downloadTicket(TicketPurchase $ticket)
    {
        $this->authorize('view', $ticket);

        // Generate PDF ticket (you would implement this)
        return view('user.tickets.download', compact('ticket'));
    }

    public function addTicketType(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'sale_start' => 'nullable|date',
            'sale_end' => 'nullable|date|after:sale_start',
        ]);

        $validated['event_id'] = $event->id;

        TicketType::create($validated);

        return back()->with('success', 'Ticket type added successfully');
    }

    public function updateTicketType(Request $request, TicketType $ticketType)
    {
        $this->authorize('update', $ticketType->event);

        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:' . $ticketType->quantity_sold,
            'sale_start' => 'nullable|date',
            'sale_end' => 'nullable|date|after:sale_start',
            'is_active' => 'boolean',
        ]);

        $ticketType->update($validated);

        return back()->with('success', 'Ticket type updated successfully');
    }
}
