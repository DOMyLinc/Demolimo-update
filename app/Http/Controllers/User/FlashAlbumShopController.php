<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FlashAlbum;
use App\Models\FlashAlbumOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FlashAlbumShopController extends Controller
{
    /**
     * Browse all available flash albums
     */
    public function index()
    {
        $featured = FlashAlbum::where('is_featured', true)
            ->where('is_available', true)
            ->whereNotNull('approved_at')
            ->with('user')
            ->latest()
            ->take(6)
            ->get();

        $albums = FlashAlbum::where('is_available', true)
            ->whereNotNull('approved_at')
            ->with('user')
            ->latest()
            ->paginate(12);

        return view('user.shop.flash-albums.index', compact('featured', 'albums'));
    }

    /**
     * Show flash album details and purchase page
     */
    public function show($slug)
    {
        $album = FlashAlbum::where('slug', $slug)
            ->where('is_available', true)
            ->whereNotNull('approved_at')
            ->with(['user', 'tracks'])
            ->firstOrFail();

        return view('user.shop.flash-albums.show', compact('album'));
    }

    /**
     * Add to cart / Purchase
     */
    public function purchase(Request $request, FlashAlbum $flashAlbum)
    {
        if (!$flashAlbum->isInStock()) {
            return back()->with('error', 'This flash album is currently out of stock.');
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'shipping_name' => 'required|string',
            'shipping_address' => 'required|string',
            'shipping_city' => 'required|string',
            'shipping_state' => 'nullable|string',
            'shipping_postal_code' => 'required|string',
            'shipping_country' => 'required|string',
            'shipping_phone' => 'nullable|string',
        ]);

        // Check stock availability
        if (!$flashAlbum->is_pre_order && $flashAlbum->stock_quantity < $validated['quantity']) {
            return back()->with('error', 'Not enough stock available.');
        }

        DB::beginTransaction();
        try {
            // Calculate totals
            $unitPrice = $flashAlbum->base_price;
            $shippingCost = $flashAlbum->free_shipping ? 0 : $flashAlbum->shipping_cost;
            $tax = 0; // Calculate based on region if needed
            $totalAmount = ($unitPrice * $validated['quantity']) + $shippingCost + $tax;

            // Create order
            $order = FlashAlbumOrder::create([
                'flash_album_id' => $flashAlbum->id,
                'user_id' => auth()->id(),
                'quantity' => $validated['quantity'],
                'unit_price' => $unitPrice,
                'shipping_cost' => $shippingCost,
                'tax' => $tax,
                'total_amount' => $totalAmount,
                'shipping_name' => $validated['shipping_name'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_city' => $validated['shipping_city'],
                'shipping_state' => $validated['shipping_state'],
                'shipping_postal_code' => $validated['shipping_postal_code'],
                'shipping_country' => $validated['shipping_country'],
                'shipping_phone' => $validated['shipping_phone'],
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            // Generate download code if digital copy is included
            if ($flashAlbum->include_digital_copy) {
                $order->update([
                    'download_code' => $flashAlbum->generateDownloadCode(),
                    'download_code_expires_at' => now()->addYear(),
                ]);
            }

            DB::commit();

            // Redirect to payment
            return redirect()->route('flash-albums.payment', $order)
                ->with('success', 'Order created! Please complete payment.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create order. Please try again.');
        }
    }

    /**
     * Payment page
     */
    public function payment(FlashAlbumOrder $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        return view('user.shop.flash-albums.payment', compact('order'));
    }

    /**
     * Process payment
     */
    public function processPayment(Request $request, FlashAlbumOrder $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:stripe,paypal,wallet',
        ]);

        DB::beginTransaction();
        try {
            // Process payment based on method
            // This would integrate with Stripe/PayPal/Wallet
            // For now, we'll mark as paid

            $order->update([
                'payment_status' => 'paid',
                'payment_method' => $validated['payment_method'],
                'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                'status' => 'processing',
            ]);

            // Decrement stock
            $order->flashAlbum->decrementStock($order->quantity);

            DB::commit();

            return redirect()->route('flash-albums.order-confirmation', $order)
                ->with('success', 'Payment successful! Your order is being processed.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Payment failed. Please try again.');
        }
    }

    /**
     * Order confirmation page
     */
    public function orderConfirmation(FlashAlbumOrder $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        return view('user.shop.flash-albums.confirmation', compact('order'));
    }

    /**
     * My orders
     */
    public function myOrders()
    {
        $orders = auth()->user()->flashAlbumOrders()
            ->with('flashAlbum')
            ->latest()
            ->paginate(20);

        return view('user.shop.flash-albums.my-orders', compact('orders'));
    }

    /**
     * Download digital copy
     */
    public function downloadDigitalCopy(Request $request)
    {
        $request->validate([
            'download_code' => 'required|string',
        ]);

        $order = FlashAlbumOrder::where('download_code', $request->download_code)
            ->where('payment_status', 'paid')
            ->firstOrFail();

        // Check if code is expired
        if ($order->download_code_expires_at && $order->download_code_expires_at->isPast()) {
            return back()->with('error', 'Download code has expired.');
        }

        $album = $order->flashAlbum;
        $tracks = Track::whereIn('id', $album->track_ids)->get();

        return view('user.shop.flash-albums.download', compact('order', 'album', 'tracks'));
    }
}
