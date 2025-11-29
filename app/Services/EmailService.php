<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\EmailQueue;
use App\Models\EmailSettings;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    protected $settings;

    public function __construct()
    {
        $this->settings = EmailSettings::where('is_active', true)->first();
    }

    /**
     * Send email using template
     */
    public function sendTemplate($templateSlug, $toEmail, $toName, $data = [], $userId = null)
    {
        $template = EmailTemplate::where('slug', $templateSlug)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            Log::error("Email template not found: {$templateSlug}");
            return false;
        }

        $rendered = $template->render($data);

        return $this->send(
            $toEmail,
            $toName,
            $rendered['subject'],
            $rendered['body'],
            $userId,
            $templateSlug,
            $data
        );
    }

    /**
     * Send email
     */
    public function send($toEmail, $toName, $subject, $body, $userId = null, $templateSlug = null, $variables = [])
    {
        // Add logo to body if available
        if ($this->settings && $this->settings->logo_url) {
            $body = $this->addLogo($body);
        }

        // Queue or send immediately
        if ($this->settings && $this->settings->use_queue) {
            return $this->queue($toEmail, $toName, $subject, $body, $userId, $templateSlug, $variables);
        }

        return $this->sendNow($toEmail, $toName, $subject, $body);
    }

    /**
     * Queue email for later sending
     */
    protected function queue($toEmail, $toName, $subject, $body, $userId, $templateSlug, $variables)
    {
        EmailQueue::create([
            'user_id' => $userId,
            'to_email' => $toEmail,
            'to_name' => $toName,
            'subject' => $subject,
            'body' => $body,
            'template_slug' => $templateSlug,
            'variables' => $variables,
            'status' => 'pending',
        ]);

        return true;
    }

    /**
     * Send email immediately
     */
    protected function sendNow($toEmail, $toName, $subject, $body)
    {
        try {
            Mail::send([], [], function ($message) use ($toEmail, $toName, $subject, $body) {
                $message->to($toEmail, $toName)
                    ->subject($subject)
                    ->html($body);

                if ($this->settings) {
                    $message->from($this->settings->from_address, $this->settings->from_name);
                }
            });

            return true;
        } catch (\Exception $e) {
            Log::error("Email send failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Process email queue
     */
    public function processQueue($limit = 50)
    {
        $emails = EmailQueue::where('status', 'pending')
            ->where('attempts', '<', 3)
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        foreach ($emails as $email) {
            $email->attempts++;
            $email->save();

            $success = $this->sendNow($email->to_email, $email->to_name, $email->subject, $email->body);

            if ($success) {
                $email->status = 'sent';
                $email->sent_at = now();
            } else {
                $email->status = $email->attempts >= 3 ? 'failed' : 'pending';
                $email->error_message = 'Failed to send email';
            }

            $email->save();
        }

        return $emails->count();
    }

    /**
     * Add logo to email body
     */
    protected function addLogo($body)
    {
        $logoHtml = '<div style="text-align: center; margin-bottom: 20px;">
            <img src="' . $this->settings->logo_url . '" alt="Logo" style="max-width: 200px;">
        </div>';

        return $logoHtml . $body;
    }

    /**
     * Send verification email
     */
    public function sendVerificationEmail($user, $verificationLink)
    {
        return $this->sendTemplate('email_verification', $user->email, $user->name, [
            'user_name' => $user->name,
            'app_name' => config('app.name'),
            'verification_link' => $verificationLink,
        ], $user->id);
    }

    /**
     * Send welcome email
     */
    public function sendWelcomeEmail($user)
    {
        return $this->sendTemplate('welcome', $user->email, $user->name, [
            'user_name' => $user->name,
            'app_name' => config('app.name'),
            'dashboard_link' => url('/dashboard'),
        ], $user->id);
    }

    /**
     * Send withdrawal approved email
     */
    public function sendWithdrawalApproved($user, $amount)
    {
        return $this->sendTemplate('withdrawal_approved', $user->email, $user->name, [
            'user_name' => $user->name,
            'amount' => number_format($amount, 2),
        ], $user->id);
    }

    /**
     * Send monetization approved email
     */
    public function sendMonetizationApproved($user)
    {
        return $this->sendTemplate('monetization_approved', $user->email, $user->name, [
            'user_name' => $user->name,
        ], $user->id);
    }
}
