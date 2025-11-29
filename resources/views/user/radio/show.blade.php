@extends('layouts.app')

@section('title', $station->name . ' - Radio')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-purple-900 to-gray-900">
        <!-- Radio Player Header -->
        <div class="relative overflow-hidden">
            <!-- Background Image with Blur -->
            @if($station->cover_image)
                <div class="absolute inset-0 bg-cover bg-center filter blur-3xl opacity-30"
                    style="background-image: url('{{ Storage::url($station->cover_image) }}')"></div>
            @endif

            <div class="relative z-10 container mx-auto px-4 py-12">
                <div class="flex flex-col md:flex-row items-center gap-8">
                    <!-- Station Cover -->
                    <div
                        class="w-64 h-64 rounded-3xl overflow-hidden shadow-2xl shadow-purple-500/50 transform hover:scale-105 transition duration-300">
                        <img src="{{ $station->cover_image ? Storage::url($station->cover_image) : '/images/default-radio.png' }}"
                            alt="{{ $station->name }}" class="w-full h-full object-cover">
                    </div>

                    <!-- Station Info -->
                    <div class="flex-1 text-center md:text-left">
                        <div
                            class="inline-block px-4 py-1 bg-red-500/20 border border-red-500 rounded-full text-red-400 text-sm font-bold mb-4">
                            <i class="fas fa-broadcast-tower mr-2"></i>
                            {{ $station->type === 'live' ? 'LIVE NOW' : 'AUTO-DJ' }}
                        </div>

                        <h1 class="text-5xl font-black text-white mb-4">{{ $station->name }}</h1>

                        @if($station->description)
                            <p class="text-gray-300 text-lg mb-6 max-w-2xl">{{ $station->description }}</p>
                        @endif

                        <!-- Stats -->
                        <div class="flex flex-wrap gap-6 mb-6">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-users text-purple-400"></i>
                                <span class="text-white font-bold" id="listeners-count">{{ $activeListeners }}</span>
                                <span class="text-gray-400">Listening</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-play-circle text-purple-400"></i>
                                <span class="text-white font-bold">{{ number_format($station->total_plays) }}</span>
                                <span class="text-gray-400">Total Plays</span>
                            </div>
                            @if($station->genre)
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-music text-purple-400"></i>
                                    <span class="text-white font-bold">{{ $station->genre }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Play Button -->
                        <button id="play-button" onclick="togglePlay()"
                            class="px-12 py-4 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold rounded-full shadow-lg shadow-purple-500/50 transform hover:scale-105 transition duration-300 flex items-center gap-3 mx-auto md:mx-0">
                            <i class="fas fa-play text-2xl" id="play-icon"></i>
                            <span id="play-text">Listen Live</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Now Playing & Playlist -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Now Playing -->
                    @if($station->type === 'auto' && $currentTrack)
                        <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
                            <h2 class="text-2xl font-bold text-white mb-4 flex items-center gap-3">
                                <i class="fas fa-headphones text-purple-400"></i>
                                Now Playing
                            </h2>

                            <div class="flex items-center gap-4" id="current-track">
                                <img src="{{ $currentTrack->cover_image ? Storage::url($currentTrack->cover_image) : '/images/default-track.png' }}"
                                    alt="{{ $currentTrack->title }}" class="w-20 h-20 rounded-lg object-cover">
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-white">{{ $currentTrack->title }}</h3>
                                    <p class="text-gray-400">{{ $currentTrack->artist->name ?? 'Unknown Artist' }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        class="w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-white transition">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                    <button
                                        class="w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-white transition">
                                        <i class="fas fa-share"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Upcoming Tracks -->
                    @if($station->type === 'auto' && $station->playlist->count() > 0)
                        <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
                            <h2 class="text-2xl font-bold text-white mb-4 flex items-center gap-3">
                                <i class="fas fa-list-music text-purple-400"></i>
                                Upcoming Tracks
                            </h2>

                            <div class="space-y-3">
                                @foreach($station->playlist->take(10) as $item)
                                    <div class="flex items-center gap-4 p-3 rounded-lg hover:bg-white/5 transition">
                                        <img src="{{ $item->track->cover_image ? Storage::url($item->track->cover_image) : '/images/default-track.png' }}"
                                            alt="{{ $item->track->title }}" class="w-12 h-12 rounded object-cover">
                                        <div class="flex-1">
                                            <h4 class="text-white font-medium">{{ $item->track->title }}</h4>
                                            <p class="text-gray-400 text-sm">{{ $item->track->artist->name ?? 'Unknown' }}</p>
                                        </div>
                                        <span class="text-gray-500 text-sm">{{ gmdate('i:s', $item->track->duration) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Schedule (if available) -->
                    @if($station->schedules->count() > 0)
                        <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
                            <h2 class="text-2xl font-bold text-white mb-4 flex items-center gap-3">
                                <i class="fas fa-calendar text-purple-400"></i>
                                Weekly Schedule
                            </h2>

                            <div class="space-y-3">
                                @foreach($station->schedules as $schedule)
                                    <div class="flex items-center justify-between p-4 bg-white/5 rounded-lg">
                                        <div>
                                            <h4 class="text-white font-bold">{{ $schedule->show_name }}</h4>
                                            <p class="text-gray-400 text-sm">{{ $schedule->host_name }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-purple-400 font-bold">{{ ucfirst($schedule->day_of_week) }}</p>
                                            <p class="text-gray-400 text-sm">{{ $schedule->start_time }} - {{ $schedule->end_time }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Column: DJ Info & Social Links -->
                <div class="space-y-6">
                    <!-- DJ Information -->
                    @if($station->dj_name)
                        <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
                            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-3">
                                <i class="fas fa-microphone text-purple-400"></i>
                                Your DJ
                            </h2>

                            <div class="text-center">
                                @if($station->dj_avatar)
                                    <img src="{{ Storage::url($station->dj_avatar) }}" alt="{{ $station->dj_name }}"
                                        class="w-32 h-32 rounded-full mx-auto mb-4 border-4 border-purple-500">
                                @endif

                                <h3 class="text-2xl font-bold text-white mb-2">{{ $station->dj_name }}</h3>

                                @if($station->dj_bio)
                                    <p class="text-gray-400 text-sm">{{ $station->dj_bio }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Social Media Links -->
                    @if($station->social_links || $station->website_url)
                        <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
                            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-3">
                                <i class="fas fa-share-nodes text-purple-400"></i>
                                Connect With Us
                            </h2>

                            <div class="space-y-3">
                                @if($station->website_url)
                                    <a href="{{ $station->website_url }}" target="_blank"
                                        class="flex items-center gap-3 p-3 bg-white/5 hover:bg-white/10 rounded-lg transition">
                                        <i class="fas fa-globe text-purple-400 text-xl"></i>
                                        <span class="text-white font-medium">Visit Website</span>
                                    </a>
                                @endif

                                @if($station->social_links)
                                    @foreach($station->social_links as $platform => $url)
                                        <a href="{{ $url }}" target="_blank"
                                            class="flex items-center gap-3 p-3 bg-white/5 hover:bg-white/10 rounded-lg transition">
                                            <i class="fab fa-{{ strtolower($platform) }} text-purple-400 text-xl"></i>
                                            <span class="text-white font-medium">{{ ucfirst($platform) }}</span>
                                        </a>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Embed Code -->
                    <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
                        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-3">
                            <i class="fas fa-code text-purple-400"></i>
                            Embed on Your Site
                        </h2>

                        <textarea readonly onclick="this.select()"
                            class="w-full bg-black/30 border border-white/10 rounded-lg p-3 text-gray-300 text-sm font-mono resize-none"
                            rows="4">{{ $station->embed_code }}</textarea>

                        <button onclick="copyEmbedCode()"
                            class="mt-3 w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-lg transition">
                            <i class="fas fa-copy mr-2"></i> Copy Code
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Audio Element -->
    <audio id="radio-player" preload="none"></audio>

    <script>
        let isPlaying = false;
        let sessionId = null;
        const player = document.getElementById('radio-player');
        const playButton = document.getElementById('play-button');
        const playIcon = document.getElementById('play-icon');
        const playText = document.getElementById('play-text');

        async function togglePlay() {
            if (!isPlaying) {
                await startPlaying();
            } else {
                stopPlaying();
            }
        }

        async function startPlaying() {
            try {
                // Connect to station
                const response = await fetch('{{ route("radio.listen", $station->slug) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();
                sessionId = data.session_id;

                @if($station->type === 'live')
                    // Live stream
                    player.src = '{{ $station->stream_url }}';
                @else
                    // Auto-DJ - play current track
                    if (data.current_track) {
                        player.src = data.current_track.audio_url;
                    }
                @endif

            await player.play();
                isPlaying = true;

                playIcon.className = 'fas fa-pause text-2xl';
                playText.textContent = 'Pause';
                playButton.classList.add('animate-pulse');

                // Update current track every 30 seconds for auto-DJ
                @if($station->type === 'auto')
                    setInterval(updateCurrentTrack, 30000);
                @endif

        } catch (error) {
                console.error('Error starting playback:', error);
                alert('Failed to start playback. Please try again.');
            }
        }

        function stopPlaying() {
            player.pause();
            isPlaying = false;

            playIcon.className = 'fas fa-play text-2xl';
            playText.textContent = 'Listen Live';
            playButton.classList.remove('animate-pulse');

            // Disconnect from station
            if (sessionId) {
                fetch('{{ route("radio.disconnect", $station->slug) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ session_id: sessionId })
                });
            }
        }

        async function updateCurrentTrack() {
            const response = await fetch('{{ route("radio.current-track", $station->slug) }}');
            const data = await response.json();

            if (data.track) {
                // Update UI with new track
                document.getElementById('current-track').innerHTML = `
                <img src="${data.track.cover}" alt="${data.track.title}" class="w-20 h-20 rounded-lg object-cover">
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-white">${data.track.title}</h3>
                    <p class="text-gray-400">${data.track.artist}</p>
                </div>
            `;

                // Play new track
                if (isPlaying) {
                    player.src = data.track.audio_url;
                    player.play();
                }
            }
        }

        function copyEmbedCode() {
            const textarea = document.querySelector('textarea');
            textarea.select();
            document.execCommand('copy');

            alert('Embed code copied to clipboard!');
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (isPlaying) {
                stopPlaying();
            }
        });
    </script>
@endsection