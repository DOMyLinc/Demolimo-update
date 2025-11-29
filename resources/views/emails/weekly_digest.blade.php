<!DOCTYPE html>
<html>

<head>
    <title>Your Weekly Digest</title>
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
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
        }

        .stat-box {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
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
            <h2>Your Weekly Music Stats</h2>
        </div>
        <div class="content">
            <p>Here's how your music performed this week:</p>

            <div class="stat-box">
                <div class="stat-number">{{ $stats['new_followers'] }}</div>
                <div>New Followers</div>
            </div>

            <div class="stat-box">
                <div class="stat-number">{{ $stats['new_likes'] }}</div>
                <div>New Likes</div>
            </div>

            <div class="stat-box">
                <div class="stat-number">{{ $stats['new_plays'] }}</div>
                <div>New Plays</div>
            </div>

            <p style="text-align: center; margin-top: 30px;">
                <a href="{{ route('studio.index') }}"
                    style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px;">Go
                    to Studio</a>
            </p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>