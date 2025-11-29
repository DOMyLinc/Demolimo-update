<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Gift;
use App\Models\GiftTransaction;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GiftController extends Controller
{
    public function index()
    {
        $gifts = Gift::active()->get();
        return view('user.gifts.index', compact('gifts'));
    }

    public function send(Request $request, Track $track)
    {
        $validated = $request->validate([
            'gift_id' => 'required|exists:gifts,id',
            'quantity' => 'required|integer|min:1|max:100',
            'message' => 'nullable|string|max:500',
            'is_anonymous' => 'boolean',
        ]);

        $gift = Gift::findOrFail($validated['gift_id']);
        $totalAmount = $gift->price * $validated['quantity'];

        DB::beginTransaction();
        try {
            $transaction = GiftTransaction::create([
                'sender_id' => auth()->id(),
                'receiver_id' => $track->user_id,
                'track_id' => $track->id,
                'gift_id' => $gift->id,
                'quantity' => $validated['quantity'],
                'total_amount' => $totalAmount,
                'message' => $validated['message'] ?? null,
                'is_anonymous' => $request->has('is_anonymous'),
                'payment_status' => 'completed',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Gift sent successfully!',
                'transaction' => $transaction,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to send gift.',
            ], 500);
        }
    }

    public function received()
    {
        $transactions = GiftTransaction::with(['sender', 'gift', 'track'])
            ->where('receiver_id', auth()->id())
            ->latest()
            ->paginate(20);

        $stats = [
            'total_received' => GiftTransaction::where('receiver_id', auth()->id())->count(),
            'total_earnings' => GiftTransaction::where('receiver_id', auth()->id())
                ->completed()
                ->sum('artist_earning'),
            'this_month' => GiftTransaction::where('receiver_id', auth()->id())
                ->whereMonth('created_at', now()->month)
                ->completed()
                ->sum('artist_earning'),
        ];

        return view('user.gifts.received', compact('transactions', 'stats'));
    }

    public function earnings()
    {
        $earningsByGift = GiftTransaction::with('gift')
            ->select('gift_id', DB::raw('SUM(artist_earning) as total'), DB::raw('COUNT(*) as count'))
            ->where('receiver_id', auth()->id())
            ->completed()
            ->groupBy('gift_id')
            ->get();

        $monthlyEarnings = GiftTransaction::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('SUM(artist_earning) as total')
        )
            ->where('receiver_id', auth()->id())
            ->completed()
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        $totalEarnings = GiftTransaction::where('receiver_id', auth()->id())
            ->completed()
            ->sum('artist_earning');

        return view('user.gifts.earnings', compact('earningsByGift', 'monthlyEarnings', 'totalEarnings'));
    }
}
