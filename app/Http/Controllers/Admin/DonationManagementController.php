<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArtistDonation;
use App\Models\ArtistTip;
use App\Models\ArtistGift;
use App\Models\ArtistDonationSettings;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DonationManagementController extends Controller
{
    // Donations Management
    public function donations()
    {
        $donations = ArtistDonation::with(['artist', 'donor'])
            ->latest()
            ->paginate(20);

        $stats = [
            'total_donations' => ArtistDonation::where('status', 'completed')->sum('amount'),
            'pending_donations' => ArtistDonation::where('status', 'pending')->sum('amount'),
            'total_count' => ArtistDonation::where('status', 'completed')->count(),
            'pending_count' => ArtistDonation::where('status', 'pending')->count(),
        ];

        return view('admin.donations.index', compact('donations', 'stats'));
    }

    public function showDonation(ArtistDonation $donation)
    {
        $donation->load(['artist', 'donor']);
        return view('admin.donations.show', compact('donation'));
    }

    public function processDonation(ArtistDonation $donation)
    {
        try {
            $donation->process();
            return back()->with('success', 'Donation processed successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to process donation: ' . $e->getMessage());
        }
    }

    public function refundDonation(Request $request, ArtistDonation $donation)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        if ($donation->status === 'refunded') {
            return back()->with('error', 'Donation already refunded.');
        }

        try {
            // Get the payment transaction
            $transaction = $donation->paymentTransaction;

            if ($transaction && $transaction->gateway) {
                // Refund via payment gateway
                if ($transaction->gateway->slug === 'stripe') {
                    $stripeService = new \App\Services\StripePaymentService();
                    $refundResult = $stripeService->refund($transaction->gateway_transaction_id, $donation->amount);

                    if (!$refundResult['success']) {
                        return back()->with('error', 'Stripe refund failed: ' . $refundResult['error']);
                    }
                } elseif ($transaction->gateway->slug === 'paypal') {
                    $paypalService = new \App\Services\PayPalPaymentService();
                    $refundResult = $paypalService->refund($transaction->gateway_transaction_id, $donation->amount);

                    if (!$refundResult['success']) {
                        return back()->with('error', 'PayPal refund failed: ' . $refundResult['error']);
                    }
                }
            }

            // Update donation status
            $donation->status = 'refunded';
            $donation->save();

            // Return money to donor's wallet
            if ($donation->donor && $donation->donor->wallet) {
                $donation->donor->wallet->addBalance(
                    $donation->amount,
                    "Refund for donation to {$donation->artist->name}",
                    'refund',
                    $donation
                );
            }

            // Deduct from artist's wallet
            if ($donation->artist && $donation->artist->wallet) {
                $donation->artist->wallet->deductBalance(
                    $donation->amount,
                    "Refunded donation from {$donation->donor->name}",
                    'refund',
                    $donation
                );
            }

            return back()->with('success', 'Donation refunded successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Refund failed: ' . $e->getMessage());
        }
    }

    // Tips Management
    public function tips()
    {
        $tips = ArtistTip::with(['artist', 'tipper', 'tippable'])
            ->latest()
            ->paginate(20);

        $stats = [
            'total_tips' => ArtistTip::where('status', 'completed')->sum('amount'),
            'pending_tips' => ArtistTip::where('status', 'pending')->sum('amount'),
            'total_count' => ArtistTip::where('status', 'completed')->count(),
            'pending_count' => ArtistTip::where('status', 'pending')->count(),
        ];

        return view('admin.tips.index', compact('tips', 'stats'));
    }

    public function showTip(ArtistTip $tip)
    {
        $tip->load(['artist', 'tipper', 'tippable']);
        return view('admin.tips.show', compact('tip'));
    }

    public function processTip(ArtistTip $tip)
    {
        try {
            $tip->process();
            return back()->with('success', 'Tip processed successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to process tip: ' . $e->getMessage());
        }
    }

    public function refundTip(Request $request, ArtistTip $tip)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        if ($tip->status === 'refunded') {
            return back()->with('error', 'Tip already refunded.');
        }

        try {
            // Get the payment transaction
            $transaction = $tip->paymentTransaction;

            if ($transaction && $transaction->gateway) {
                // Refund via payment gateway
                if ($transaction->gateway->slug === 'stripe') {
                    $stripeService = new \App\Services\StripePaymentService();
                    $refundResult = $stripeService->refund($transaction->gateway_transaction_id, $tip->amount);

                    if (!$refundResult['success']) {
                        return back()->with('error', 'Stripe refund failed: ' . $refundResult['error']);
                    }
                } elseif ($transaction->gateway->slug === 'paypal') {
                    $paypalService = new \App\Services\PayPalPaymentService();
                    $refundResult = $paypalService->refund($transaction->gateway_transaction_id, $tip->amount);

                    if (!$refundResult['success']) {
                        return back()->with('error', 'PayPal refund failed: ' . $refundResult['error']);
                    }
                }
            }

            // Update tip status
            $tip->status = 'refunded';
            $tip->save();

            // Return money to tipper's wallet
            if ($tip->tipper && $tip->tipper->wallet) {
                $tip->tipper->wallet->addBalance(
                    $tip->amount,
                    "Refund for tip to {$tip->artist->name}",
                    'refund',
                    $tip
                );
            }

            // Deduct from artist's wallet
            if ($tip->artist && $tip->artist->wallet) {
                $tip->artist->wallet->deductBalance(
                    $tip->amount,
                    "Refunded tip from {$tip->tipper->name}",
                    'refund',
                    $tip
                );
            }

            return back()->with('success', 'Tip refunded successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Refund failed: ' . $e->getMessage());
        }
    }

    // Gifts Management
    public function gifts()
    {
        $gifts = ArtistGift::with(['recipient', 'sender'])
            ->latest()
            ->paginate(20);

        return view('admin.gifts.index', compact('gifts'));
    }

    public function createGift()
    {
        $users = User::where('role', '!=', 'admin')->get();
        return view('admin.gifts.create', compact('users'));
    }

    public function storeGift(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'gift_type' => 'required|in:cash,points,premium_subscription,custom',
            'cash_amount' => 'nullable|numeric|min:0',
            'points_amount' => 'nullable|integer|min:0',
            'premium_days' => 'nullable|integer|min:1',
            'custom_gift_description' => 'nullable|string',
            'message' => 'nullable|string',
            'auto_send' => 'boolean',
        ]);

        $gift = ArtistGift::create([
            'recipient_id' => $validated['recipient_id'],
            'sender_id' => auth()->id(),
            'gift_type' => $validated['gift_type'],
            'cash_amount' => $validated['cash_amount'] ?? null,
            'points_amount' => $validated['points_amount'] ?? null,
            'premium_days' => $validated['premium_days'] ?? null,
            'custom_gift_description' => $validated['custom_gift_description'] ?? null,
            'message' => $validated['message'] ?? null,
        ]);

        if ($request->boolean('auto_send')) {
            $gift->send();
            $message = 'Gift created and sent successfully!';
        } else {
            $message = 'Gift created successfully!';
        }

        return redirect()->route('admin.gifts.index')->with('success', $message);
    }

    public function sendGift(ArtistGift $gift)
    {
        try {
            $gift->send();
            return back()->with('success', 'Gift sent successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send gift: ' . $e->getMessage());
        }
    }

    public function deleteGift(ArtistGift $gift)
    {
        if ($gift->status === 'claimed') {
            return back()->with('error', 'Cannot delete a claimed gift.');
        }

        $gift->delete();
        return back()->with('success', 'Gift deleted successfully!');
    }

    // Donation Settings Management
    public function settings()
    {
        $settings = ArtistDonationSettings::with('user')->paginate(20);

        $globalSettings = DB::table('platform_settings')
            ->whereIn('key', ['default_platform_fee', 'min_donation_amount', 'min_tip_amount'])
            ->pluck('value', 'key');

        return view('admin.donations.settings', compact('settings', 'globalSettings'));
    }

    public function updateUserSettings(Request $request, User $user)
    {
        $validated = $request->validate([
            'donations_enabled' => 'boolean',
            'tips_enabled' => 'boolean',
            'minimum_donation' => 'numeric|min:0',
            'minimum_tip' => 'numeric|min:0',
            'platform_fee_percentage' => 'numeric|min:0|max:100',
        ]);

        $settings = $user->donationSettings()->firstOrCreate(['user_id' => $user->id]);
        $settings->update($validated);

        return back()->with('success', 'Donation settings updated successfully!');
    }

    public function updateGlobalSettings(Request $request)
    {
        $validated = $request->validate([
            'default_platform_fee' => 'numeric|min:0|max:100',
            'min_donation_amount' => 'numeric|min:0',
            'min_tip_amount' => 'numeric|min:0',
        ]);

        foreach ($validated as $key => $value) {
            DB::table('platform_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now()]
            );
        }

        return back()->with('success', 'Global donation settings updated successfully!');
    }

    // Analytics
    public function analytics()
    {
        $donationStats = [
            'total' => ArtistDonation::where('status', 'completed')->sum('amount'),
            'count' => ArtistDonation::where('status', 'completed')->count(),
            'this_month' => ArtistDonation::where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
        ];

        $tipStats = [
            'total' => ArtistTip::where('status', 'completed')->sum('amount'),
            'count' => ArtistTip::where('status', 'completed')->count(),
            'this_month' => ArtistTip::where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
        ];

        $topRecipients = User::withCount(['receivedDonations', 'receivedTips'])
            ->withSum('receivedDonations as total_donations', 'amount')
            ->withSum('receivedTips as total_tips', 'amount')
            ->orderByDesc('total_donations')
            ->limit(10)
            ->get();

        $topDonors = User::withCount(['sentDonations', 'sentTips'])
            ->withSum('sentDonations as total_donated', 'amount')
            ->withSum('sentTips as total_tipped', 'amount')
            ->orderByDesc('total_donated')
            ->limit(10)
            ->get();

        return view('admin.donations.analytics', compact(
            'donationStats',
            'tipStats',
            'topRecipients',
            'topDonors'
        ));
    }
}
