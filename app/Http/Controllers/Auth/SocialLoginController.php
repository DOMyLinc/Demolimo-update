<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialProvider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    /**
     * Redirect to provider
     */
    public function redirect(string $provider)
    {
        $socialProvider = SocialProvider::where('provider', $provider)
            ->where('enabled', true)
            ->first();

        if (!$socialProvider || !$socialProvider->isConfigured()) {
            return redirect()->route('login')
                ->with('error', 'Social login provider is not available.');
        }

        try {
            // Configure Socialite dynamically
            config([
                "services.{$provider}.client_id" => $socialProvider->client_id,
                "services.{$provider}.client_secret" => $socialProvider->client_secret,
                "services.{$provider}.redirect" => $socialProvider->redirect_url,
            ]);

            return Socialite::driver($provider)
                ->scopes($socialProvider->scopes ?? [])
                ->redirect();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Failed to connect to ' . $socialProvider->name . ': ' . $e->getMessage());
        }
    }

    /**
     * Handle provider callback
     */
    public function callback(string $provider)
    {
        $socialProvider = SocialProvider::where('provider', $provider)
            ->where('enabled', true)
            ->first();

        if (!$socialProvider) {
            return redirect()->route('login')
                ->with('error', 'Social login provider is not available.');
        }

        try {
            // Configure Socialite dynamically
            config([
                "services.{$provider}.client_id" => $socialProvider->client_id,
                "services.{$provider}.client_secret" => $socialProvider->client_secret,
                "services.{$provider}.redirect" => $socialProvider->redirect_url,
            ]);

            $socialUser = Socialite::driver($provider)->user();

            // Find or create user
            $user = $this->findOrCreateUser($socialUser, $provider);

            // Log the user in
            Auth::login($user, true);

            return redirect()->intended(route('home'))
                ->with('success', 'Successfully logged in with ' . $socialProvider->name . '!');
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Failed to authenticate with ' . $socialProvider->name . ': ' . $e->getMessage());
        }
    }

    /**
     * Find or create user from social provider
     */
    protected function findOrCreateUser($socialUser, string $provider): User
    {
        // Try to find user by email
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Update social provider info if needed
            $this->updateSocialInfo($user, $socialUser, $provider);
            return $user;
        }

        // Create new user
        $user = User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
            'email' => $socialUser->getEmail(),
            'password' => Hash::make(Str::random(32)), // Random password
            'email_verified_at' => now(), // Auto-verify social login emails
            'avatar' => $socialUser->getAvatar(),
            'role' => 'user',
            'username' => \App\Helpers\UsernameHelper::generate($socialUser->getName() ?? $socialUser->getNickname() ?? 'User'),
        ]);

        $this->updateSocialInfo($user, $socialUser, $provider);

        return $user;
    }

    /**
     * Update user's social provider information
     */
    protected function updateSocialInfo(User $user, $socialUser, string $provider)
    {
        // You could store social provider IDs in a separate table
        // For now, we'll just update the avatar if it's not set
        if (!$user->avatar && $socialUser->getAvatar()) {
            $user->update(['avatar' => $socialUser->getAvatar()]);
        }
    }
}
