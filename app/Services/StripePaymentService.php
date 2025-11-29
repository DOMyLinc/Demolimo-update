<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Exception;

class StripePaymentService
{
    protected $gateway;

    public function __construct()
    {
        $this->gateway = PaymentGateway::where('slug', 'stripe')->where('is_active', true)->first();

        if ($this->gateway && isset($this->gateway->credentials['secret_key'])) {
            Stripe::setApiKey($this->gateway->credentials['secret_key']);
        }
    }

    public function isConfigured(): bool
    {
        return $this->gateway &&
            isset($this->gateway->credentials['secret_key']) &&
            !empty($this->gateway->credentials['secret_key']);
    }

    public function createPaymentIntent(PaymentTransaction $transaction, array $metadata = [])
    {
        if (!$this->isConfigured()) {
            throw new Exception('Stripe is not configured. Please add your API keys in the admin panel.');
        }

        try {
            $amount = ($transaction->amount + $transaction->gateway_fee) * 100; // Convert to cents

            $paymentIntent = PaymentIntent::create([
                'amount' => (int) $amount,
                'currency' => strtolower($transaction->currency),
                'description' => $this->getDescription($transaction),
                'metadata' => array_merge([
                    'transaction_id' => $transaction->transaction_id,
                    'user_id' => $transaction->user_id,
                    'type' => $transaction->type,
                ], $metadata),
                'statement_descriptor' => $this->gateway->settings['statement_descriptor'] ?? 'MUSIC PLATFORM',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            // Update transaction with Stripe payment intent ID
            $transaction->update([
                'payment_details' => json_encode([
                    'payment_intent_id' => $paymentIntent->id,
                    'client_secret' => $paymentIntent->client_secret,
                    'status' => $paymentIntent->status,
                ]),
            ]);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function confirmPayment(string $paymentIntentId)
    {
        if (!$this->isConfigured()) {
            throw new Exception('Stripe is not configured.');
        }

        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            return [
                'success' => $paymentIntent->status === 'succeeded',
                'status' => $paymentIntent->status,
                'payment_intent' => $paymentIntent,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function refundPayment(PaymentTransaction $transaction, $amount = null)
    {
        if (!$this->isConfigured()) {
            throw new Exception('Stripe is not configured.');
        }

        try {
            $paymentDetails = json_decode($transaction->payment_details, true);

            if (!isset($paymentDetails['payment_intent_id'])) {
                throw new Exception('Payment intent ID not found.');
            }

            $refundData = [
                'payment_intent' => $paymentDetails['payment_intent_id'],
            ];

            if ($amount) {
                $refundData['amount'] = (int) ($amount * 100); // Convert to cents
            }

            $refund = Refund::create($refundData);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'status' => $refund->status,
                'amount' => $refund->amount / 100,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function refund(string $paymentIntentId, $amount = null)
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Stripe is not configured.'];
        }

        try {
            $refundData = ['payment_intent' => $paymentIntentId];

            if ($amount) {
                $refundData['amount'] = (int) ($amount * 100); // Convert to cents
            }

            $refund = Refund::create($refundData);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'status' => $refund->status,
                'amount' => $refund->amount / 100,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function handleWebhook(array $payload)
    {
        if (!$this->isConfigured()) {
            throw new Exception('Stripe is not configured.');
        }

        $event = $payload;

        switch ($event['type']) {
            case 'payment_intent.succeeded':
                return $this->handlePaymentSucceeded($event['data']['object']);

            case 'payment_intent.payment_failed':
                return $this->handlePaymentFailed($event['data']['object']);

            case 'charge.refunded':
                return $this->handleRefund($event['data']['object']);

            default:
                return ['success' => true, 'message' => 'Unhandled event type'];
        }
    }

    protected function handlePaymentSucceeded($paymentIntent)
    {
        $transaction = PaymentTransaction::where('payment_details->payment_intent_id', $paymentIntent['id'])->first();

        if ($transaction) {
            $transaction->markAsCompleted();

            // Process the purchase (create sale record, update wallet, etc.)
            $this->processPurchase($transaction);

            return ['success' => true, 'message' => 'Payment processed successfully'];
        }

        return ['success' => false, 'message' => 'Transaction not found'];
    }

    protected function handlePaymentFailed($paymentIntent)
    {
        $transaction = PaymentTransaction::where('payment_details->payment_intent_id', $paymentIntent['id'])->first();

        if ($transaction) {
            $transaction->markAsFailed('Payment failed: ' . ($paymentIntent['last_payment_error']['message'] ?? 'Unknown error'));
            return ['success' => true, 'message' => 'Payment failure recorded'];
        }

        return ['success' => false, 'message' => 'Transaction not found'];
    }

    protected function handleRefund($charge)
    {
        // Handle refund webhook
        return ['success' => true, 'message' => 'Refund processed'];
    }

    protected function processPurchase(PaymentTransaction $transaction)
    {
        // This will be called after successful payment
        // The actual purchase processing is handled in the PurchaseController
        // This is just a placeholder for any additional processing needed
    }

    protected function getDescription(PaymentTransaction $transaction): string
    {
        $descriptions = [
            'track_purchase' => 'Track Purchase',
            'album_purchase' => 'Album Purchase',
            'donation' => 'Donation',
            'tip' => 'Tip',
            'subscription' => 'Subscription',
            'points' => 'Points Purchase',
        ];

        return $descriptions[$transaction->type] ?? 'Purchase';
    }

    public function getPublishableKey(): ?string
    {
        return $this->gateway->credentials['publishable_key'] ?? null;
    }
}
