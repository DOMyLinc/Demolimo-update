<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductOrder;
use App\Models\ProductOrderItem;
use App\Models\ProductReview;
use App\Models\ShoppingCart;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::published()->featured()->inStock()->limit(8)->get();
        $categories = ProductCategory::active()->ordered()->get();
        $newProducts = Product::published()->inStock()->latest()->limit(12)->get();

        return view('user.store.index', compact('featuredProducts', 'categories', 'newProducts'));
    }

    public function category(ProductCategory $category)
    {
        $products = Product::published()
            ->inStock()
            ->where('product_category_id', $category->id)
            ->paginate(24);

        return view('user.store.category', compact('category', 'products'));
    }

    public function show(Product $product)
    {
        $product->incrementViews();
        $product->load(['user', 'category', 'reviews.user']);

        $relatedProducts = Product::published()
            ->inStock()
            ->where('product_category_id', $product->product_category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        return view('user.store.show', compact('product', 'relatedProducts'));
    }

    public function cart()
    {
        $cartItems = ShoppingCart::where('user_id', Auth::id())
            ->with('product')
            ->get();

        $total = ShoppingCart::getTotal(Auth::id());

        return view('user.store.cart', compact('cartItems', 'total'));
    }

    public function addToCart(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if (!$product->is_in_stock) {
            return back()->with('error', 'Product is out of stock.');
        }

        ShoppingCart::addItem(Auth::id(), $product->id, $validated['quantity']);

        return back()->with('success', 'Product added to cart!');
    }

    public function updateCart(Request $request, ShoppingCart $cartItem)
    {
        $this->authorize('update', $cartItem);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem->update($validated);

        return back()->with('success', 'Cart updated!');
    }

    public function removeFromCart(ShoppingCart $cartItem)
    {
        $this->authorize('delete', $cartItem);

        $cartItem->delete();

        return back()->with('success', 'Item removed from cart!');
    }

    public function checkout()
    {
        $cartItems = ShoppingCart::where('user_id', Auth::id())
            ->with('product.user')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('user.store.cart')->with('error', 'Your cart is empty.');
        }

        $subtotal = ShoppingCart::getTotal(Auth::id());
        $platformFeePercentage = config('store.commission_percentage', 15);
        $platformFee = $subtotal * ($platformFeePercentage / 100);
        $total = $subtotal;

        return view('user.store.checkout', compact('cartItems', 'subtotal', 'platformFee', 'total'));
    }

    public function processCheckout(Request $request)
    {
        $validated = $request->validate([
            'shipping_address' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $cartItems = ShoppingCart::where('user_id', Auth::id())
            ->with('product.user')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('user.store.cart')->with('error', 'Your cart is empty.');
        }

        $subtotal = ShoppingCart::getTotal(Auth::id());
        $platformFeePercentage = config('store.commission_percentage', 15);
        $platformFee = $subtotal * ($platformFeePercentage / 100);
        $total = $subtotal;

        // Check wallet balance
        $wallet = Auth::user()->wallet ?? Wallet::create(['user_id' => Auth::id()]);

        if ($wallet->available_balance < $total) {
            return back()->with('error', 'Insufficient wallet balance.');
        }

        // Create order
        $order = ProductOrder::create([
            'user_id' => Auth::id(),
            'subtotal' => $subtotal,
            'platform_fee' => $platformFee,
            'total' => $total,
            'shipping_address' => $validated['shipping_address'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Create order items
        foreach ($cartItems as $cartItem) {
            $itemPlatformFee = $cartItem->subtotal * ($platformFeePercentage / 100);
            $sellerAmount = $cartItem->subtotal - $itemPlatformFee;

            ProductOrderItem::create([
                'product_order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'seller_id' => $cartItem->product->user_id,
                'quantity' => $cartItem->quantity,
                'price' => $cartItem->product->active_price,
                'seller_amount' => $sellerAmount,
                'platform_fee' => $itemPlatformFee,
            ]);
        }

        // Deduct from wallet
        $wallet->deductBalance($total, "Order #{$order->order_number}", 'product_purchase');

        // Complete order
        $order->complete('wallet', 'WALLET-' . time());

        // Clear cart
        ShoppingCart::clearCart(Auth::id());

        return redirect()->route('user.store.orders')->with('success', 'Order placed successfully!');
    }

    public function orders()
    {
        $orders = ProductOrder::where('user_id', Auth::id())
            ->with('items.product')
            ->latest()
            ->paginate(10);

        return view('user.store.orders', compact('orders'));
    }

    public function orderDetails(ProductOrder $order)
    {
        $this->authorize('view', $order);

        $order->load('items.product.user');

        return view('user.store.order-details', compact('order'));
    }

    public function addReview(Request $request, Product $product)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        // Check if user has purchased this product
        $hasPurchased = ProductOrderItem::whereHas('order', function ($query) {
            $query->where('user_id', Auth::id())
                ->where('payment_status', 'completed');
        })
            ->where('product_id', $product->id)
            ->exists();

        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'review' => $validated['review'] ?? null,
            'is_verified_purchase' => $hasPurchased,
        ]);

        return back()->with('success', 'Review submitted successfully!');
    }
}
