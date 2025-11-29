<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gift;
use App\Models\GiftTransaction;
use Illuminate\Http\Request;

class GiftController extends Controller
{
    public function index()
    {
        $gifts = Gift::withCount('transactions')
            ->orderBy('sort_order')
            ->paginate(20);

        $stats = [
            'total_gifts' => Gift::count(),
            'active_gifts' => Gift::where('is_active', true)->count(),
            'total_transactions' => GiftTransaction::count(),
            'total_revenue' => GiftTransaction::completed()->sum('total_amount'),
            'platform_earnings' => GiftTransaction::completed()->sum('platform_fee'),
        ];

        return view('admin.gifts.index', compact('gifts', 'stats'));
    }

    public function create()
    {
        return view('admin.gifts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:10',
            'price' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        Gift::create($validated);

        return redirect()->route('admin.gifts.index')
            ->with('success', 'Gift created successfully!');
    }

    public function edit(Gift $gift)
    {
        return view('admin.gifts.edit', compact('gift'));
    }

    public function update(Request $request, Gift $gift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:10',
            'price' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $gift->update($validated);

        return redirect()->route('admin.gifts.index')
            ->with('success', 'Gift updated successfully!');
    }

    public function destroy(Gift $gift)
    {
        // Check if gift has transactions
        if ($gift->transactions()->count() > 0) {
            return back()->with('error', 'Cannot delete gift with existing transactions.');
        }

        $gift->delete();

        return redirect()->route('admin.gifts.index')
            ->with('success', 'Gift deleted successfully!');
    }

    public function analytics()
    {
        $topGifts = Gift::withCount('transactions')
            ->withSum('transactions as revenue', 'total_amount')
            ->orderByDesc('transactions_count')
            ->limit(10)
            ->get();

        $recentTransactions = GiftTransaction::with(['sender', 'receiver', 'gift', 'track'])
            ->latest()
            ->limit(50)
            ->get();

        $monthlyRevenue = GiftTransaction::completed()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(total_amount) as total')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        $stats = [
            'total_revenue' => GiftTransaction::completed()->sum('total_amount'),
            'platform_earnings' => GiftTransaction::completed()->sum('platform_fee'),
            'artist_earnings' => GiftTransaction::completed()->sum('artist_earning'),
            'total_transactions' => GiftTransaction::completed()->count(),
            'average_transaction' => GiftTransaction::completed()->avg('total_amount'),
        ];

        return view('admin.gifts.analytics', compact('topGifts', 'recentTransactions', 'monthlyRevenue', 'stats'));
    }
}
