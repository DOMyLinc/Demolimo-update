<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductOrder;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreManagementController extends Controller
{
    public function index()
    {
        $products = Product::with(['user', 'category'])->latest()->paginate(20);

        $stats = [
            'total_products' => Product::count(),
            'published_products' => Product::published()->count(),
            'total_orders' => ProductOrder::count(),
            'total_revenue' => ProductOrder::completed()->sum('total'),
            'pending_reviews' => ProductReview::where('is_approved', false)->count(),
        ];

        return view('admin.store.index', compact('products', 'stats'));
    }

    public function categories()
    {
        $categories = ProductCategory::withCount('products')->get();

        return view('admin.store.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        ProductCategory::create($validated);

        return back()->with('success', 'Category created successfully!');
    }

    public function approveProduct(Product $product)
    {
        $product->update(['status' => 'published']);
        return back()->with('success', 'Product approved!');
    }

    public function featureProduct(Product $product)
    {
        $product->update(['is_featured' => !$product->is_featured]);
        $message = $product->is_featured ? 'Product featured!' : 'Product unfeatured.';
        return back()->with('success', $message);
    }

    public function orders()
    {
        $orders = ProductOrder::with(['user', 'items.product'])
            ->latest()
            ->paginate(20);

        return view('admin.store.orders', compact('orders'));
    }

    public function updateOrderStatus(Request $request, ProductOrder $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order->update($validated);

        return back()->with('success', 'Order status updated!');
    }

    public function reviews()
    {
        $reviews = ProductReview::with(['product', 'user'])
            ->latest()
            ->paginate(20);

        return view('admin.store.reviews', compact('reviews'));
    }

    public function approveReview(ProductReview $review)
    {
        $review->update(['is_approved' => true]);
        return back()->with('success', 'Review approved!');
    }

    public function deleteReview(ProductReview $review)
    {
        $review->delete();
        return back()->with('success', 'Review deleted!');
    }

    public function settings()
    {
        return view('admin.store.settings');
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'require_product_approval' => 'boolean',
            'require_review_approval' => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            config(['store.' . $key => $value]);
        }

        return back()->with('success', 'Store settings updated!');
    }
}
