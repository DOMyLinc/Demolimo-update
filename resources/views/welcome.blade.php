@extends('layouts.app')

@section('title', 'DemoLimo - Create. Share. Inspire.')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --accent-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --dark-bg: #0a0e27;
            --card-bg: rgba(255, 255, 255, 0.03);
        }

        body {
            background: var(--dark-bg);
            overflow-x: hidden;
        }

        /* Animated Background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.6;
            animation: float 20s ease-in-out infinite;
        }

        .orb-1 {
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            top: -10%;
            left: -10%;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #f093fb, #f5576c);
            bottom: -10%;
            right: -10%;
            animation-delay: 5s;
        }

        .orb-3 {
            width: 350px;
            height: 350px;
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: 10s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            25% {
                transform: translate(50px, -50px) scale(1.1);
            }

            50% {
                transform: translate(-30px, 30px) scale(0.9);
            }

            75% {
                transform: translate(30px, 50px) scale(1.05);
            }
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            z-index: 1;
        }

        .hero-content {
            max-width: 1000px;
            z-index: 10;
        }

        .hero-badge {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 2rem;
            animation: fadeInDown 1s ease;
        }

        .hero-title {
            font-size: clamp(3rem, 10vw, 7rem);
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #fff 0%, #667eea 50%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: fadeInUp 1s ease 0.2s both;
        }

        .hero-subtitle {
            font-size: clamp(1.125rem, 2.5vw, 1.75rem);
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 3rem;
            font-weight: 400;
            line-height: 1.6;
            animation: fadeInUp 1s ease 0.4s both;
        }

        .hero-cta {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease 0.6s both;
        }

        .btn-primary-gradient {
            padding: 1.25rem 3rem;
            font-size: 1.125rem;
            font-weight: 700;
            background: var(--primary-gradient);
            border: none;
            border-radius: 50px;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.4);
            position: relative;
            overflow: hidden;
        }

        .btn-primary-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary-gradient:hover::before {
            left: 100%;
        }

        .btn-primary-gradient:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 50px rgba(102, 126, 234, 0.6);
        }

        .btn-outline-gradient {
            padding: 1.25rem 3rem;
            font-size: 1.125rem;
            font-weight: 700;
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50px;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .btn-outline-gradient:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-3px);
        }

        /* Features Section */
        .features-section {
            position: relative;
            padding: 8rem 2rem;
            z-index: 1;
        }

        .section-header {
            text-align: center;
            margin-bottom: 5rem;
        }

        .section-badge {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            background: var(--primary-gradient);
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section-title {
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            font-weight: 900;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #fff 0%, #667eea 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-description {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.7);
            max-width: 700px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .feature-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 32px;
            padding: 3rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--primary-gradient);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .feature-card:hover::before {
            opacity: 0.1;
        }

        .feature-card:hover {
            transform: translateY(-15px);
            border-color: rgba(102, 126, 234, 0.5);
            box-shadow: 0 30px 80px rgba(102, 126, 234, 0.4);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 24px;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }

        .feature-card:nth-child(2) .feature-icon {
            background: var(--secondary-gradient);
        }

        .feature-card:nth-child(3) .feature-icon {
            background: var(--accent-gradient);
        }

        .feature-card:nth-child(4) .feature-icon {
            background: var(--primary-gradient);
        }

        .feature-card:nth-child(5) .feature-icon {
            background: var(--secondary-gradient);
        }

        .feature-card:nth-child(6) .feature-icon {
            background: var(--accent-gradient);
        }

        .feature-title {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .feature-description {
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.7;
            font-size: 1.0625rem;
            position: relative;
            z-index: 1;
        }

        /* Stats Section */
        .stats-section {
            position: relative;
            padding: 6rem 2rem;
            z-index: 1;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .stat-card {
            text-align: center;
        }

        .stat-number {
            font-size: clamp(3rem, 5vw, 4.5rem);
            font-weight: 900;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.125rem;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 600;
        }

        /* CTA Section */
        .cta-section {
            position: relative;
            padding: 8rem 2rem;
            z-index: 1;
        }

        .cta-container {
            max-width: 1000px;
            margin: 0 auto;
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 48px;
            padding: 5rem 3rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(102, 126, 234, 0.3), transparent 30%);
            animation: rotate 10s linear infinite;
        }

        @keyframes rotate {
            100% {
                transform: rotate(360deg);
            }
        }

        .cta-content {
            position: relative;
            z-index: 1;
        }

        .cta-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 900;
            margin-bottom: 1.5rem;
        }

        .cta-description {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 3rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Scroll Reveal */
        .reveal {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.8s ease;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .features-grid {
                grid-template-columns: 1fr;
            }

            .hero-cta {
                flex-direction: column;
            }

            .btn-primary-gradient,
            .btn-outline-gradient {
                width: 100%;
                max-width: 400px;
            }
        }
    </style>

    <!-- Animated Background -->
    <div class="animated-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <div class="hero-badge">🎵 The Future of Music Creation</div>
            <h1 class="hero-title">Create. Share. Inspire.</h1>
            <p class="hero-subtitle">
                The ultimate music platform for creators and listeners. Upload your tracks, use our online studio, and reach
                millions worldwide.
            </p>
            <div class="hero-cta">
                <a href="{{ route('register') }}" class="btn-primary-gradient">Start Creating Free</a>
                <a href="#features" class="btn-outline-gradient">Explore Features</a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section reveal">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">10M+</div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">50M+</div>
                <div class="stat-label">Tracks Uploaded</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">1B+</div>
                <div class="stat-label">Plays Per Month</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">180+</div>
                <div class="stat-label">Countries</div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="section-header reveal">
            <div class="section-badge">Features</div>
            <h2 class="section-title">Everything You Need</h2>
            <p class="section-description">
                Powerful tools and features designed to help you create, distribute, and monetize your music
            </p>
        </div>

        <div class="features-grid">
            <div class="feature-card reveal">
                <div class="feature-icon">🎵</div>
                <h3 class="feature-title">Upload & Share</h3>
                <p class="feature-description">
                    Upload unlimited tracks in high-quality formats. Share your music with the world instantly and build
                    your fanbase.
                </p>
            </div>

            <div class="feature-card reveal">
                <div class="feature-icon">🎹</div>
                <h3 class="feature-title">Online Studio</h3>
                <p class="feature-description">
                    Create music directly in your browser with our powerful DAW. Professional tools, no downloads required.
                </p>
            </div>

            <div class="feature-card reveal">
                <div class="feature-icon">📊</div>
                <h3 class="feature-title">Advanced Analytics</h3>
                <p class="feature-description">
                    Track your performance with detailed analytics. Understand your audience and grow strategically.
                </p>
            </div>

            <div class="feature-card reveal">
                <div class="feature-icon">💰</div>
                <h3 class="feature-title">Monetization</h3>
                <p class="feature-description">
                    Earn money from your music through subscriptions, tips, track sales, and more revenue streams.
                </p>
            </div>

            <div class="feature-card reveal">
                <div class="feature-icon">🌍</div>
                <h3 class="feature-title">Distribution</h3>
                <p class="feature-description">
                    Distribute your music to Spotify, Apple Music, and all major streaming platforms with one click.
                </p>
            </div>

            <div class="feature-card reveal">
                <div class="feature-icon">🎬</div>
                <h3 class="feature-title">Video Support</h3>
                <p class="feature-description">
                    Upload music videos and visual content to engage your audience and tell your story.
                </p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-container reveal">
            <div class="cta-content">
                <h2 class="cta-title">Ready to Start Your Journey?</h2>
                <p class="cta-description">
                    Join thousands of artists already creating amazing music on DemoLimo. Start for free today.
                </p>
                <a href="{{ route('register') }}" class="btn-primary-gradient">Sign Up Now - It's Free</a>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            // Smooth scrolling
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });

            // Scroll reveal animation
            function reveal() {
                const reveals = document.querySelectorAll('.reveal');

                reveals.forEach(element => {
                    const windowHeight = window.innerHeight;
                    const elementTop = element.getBoundingClientRect().top;
                    const elementVisible = 150;

                    if (elementTop < windowHeight - elementVisible) {
                        element.classList.add('active');
                    }
                });
            }

            window.addEventListener('scroll', reveal);
            reveal(); // Check on load
        </script>
    @endpush
@endsection