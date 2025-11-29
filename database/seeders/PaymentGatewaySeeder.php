<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentGateway;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $gateways = [
            // Automatic Gateways
            [
                'name' => 'Stripe',
                'slug' => 'stripe',
                'type' => 'automatic',
                'is_active' => true,
                'description' => 'Pay securely with credit/debit card via Stripe',
                'logo' => '/images/gateways/stripe.png',
                'credentials' => [
                    'publishable_key' => env('STRIPE_PUBLISHABLE_KEY', ''),
                    'secret_key' => env('STRIPE_SECRET_KEY', ''),
                    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),
                ],
                'settings' => [
                    'auto_capture' => true,
                    'statement_descriptor' => 'MUSIC PLATFORM',
                ],
                'fixed_fee' => 0.30,
                'percentage_fee' => 2.9,
                'min_amount' => 0.50,
                'max_amount' => 999999.99,
                'supported_currencies' => ['USD', 'EUR', 'GBP', 'CAD', 'AUD'],
                'instructions' => null,
                'processing_time' => 0, // Instant
                'display_order' => 1,
            ],
            [
                'name' => 'PayPal',
                'slug' => 'paypal',
                'type' => 'automatic',
                'is_active' => true,
                'description' => 'Pay with your PayPal account',
                'logo' => '/images/gateways/paypal.png',
                'credentials' => [
                    'client_id' => env('PAYPAL_CLIENT_ID', ''),
                    'client_secret' => env('PAYPAL_CLIENT_SECRET', ''),
                    'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox or live
                ],
                'settings' => [
                    'brand_name' => 'Music Platform',
                    'landing_page' => 'BILLING',
                ],
                'fixed_fee' => 0.30,
                'percentage_fee' => 2.9,
                'min_amount' => 1.00,
                'max_amount' => 999999.99,
                'supported_currencies' => ['USD', 'EUR', 'GBP', 'CAD', 'AUD'],
                'instructions' => null,
                'processing_time' => 0, // Instant
                'display_order' => 2,
            ],

            // Manual Gateways
            [
                'name' => 'Bank Transfer',
                'slug' => 'bank_transfer',
                'type' => 'manual',
                'is_active' => true,
                'description' => 'Direct bank transfer - Manual verification required',
                'logo' => '/images/gateways/bank.png',
                'credentials' => null,
                'settings' => [
                    'bank_name' => 'Example Bank',
                    'account_name' => 'Music Platform Inc.',
                    'account_number' => '1234567890',
                    'routing_number' => '021000021',
                    'swift_code' => 'EXAMPLEXXX',
                ],
                'fixed_fee' => 0.00,
                'percentage_fee' => 0.00,
                'min_amount' => 10.00,
                'max_amount' => 999999.99,
                'supported_currencies' => ['USD'],
                'instructions' => "Please transfer the exact amount to the bank account details shown above.\n\nAfter making the transfer:\n1. Take a screenshot or photo of the transfer receipt\n2. Upload it during checkout\n3. Include your transaction reference number\n4. Wait for admin approval (usually within 24 hours)\n\nImportant: Your purchase will be processed only after we verify your payment.",
                'processing_time' => 24, // 24 hours
                'display_order' => 3,
            ],
            [
                'name' => 'Cash Payment',
                'slug' => 'cash',
                'type' => 'manual',
                'is_active' => false,
                'description' => 'Cash payment at authorized locations',
                'logo' => '/images/gateways/cash.png',
                'credentials' => null,
                'settings' => [
                    'locations' => [
                        'Main Office - 123 Music Street, City',
                        'Branch Office - 456 Sound Avenue, Town',
                    ],
                ],
                'fixed_fee' => 0.00,
                'percentage_fee' => 0.00,
                'min_amount' => 5.00,
                'max_amount' => 1000.00,
                'supported_currencies' => ['USD'],
                'instructions' => "Visit any of our authorized locations with:\n1. Your order number\n2. Exact cash amount\n3. Valid ID\n\nOur staff will verify your payment and activate your purchase immediately.",
                'processing_time' => 1, // 1 hour
                'display_order' => 4,
            ],
            [
                'name' => 'Cryptocurrency',
                'slug' => 'crypto',
                'type' => 'manual',
                'is_active' => false,
                'description' => 'Pay with Bitcoin, Ethereum, or other cryptocurrencies',
                'logo' => '/images/gateways/crypto.png',
                'credentials' => null,
                'settings' => [
                    'btc_address' => 'bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh',
                    'eth_address' => '0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb',
                    'usdt_address' => 'TN3W4H6rK2ce4vX9YnFQHwKENnHjoxb3m9',
                ],
                'fixed_fee' => 0.00,
                'percentage_fee' => 1.00,
                'min_amount' => 10.00,
                'max_amount' => 999999.99,
                'supported_currencies' => ['USD'],
                'instructions' => "Send the exact amount in cryptocurrency to the address shown above.\n\nSupported cryptocurrencies:\n- Bitcoin (BTC)\n- Ethereum (ETH)\n- Tether (USDT)\n\nAfter sending:\n1. Copy the transaction hash\n2. Upload a screenshot of the transaction\n3. Paste the transaction hash in the reference field\n4. Wait for blockchain confirmation and admin approval",
                'processing_time' => 48, // 48 hours
                'display_order' => 5,
            ],
            [
                'name' => 'Mobile Money',
                'slug' => 'mobile_money',
                'type' => 'manual',
                'is_active' => false,
                'description' => 'Pay via mobile money services',
                'logo' => '/images/gateways/mobile-money.png',
                'credentials' => null,
                'settings' => [
                    'provider' => 'M-Pesa',
                    'business_number' => '123456',
                    'account_name' => 'Music Platform',
                ],
                'fixed_fee' => 0.00,
                'percentage_fee' => 0.50,
                'min_amount' => 1.00,
                'max_amount' => 10000.00,
                'supported_currencies' => ['USD'],
                'instructions' => "Send money to our mobile money account:\n\nProvider: M-Pesa\nBusiness Number: 123456\nAccount Name: Music Platform\n\nAfter payment:\n1. Note down the M-Pesa confirmation code\n2. Upload a screenshot of the confirmation SMS\n3. Enter the confirmation code as reference\n4. Your purchase will be activated within 1 hour",
                'processing_time' => 1, // 1 hour
                'display_order' => 6,
            ],
        ];

        foreach ($gateways as $gateway) {
            PaymentGateway::create($gateway);
        }

        $this->command->info('Payment gateways seeded successfully!');
    }
}
