@extends('layouts.app')

@section('title', 'Search Results')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-4">Search Results</h1>
        <form action="{{ route('search.index') }}" method="GET" class="max-w-2xl">
            <div class="relative">
                <input type="text" name="q" value="{{ $query }}" 
                    placeholder="Search for tracks, albums, artists, playlists..." 
                    class="w-full px-6 py-4 bg-white/10 border border-white/20 rounded-full text-white placeholder-gray-400 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/50 transition">
                <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 px-6 py-2 bg-purple-600 hover:bg-purple-500 rounded-full font-bold transition">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>

    @if(empty($query))
        <div class="text-center py-20">
            <i class="fas fa-search text-6xl text-gray-500 mb-4"></i>
            <h3 class="text-2xl font-bold mb-2">Start Searching</h3>
            <p class="text-gray-400">Enter a search term to find tracks, albums, artists, and playlists.</p>
        </div>
    @else
        <!-- Tracks -->
        @if($tracks->isNotEmpty())
            <section class="mb-12">
                <h2 class="text-2xl font-bold mb-6">Tracks</h2>
                <div class="space-y-3">
                    @foreach($tracks as $track)
                        <div class="bg-white/5 rounded-xl p-4 border border-white/10 hover:bg-white/10 transition flex items-center gap-4">
                            <div class="w-16 h-16 rounded-lg overflow-hidden bg-white/5 flex-shrink-0">
                                @if($track->cover_image)
                                    <img src="{{ Storage::url($track->cover_image) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-music text-gray-500"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold truncate">{{ $track->title }}</h3>
                                <p class="text-sm text-gray-400 truncate">{{ $track->user->name }}</p>
                            </div>
                            <button class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center hover:bg-purple-500 transition">
                                <i class="fas fa-play pl-1"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Albums -->
        @if($albums->isNotEmpty())
            <section class="mb-12">
                <h2 class="text-2xl font-bold mb-6">Albums</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                    @foreach($albums as $album)
                        <div class="group">
                            <div class="aspect-square mb-3 rounded-xl overflow-hidden bg-white/5 shadow-lg">
                                @if($album->cover_image)
                                    <img src="{{ Storage::url($album->cover_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-gray-700 to-gray-900 flex items-center justify-center">
                                        <i class="fas fa-compact-disc text-4xl text-white/20"></i>
                                    </div>
                                @endif
                            </div>
                            <h3 class="font-bold truncate group-hover:text-purple-400 transition">{{ $album->title }}</h3>
                            <p class="text-sm text-gray-400 truncate">{{ $album->user->name }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Artists -->
        @if($artists->isNotEmpty())
            <section class="mb-12">
                <h2 class="text-2xl font-bold mb-6">Artists</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                    @foreach($artists as $artist)
                        <div class="text-center group">
                            <div class="w-32 h-32 mx-auto mb-3 rounded-full overflow-hidden bg-white/5 shadow-lg">
                                @if($artist->avatar)
                                    <img src="{{ Storage::url($artist->avatar) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-purple-700 to-blue-700 flex items-center justify-center">
                                        <i class="fas fa-user text-4xl text-white/20"></i>
                                    </div>
                                @endif
                            </div>
                            <h3 class="font-bold truncate group-hover:text-purple-400 transition">{{ $artist->name }}</h3>
                            <p class="text-sm text-gray-400">{{ $artist->tracks_count }} tracks</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Playlists -->
        @if($playlists->isNotEmpty())
            <section>
                <h2 class="text-2xl font-bold mb-6">Playlists</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                    @foreach($playlists as $playlist)
                        <div class="group">
                            <div class="aspect-square mb-3 rounded-xl overflow-hidden bg-white/5 shadow-lg">
                                @if($playlist->cover_image)
                                    <img src="{{ Storage::url($playlist->cover_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-green-700 to-teal-700 flex items-center justify-center">
                                        <i class="fas fa-list-music text-4xl text-white/20"></i>
                                    </div>
                                @endif
                            </div>
                            <h3 class="font-bold truncate group-hover:text-purple-400 transition">{{ $playlist->name }}</h3>
                            <p class="text-sm text-gray-400 truncate">by {{ $playlist->user->name }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if($tracks->isEmpty() && $albums->isEmpty() && $artists->isEmpty() && $playlists->isEmpty())
            <div class="text-center py-20">
                <i class="fas fa-search-minus text-6xl text-gray-500 mb-4"></i>
                <h3 class="text-2xl font-bold mb-2">No Results Found</h3>
                <p class="text-gray-400">Try searching with different keywords.</p>
            </div>
        @endif
    @endif
</div>
@endsection