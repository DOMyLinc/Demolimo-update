@extends('install.layout', ['currentStep' => 5])

@section('title', 'Installation Complete')

@section('styles')
    <style>
        @keyframes confetti {
            0% {
                transform: translateY(-100vh) rotate(0deg);
                opacity: 1;
            }

            100% {
                transform: translateY(100vh) rotate(720deg);
                opacity: 0;
            }
        }

        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background: var(--primary);
            animation: confetti 3s linear infinite;
        }

        .success-icon {
            font-size: 80px;
            color: var(--success);
            text-align: center;
            margin: 20px 0;
            animation: bounce 1s ease-in-out;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .credentials-box {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .credential-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .credential-item:last-child {
            border-bottom: none;
        }

        .credential-label {
            color: var(--text-muted);
            font-weight: 500;
        }

        .credential-value {
            color: var(--text-main);
            font-weight: 600;
        }

        .quick-links {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 30px;
        }

        .quick-link {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            text-decoration: none;
            color: var(--text-main);
            transition: all 0.2s ease;
        }

        .quick-link:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .quick-link i {
            font-size: 32px;
            margin-bottom: 10px;
            display: block;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>

        <h2 class="card-title" style="text-align: center;">Installation Complete!</h2>
        <p class="card-subtitle" style="text-align: center;">Your DemoLimo platform is ready to use 🎉</p>

        <div class="credentials-box">
            <h3 style="margin-bottom: 15px;">Admin Login Credentials</h3>
            <div class="credential-item">
                <span class="credential-label">Admin URL:</span>
                <span class="credential-value">{{ url('/admin') }}</span>
            </div>
            <div class="credential-item">
                <span class="credential-label">Username:</span>
                <span class="credential-value">{{ $config['admin_username'] ?? 'admin' }}</span>
            </div>
            <div class="credential-item">
                <span class="credential-label">Email:</span>
                <span class="credential-value">{{ $config['admin_email'] ?? 'admin@example.com' }}</span>
            </div>
            <div class="credential-item">
                <span class="credential-label">Password:</span>
                <span class="credential-value">••••••••</span>
            </div>
        </div>

        <div
            style="background: rgba(0, 255, 136, 0.1); border: 1px solid var(--success); border-radius: 8px; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; color: var(--success);">
                <i class="fas fa-info-circle"></i>
                <strong>Important:</strong> Please save your admin credentials in a secure location. You will need them to
                access the admin panel.
            </p>
        </div>

        <div class="quick-links">
            <a href="{{ url('/admin') }}" class="quick-link">
                <i class="fas fa-user-shield"></i>
                <div style="font-weight: 600;">Admin Panel</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 5px;">Manage your platform</div>
            </a>
            <a href="{{ url('/') }}" class="quick-link">
                <i class="fas fa-home"></i>
                <div style="font-weight: 600;">Visit Site</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 5px;">See your platform</div>
            </a>
        </div>

        <div
            style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border-color); text-align: center; color: var(--text-muted);">
            <p><strong>What's Next?</strong></p>
            <ul style="text-align: left; margin: 15px 0; padding-left: 20px;">
                <li>Configure your site settings in the admin panel</li>
                <li>Set up payment gateways for monetization</li>
                <li>Customize your site branding and theme</li>
                <li>Configure email settings for notifications</li>
                <li>Upload your first tracks and albums</li>
            </ul>
        </div>
    </div>

    <!-- Confetti Animation -->
    <script>
        // Create confetti
        for (let i = 0; i < 50; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + '%';
            confetti.style.animationDelay = Math.random() * 3 + 's';
            confetti.style.background = ['var(--primary)', 'var(--secondary)', 'var(--success)'][Math.floor(Math.random() * 3)];
            document.body.appendChild(confetti);

            // Remove after animation
            setTimeout(() => confetti.remove(), 3000);
        }
    </script>
@endsection