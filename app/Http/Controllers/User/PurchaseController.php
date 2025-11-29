<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Models\Album;
use App\Models\TrackSale;
use App\Models\AlbumSale;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use App\Models\ManualPaymentVerification;
use App\Models\FeeSetting;
use App\Services\StripePaymentService;
use App\Services\PayPalPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PurchaseController extends Controller
{
    // Track Purchase
    public function showTrackPurchase(Track $track)
    {
        if (!$track->is_for_sale) {
            return back()->with('error', 'This track is not for sale.');
        }

        // Check if user already purchased
        $alreadyPurchased = TrackSale::where('track_id', $track->id)
            ->where('buyer_id', auth()->id())
            ->exists();

        if ($alreadyPurchased) {
            return redirect()->route('user.purchases.index')
                ->with('info', 'You have already purchased this track.');
        }

        $gateways = PaymentGateway::active()
            ->ordered()
            ->get()
            ->filter(function ($gateway) use ($track) {
                return $gateway->supportsAmount($track->price);
            });

        // Calculate fees
        $gatewayFees = [];
        $feeBreakdowns = [];

        foreach ($gateways as $gateway) {
            $gatewayFee = $gateway->calculateFee($track->price);
            $breakdown = FeeSetting::getFeeBreakdown('track_sale', $track->price, $gatewayFee);

            $gatewayFees[$gateway->id] = $gatewayFee;
            $feeBreakdowns[$gateway->id] = $breakdown;
        }

        return view('user.purchases.track', compact('track', 'gateways', 'gatewayFees', 'feeBreakdowns'));
    }

    public function purchaseTrack(Request $request, Track $track)
    {
        $validated = $request->validate([
            'payment_gateway_id' => 'required|exists:payment_gateways,id',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'transaction_reference' => 'nullable|string',
        ]);

        if (!$track->is_for_sale) {
            return back()->with('error', 'This track is not for sale.');
        }

        // Check if already purchased
        $alreadyPurchased = TrackSale::where('track_id', $track->id)
            ->where('buyer_id', auth()->id())
            ->exists();

        if ($alreadyPurchased) {
            return back()->with('error', 'You have already purchased this track.');
        }

        $gateway = PaymentGateway::findOrFail($validated['payment_gateway_id']);

        if (!$gateway->is_active || !$gateway->supportsAmount($track->price)) {
            return back()->with('error', 'Selected payment gateway is not available.');
        }

        DB::beginTransaction();
        try {
            // Calculate fees
            $gatewayFee = $gateway->calculateFee($track->price);
            $breakdown = FeeSetting::getFeeBreakdown('track_sale', $track->price, $gatewayFee);

            // Create payment transaction
            $transaction = PaymentTransaction::create([
                'user_id' => auth()->id(),
                'payment_gateway_id' => $gateway->id,
                'payable_type' => Track::class,
                'payable_id' => $track->id,
                'type' => 'track_purchase',
                'amount' => $track->price,
                'gateway_fee' => $breakdown['gateway_fee'],
                'platform_fee' => $breakdown['platform_fee'],
                'artist_amount' => $breakdown['artist_amount'],
                'currency' => 'USD',
                'status' => $gateway->isManual() ? 'pending' : 'processing',
            ]);

            // Handle manual payment
            if ($gateway->isManual()) {
                $proofPath = null;
                if ($request->hasFile('payment_proof')) {
                    $proofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
                }

                ManualPaymentVerification::create([
                    'payment_transaction_id' => $transaction->id,
                    'status' => 'pending',
                    'proof_image' => $proofPath,
                    'transaction_reference' => $validated['transaction_reference'] ?? null,
                ]);

                DB::commit();

                return redirect()->route('user.purchases.pending', $transaction)
                    ->with('success', 'Your payment is pending verification. You will be notified once approved.');
            }

            // Handle automatic payment (Stripe, PayPal, etc.)
            if ($gateway->slug === 'stripe') {
                $stripeService = new StripePaymentService();

                if (!$stripeService->isConfigured()) {
                    DB::rollBack();
                    return back()->with('error', 'Stripe is not configured. Please contact support.');
                }

                $paymentIntent = $stripeService->createPaymentIntent($transaction, [
                    'track_id' => $track->id,
                    'track_title' => $track->title,
                ]);

                if (!$paymentIntent['success']) {
                    DB::rollBack();
                    return back()->with('error', 'Payment failed: ' . $paymentIntent['error']);
                }

                DB::commit();

                // Return view with Stripe client secret for frontend processing
                return view('user.purchases.stripe_checkout', [
                    'transaction' => $transaction,
                    'track' => $track,
                    'client_secret' => $paymentIntent['client_secret'],
                    'publishable_key' => $stripeService->getPublishableKey(),
                ]);
            }

            if ($gateway->slug === 'paypal') {
                $paypalService = new PayPalPaymentService();

                if (!$paypalService->isConfigured()) {
                    DB::rollBack();
                    return back()->with('error', 'PayPal is not configured. Please contact support.');
                }

                $returnUrl = route('user.purchases.paypal.return', ['transaction' => $transaction->id]);
                $cancelUrl = route('user.purchases.paypal.cancel', ['transaction' => $transaction->id]);

                $order = $paypalService->createOrder($transaction, $returnUrl, $cancelUrl);

                if (!$order['success']) {
                    DB::rollBack();
                    return back()->with('error', 'Payment failed: ' . $order['error']);
                }

                DB::commit();

                // Redirect to PayPal for payment
                return redirect($order['approval_url']);
            }

            // For other automatic gateways, create the sale immediately (for testing)
            // In production, this should only happen after payment confirmation
            $sale = TrackSale::create([
                'track_id' => $track->id,
                'buyer_id' => auth()->id(),
                'seller_id' => $track->user_id,
                'payment_transaction_id' => $transaction->id,
                'price' => $track->price,
                'platform_fee' => $breakdown['platform_fee'],
                'gateway_fee' => $breakdown['gateway_fee'],
                'seller_earnings' => $breakdown['artist_amount'],
                'license_type' => 'standard',
                'max_downloads' => auth()->user()->isPro() ? 100 : 3,
                'expires_at' => now()->addYear(),
            ]);

            // Update track stats
            $track->increment('total_sales');
            $track->increment('total_revenue', $track->price);

            // Add earnings to seller wallet
            $track->user->wallet->addBalance(
                $breakdown['artist_amount'],
                "Track sale: {$track->title}",
                'track_sale',
                $sale
            );

            // Record Revenue
            \App\Models\Revenue::create([
                'user_id' => $track->user_id,
                'amount' => $breakdown['artist_amount'],
                'commission' => $breakdown['platform_fee'],
                'source_type' => 'track',
                'source_id' => $track->id,
                'currency' => 'USD',
                'status' => 'available',
            ]);

            $transaction->markAsCompleted();

            DB::commit();

            return redirect()->route('user.purchases.success', $sale)
                ->with('success', 'Track purchased successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Purchase failed: ' . $e->getMessage());
        }
    }

    // Album Purchase
    public function showAlbumPurchase(Album $album)
    {
        if (!$album->is_for_sale) {
            return back()->with('error', 'This album is not for sale.');
        }

        $alreadyPurchased = AlbumSale::where('album_id', $album->id)
            ->where('buyer_id', auth()->id())
            ->exists();

        if ($alreadyPurchased) {
            return redirect()->route('user.purchases.index')
                ->with('info', 'You have already purchased this album.');
        }

        $gateways = PaymentGateway::active()
            ->ordered()
            ->get()
            ->filter(function ($gateway) use ($album) {
                return $gateway->supportsAmount($album->price);
            });

        $gatewayFees = [];
        $feeBreakdowns = [];

        foreach ($gateways as $gateway) {
            $gatewayFee = $gateway->calculateFee($album->price);
            $breakdown = FeeSetting::getFeeBreakdown('album_sale', $album->price, $gatewayFee);

            $gatewayFees[$gateway->id] = $gatewayFee;
            $feeBreakdowns[$gateway->id] = $breakdown;
        }

        return view('user.purchases.album', compact('album', 'gateways', 'gatewayFees', 'feeBreakdowns'));
    }

    public function purchaseAlbum(Request $request, Album $album)
    {
        $validated = $request->validate([
            'payment_gateway_id' => 'required|exists:payment_gateways,id',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'transaction_reference' => 'nullable|string',
        ]);

        if (!$album->is_for_sale) {
            return back()->with('error', 'This album is not for sale.');
        }

        $alreadyPurchased = AlbumSale::where('album_id', $album->id)
            ->where('buyer_id', auth()->id())
            ->exists();

        if ($alreadyPurchased) {
            return back()->with('error', 'You have already purchased this album.');
        }

        $gateway = PaymentGateway::findOrFail($validated['payment_gateway_id']);

        if (!$gateway->is_active || !$gateway->supportsAmount($album->price)) {
            return back()->with('error', 'Selected payment gateway is not available.');
        }

        DB::beginTransaction();
        try {
            $gatewayFee = $gateway->calculateFee($album->price);
            $breakdown = FeeSetting::getFeeBreakdown('album_sale', $album->price, $gatewayFee);

            $transaction = PaymentTransaction::create([
                'user_id' => auth()->id(),
                'payment_gateway_id' => $gateway->id,
                'payable_type' => Album::class,
                'payable_id' => $album->id,
                'type' => 'album_purchase',
                'amount' => $album->price,
                'gateway_fee' => $breakdown['gateway_fee'],
                'platform_fee' => $breakdown['platform_fee'],
                'artist_amount' => $breakdown['artist_amount'],
                'currency' => 'USD',
                'status' => $gateway->isManual() ? 'pending' : 'processing',
            ]);

            if ($gateway->isManual()) {
                $proofPath = null;
                if ($request->hasFile('payment_proof')) {
                    $proofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
                }

                ManualPaymentVerification::create([
                    'payment_transaction_id' => $transaction->id,
                    'status' => 'pending',
                    'proof_image' => $proofPath,
                    'transaction_reference' => $validated['transaction_reference'] ?? null,
                ]);

                DB::commit();

                return redirect()->route('user.purchases.pending', $transaction)
                    ->with('success', 'Your payment is pending verification.');
            }

            $sale = AlbumSale::create([
                'album_id' => $album->id,
                'buyer_id' => auth()->id(),
                'seller_id' => $album->user_id,
                'payment_transaction_id' => $transaction->id,
                'price' => $album->price,
                'platform_fee' => $breakdown['platform_fee'],
                'gateway_fee' => $breakdown['gateway_fee'],
                'seller_earnings' => $breakdown['artist_amount'],
                'license_type' => 'standard',
                'max_downloads' => 3,
                'expires_at' => now()->addYear(),
            ]);

            $album->increment('total_sales');
            $album->increment('total_revenue', $album->price);

            $album->user->wallet->addBalance(
                $breakdown['artist_amount'],
                "Album sale: {$album->title}",
                'album_sale',
                $sale
            );

            // Record Revenue
            \App\Models\Revenue::create([
                'user_id' => $album->user_id,
                'amount' => $breakdown['artist_amount'],
                'commission' => $breakdown['platform_fee'],
                'source_type' => 'album',
                'source_id' => $album->id,
                'currency' => 'USD',
                'status' => 'available',
            ]);

            $transaction->markAsCompleted();

            DB::commit();

            return redirect()->route('user.purchases.success.album', $sale)
                ->with('success', 'Album purchased successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Purchase failed: ' . $e->getMessage());
        }
    }

    // My Purchases
    public function index()
    {
        $trackPurchases = TrackSale::where('buyer_id', auth()->id())
            ->with('track')
            ->latest()
            ->get();

        $albumPurchases = AlbumSale::where('buyer_id', auth()->id())
            ->with('album')
            ->latest()
            ->get();

        return view('user.purchases.index', compact('trackPurchases', 'albumPurchases'));
    }

    // Download purchased track
    public function downloadTrack($token)
    {
        $sale = TrackSale::where('download_token', $token)
            ->where('buyer_id', auth()->id())
            ->firstOrFail();

        if (!$sale->canDownload()) {
            return back()->with('error', 'Download limit exceeded or expired.');
        }

        $sale->incrementDownload();

        $track = $sale->track;
        $filePath = storage_path('app/public/' . $track->audio_file);

        if (!file_exists($filePath)) {
            return back()->with('error', 'File not found.');
        }

        $fileName = \Illuminate\Support\Str::slug($track->title) . '.' . pathinfo($filePath, PATHINFO_EXTENSION);

        return response()->download($filePath, $fileName, [
            'Content-Type' => 'audio/mpeg',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    // Download purchased album
    public function downloadAlbum($token)
    {
        $sale = AlbumSale::where('download_token', $token)
            ->where('buyer_id', auth()->id())
            ->firstOrFail();

        if (!$sale->canDownload()) {
            return back()->with('error', 'Download limit exceeded or expired.');
        }

        $sale->incrementDownload();

        $album = $sale->album;
        $zipFileName = \Illuminate\Support\Str::slug($album->title) . '-' . time() . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Create temp directory if it doesn't exist
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== TRUE) {
            return back()->with('error', 'Could not create ZIP file.');
        }

        // Add all tracks to ZIP
        foreach ($album->tracks as $track) {
            $trackPath = storage_path('app/public/' . $track->audio_file);
            if (file_exists($trackPath)) {
                $trackName = \Illuminate\Support\Str::slug($track->title) . '.' . pathinfo($trackPath, PATHINFO_EXTENSION);
                $zip->addFile($trackPath, $trackName);
            }
        }

        $zip->close();

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    // Success pages
    public function success(TrackSale $sale)
    {
        if ($sale->buyer_id !== auth()->id()) {
            abort(403);
        }

        return view('user.purchases.success', compact('sale'));
    }

    public function successAlbum(AlbumSale $sale)
    {
        if ($sale->buyer_id !== auth()->id()) {
            abort(403);
        }

        return view('user.purchases.success_album', compact('sale'));
    }

    // Pending payment
    public function pending(PaymentTransaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $transaction->load('manualVerification');

        return view('user.purchases.pending', compact('transaction'));
    }
}
