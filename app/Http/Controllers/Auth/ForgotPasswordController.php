<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    // Show the form where user enters email
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    // Process the form, send reset link
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'We can\'t find a user with that email address.']);
        }

        $token = Str::random(64);
        PasswordReset::updateOrCreate(
            ['email' => $user->email],
            ['token' => hash('sha256', $token), 'created_at' => Carbon::now()]
        );

        $resetLink = url(route('password.reset', ['token' => $token, 'email' => $user->email]));

        $this->emailService->sendTemplate(
            'password_reset',
            $user->email,
            $user->name ?? $user->email,
            [
                'user_name' => $user->name ?? $user->email,
                'app_name' => config('app.name'),
                'reset_link' => $resetLink,
            ]
        );

        return back()->with('status', 'Password reset link sent! Please check your email.');
    }
}
