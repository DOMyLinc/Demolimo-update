<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DemoLimo') - Music Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    @livewireStyles
    @stack('styles')
</head>

<body>
    <!-- Navigation -->
    <nav>
        <div class="nav-container">
            <a href="{{ route('home') }}" class="logo" wire:navigate>🎵 DemoLimo</a>
            <ul class="nav-links">
                <li><a href="{{ route('home') }}" wire:navigate>Home</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#pricing">Pricing</a></li>
                @auth
                    <li><a href="{{ route('dashboard') }}" wire:navigate>Dashboard</a></li>
                    <li><a href="{{ route('studio') }}" wire:navigate>Studio</a></li>
                    @if(auth()->user()->role === 'admin')
                        <li><a href="{{ route('admin.dashboard') }}" wire:navigate>Admin</a></li>
                    @endif
                    <li>
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-outline">Logout</button>
                        </form>
                    </li>
                @else
                    <li><a href="{{ route('login') }}" class="btn btn-outline" wire:navigate>Login</a></li>
                    <li><a href="{{ route('register') }}" class="btn btn-primary" wire:navigate>Sign Up</a></li>
                @endauth
            </ul>
        </div>
    </nav>

    <!-- Announcements -->
    @php
        $activeAnnouncements = \App\Models\Announcement::active()->get();
    @endphp
    @foreach($activeAnnouncements as $announcement)
        <div class="announcement announcement-{{ $announcement->type }}">
            <div class="announcement-content">
                <strong>{{ $announcement->title }}</strong>
                <p>{{ $announcement->message }}</p>
            </div>
        </div>
    @endforeach

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>DemoLimo</h3>
                <p style="color: rgba(255, 255, 255, 0.7);">
                    The ultimate music platform for creators and listeners.
                </p>
            </div>
            <div class="footer-section">
                <h3>Platform</h3>
                <ul>
                    <li><a href="#">Upload Music</a></li>
                    <li><a href="#">Studio</a></li>
                    <li><a href="#">Distribution</a></li>
                    <li><a href="#">Analytics</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Community</h3>
                <ul>
                    <li><a href="#">Artists</a></li>
                    <li><a href="#">Discover</a></li>
                    <li><a href="#">Charts</a></li>
                    <li><a href="#">Events</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Support</h3>
                <ul>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">Privacy</a></li>
                    <li><a href="#">Terms</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} DemoLimo. All rights reserved.</p>
        </div>
    </footer>

    <livewire:music-player />
    @livewireScripts
    @stack('scripts')
</body>

</html>