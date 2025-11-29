<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TicketPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'ticket_type_id',
        'ticket_code',
        'qr_code',
        'price_paid',
        'payment_status',
        'payment_method',
        'transaction_id',
        'is_checked_in',
        'checked_in_at',
    ];

    protected $casts = [
        'price_paid' => 'decimal:2',
        'is_checked_in' => 'boolean',
        'checked_in_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (!$ticket->ticket_code) {
                $ticket->ticket_code = static::generateTicketCode();
            }
        });

        static::created(function ($ticket) {
            $ticket->generateQRCode();
        });
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }

    /**
     * Scopes
     */
    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeCheckedIn($query)
    {
        return $query->where('is_checked_in', true);
    }

    /**
     * Methods
     */
    public static function generateTicketCode()
    {
        do {
            $code = 'TKT-' . strtoupper(Str::random(10));
        } while (static::where('ticket_code', $code)->exists());

        return $code;
    }

    public function generateQRCode()
    {
        $qrData = json_encode([
            'ticket_code' => $this->ticket_code,
            'event_id' => $this->event_id,
            'user_id' => $this->user_id,
            'ticket_type_id' => $this->ticket_type_id,
        ]);

        $qrCodePath = 'qr_codes/tickets/' . $this->ticket_code . '.png';
        $fullPath = storage_path('app/public/' . $qrCodePath);

        // Create directory if it doesn't exist
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Generate QR code
        QrCode::format('png')
            ->size(300)
            ->generate($qrData, $fullPath);

        $this->update(['qr_code' => '/storage/' . $qrCodePath]);
    }

    public function checkIn()
    {
        if ($this->is_checked_in) {
            throw new \Exception('Ticket already checked in');
        }

        if ($this->payment_status !== 'completed') {
            throw new \Exception('Payment not completed');
        }

        $this->update([
            'is_checked_in' => true,
            'checked_in_at' => now(),
        ]);

        return $this;
    }

    public function refund()
    {
        if ($this->payment_status === 'refunded') {
            throw new \Exception('Ticket already refunded');
        }

        // Decrement sold count
        $this->ticketType->decrementSold();

        // Update status
        $this->update(['payment_status' => 'refunded']);

        // Return money to wallet
        $wallet = $this->user->wallet ?? Wallet::create(['user_id' => $this->user_id]);
        $wallet->addBalance(
            $this->price_paid,
            "Refund for {$this->event->title} ticket",
            'refund',
            $this
        );

        return $this;
    }

    public function complete($paymentMethod = null, $transactionId = null)
    {
        $this->update([
            'payment_status' => 'completed',
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
        ]);

        // Increment sold count
        $this->ticketType->incrementSold();

        // Add earnings to event creator's wallet
        $commission = config('events.commission_percentage', 10);
        $platformFee = $this->price_paid * ($commission / 100);
        $artistAmount = $this->price_paid - $platformFee;

        $creatorWallet = $this->event->user->wallet ?? Wallet::create(['user_id' => $this->event->user_id]);
        $creatorWallet->addBalance(
            $artistAmount,
            "Ticket sale for {$this->event->title}",
            'ticket_sale',
            $this
        );

        return $this;
    }
}
