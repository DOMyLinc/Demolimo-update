<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DemoLimo Installation - @yield('title')</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ff3333;
            --primary-dark: #cc0000;
            --secondary: #ff6b00;
            --success: #00ff88;
            --warning: #ffaa00;
            --danger: #ff3333;
            --bg-dark: #0f0f0f;
            --bg-panel: #1a1a1a;
            --bg-card: #222222;
            --text-main: #ffffff;
            --text-muted: #888888;
            --border-color: rgba(255, 51, 51, 0.1);
            --glass-bg: rgba(30, 30, 30, 0.6);
            --glass-border: rgba(255, 255, 255, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated Lava Background */
        .lava-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: radial-gradient(circle at 50% 50%, #1a0505 0%, #000000 100%);
            overflow: hidden;
        }

        .lava-blob {
            position: absolute;
            filter: blur(80px);
            opacity: 0.4;
            border-radius: 50%;
            animation: float 20s infinite ease-in-out;
        }

        .blob-1 {
            top: -10%;
            left: -10%;
            width: 50vw;
            height: 50vw;
            background: var(--primary);
            animation-delay: 0s;
        }

        .blob-2 {
            bottom: -20%;
            right: -10%;
            width: 60vw;
            height: 60vw;
            background: var(--secondary);
            animation-delay: -5s;
        }

        .blob-3 {
            top: 40%;
            left: 40%;
            width: 30vw;
            height: 30vw;
            background: #ff0000;
            animation-delay: -10s;
            opacity: 0.2;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(30px, -50px) scale(1.1);
            }

            66% {
                transform: translate(-20px, 20px) scale(0.9);
            }
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo h1 {
            font-size: 48px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        .logo p {
            color: var(--text-muted);
            font-size: 18px;
        }

        /* Progress Steps */
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--border-color);
            z-index: 0;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--bg-card);
            border: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            transition: all 0.3s ease;
        }

        .step.active .step-circle {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-color: var(--primary);
            box-shadow: 0 0 20px rgba(255, 51, 51, 0.5);
        }

        .step.completed .step-circle {
            background: var(--success);
            border-color: var(--success);
        }

        .step-label {
            font-size: 12px;
            color: var(--text-muted);
        }

        .step.active .step-label {
            color: var(--text-main);
            font-weight: 600;
        }

        /* Card */
        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 40px;
        }

        .card-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .card-subtitle {
            color: var(--text-muted);
            margin-bottom: 30px;
        }

        /* Form */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-main);
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255, 51, 51, 0.1);
        }

        .form-text {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 5px;
        }

        /* Buttons */
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 51, 51, 0.4);
        }

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary {
            background: var(--bg-card);
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }

        /* Alerts */
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid var(--success);
            color: var(--success);
        }

        .alert-danger {
            background: rgba(255, 51, 51, 0.1);
            border: 1px solid var(--danger);
            color: var(--danger);
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            font-weight: 600;
            color: var(--text-muted);
            font-size: 12px;
            text-transform: uppercase;
        }

        .status-icon {
            font-size: 20px;
        }

        .status-pass {
            color: var(--success);
        }

        .status-fail {
            color: var(--danger);
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            color: var(--text-muted);
            font-size: 14px;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 30px;
        }
    </style>
    @yield('styles')
</head>

<body>
    <!-- Lava Background -->
    <div class="lava-bg">
        <div class="lava-blob blob-1"></div>
        <div class="lava-blob blob-2"></div>
        <div class="lava-blob blob-3"></div>
    </div>

    <div class="container">
        <!-- Logo -->
        <div class="logo">
            <h1><i class="fas fa-fire"></i> DemoLimo</h1>
            <p>Installation Wizard</p>
        </div>

        <!-- Progress Steps -->
        <div class="progress-steps">
            <div class="step @if($currentStep >= 1) {{ $currentStep == 1 ? 'active' : 'completed' }} @endif">
                <div class="step-circle">
                    @if($currentStep > 1)
                        <i class="fas fa-check"></i>
                    @else
                        1
                    @endif
                </div>
                <div class="step-label">Requirements</div>
            </div>
            <div class="step @if($currentStep >= 2) {{ $currentStep == 2 ? 'active' : 'completed' }} @endif">
                <div class="step-circle">
                    @if($currentStep > 2)
                        <i class="fas fa-check"></i>
                    @else
                        2
                    @endif
                </div>
                <div class="step-label">Database</div>
            </div>
            <div class="step @if($currentStep >= 3) {{ $currentStep == 3 ? 'active' : 'completed' }} @endif">
                <div class="step-circle">
                    @if($currentStep > 3)
                        <i class="fas fa-check"></i>
                    @else
                        3
                    @endif
                </div>
                <div class="step-label">Configuration</div>
            </div>
            <div class="step @if($currentStep >= 4) {{ $currentStep == 4 ? 'active' : 'completed' }} @endif">
                <div class="step-circle">
                    @if($currentStep > 4)
                        <i class="fas fa-check"></i>
                    @else
                        4
                    @endif
                </div>
                <div class="step-label">Installation</div>
            </div>
            <div class="step @if($currentStep >= 5) active @endif">
                <div class="step-circle">5</div>
                <div class="step-label">Complete</div>
            </div>
        </div>

        <!-- Content -->
        @yield('content')

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} DemoLimo. All rights reserved.</p>
        </div>
    </div>

    @yield('scripts')
</body>

</html>