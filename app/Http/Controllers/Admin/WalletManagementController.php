<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointPackage;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Http\Request;

class WalletManagementController extends Controller
{
    public function index()
    {
        $stats = [
            'total_balance' => Wallet::sum('balance'),
            'total_points' => Wallet::sum('points'),
            'pending_withdrawals' => WithdrawalRequest::where('status', 'pending')->sum('amount'),
            'total_withdrawn' => Wallet::sum('total_withdrawn'),
            'total_earned' => Wallet::sum('total_earned'),
        ];

        $recentTransactions = WalletTransaction::with('user')
            ->latest()
            ->limit(20)
            ->get();

        $topEarners = Wallet::with('user')
            ->orderBy('total_earned', 'desc')
            ->limit(10)
            ->get();

        return view('admin.wallet.index', compact('stats', 'recentTransactions', 'topEarners'));
    }

    /**
     * Point Packages Management
     */
    public function packages()
    {
        $packages = PointPackage::orderBy('sort_order')->get();
        return view('admin.wallet.packages', compact('packages'));
    }

    public function createPackage()
    {
        return view('admin.wallet.package-form');
    }

    public function storePackage(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'points' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'bonus_points' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        PointPackage::create($validated);

        return redirect()->route('admin.wallet.packages')
            ->with('success', 'Point package created successfully!');
    }

    public function editPackage(PointPackage $package)
    {
        return view('admin.wallet.package-form', compact('package'));
    }

    public function updatePackage(Request $request, PointPackage $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'points' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'bonus_points' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $package->update($validated);

        return redirect()->route('admin.wallet.packages')
            ->with('success', 'Point package updated successfully!');
    }

    public function deletePackage(PointPackage $package)
    {
        $package->delete();
        return back()->with('success', 'Point package deleted!');
    }

    /**
     * User Wallet Management
     */
    public function userWallet(User $user)
    {
        $wallet = $user->wallet ?? Wallet::create(['user_id' => $user->id]);

        $transactions = WalletTransaction::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        $pointTransactions = PointTransaction::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return view('admin.wallet.user-wallet', compact('user', 'wallet', 'transactions', 'pointTransactions'));
    }

    public function addBalance(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
        ]);

        $wallet = $user->wallet ?? Wallet::create(['user_id' => $user->id]);
        $wallet->addBalance($validated['amount'], $validated['description'], 'admin_add');

        return back()->with('success', "Added \${$validated['amount']} to {$user->name}'s wallet!");
    }

    public function deductBalance(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
        ]);

        $wallet = $user->wallet ?? Wallet::create(['user_id' => $user->id]);

        try {
            $wallet->deductBalance($validated['amount'], $validated['description'], 'admin_remove');
            return back()->with('success', "Deducted \${$validated['amount']} from {$user->name}'s wallet!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function addPoints(Request $request, User $user)
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:1',
            'description' => 'required|string|max:500',
        ]);

        $wallet = $user->wallet ?? Wallet::create(['user_id' => $user->id]);
        $wallet->addPoints($validated['points'], $validated['description'], 'admin_add');

        return back()->with('success', "Added {$validated['points']} points to {$user->name}!");
    }

    public function deductPoints(Request $request, User $user)
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:1',
            'description' => 'required|string|max:500',
        ]);

        $wallet = $user->wallet ?? Wallet::create(['user_id' => $user->id]);

        try {
            $wallet->deductPoints($validated['points'], $validated['description'], 'admin_remove');
            return back()->with('success', "Deducted {$validated['points']} points from {$user->name}!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Withdrawal Requests
     */
    public function withdrawals()
    {
        $pending = WithdrawalRequest::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $processed = WithdrawalRequest::with(['user', 'processor'])
            ->whereIn('status', ['completed', 'rejected'])
            ->latest()
            ->paginate(20);

        $stats = [
            'pending_count' => WithdrawalRequest::where('status', 'pending')->count(),
            'pending_amount' => WithdrawalRequest::where('status', 'pending')->sum('amount'),
            'completed_today' => WithdrawalRequest::where('status', 'completed')
                ->whereDate('processed_at', today())
                ->sum('amount'),
        ];

        return view('admin.wallet.withdrawals', compact('pending', 'processed', 'stats'));
    }

    public function approveWithdrawal(Request $request, WithdrawalRequest $withdrawal)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $withdrawal->approve(auth()->id(), $validated['notes'] ?? null);

        return back()->with('success', 'Withdrawal approved and processed!');
    }

    public function rejectWithdrawal(Request $request, WithdrawalRequest $withdrawal)
    {
        $validated = $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $withdrawal->reject(auth()->id(), $validated['notes']);

        return back()->with('success', 'Withdrawal rejected. Balance returned to user.');
    }

    /**
     * Wallet Settings
     */
    public function settings()
    {
        $settings = [
            'points_to_cash_rate' => \App\Models\PlatformSetting::get('points_to_cash_rate', 0.01),
            'minimum_withdrawal' => \App\Models\PlatformSetting::get('minimum_withdrawal', 50),
            'withdrawal_fee_percentage' => \App\Models\PlatformSetting::get('withdrawal_fee_percentage', 0),
            'enable_points_system' => \App\Models\PlatformSetting::get('enable_points_system', true),
            'enable_wallet_system' => \App\Models\PlatformSetting::get('enable_wallet_system', true),
        ];

        return view('admin.wallet.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'points_to_cash_rate' => 'required|numeric|min:0',
            'minimum_withdrawal' => 'required|numeric|min:0',
            'withdrawal_fee_percentage' => 'required|numeric|min:0|max:100',
            'enable_points_system' => 'boolean',
            'enable_wallet_system' => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            \App\Models\PlatformSetting::set($key, $value);
        }

        return back()->with('success', 'Wallet settings updated!');
    }
}
