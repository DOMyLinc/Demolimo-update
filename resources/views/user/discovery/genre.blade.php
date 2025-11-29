@extends('layouts.app')

@section('title', $genre . ' Music')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="relative h-64 rounded-3xl overflow-hidden mb-12">
            <div class="absolute inset-0 bg-gradient-to-r from-purple-900 to-blue-900"></div>
            <div class="absolute inset-0 flex items-center px-12">
                <div>
                    <p class="text-blue-300 font-bold mb-2 uppercase tracking-wider">Genre</p>
                    <h1 class="text-6xl font-bold text-white mb-4">{{ $genre }}</h1>
                    <p class="text-gray-300 text-lg">Discover the best new {{ strtolower($genre) }} tracks and artists.</p>
                </div>
            </div>
        </div>

        <div class="flex gap-8">
            <!-- Main Content -->
            <div class="flex-1">
                <h2 class="text-2xl font-bold mb-6">Top Tracks</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($tracks as $track)
                        <div class="bg-white/5 rounded-xl p-4 border border-white/10 hover:bg-white/10 transition group">
                            <div class="relative aspect-square mb-4 rounded-lg overflow-hidden">
                                <img src="{{ $track->cover_image }}" class="w-full h-full object-cover">
                                <button
                                    class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                    <div
                                        class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-black shadow-lg">
                                        <i class="fas fa-play pl-1"></i>
                                    </div>
                                </button>
                            </div>
                            <h3 class="font-bold truncate hover:text-blue-400 cursor-pointer">{{ $track->title }}</h3>
                            <p class="text-sm text-gray-400 truncate mb-3">{{ $track->user->name }}</p>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span><i class="fas fa-play mr-1"></i> {{ number_format($track->plays) }}</span>
                                <span>{{ $track->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-8">
                    {{ $tracks->links() }}
                </div>
            </div>

            <!-- Sidebar -->
            <div class="w-80 hidden lg:block">
                <div class="bg-white/5 rounded-2xl p-6 border border-white/10 sticky top-24">
                    <h3 class="font-bold mb-4">Top Artists</h3>
                    <div class="space-y-4">
                        @foreach($topArtists as $artist)
                            <div class="flex items-center gap-3">
                                <img src="{{ $artist->avatar_url ?? 'https://via.placeholder.com/40' }}"
                                    class="w-10 h-10 rounded-full">
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-sm truncate">{{ $artist->name }}</p>
                                    <p class="text-xs text-gray-400">{{ number_format($artist->followers_count) }} followers</p>
                                </div>
                                <button class="text-blue-400 hover:text-blue-300 text-xs font-bold">Follow</button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection