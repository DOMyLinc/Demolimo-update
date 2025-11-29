<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ArtistDonation;
use App\Models\ArtistTip;
use App\Models\User;
use App\Models\Track;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use App\Models\FeeSetting;
use App\Services\StripePaymentService;
use App\Services\PayPalPaymentService;
use Illuminate\Support\Facades\DB;

class DonationController extends Controller
{
    public function showDonationForm(User $artist)
    {
        $artist->load('donationSettings');

        if (!$artist->donationSettings || !$artist->donationSettings->donations_enabled) {
            return back()->with('error', 'This artist is not accepting donations at this time.');
        }

        return view('user.donations.create', compact('artist'));
    }

    public function donate(Request $request, User $artist)
    {
        $settings = $artist->donationSettings;

        if (!$settings || !$settings->donations_enabled) {
            return back()->with('error', 'This artist is not accepting donations at this time.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:' . $settings->minimum_donation,
            'message' => 'nullable|string|max:500',
            'is_anonymous' => 'boolean',
            'payment_method' => 'required|in:stripe,paypal,wallet',
        ]);

        // Check if user has sufficient wallet balance if using wallet
        if ($validated['payment_method'] === 'wallet') {
            $wallet = auth()->user()->wallet;
            if ($wallet->available_balance < $validated['amount']) {
                return back()->with('error', 'Insufficient wallet balance.');
            }
        }

        $donation = ArtistDonation::create([
            'artist_id' => $artist->id,
            'donor_id' => auth()->id(),
            'amount' => $validated['amount'],
            'message' => $validated['message'] ?? null,
            'is_anonymous' => $validated['is_anonymous'] ?? false,
            'payment_method' => $validated['payment_method'],
            'status' => 'pending',
        ]);

        // Process payment based on method
        if ($validated['payment_method'] === 'wallet') {
            // Deduct from donor's wallet
            auth()->user()->wallet->deductBalance(
                $validated['amount'],
                "Donation to {$artist->name}",
                'donation',
                $donation
            );

            // Process the donation immediately
            $donation->process();
        } else {
            // Redirect to payment gateway (Stripe/PayPal)
            $gateway = PaymentGateway::where('slug', $validated['payment_method'])->where('is_active', true)->firstOrFail();

            DB::beginTransaction();
            try {
                // Calculate fees (donations might have different fee structure, assuming standard for now)
                $gatewayFee = $gateway->calculateFee($validated['amount']);
                // For donations, platform fee might be 0 or a percentage
                $platformFeePercentage = $settings->platform_fee_percentage ?? 5; // Default 5%
                $platformFee = ($validated['amount'] * $platformFeePercentage) / 100;
                $artistAmount = $validated['amount'] - $platformFee - $gatewayFee;

                $transaction = PaymentTransaction::create([
                    'user_id' => auth()->id(),
                    'payment_gateway_id' => $gateway->id,
                    'payable_type' => ArtistDonation::class,
                    'payable_id' => $donation->id,
                    'type' => 'donation',
                    'amount' => $validated['amount'],
                    'gateway_fee' => $gatewayFee,
                    'platform_fee' => $platformFee,
                    'artist_amount' => $artistAmount,
                    'currency' => 'USD',
                    'status' => 'processing',
                ]);

                $donation->update(['payment_transaction_id' => $transaction->id]);

                if ($gateway->slug === 'stripe') {
                    $stripeService = new StripePaymentService();
                    $paymentIntent = $stripeService->createPaymentIntent($transaction, [
                        'donation_id' => $donation->id,
                        'artist_name' => $artist->name,
                    ]);

                    if (!$paymentIntent['success']) {
                        throw new \Exception($paymentIntent['error']);
                    }

                    DB::commit();

                    return view('user.purchases.stripe_checkout', [
                        'transaction' => $transaction,
                        'client_secret' => $paymentIntent['client_secret'],
                        'publishable_key' => $stripeService->getPublishableKey(),
                        'redirect_route' => 'user.profile',
                        'redirect_param' => $artist,
                    ]);
                } elseif ($gateway->slug === 'paypal') {
                    $paypalService = new PayPalPaymentService();
                    $returnUrl = route('user.donations.paypal.return', ['transaction' => $transaction->id]);
                    $cancelUrl = route('user.donations.paypal.cancel', ['transaction' => $transaction->id]);

                    $order = $paypalService->createOrder($transaction, $returnUrl, $cancelUrl);

                    if (!$order['success']) {
                        throw new \Exception($order['error']);
                    }

                    DB::commit();
                    return redirect($order['approval_url']);
                }

            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Payment initialization failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('user.profile', $artist)
            ->with('success', 'Thank you for your donation!');
    }

    public function showTipForm(Request $request)
    {
        $tippableType = $request->get('type'); // track, album, event, etc.
        $tippableId = $request->get('id');

        $tippable = null;
        $artist = null;

        switch ($tippableType) {
            case 'track':
                $tippable = Track::findOrFail($tippableId);
                $artist = $tippable->user;
                break;
            // Add other types as needed
        }

        if (!$artist || !$artist->donationSettings || !$artist->donationSettings->tips_enabled) {
            return back()->with('error', 'Tips are not enabled for this content.');
        }

        return view('user.tips.create', compact('artist', 'tippable', 'tippableType'));
    }

    public function tip(Request $request)
    {
        $validated = $request->validate([
            'artist_id' => 'required|exists:users,id',
            'tippable_type' => 'required|string',
            'tippable_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.50',
            'message' => 'nullable|string|max:500',
            'is_anonymous' => 'boolean',
            'payment_method' => 'required|in:stripe,paypal,wallet',
        ]);

        $artist = User::findOrFail($validated['artist_id']);
        $settings = $artist->donationSettings;

        if (!$settings || !$settings->tips_enabled) {
            return back()->with('error', 'This artist is not accepting tips at this time.');
        }

        if ($validated['amount'] < $settings->minimum_tip) {
            return back()->with('error', 'Minimum tip amount is $' . $settings->minimum_tip);
        }

        // Check if user has sufficient wallet balance if using wallet
        if ($validated['payment_method'] === 'wallet') {
            $wallet = auth()->user()->wallet;
            if ($wallet->available_balance < $validated['amount']) {
                return back()->with('error', 'Insufficient wallet balance.');
            }
        }

        $tip = ArtistTip::create([
            'artist_id' => $artist->id,
            'tipper_id' => auth()->id(),
            'amount' => $validated['amount'],
            'tippable_type' => $validated['tippable_type'],
            'tippable_id' => $validated['tippable_id'],
            'message' => $validated['message'] ?? null,
            'is_anonymous' => $validated['is_anonymous'] ?? false,
            'payment_method' => $validated['payment_method'],
            'status' => 'pending',
        ]);

        // Process payment based on method
        if ($validated['payment_method'] === 'wallet') {
            // Deduct from tipper's wallet
            auth()->user()->wallet->deductBalance(
                $validated['amount'],
                "Tip to {$artist->name}",
                'tip',
                $tip
            );

            // Process the tip immediately
            $tip->process();
        } else {
            // Redirect to payment gateway (Stripe/PayPal)
            $gateway = PaymentGateway::where('slug', $validated['payment_method'])->where('is_active', true)->firstOrFail();

            DB::beginTransaction();
            try {
                $gatewayFee = $gateway->calculateFee($validated['amount']);
                $platformFeePercentage = $settings->platform_fee_percentage ?? 5;
                $platformFee = ($validated['amount'] * $platformFeePercentage) / 100;
                $artistAmount = $validated['amount'] - $platformFee - $gatewayFee;

                $transaction = PaymentTransaction::create([
                    'user_id' => auth()->id(),
                    'payment_gateway_id' => $gateway->id,
                    'payable_type' => ArtistTip::class,
                    'payable_id' => $tip->id,
                    'type' => 'tip',
                    'amount' => $validated['amount'],
                    'gateway_fee' => $gatewayFee,
                    'platform_fee' => $platformFee,
                    'artist_amount' => $artistAmount,
                    'currency' => 'USD',
                    'status' => 'processing',
                ]);

                $tip->update(['payment_transaction_id' => $transaction->id]);

                if ($gateway->slug === 'stripe') {
                    $stripeService = new StripePaymentService();
                    $paymentIntent = $stripeService->createPaymentIntent($transaction, [
                        'tip_id' => $tip->id,
                        'artist_name' => $artist->name,
                    ]);

                    if (!$paymentIntent['success']) {
                        throw new \Exception($paymentIntent['error']);
                    }

                    DB::commit();

                    return view('user.purchases.stripe_checkout', [
                        'transaction' => $transaction,
                        'client_secret' => $paymentIntent['client_secret'],
                        'publishable_key' => $stripeService->getPublishableKey(),
                        'redirect_route' => 'user.profile', // Or back to content
                        'redirect_param' => $artist,
                    ]);
                } elseif ($gateway->slug === 'paypal') {
                    $paypalService = new PayPalPaymentService();
                    $returnUrl = route('user.tips.paypal.return', ['transaction' => $transaction->id]);
                    $cancelUrl = route('user.tips.paypal.cancel', ['transaction' => $transaction->id]);

                    $order = $paypalService->createOrder($transaction, $returnUrl, $cancelUrl);

                    if (!$order['success']) {
                        throw new \Exception($order['error']);
                    }

                    DB::commit();
                    return redirect($order['approval_url']);
                }

            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Payment initialization failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Thank you for your tip!');
    }

    public function myDonations()
    {
        $donations = auth()->user()->sentDonations()
            ->with('artist')
            ->latest()
            ->paginate(20);

        return view('user.donations.my-donations', compact('donations'));
    }

    public function myTips()
    {
        $tips = auth()->user()->sentTips()
            ->with(['artist', 'tippable'])
            ->latest()
            ->paginate(20);

        return view('user.tips.my-tips', compact('tips'));
    }

    public function receivedDonations()
    {
        $donations = auth()->user()->receivedDonations()
            ->with('donor')
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => auth()->user()->receivedDonations()->where('status', 'completed')->sum('amount'),
            'this_month' => auth()->user()->receivedDonations()
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'count' => auth()->user()->receivedDonations()->where('status', 'completed')->count(),
        ];

        return view('user.donations.received', compact('donations', 'stats'));
    }

    public function receivedTips()
    {
        $tips = auth()->user()->receivedTips()
            ->with(['tipper', 'tippable'])
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => auth()->user()->receivedTips()->where('status', 'completed')->sum('amount'),
            'this_month' => auth()->user()->receivedTips()
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'count' => auth()->user()->receivedTips()->where('status', 'completed')->count(),
        ];

        return view('user.tips.received', compact('tips', 'stats'));
    }

    public function donationSettings()
    {
        $settings = auth()->user()->donationSettings()->firstOrCreate([
            'user_id' => auth()->id()
        ]);

        return view('user.donations.settings', compact('settings'));
    }

    public function updateDonationSettings(Request $request)
    {
        $validated = $request->validate([
            'donations_enabled' => 'boolean',
            'tips_enabled' => 'boolean',
            'minimum_donation' => 'numeric|min:0',
            'minimum_tip' => 'numeric|min:0',
            'suggested_amounts' => 'nullable|array',
            'suggested_amounts.*' => 'numeric|min:0',
            'donation_message' => 'nullable|string|max:500',
            'paypal_email' => 'nullable|email',
        ]);

        $settings = auth()->user()->donationSettings()->firstOrCreate([
            'user_id' => auth()->id()
        ]);

        $settings->update($validated);

        return back()->with('success', 'Donation settings updated successfully!');
    }
}
