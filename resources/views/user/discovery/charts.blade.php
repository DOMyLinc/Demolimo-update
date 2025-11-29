@extends('layouts.app')

@section('title', 'Top Charts')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold mb-4">Top Charts</h1>
            <p class="text-gray-400 text-lg">The most played tracks, popular artists, and top albums on DemoLimo.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Top Tracks -->
            <div class="lg:col-span-2">
                <div class="bg-white/5 rounded-2xl border border-white/10 overflow-hidden">
                    <div class="p-6 border-b border-white/10 flex justify-between items-center">
                        <h2 class="text-xl font-bold">Top 50 Tracks</h2>
                        <span class="text-xs text-gray-400 uppercase font-bold tracking-wider">All Time</span>
                    </div>
                    <div class="divide-y divide-white/5">
                        @foreach($topTracks as $index => $track)
                            <div class="flex items-center gap-4 p-4 hover:bg-white/5 transition group">
                                <span
                                    class="text-xl font-bold {{ $index < 3 ? 'text-blue-400' : 'text-gray-600' }} w-8 text-center">{{ $index + 1 }}</span>
                                <img src="{{ $track->cover_image }}" class="w-12 h-12 rounded object-cover">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold truncate hover:text-blue-400 cursor-pointer">{{ $track->title }}</h3>
                                    <p class="text-sm text-gray-400 truncate">{{ $track->user->name }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-300">{{ number_format($track->plays) }}</p>
                                    <p class="text-xs text-gray-500">plays</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Sidebar Charts -->
            <div class="space-y-8">

                <!-- Top Artists -->
                <div class="bg-white/5 rounded-2xl border border-white/10 overflow-hidden">
                    <div class="p-6 border-b border-white/10">
                        <h2 class="text-xl font-bold">Top Artists</h2>
                    </div>
                    <div class="divide-y divide-white/5">
                        @foreach($topArtists as $index => $artist)
                            <div class="flex items-center gap-4 p-4 hover:bg-white/5 transition">
                                <span class="font-bold text-gray-600 w-6 text-center">{{ $index + 1 }}</span>
                                <img src="{{ $artist->avatar_url ?? 'https://via.placeholder.com/40' }}"
                                    class="w-10 h-10 rounded-full object-cover">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold truncate">{{ $artist->name }}</h3>
                                    <p class="text-xs text-gray-400">{{ number_format($artist->followers_count) }} followers</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Top Albums -->
                <div class="bg-white/5 rounded-2xl border border-white/10 overflow-hidden">
                    <div class="p-6 border-b border-white/10">
                        <h2 class="text-xl font-bold">Top Albums</h2>
                    </div>
                    <div class="divide-y divide-white/5">
                        @foreach($topAlbums as $index => $album)
                            <div class="flex items-center gap-4 p-4 hover:bg-white/5 transition">
                                <span class="font-bold text-gray-600 w-6 text-center">{{ $index + 1 }}</span>
                                <img src="{{ $album->cover_image ?? 'https://via.placeholder.com/40' }}"
                                    class="w-10 h-10 rounded object-cover">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold truncate">{{ $album->title }}</h3>
                                    <p class="text-xs text-gray-400">{{ $album->user->name }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection