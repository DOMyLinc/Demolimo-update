<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LiveStream;
use App\Models\LiveStreamMessage;
use App\Models\FeatureFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LiveStreamController extends Controller
{
    public function index()
    {
        if (!FeatureFlag::isEnabled('enable_livestreaming')) {
            return back()->with('error', 'Live streaming is currently disabled.');
        }

        $liveStreams = LiveStream::where('status', 'live')
            ->with('user')
            ->orderBy('current_viewers', 'desc')
            ->get();

        $scheduled = LiveStream::where('status', 'scheduled')
            ->where('scheduled_at', '>', now())
            ->with('user')
            ->orderBy('scheduled_at')
            ->get();

        return view('user.livestreams.index', compact('liveStreams', 'scheduled'));
    }

    public function show(LiveStream $stream)
    {
        $stream->load('user');

        // Increment viewers if live
        if ($stream->status === 'live') {
            $stream->increment('current_viewers');
            $stream->increment('total_views');

            if ($stream->current_viewers > $stream->peak_viewers) {
                $stream->update(['peak_viewers' => $stream->current_viewers]);
            }
        }

        return view('user.livestreams.show', compact('stream'));
    }

    public function create()
    {
        $user = Auth::user();

        if (!$user->isPro()) {
            return redirect()->route('user.subscription.plans')
                ->with('error', 'Live streaming requires a Pro subscription.');
        }

        if (!$user->can_livestream) {
            return back()->with('error', 'Your account is not enabled for live streaming. Contact support.');
        }

        return view('user.livestreams.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isPro() || !$user->can_livestream) {
            return back()->with('error', 'Live streaming not available.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:5120',
            'scheduled_at' => 'nullable|date|after:now',
            'enable_chat' => 'boolean',
            'enable_donations' => 'boolean',
        ]);

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('livestreams/thumbnails', 'public');
        }

        $stream = LiveStream::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'thumbnail' => $thumbnailPath,
            'stream_key' => Str::random(32),
            'status' => $validated['scheduled_at'] ? 'scheduled' : 'live',
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'enable_chat' => $validated['enable_chat'] ?? true,
            'enable_donations' => $validated['enable_donations'] ?? true,
        ]);

        // Generate stream URL (would integrate with streaming service)
        $stream->update([
            'stream_url' => "rtmp://stream.yourplatform.com/live/{$stream->stream_key}",
        ]);

        if (!$validated['scheduled_at']) {
            $stream->start();
        }

        return redirect()->route('user.livestreams.show', $stream)
            ->with('success', 'Live stream created!');
    }

    public function start(LiveStream $stream)
    {
        if ($stream->user_id !== Auth::id()) {
            abort(403);
        }

        $stream->start();

        return response()->json(['success' => true]);
    }

    public function end(LiveStream $stream)
    {
        if ($stream->user_id !== Auth::id()) {
            abort(403);
        }

        $stream->end();

        return response()->json(['success' => true]);
    }

    public function sendMessage(Request $request, LiveStream $stream)
    {
        if (!$stream->enable_chat) {
            return response()->json(['error' => 'Chat is disabled'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $message = LiveStreamMessage::create([
            'stream_id' => $stream->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
        ]);

        // Broadcast message via WebSocket
        // broadcast(new LiveStreamMessageSent($message));

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function getMessages(LiveStream $stream)
    {
        $messages = $stream->messages()
            ->with('user')
            ->where('is_deleted', false)
            ->latest()
            ->limit(100)
            ->get()
            ->reverse()
            ->values();

        return response()->json($messages);
    }
}
