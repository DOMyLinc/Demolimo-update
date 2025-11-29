<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NewsletterController extends Controller
{
    public function index()
    {
        $newsletters = Newsletter::latest()->paginate(20);
        return view('admin.newsletters.index', compact('newsletters'));
    }

    public function create()
    {
        return view('admin.newsletters.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $newsletter = Newsletter::create($request->only(['subject', 'content']));

        return redirect()->route('admin.newsletters.show', $newsletter)
            ->with('success', 'Newsletter created. Review and send when ready.');
    }

    public function show(Newsletter $newsletter)
    {
        return view('admin.newsletters.show', compact('newsletter'));
    }

    public function send(Newsletter $newsletter)
    {
        if ($newsletter->isSent()) {
            return back()->with('error', 'Newsletter has already been sent.');
        }

        // Get all verified users
        $users = User::where('is_verified', true)->get();

        $count = 0;
        foreach ($users as $user) {
            try {
                Mail::to($user->email)->send(new \App\Mail\NewsletterMail($newsletter));
                $count++;
            } catch (\Exception $e) {
                // Log error but continue
                Log::error('Newsletter send failed for user ' . $user->id . ': ' . $e->getMessage());
            }
        }

        $newsletter->update([
            'sent_at' => now(),
            'recipient_count' => $count,
        ]);

        return redirect()->route('admin.newsletters.index')
            ->with('success', "Newsletter sent to {$count} users successfully.");
    }

    public function destroy(Newsletter $newsletter)
    {
        if ($newsletter->isSent()) {
            return back()->with('error', 'Cannot delete sent newsletters.');
        }

        $newsletter->delete();

        return redirect()->route('admin.newsletters.index')
            ->with('success', 'Newsletter deleted successfully.');
    }
}
