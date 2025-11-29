@extends('layouts.app')

@section('title', 'Discover Music')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Discover</h1>

        <!-- Personalized Recommendations -->
        <section class="mb-12">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold">Made for You</h2>
                <a href="{{ route('discovery.for-you') }}" class="text-gray-400 hover:text-white text-sm font-bold">See
                    All</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                @foreach($personalized as $track)
                    <div class="group">
                        <div class="relative aspect-square mb-3 rounded-xl overflow-hidden bg-white/5">
                            <img src="{{ $track->cover_image }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            <button
                                class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                <div
                                    class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-black shadow-lg transform scale-90 group-hover:scale-100 transition">
                                    <i class="fas fa-play pl-1"></i>
                                </div>
                            </button>
                        </div>
                        <h3 class="font-bold truncate hover:text-blue-400 cursor-pointer">{{ $track->title }}</h3>
                        <p class="text-sm text-gray-400 truncate">{{ $track->user->name }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Trending -->
        <section class="mb-12">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold">Trending Now</h2>
                <a href="{{ route('discovery.trending') }}" class="text-gray-400 hover:text-white text-sm font-bold">See
                    All</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($trending->take(6) as $index => $track)
                    <div class="flex items-center gap-4 p-3 rounded-xl hover:bg-white/5 transition group">
                        <span class="text-2xl font-bold text-gray-600 w-8 text-center">{{ $index + 1 }}</span>
                        <img src="{{ $track->cover_image }}" class="w-16 h-16 rounded-lg object-cover">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold truncate hover:text-blue-400 cursor-pointer">{{ $track->title }}</h3>
                            <p class="text-sm text-gray-400 truncate">{{ $track->user->name }}</p>
                        </div>
                        <div class="text-sm text-gray-500 flex items-center gap-4">
                            <span><i class="fas fa-play text-xs mr-1"></i> {{ number_format($track->plays) }}</span>
                            <button class="opacity-0 group-hover:opacity-100 text-white hover:text-blue-400">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- New Releases -->
        <section class="mb-12">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold">New Releases</h2>
                <a href="{{ route('discovery.new-releases') }}" class="text-gray-400 hover:text-white text-sm font-bold">See
                    All</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                @foreach($newReleases as $track)
                    <div class="group">
                        <div class="relative aspect-square mb-3 rounded-xl overflow-hidden bg-white/5">
                            <img src="{{ $track->cover_image }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            <div
                                class="absolute top-2 right-2 bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded shadow">
                                NEW</div>
                            <button
                                class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                <div
                                    class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-black shadow-lg transform scale-90 group-hover:scale-100 transition">
                                    <i class="fas fa-play pl-1"></i>
                                </div>
                            </button>
                        </div>
                        <h3 class="font-bold truncate hover:text-blue-400 cursor-pointer">{{ $track->title }}</h3>
                        <p class="text-sm text-gray-400 truncate">{{ $track->user->name }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Top Genres -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold mb-6">Browse by Genre</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @foreach($topGenres as $genre)
                    <a href="{{ route('discovery.genre', $genre->genre) }}"
                        class="relative h-32 rounded-xl overflow-hidden group">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-purple-600 to-blue-600 group-hover:scale-110 transition duration-500">
                        </div>
                        <div
                            class="absolute inset-0 flex items-center justify-center bg-black/20 group-hover:bg-black/40 transition">
                            <span class="text-xl font-bold text-white">{{ $genre->genre }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        <!-- Featured Playlists -->
        <section>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold">Featured Playlists</h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                @foreach($featuredPlaylists as $playlist)
                    <div class="group">
                        <div class="relative aspect-square mb-3 rounded-xl overflow-hidden bg-white/5 shadow-lg">
                            @if($playlist->cover_image)
                                <img src="{{ Storage::url($playlist->cover_image) }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @else
                                <div
                                    class="w-full h-full bg-gradient-to-br from-gray-700 to-gray-900 flex items-center justify-center">
                                    <i class="fas fa-music text-4xl text-white/20"></i>
                                </div>
                            @endif
                            <button
                                class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                <div
                                    class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-black shadow-lg transform scale-90 group-hover:scale-100 transition">
                                    <i class="fas fa-play pl-1"></i>
                                </div>
                            </button>
                        </div>
                        <h3 class="font-bold truncate hover:text-blue-400 cursor-pointer">{{ $playlist->name }}</h3>
                        <p class="text-sm text-gray-400 truncate">{{ $playlist->tracks_count }} tracks</p>
                    </div>
                @endforeach
            </div>
        </section>

    </div>
@endsection