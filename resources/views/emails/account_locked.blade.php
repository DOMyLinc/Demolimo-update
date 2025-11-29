<!DOCTYPE html>
<html>

<head>
    <title>Account Temporarily Locked</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #dc3545;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        .content {
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
        }

        .alert {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Security Alert</h2>
        </div>
        <div class="content">
            <p>Hello {{ $user->name }},</p>

            <div class="alert">
                <strong>Your account has been temporarily locked due to multiple failed login attempts.</strong>
            </div>

            <p>To protect your account, we have temporarily disabled login access.</p>

            <p><strong>Lockout Duration:</strong> {{ $duration }} minutes</p>
            <p><strong>Unlock Time:</strong> {{ $unlockAt->format('F j, Y, g:i a') }}</p>

            <p>If this was you, please wait until the unlock time to try again. If you did not attempt to log in, we
                recommend resetting your password immediately after your account is unlocked.</p>

            <p style="text-align: center; margin-top: 30px;">
                <a href="{{ route('password.request') }}"
                    style="display: inline-block; padding: 10px 20px; background-color: #dc3545; color: #fff; text-decoration: none; border-radius: 5px;">Reset
                    Password</a>
            </p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>