<?php

namespace App\Services;

use App\Models\PaymentGateway;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Payments\CapturesRefundRequest;
use Exception;

class PayPalPaymentService
{
    protected $gateway;
    protected $client;

    public function __construct()
    {
        $this->gateway = PaymentGateway::where('slug', 'paypal')->where('is_active', true)->first();

        if ($this->isConfigured()) {
            $this->initializeClient();
        }
    }

    protected function initializeClient()
    {
        $clientId = $this->gateway->credentials['client_id'];
        $clientSecret = $this->gateway->credentials['client_secret'];
        $mode = $this->gateway->settings['mode'] ?? 'sandbox';

        $environment = $mode === 'live'
            ? new ProductionEnvironment($clientId, $clientSecret)
            : new SandboxEnvironment($clientId, $clientSecret);

        $this->client = new PayPalHttpClient($environment);
    }

    public function isConfigured(): bool
    {
        return $this->gateway &&
            isset($this->gateway->credentials['client_id']) &&
            isset($this->gateway->credentials['client_secret']) &&
            !empty($this->gateway->credentials['client_id']) &&
            !empty($this->gateway->credentials['client_secret']);
    }

    public function refund(string $captureId, $amount = null)
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'PayPal is not configured.'];
        }

        try {
            $request = new CapturesRefundRequest($captureId);

            if ($amount) {
                $request->body = [
                    'amount' => [
                        'value' => number_format($amount, 2, '.', ''),
                        'currency_code' => 'USD'
                    ]
                ];
            }

            $response = $this->client->execute($request);

            return [
                'success' => true,
                'refund_id' => $response->result->id,
                'status' => $response->result->status,
                'amount' => $response->result->amount->value ?? $amount,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function createOrder($transaction, $returnUrl, $cancelUrl)
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'PayPal is not configured.'];
        }

        try {
            $request = new OrdersCreateRequest();
            $request->prefer('return=representation');
            $request->body = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => number_format($transaction->amount + $transaction->gateway_fee, 2, '.', '')
                        ],
                        'description' => $this->getDescription($transaction),
                    ]
                ],
                'application_context' => [
                    'return_url' => $returnUrl,
                    'cancel_url' => $cancelUrl,
                    'brand_name' => config('app.name'),
                    'user_action' => 'PAY_NOW'
                ]
            ];

            $response = $this->client->execute($request);

            // Get approval URL
            $approvalUrl = null;
            foreach ($response->result->links as $link) {
                if ($link->rel === 'approve') {
                    $approvalUrl = $link->href;
                    break;
                }
            }

            return [
                'success' => true,
                'order_id' => $response->result->id,
                'approval_url' => $approvalUrl,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function captureOrder(string $orderId)
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'PayPal is not configured.'];
        }

        try {
            $request = new OrdersCaptureRequest($orderId);
            $response = $this->client->execute($request);

            return [
                'success' => true,
                'capture_id' => $response->result->purchase_units[0]->payments->captures[0]->id ?? null,
                'status' => $response->result->status,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function getDescription($transaction): string
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
}
