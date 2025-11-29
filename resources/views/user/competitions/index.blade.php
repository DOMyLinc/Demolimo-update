@extends('layouts.app')

@section('title', 'Music Competitions')

@section('content')
    <style>
        .competitions-page {
            padding: 4rem 0;
            min-height: 100vh;
        }

        .page-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .page-title {
            font-size: 3.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #fff 0%, #667eea 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .page-subtitle {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.7);
            max-width: 600px;
            margin: 0 auto;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .competition-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .competition-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 2rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .competition-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .competition-card:hover {
            transform: translateY(-5px);
            border-color: rgba(102, 126, 234, 0.3);
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.2);
        }

        .competition-card:hover::before {
            opacity: 1;
        }

        .competition-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .badge-active {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .badge-upcoming {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
        }

        .badge-completed {
            background: rgba(139, 92, 246, 0.2);
            color: #8b5cf6;
        }

        .competition-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .competition-meta {
            display: flex;
            gap: 1.5rem;
            margin: 1rem 0;
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .competition-entries {
            display: flex;
            gap: 0.5rem;
            margin: 1.5rem 0;
        }

        .entry-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.2);
            object-fit: cover;
        }

        .btn-compete {
            width: 100%;
            padding: 1rem;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-compete:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .winner-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 1.5rem;
            display: flex;
            gap: 1.5rem;
            align-items: center;
            transition: all 0.3s ease;
        }

        .winner-card:hover {
            transform: translateX(5px);
            border-color: rgba(139, 92, 246, 0.3);
        }

        .winner-trophy {
            font-size: 3rem;
        }

        .winner-info h4 {
            font-size: 1.125rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .winner-info p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.875rem;
        }
    </style>

    <div class="competitions-page">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">🏆 Music Competitions</h1>
                <p class="page-subtitle">
                    Showcase your talent, compete with artists worldwide, and win amazing prizes!
                </p>
            </div>

            <!-- Active Competitions -->
            @if($activeCompetitions->count() > 0)
                <section>
                    <h2 class="section-title">
                        <span>🔥</span> Active Competitions
                    </h2>
                    <div class="competition-grid">
                        @foreach($activeCompetitions as $competition)
                            <div class="competition-card">
                                <span class="competition-badge badge-active">LIVE NOW</span>
                                <h3 class="competition-title">{{ $competition->title }}</h3>
                                <p style="color: rgba(255, 255, 255, 0.7); margin-bottom: 1rem;">
                                    {{ $competition->description }}
                                </p>
                                <div class="competition-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-users"></i>
                                        <span>{{ $competition->versions->count() }} Entries</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-clock"></i>
                                        <span>Ends {{ $competition->end_date->diffForHumans() }}</span>
                                    </div>
                                </div>
                                @if($competition->versions->count() > 0)
                                    <div class="competition-entries">
                                        @foreach($competition->versions->take(5) as $version)
                                            <img src="{{ $version->user->profile_photo_url ?? 'https://via.placeholder.com/40' }}"
                                                alt="{{ $version->user->name }}" class="entry-avatar" title="{{ $version->user->name }}">
                                        @endforeach
                                        @if($competition->versions->count() > 5)
                                            <div class="entry-avatar"
                                                style="display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.1);">
                                                +{{ $competition->versions->count() - 5 }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                <a href="{{ route('user.song_battles.show', $competition) }}" class="btn-compete">
                                    View Competition
                                </a>
                            </div>
                        @endforeach
                    </div>
                    {{ $activeCompetitions->links() }}
                </section>
            @endif

            <!-- Upcoming Competitions -->
            @if($upcomingCompetitions->count() > 0)
                <section>
                    <h2 class="section-title">
                        <span>📅</span> Coming Soon
                    </h2>
                    <div class="competition-grid">
                        @foreach($upcomingCompetitions as $competition)
                            <div class="competition-card">
                                <span class="competition-badge badge-upcoming">UPCOMING</span>
                                <h3 class="competition-title">{{ $competition->title }}</h3>
                                <p style="color: rgba(255, 255, 255, 0.7); margin-bottom: 1rem;">
                                    {{ $competition->description }}
                                </p>
                                <div class="competition-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <span>Starts {{ $competition->start_date->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Past Winners -->
            @if($pastWinners->count() > 0)
                <section>
                    <h2 class="section-title">
                        <span>👑</span> Hall of Fame
                    </h2>
                    <div style="display: grid; gap: 1rem;">
                        @foreach($pastWinners as $competition)
                            @php
                                $winner = $competition->versions->first();
                            @endphp
                            @if($winner)
                                <div class="winner-card">
                                    <div class="winner-trophy">🏆</div>
                                    <div class="winner-info" style="flex: 1;">
                                        <h4>{{ $competition->title }}</h4>
                                        <p>Winner: {{ $winner->user->name }} • {{ $winner->votes }} votes</p>
                                    </div>
                                    <div style="color: rgba(255, 255, 255, 0.5); font-size: 0.875rem;">
                                        {{ $competition->end_date->format('M Y') }}
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endif

            @if($activeCompetitions->count() == 0 && $upcomingCompetitions->count() == 0 && $pastWinners->count() == 0)
                <div style="text-align: center; padding: 4rem 0;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">🎵</div>
                    <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">No Competitions Yet</h3>
                    <p style="color: rgba(255, 255, 255, 0.6);">Check back soon for exciting music competitions!</p>
                </div>
            @endif
        </div>
    </div>
@endsection