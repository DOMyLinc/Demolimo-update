<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }

        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>🎵 DemoLimo</h1>
    </div>
    <div class="content">
        {!! nl2br(e($newsletter->content)) !!}
    </div>
    <div class="footer">
        <p>&copy; {{ date('Y') }} DemoLimo. All rights reserved.</p>
        <p>You're receiving this because you're a valued member of our community.</p>
    </div>
</body>

</html>