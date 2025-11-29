<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\UserSubscription;
use Illuminate\Http\Request;

class SubscriptionManagementController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::withCount('userSubscriptions')->get();
        $activeSubscriptions = UserSubscription::where('status', 'active')->with(['user', 'subscription'])->paginate(20);

        $stats = [
            'total_plans' => Subscription::count(),
            'active_subscriptions' => UserSubscription::where('status', 'active')->count(),
            'total_revenue' => UserSubscription::where('status', 'active')
                ->join('subscriptions', 'user_subscriptions.subscription_id', '=', 'subscriptions.id')
                ->sum('subscriptions.price'),
        ];

        return view('admin.subscriptions.index', compact('subscriptions', 'activeSubscriptions', 'stats'));
    }
}
