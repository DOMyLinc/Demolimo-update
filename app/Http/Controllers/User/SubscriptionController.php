<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function index()
    {
        $plans = Subscription::where('is_active', true)->get();

        // Fallback if no plans in DB (for demo purposes)
        if ($plans->isEmpty()) {
            $plans = collect([
                (object) [
                    'id' => 1,
                    'name' => 'Free',
                    'price_monthly' => 0,
                    'features' => ['Upload 5 Tracks', 'Basic Stats', 'Standard Support'],
                    'is_default' => true
                ],
                (object) [
                    'id' => 2,
                    'name' => 'Pro',
                    'price_monthly' => 9.99,
                    'features' => ['Unlimited Uploads', 'Advanced Stats', 'Priority Support', 'Pro Badge'],
                    'is_default' => false
                ]
            ]);
        }

        return view('user.subscription.index', compact('plans'));
    }

    public function store(Request $request)
    {
        // Mock Subscription Logic
        // In real app, redirect to Stripe/PayPal

        return redirect()->route('user.subscription.index')->with('success', 'Subscribed successfully (Mock)!');
    }
}
