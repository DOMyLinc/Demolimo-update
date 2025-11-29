<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\EmailSettings;
use App\Models\EmailQueue;
use App\Services\EmailService;
use Illuminate\Http\Request;

class EmailManagementController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Email Dashboard
     */
    public function index()
    {
        $stats = [
            'total_templates' => EmailTemplate::count(),
            'active_templates' => EmailTemplate::where('is_active', true)->count(),
            'pending_emails' => EmailQueue::where('status', 'pending')->count(),
            'sent_today' => EmailQueue::where('status', 'sent')
                ->whereDate('sent_at', today())
                ->count(),
            'failed_emails' => EmailQueue::where('status', 'failed')->count(),
        ];

        $recentEmails = EmailQueue::with('user')
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.email.index', compact('stats', 'recentEmails'));
    }

    /**
     * Email Templates
     */
    public function templates()
    {
        $templates = EmailTemplate::orderBy('category')->orderBy('name')->get();
        return view('admin.email.templates', compact('templates'));
    }

    public function createTemplate()
    {
        return view('admin.email.template-form');
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:email_templates,slug',
            'subject' => 'required|string|max:500',
            'body' => 'required|string',
            'variables' => 'nullable|json',
            'from_name' => 'nullable|string|max:255',
            'from_email' => 'nullable|email',
            'category' => 'required|string',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['variables'])) {
            $validated['variables'] = json_decode($validated['variables'], true);
        }

        EmailTemplate::create($validated);

        return redirect()->route('admin.email.templates')
            ->with('success', 'Email template created successfully!');
    }

    public function editTemplate(EmailTemplate $template)
    {
        return view('admin.email.template-form', compact('template'));
    }

    public function updateTemplate(Request $request, EmailTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:email_templates,slug,' . $template->id,
            'subject' => 'required|string|max:500',
            'body' => 'required|string',
            'variables' => 'nullable|json',
            'from_name' => 'nullable|string|max:255',
            'from_email' => 'nullable|email',
            'category' => 'required|string',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['variables'])) {
            $validated['variables'] = json_decode($validated['variables'], true);
        }

        $template->update($validated);

        return redirect()->route('admin.email.templates')
            ->with('success', 'Email template updated successfully!');
    }

    public function deleteTemplate(EmailTemplate $template)
    {
        $template->delete();
        return back()->with('success', 'Email template deleted!');
    }

    /**
     * Email Settings
     */
    public function settings()
    {
        $settings = EmailSettings::first() ?? new EmailSettings();
        return view('admin.email.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'driver' => 'required|string',
            'host' => 'nullable|string',
            'port' => 'nullable|integer',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'encryption' => 'nullable|string',
            'from_address' => 'required|email',
            'from_name' => 'required|string',
            'logo_url' => 'nullable|url',
            'use_queue' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $settings = EmailSettings::first();

        if ($settings) {
            $settings->update($validated);
        } else {
            EmailSettings::create($validated);
        }

        return back()->with('success', 'Email settings updated!');
    }

    /**
     * Email Queue
     */
    public function queue()
    {
        $pending = EmailQueue::where('status', 'pending')
            ->with('user')
            ->latest()
            ->paginate(20);

        $failed = EmailQueue::where('status', 'failed')
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('admin.email.queue', compact('pending', 'failed'));
    }

    public function processQueue()
    {
        $processed = $this->emailService->processQueue(50);

        return back()->with('success', "Processed {$processed} emails from queue!");
    }

    public function retryFailed()
    {
        EmailQueue::where('status', 'failed')
            ->update([
                'status' => 'pending',
                'attempts' => 0,
                'error_message' => null,
            ]);

        return back()->with('success', 'All failed emails reset to pending!');
    }

    public function clearQueue()
    {
        EmailQueue::where('status', 'sent')->delete();
        return back()->with('success', 'Sent emails cleared from queue!');
    }

    /**
     * Test Email
     */
    public function testEmail(Request $request)
    {
        $validated = $request->validate([
            'to_email' => 'required|email',
            'template_slug' => 'required|exists:email_templates,slug',
        ]);

        $success = $this->emailService->sendTemplate(
            $validated['template_slug'],
            $validated['to_email'],
            'Test User',
            [
                'user_name' => 'Test User',
                'app_name' => config('app.name'),
                'verification_link' => url('/'),
                'dashboard_link' => url('/dashboard'),
                'reset_link' => url('/'),
                'amount' => '100.00',
            ]
        );

        if ($success) {
            return back()->with('success', 'Test email sent successfully!');
        }

        return back()->with('error', 'Failed to send test email!');
    }

    /**
     * Initialize Default Templates
     */
    public function initializeTemplates()
    {
        $defaults = EmailTemplate::getDefaultTemplates();

        foreach ($defaults as $template) {
            EmailTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }

        return back()->with('success', 'Default email templates initialized!');
    }
}
