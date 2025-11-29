<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'subject',
        'body',
        'variables',
        'from_name',
        'from_email',
        'is_active',
        'category',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    public function render($data = [])
    {
        $body = $this->body;
        $subject = $this->subject;

        foreach ($data as $key => $value) {
            $body = str_replace('{' . $key . '}', $value, $body);
            $subject = str_replace('{' . $key . '}', $value, $subject);
        }

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }

    public static function getDefaultTemplates()
    {
        return [
            [
                'name' => 'Welcome Email',
                'slug' => 'welcome',
                'subject' => 'Welcome to {app_name}!',
                'body' => '<h1>Welcome {user_name}!</h1><p>Thank you for joining {app_name}. We\'re excited to have you!</p><p><a href="{dashboard_link}">Get Started</a></p>',
                'variables' => ['user_name', 'app_name', 'dashboard_link'],
                'category' => 'general',
            ],
            [
                'name' => 'Email Verification',
                'slug' => 'email_verification',
                'subject' => 'Verify Your Email - {app_name}',
                'body' => '<h1>Verify Your Email</h1><p>Hi {user_name},</p><p>Please click the link below to verify your email:</p><p><a href="{verification_link}">Verify Email</a></p>',
                'variables' => ['user_name', 'app_name', 'verification_link'],
                'category' => 'transactional',
            ],
            [
                'name' => 'Password Reset',
                'slug' => 'password_reset',
                'subject' => 'Reset Your Password - {app_name}',
                'body' => '<h1>Reset Password</h1><p>Hi {user_name},</p><p>Click the link below to reset your password:</p><p><a href="{reset_link}">Reset Password</a></p>',
                'variables' => ['user_name', 'app_name', 'reset_link'],
                'category' => 'transactional',
            ],
            [
                'name' => 'Withdrawal Approved',
                'slug' => 'withdrawal_approved',
                'subject' => 'Withdrawal Approved - ${amount}',
                'body' => '<h1>Withdrawal Approved</h1><p>Hi {user_name},</p><p>Your withdrawal of ${amount} has been approved and processed.</p>',
                'variables' => ['user_name', 'amount'],
                'category' => 'transactional',
            ],
            [
                'name' => 'Monetization Approved',
                'slug' => 'monetization_approved',
                'subject' => 'Monetization Approved!',
                'body' => '<h1>Congratulations!</h1><p>Hi {user_name},</p><p>Your monetization request has been approved. You can now start earning!</p>',
                'variables' => ['user_name'],
                'category' => 'transactional',
            ],
        ];
    }
}
