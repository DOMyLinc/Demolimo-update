<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ApiApplication;
use App\Models\Webhook;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DeveloperController extends Controller
{
    /**
     * Developer dashboard
     */
    public function index()
    {
        $user = Auth::user();

        // Check if API access is enabled
        if (!$user->api_access_enabled) {
            return view('user.developer.disabled');
        }

        $applications = ApiApplication::where('user_id', $user->id)
            ->withCount('tokens')
            ->get();

        $webhooks = Webhook::where('user_id', $user->id)->get();

        $stats = [
            'total_apps' => $applications->count(),
            'active_apps' => $applications->where('is_active', true)->count(),
            'total_webhooks' => $webhooks->count(),
            'api_calls_today' => $this->getApiCallsToday($user->id),
        ];

        return view('user.developer.index', compact('applications', 'webhooks', 'stats'));
    }

    /**
     * API documentation
     */
    public function documentation()
    {
        return view('user.developer.documentation');
    }

    /**
     * Create application
     */
    public function createApplication()
    {
        $user = Auth::user();

        if (!$user->api_access_enabled) {
            return redirect()->route('user.developer.index')
                ->with('error', 'API access is disabled for your account.');
        }

        return view('user.developer.create-app');
    }

    /**
     * Store application
     */
    public function storeApplication(Request $request)
    {
        $user = Auth::user();

        if (!$user->api_access_enabled) {
            return back()->with('error', 'API access is disabled.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'website_url' => 'nullable|url',
            'redirect_uris' => 'nullable|array',
            'redirect_uris.*' => 'url',
        ]);

        $credentials = ApiApplication::generateCredentials();

        $app = ApiApplication::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'website_url' => $validated['website_url'],
            'redirect_uris' => $validated['redirect_uris'] ?? [],
            'client_id' => $credentials['client_id'],
            'client_secret' => $credentials['client_secret'],
            'is_approved' => !config('api.require_approval', false),
            'is_active' => true,
            'rate_limit' => config('api.default_rate_limit', 1000),
        ]);

        return redirect()->route('user.developer.show-app', $app)
            ->with('success', 'Application created successfully!')
            ->with('client_secret', $credentials['client_secret']); // Show once
    }

    /**
     * Show application
     */
    public function showApplication(ApiApplication $app)
    {
        if ($app->user_id !== Auth::id()) {
            abort(403);
        }

        $app->load('tokens');

        $recentLogs = \DB::table('api_logs')
            ->where('application_id', $app->id)
            ->latest()
            ->limit(100)
            ->get();

        return view('user.developer.show-app', compact('app', 'recentLogs'));
    }

    /**
     * Regenerate secret
     */
    public function regenerateSecret(ApiApplication $app)
    {
        if ($app->user_id !== Auth::id()) {
            abort(403);
        }

        $newSecret = Str::random(64);
        $app->update(['client_secret' => $newSecret]);

        return back()
            ->with('success', 'Client secret regenerated!')
            ->with('client_secret', $newSecret);
    }

    /**
     * Delete application
     */
    public function deleteApplication(ApiApplication $app)
    {
        if ($app->user_id !== Auth::id()) {
            abort(403);
        }

        // Revoke all tokens
        $app->tokens()->delete();

        $app->delete();

        return redirect()->route('user.developer.index')
            ->with('success', 'Application deleted successfully!');
    }

    /**
     * Webhooks management
     */
    public function webhooks()
    {
        $webhooks = Webhook::where('user_id', Auth::id())
            ->withCount('logs')
            ->get();

        $availableEvents = $this->getAvailableEvents();

        return view('user.developer.webhooks', compact('webhooks', 'availableEvents'));
    }

    /**
     * Create webhook
     */
    public function storeWebhook(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'events' => 'required|array|min:1',
            'events.*' => 'string',
        ]);

        $webhook = Webhook::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'url' => $validated['url'],
            'secret' => Str::random(32),
            'events' => $validated['events'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Webhook created successfully!');
    }

    /**
     * Test webhook
     */
    public function testWebhook(Webhook $webhook)
    {
        if ($webhook->user_id !== Auth::id()) {
            abort(403);
        }

        $testPayload = [
            'event' => 'webhook.test',
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'message' => 'This is a test webhook',
            ],
        ];

        $response = $this->sendWebhook($webhook, 'webhook.test', $testPayload);

        return back()->with('success', 'Test webhook sent! Check your endpoint.');
    }

    /**
     * Delete webhook
     */
    public function deleteWebhook(Webhook $webhook)
    {
        if ($webhook->user_id !== Auth::id()) {
            abort(403);
        }

        $webhook->delete();

        return back()->with('success', 'Webhook deleted successfully!');
    }

    /**
     * Webhook logs
     */
    public function webhookLogs(Webhook $webhook)
    {
        if ($webhook->user_id !== Auth::id()) {
            abort(403);
        }

        $logs = $webhook->logs()->latest()->paginate(50);

        return view('user.developer.webhook-logs', compact('webhook', 'logs'));
    }

    /**
     * Get available webhook events
     */
    protected function getAvailableEvents()
    {
        return [
            'track.uploaded' => 'Track Uploaded',
            'track.deleted' => 'Track Deleted',
            'track.liked' => 'Track Liked',
            'album.created' => 'Album Created',
            'playlist.created' => 'Playlist Created',
            'user.followed' => 'User Followed',
            'payment.received' => 'Payment Received',
            'subscription.renewed' => 'Subscription Renewed',
            'event.created' => 'Event Created',
            'product.sold' => 'Product Sold',
        ];
    }

    /**
     * Send webhook
     */
    protected function sendWebhook($webhook, $event, $payload)
    {
        try {
            $signature = hash_hmac('sha256', json_encode($payload), $webhook->secret);

            $response = \Http::withHeaders([
                'X-Webhook-Signature' => $signature,
                'X-Webhook-Event' => $event,
            ])->post($webhook->url, $payload);

            WebhookLog::create([
                'webhook_id' => $webhook->id,
                'event' => $event,
                'payload' => json_encode($payload),
                'response_code' => $response->status(),
                'response_body' => $response->body(),
                'success' => $response->successful(),
            ]);

            if ($response->successful()) {
                $webhook->update([
                    'failed_attempts' => 0,
                    'last_triggered_at' => now(),
                ]);
            } else {
                $webhook->increment('failed_attempts');
            }

            return $response;
        } catch (\Exception $e) {
            WebhookLog::create([
                'webhook_id' => $webhook->id,
                'event' => $event,
                'payload' => json_encode($payload),
                'response_code' => 0,
                'response_body' => $e->getMessage(),
                'success' => false,
            ]);

            $webhook->increment('failed_attempts');
        }
    }

    /**
     * Get API calls today
     */
    protected function getApiCallsToday($userId)
    {
        return \DB::table('api_logs')
            ->whereDate('created_at', today())
            ->whereIn('application_id', function ($query) use ($userId) {
                $query->select('id')
                    ->from('api_applications')
                    ->where('user_id', $userId);
            })
            ->count();
    }
}
