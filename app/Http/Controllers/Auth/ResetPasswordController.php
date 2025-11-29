<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ResetPasswordController extends Controller
{
    // Show reset form (token & email in query string)
    public function showResetForm(Request $request, $token)
    {
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    // Handle reset submission
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $record = PasswordReset::where('email', $request->email)
            ->where('token', hash('sha256', $request->token))
            ->first();

        if (!$record || $record->isExpired()) {
            return back()->withErrors(['email' => 'The password reset token is invalid or has expired.']);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->update(['password' => Hash::make($request->password)]);

        // Delete the token after successful reset
        $record->delete();

        return redirect()->route('login')->with('status', 'Your password has been reset! You can now log in.');
    }
}
