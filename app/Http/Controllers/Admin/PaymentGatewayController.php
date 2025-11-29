<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use App\Models\ManualPaymentVerification;
use App\Models\FeeSetting;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    // Payment Gateways Management
    public function index()
    {
        $gateways = PaymentGateway::ordered()->paginate(20);

        $stats = [
            'total_gateways' => PaymentGateway::count(),
            'active_gateways' => PaymentGateway::active()->count(),
            'manual_gateways' => PaymentGateway::manual()->count(),
            'automatic_gateways' => PaymentGateway::automatic()->count(),
        ];

        return view('admin.payment_gateways.index', compact('gateways', 'stats'));
    }

    public function create()
    {
        return view('admin.payment_gateways.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:payment_gateways',
            'type' => 'required|in:automatic,manual',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
            'logo' => 'nullable|string',
            'credentials' => 'nullable|array',
            'settings' => 'nullable|array',
            'fixed_fee' => 'nullable|numeric|min:0',
            'percentage_fee' => 'nullable|numeric|min:0|max:100',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'supported_currencies' => 'nullable|array',
            'instructions' => 'nullable|string',
            'processing_time' => 'nullable|integer|min:0',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $gateway = PaymentGateway::create($validated);

        return redirect()->route('admin.payment_gateways.show', $gateway)
            ->with('success', 'Payment gateway created successfully!');
    }

    public function show(PaymentGateway $gateway)
    {
        $gateway->load('transactions');

        $stats = [
            'total_transactions' => $gateway->transactions()->count(),
            'completed_transactions' => $gateway->transactions()->completed()->count(),
            'pending_transactions' => $gateway->transactions()->pending()->count(),
            'total_amount' => $gateway->transactions()->completed()->sum('amount'),
            'total_fees' => $gateway->transactions()->completed()->sum('gateway_fee'),
        ];

        return view('admin.payment_gateways.show', compact('gateway', 'stats'));
    }

    public function edit(PaymentGateway $gateway)
    {
        return view('admin.payment_gateways.edit', compact('gateway'));
    }

    public function update(Request $request, PaymentGateway $gateway)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:payment_gateways,slug,' . $gateway->id,
            'type' => 'required|in:automatic,manual',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
            'logo' => 'nullable|string',
            'credentials' => 'nullable|array',
            'settings' => 'nullable|array',
            'fixed_fee' => 'nullable|numeric|min:0',
            'percentage_fee' => 'nullable|numeric|min:0|max:100',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'supported_currencies' => 'nullable|array',
            'instructions' => 'nullable|string',
            'processing_time' => 'nullable|integer|min:0',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $gateway->update($validated);

        return redirect()->route('admin.payment_gateways.show', $gateway)
            ->with('success', 'Payment gateway updated successfully!');
    }

    public function destroy(PaymentGateway $gateway)
    {
        if ($gateway->transactions()->exists()) {
            return back()->with('error', 'Cannot delete gateway with existing transactions.');
        }

        $gateway->delete();

        return redirect()->route('admin.payment_gateways.index')
            ->with('success', 'Payment gateway deleted successfully!');
    }

    public function toggleStatus(PaymentGateway $gateway)
    {
        $gateway->is_active = !$gateway->is_active;
        $gateway->save();

        $status = $gateway->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Payment gateway {$status} successfully!");
    }

    // Fee Settings Management
    public function feeSettings()
    {
        $settings = FeeSetting::all();

        return view('admin.payment_gateways.fee_settings', compact('settings'));
    }

    public function createFeeSetting()
    {
        return view('admin.payment_gateways.create_fee_setting');
    }

    public function storeFeeSetting(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|unique:fee_settings',
            'platform_fee_percentage' => 'required|numeric|min:0|max:100',
            'platform_fee_fixed' => 'nullable|numeric|min:0',
            'min_platform_fee' => 'nullable|numeric|min:0',
            'max_platform_fee' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        FeeSetting::create($validated);

        return redirect()->route('admin.payment_gateways.fee_settings')
            ->with('success', 'Fee setting created successfully!');
    }

    public function updateFeeSetting(Request $request, FeeSetting $feeSetting)
    {
        $validated = $request->validate([
            'platform_fee_percentage' => 'required|numeric|min:0|max:100',
            'platform_fee_fixed' => 'nullable|numeric|min:0',
            'min_platform_fee' => 'nullable|numeric|min:0',
            'max_platform_fee' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $feeSetting->update($validated);

        return back()->with('success', 'Fee setting updated successfully!');
    }

    public function deleteFeeSetting(FeeSetting $feeSetting)
    {
        $feeSetting->delete();

        return back()->with('success', 'Fee setting deleted successfully!');
    }

    // Manual Payment Verifications
    public function manualPayments()
    {
        $verifications = ManualPaymentVerification::with(['transaction.user', 'transaction.gateway'])
            ->latest()
            ->paginate(20);

        $stats = [
            'pending' => ManualPaymentVerification::pending()->count(),
            'approved' => ManualPaymentVerification::approved()->count(),
            'rejected' => ManualPaymentVerification::rejected()->count(),
        ];

        return view('admin.payment_gateways.manual_payments', compact('verifications', 'stats'));
    }

    public function showManualPayment(ManualPaymentVerification $verification)
    {
        $verification->load(['transaction.user', 'transaction.gateway', 'transaction.payable']);

        return view('admin.payment_gateways.manual_payment_show', compact('verification'));
    }

    public function approveManualPayment(Request $request, ManualPaymentVerification $verification)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        $verification->approve(auth()->id(), $validated['admin_notes'] ?? null);

        return back()->with('success', 'Manual payment approved successfully!');
    }

    public function rejectManualPayment(Request $request, ManualPaymentVerification $verification)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $verification->reject(auth()->id(), $validated['admin_notes']);

        return back()->with('success', 'Manual payment rejected successfully!');
    }

    // Transactions Management
    public function transactions()
    {
        $transactions = PaymentTransaction::with(['user', 'gateway', 'payable'])
            ->latest()
            ->paginate(20);

        $stats = [
            'total_transactions' => PaymentTransaction::count(),
            'completed' => PaymentTransaction::completed()->count(),
            'pending' => PaymentTransaction::pending()->count(),
            'failed' => PaymentTransaction::failed()->count(),
            'total_amount' => PaymentTransaction::completed()->sum('amount'),
            'total_platform_fees' => PaymentTransaction::completed()->sum('platform_fee'),
            'total_gateway_fees' => PaymentTransaction::completed()->sum('gateway_fee'),
        ];

        return view('admin.payment_gateways.transactions', compact('transactions', 'stats'));
    }

    public function showTransaction(PaymentTransaction $transaction)
    {
        $transaction->load(['user', 'gateway', 'payable', 'manualVerification']);

        return view('admin.payment_gateways.transaction_show', compact('transaction'));
    }

    public function refundTransaction(Request $request, PaymentTransaction $transaction)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        try {
            $transaction->refund();
            $transaction->notes = 'Refunded: ' . $validated['reason'];
            $transaction->save();

            return back()->with('success', 'Transaction refunded successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to refund transaction: ' . $e->getMessage());
        }
    }

    // Analytics
    public function analytics()
    {
        $totalRevenue = PaymentTransaction::completed()->sum('amount');
        $platformFees = PaymentTransaction::completed()->sum('platform_fee');
        $gatewayFees = PaymentTransaction::completed()->sum('gateway_fee');
        $artistEarnings = PaymentTransaction::completed()->sum('artist_amount');

        $revenueByType = PaymentTransaction::completed()
            ->selectRaw('type, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        $revenueByGateway = PaymentTransaction::completed()
            ->selectRaw('payment_gateway_id, SUM(amount) as total, COUNT(*) as count')
            ->with('gateway')
            ->groupBy('payment_gateway_id')
            ->get();

        $monthlyRevenue = PaymentTransaction::completed()
            ->selectRaw('DATE_FORMAT(completed_at, "%Y-%m") as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return view('admin.payment_gateways.analytics', compact(
            'totalRevenue',
            'platformFees',
            'gatewayFees',
            'artistEarnings',
            'revenueByType',
            'revenueByGateway',
            'monthlyRevenue'
        ));
    }
}
