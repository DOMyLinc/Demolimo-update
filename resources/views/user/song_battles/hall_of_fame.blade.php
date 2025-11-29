@extends('layouts.app')

@section('content')
    <div class="bg-gray-900 min-h-screen py-12 text-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h1
                    class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 via-red-500 to-pink-500 mb-4">
                    Hall of Fame
                </h1>
                <p class="text-gray-400 text-xl">Celebrating the legends of the arena.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Top Artists -->
                <div class="bg-gray-800 rounded-xl shadow-2xl overflow-hidden border border-gray-700">
                    <div class="bg-gradient-to-r from-purple-800 to-indigo-900 p-6">
                        <h2 class="text-2xl font-bold flex items-center">
                            <svg class="w-8 h-8 mr-3 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            Top Artists
                        </h2>
                    </div>
                    <div class="p-6">
                        @foreach($topArtists as $index => $artist)
                            <div class="flex items-center mb-6 last:mb-0">
                                <div
                                    class="flex-shrink-0 w-12 h-12 flex items-center justify-center font-bold text-xl rounded-full 
                                        {{ $index == 0 ? 'bg-yellow-500 text-black' : ($index == 1 ? 'bg-gray-400 text-black' : ($index == 2 ? 'bg-yellow-700 text-white' : 'bg-gray-700 text-gray-400')) }}">
                                    {{ $index + 1 }}
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="font-bold text-lg">{{ $artist->name }}</h3>
                                    <p class="text-sm text-gray-400">{{ number_format($artist->points) }} Points</p>
                                </div>
                                @if($index < 3)
                                    <div class="text-2xl">🏆</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Top Battles -->
                <div class="bg-gray-800 rounded-xl shadow-2xl overflow-hidden border border-gray-700">
                    <div class="bg-gradient-to-r from-red-800 to-pink-900 p-6">
                        <h2 class="text-2xl font-bold flex items-center">
                            <svg class="w-8 h-8 mr-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Epic Battles
                        </h2>
                    </div>
                    <div class="p-6">
                        @foreach($topBattles as $index => $battle)
                            <div class="flex items-center mb-6 last:mb-0">
                                <div
                                    class="flex-shrink-0 w-10 h-10 bg-gray-700 rounded flex items-center justify-center text-gray-400 font-bold">
                                    {{ $index + 1 }}
                                </div>
                                <div class="ml-4 flex-1 overflow-hidden">
                                    <h3 class="font-bold text-lg truncate"><a href="{{ route('song_battles.show', $battle) }}"
                                            class="hover:text-red-400 transition">{{ $battle->title }}</a></h3>
                                    <p class="text-sm text-gray-400">by {{ $battle->user->name }}</p>
                                </div>
                                <div class="text-sm font-bold text-red-400">
                                    {{ $battle->total_votes ?? 0 }} Votes
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Trending Versions -->
                <div class="bg-gray-800 rounded-xl shadow-2xl overflow-hidden border border-gray-700">
                    <div class="bg-gradient-to-r from-green-800 to-teal-900 p-6">
                        <h2 class="text-2xl font-bold flex items-center">
                            <svg class="w-8 h-8 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                            </svg>
                            Trending Tracks
                        </h2>
                    </div>
                    <div class="p-6">
                        @foreach($trendingVersions as $index => $version)
                            <div class="flex items-center mb-6 last:mb-0">
                                <div
                                    class="flex-shrink-0 w-10 h-10 bg-gray-700 rounded flex items-center justify-center text-gray-400 font-bold">
                                    {{ $index + 1 }}
                                </div>
                                <div class="ml-4 flex-1 overflow-hidden">
                                    <h3 class="font-bold text-lg truncate">{{ $version->style_name }}</h3>
                                    <p class="text-sm text-gray-400">in <a
                                            href="{{ route('song_battles.show', $version->battle) }}"
                                            class="hover:text-green-400 transition">{{ $version->battle->title }}</a></p>
                                </div>
                                <div class="text-sm font-bold text-green-400">
                                    {{ number_format($version->play_count) }} Plays
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection