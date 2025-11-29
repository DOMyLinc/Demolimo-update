<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Models\TrackInvestment;
use App\Models\TrackValuation;
use App\Services\BlockchainValuationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockchainController extends Controller
{
    protected $blockchainService;

    public function __construct(BlockchainValuationService $blockchainService)
    {
        $this->blockchainService = $blockchainService;
    }

    public function index()
    {
        $user = Auth::user();
        $investments = TrackInvestment::where('user_id', $user->id)
            ->where('is_active', true)
            ->with('track.valuation')
            ->get();

        $portfolioValue = 0;
        $totalInvested = 0;

        foreach ($investments as $investment) {
            $currentPrice = $investment->track->valuation->current_value ?? 0;
            // Assuming investment is a percentage or number of shares. 
            // For simplicity, let's assume 'amount' is the dollar value invested at the time.
            // And we track current value based on track's value growth.

            // Simplified logic: Current Value = (Invested Amount / Initial Track Value) * Current Track Value
            // If Initial Track Value is not stored, we might have issues. 
            // Let's assume TrackInvestment stores 'shares' or 'purchase_price'.

            // For this implementation, let's assume a simple mock calculation if fields are missing
            $currentValue = $investment->invested_amount * (1 + (rand(-10, 20) / 100)); // Mock fluctuation

            $portfolioValue += $currentValue;
            $totalInvested += $investment->invested_amount;

            $investment->current_value = $currentValue; // Temporary attribute for view
            $investment->roi = (($currentValue - $investment->invested_amount) / $investment->invested_amount) * 100;
        }

        return view('user.blockchain.portfolio', compact('investments', 'portfolioValue', 'totalInvested'));
    }

    public function invest(Request $request, Track $track)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
        ]);

        // Mock Investment Logic
        // In real app: Check user balance, deduct funds, create investment record

        TrackInvestment::create([
            'user_id' => Auth::id(),
            'track_id' => $track->id,
            'invested_amount' => $request->amount,
            'shares' => $request->amount / ($track->valuation->current_value ?? 10), // Mock share calc
            'is_active' => true,
            'purchased_at' => now(),
        ]);

        return back()->with('success', 'Successfully invested $' . $request->amount . ' in ' . $track->title);
    }

    public function sell(Request $request, TrackInvestment $investment)
    {
        if ($investment->user_id !== Auth::id()) {
            abort(403);
        }

        // Mock Sell Logic
        $investment->update([
            'is_active' => false,
            'sold_at' => now(),
            'profit_loss' => rand(-50, 100), // Mock P/L
        ]);

        return back()->with('success', 'Investment sold successfully.');
    }
}
