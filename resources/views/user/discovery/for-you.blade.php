@extends('layouts.app')

@section('title', 'Made For You')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-12">
            <h1 class="text-3xl font-bold mb-2">Made For You</h1>
            <p class="text-gray-400">Recommendations based on your listening history and favorite genres.</p>
        </div>

        <!-- Recommended Tracks -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold mb-6">Recommended Tracks</h2>
            @if($recommendations->isEmpty())
                <div class="bg-white/5 rounded-2xl p-12 text-center border border-white/10">
                    <i class="fas fa-headphones text-4xl text-gray-500 mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Not enough data yet</h3>
                    <p class="text-gray-400 mb-6">Listen to more music to get personalized recommendations.</p>
                    <a href="{{ route('discovery.trending') }}" class="btn btn-primary">Explore Trending</a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($recommendations as $track)
                        <div class="bg-white/5 rounded-xl p-4 border border-white/10 hover:bg-white/10 transition group flex gap-4">
                            <div class="relative w-24 h-24 flex-shrink-0">
                                <img src="{{ $track->cover_image }}" class="w-full h-full rounded-lg object-cover">
                                <button
                                    class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition rounded-lg">
                                    <div
                                        class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-black shadow-lg">
                                        <i class="fas fa-play text-xs pl-0.5"></i>
                                    </div>
                                </button>
                            </div>
                            <div class="flex-1 min-w-0 flex flex-col justify-center">
                                <h3 class="font-bold text-lg truncate hover:text-blue-400 cursor-pointer">{{ $track->title }}</h3>
                                <p class="text-gray-400 text-sm truncate mb-2">{{ $track->user->name }}</p>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs bg-white/10 px-2 py-0.5 rounded text-gray-300">{{ $track->genre }}</span>
                                    <button class="text-gray-500 hover:text-red-500"><i class="far fa-heart"></i></button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <!-- Recommended Artists -->
        <section>
            <h2 class="text-2xl font-bold mb-6">Artists You Might Like</h2>
            @if($recommendedArtists->isEmpty())
                <p class="text-gray-400">Follow more artists to get better recommendations.</p>
            @else
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                    @foreach($recommendedArtists as $artist)
                        <div
                            class="bg-white/5 rounded-xl p-6 border border-white/10 text-center group hover:bg-white/10 transition">
                            <img src="{{ $artist->avatar_url ?? 'https://via.placeholder.com/80' }}"
                                class="w-20 h-20 rounded-full mx-auto mb-4 object-cover group-hover:scale-110 transition">
                            <h3 class="font-bold truncate mb-1">{{ $artist->name }}</h3>
                            <p class="text-xs text-gray-400 mb-4">{{ number_format($artist->followers_count) }} followers</p>
                            <button
                                class="w-full py-1.5 bg-blue-600 text-white rounded-full text-sm font-bold hover:bg-blue-500 transition">Follow</button>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection