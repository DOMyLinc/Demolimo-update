@props(['feature' => 'This feature', 'message' => null])

<div class="feature-disabled-container">
    <div class="feature-disabled-card">
        <!-- Animated Worker SVG -->
        <div class="worker-animation">
            <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <!-- Construction Worker -->
                <g class="worker">
                    <!-- Hard Hat -->
                    <ellipse cx="100" cy="60" rx="25" ry="15" fill="#FFA500" class="hat" />
                    <rect x="75" y="55" width="50" height="5" fill="#FF8C00" />

                    <!-- Head -->
                    <circle cx="100" cy="75" r="20" fill="#FFD4A3" />

                    <!-- Body -->
                    <rect x="85" y="95" width="30" height="40" rx="5" fill="#4A90E2" />

                    <!-- Arms -->
                    <rect x="70" y="100" width="15" height="30" rx="7" fill="#FFD4A3" class="arm-left" />
                    <rect x="115" y="100" width="15" height="30" rx="7" fill="#FFD4A3" class="arm-right" />

                    <!-- Tool -->
                    <rect x="60" y="125" width="20" height="4" fill="#666" class="tool" />
                    <circle cx="65" cy="127" r="3" fill="#888" />
                </g>

                <!-- Gears -->
                <g class="gears">
                    <circle cx="150" cy="100" r="15" fill="none" stroke="#667eea" stroke-width="3" class="gear-1" />
                    <circle cx="150" cy="100" r="8" fill="none" stroke="#667eea" stroke-width="2" />

                    <circle cx="50" cy="140" r="12" fill="none" stroke="#764ba2" stroke-width="2.5" class="gear-2" />
                    <circle cx="50" cy="140" r="6" fill="none" stroke="#764ba2" stroke-width="1.5" />
                </g>

                <!-- Progress Dots -->
                <circle cx="100" cy="160" r="4" fill="#667eea" class="dot dot-1" />
                <circle cx="115" cy="160" r="4" fill="#667eea" class="dot dot-2" />
                <circle cx="130" cy="160" r="4" fill="#667eea" class="dot dot-3" />
            </svg>
        </div>

        <!-- Message -->
        <div class="message-content">
            <h2 class="feature-title">🚧 {{ $feature }} is Currently Unavailable</h2>
            <p class="feature-message">
                {{ $message ?? "We're working hard to bring this feature to you. Please check back soon or contact your administrator to enable this feature." }}
            </p>
            <div class="action-buttons">
                <a href="{{ url()->previous() }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Go Back
                </a>
                <a href="{{ route('home') }}" class="btn-home">
                    <i class="fas fa-home"></i> Home
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .feature-disabled-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        background: linear-gradient(135deg, #0a0a0f 0%, #1a1a2e 50%, #16213e 100%);
    }

    .feature-disabled-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 32px;
        padding: 4rem 3rem;
        max-width: 600px;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .worker-animation {
        width: 200px;
        height: 200px;
        margin: 0 auto 2rem;
    }

    .worker-animation svg {
        width: 100%;
        height: 100%;
    }

    /* Animations */
    @keyframes hammer {

        0%,
        100% {
            transform: rotate(-10deg);
        }

        50% {
            transform: rotate(10deg);
        }
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 0.3;
        }

        50% {
            opacity: 1;
        }
    }

    .tool {
        animation: hammer 1s ease-in-out infinite;
        transform-origin: 70px 127px;
    }

    .arm-left {
        animation: hammer 1s ease-in-out infinite;
        transform-origin: 77px 115px;
    }

    .gear-1 {
        animation: spin 3s linear infinite;
        transform-origin: 150px 100px;
    }

    .gear-2 {
        animation: spin 2s linear infinite reverse;
        transform-origin: 50px 140px;
    }

    .hat {
        animation: pulse 2s ease-in-out infinite;
    }

    .dot {
        animation: pulse 1.5s ease-in-out infinite;
    }

    .dot-2 {
        animation-delay: 0.3s;
    }

    .dot-3 {
        animation-delay: 0.6s;
    }

    /* Message Styling */
    .message-content {
        color: #fff;
    }

    .feature-title {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #fff 0%, #667eea 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .feature-message {
        font-size: 1.125rem;
        color: rgba(255, 255, 255, 0.7);
        line-height: 1.6;
        margin-bottom: 2rem;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-back,
    .btn-home {
        padding: 0.875rem 2rem;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-back {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #fff;
    }

    .btn-back:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-2px);
    }

    .btn-home {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }

    .btn-home:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
    }

    @media (max-width: 640px) {
        .feature-disabled-card {
            padding: 3rem 2rem;
        }

        .feature-title {
            font-size: 1.5rem;
        }

        .worker-animation {
            width: 150px;
            height: 150px;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn-back,
        .btn-home {
            width: 100%;
            justify-content: center;
        }
    }
</style>