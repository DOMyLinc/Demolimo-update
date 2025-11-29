@extends('layouts.app')

@section('title', 'Track Trials')

@section('content')
    <div class="min-h-screen bg-gradient-to-b from-gray-900 to-black text-white">

        <!-- Hero Section -->
        <div class="relative overflow-hidden py-20">
            <div class="absolute inset-0 bg-red-600/10 z-0"></div>
            <div class="container mx-auto px-4 relative z-10 text-center">
                <h1
                    class="text-6xl font-black mb-6 tracking-tighter text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-orange-500 animate-pulse">
                    TRACK TRIALS
                </h1>
                <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
                    Where Creators Rise. Compete in global music challenges, showcase your talent, and become a legend.
                </p>

                @if(auth()->check() && auth()->user()->is_creator)
                    <div class="inline-block bg-white/10 backdrop-blur-md border border-white/20 rounded-full px-6 py-2 mb-8">
                        <span class="text-yellow-400 font-bold"><i class="fas fa-crown mr-2"></i>
                            {{ auth()->user()->creator_title ?? 'Creator' }} Status Active</span>
                    </div>
                @endif

                <div class="flex justify-center gap-4">
                    <a href="#current-trials"
                        class="px-8 py-3 bg-red-600 hover:bg-red-700 rounded-full font-bold transition transform hover:scale-105 shadow-lg shadow-red-600/30">
                        Explore Trials
                    </a>
                    @if(auth()->check() && !auth()->user()->is_creator)
                        <a href="#"
                            class="px-8 py-3 bg-white/10 hover:bg-white/20 border border-white/20 rounded-full font-bold transition">
                            Become a Creator
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Current Trials Section -->
        <div id="current-trials" class="container mx-auto px-4 py-16">
            <div class="flex justify-between items-end mb-10">
                <div>
                    <h2 class="text-3xl font-bold mb-2">Current Trials</h2>
                    <p class="text-gray-400">Active competitions happening right now</p>
                </div>
                <a href="#" class="text-red-500 hover:text-red-400 font-semibold">View All <i
                        class="fas fa-arrow-right ml-1"></i></a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($activeTrials as $trial)
                    <div
                        class="group relative bg-gray-800/50 rounded-2xl overflow-hidden border border-gray-700 hover:border-red-500/50 transition-all duration-300 hover:shadow-2xl hover:shadow-red-900/20">
                        <!-- Status Badge -->
                        <div class="absolute top-4 right-4 z-20">
                            <span
                                class="bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider animate-pulse">
                                Live
                            </span>
                        </div>

                        <div class="p-8">
                            <h3 class="text-2xl font-bold mb-3 group-hover:text-red-500 transition-colors">{{ $trial->title }}
                            </h3>
                            <p class="text-gray-400 mb-6 line-clamp-2">{{ $trial->description }}</p>

                            <div class="flex items-center justify-between text-sm text-gray-500 mb-6">
                                <span><i class="fas fa-users mr-2"></i> {{ $trial->entries->count() }} Entries</span>
                                <span><i class="far fa-clock mr-2"></i> Ends
                                    {{ $trial->end_date ? $trial->end_date->diffForHumans() : 'Soon' }}</span>
                            </div>

                            <div class="flex gap-3">
                                <a href="{{ route('track-trials.show', $trial) }}"
                                    class="flex-1 text-center py-3 bg-white/5 hover:bg-white/10 rounded-xl font-semibold transition">
                                    View Entries
                                </a>
                                @if(auth()->check() && auth()->user()->is_creator)
                                    <a href="{{ route('track-trials.upload', $trial) }}"
                                        class="flex-1 text-center py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold transition shadow-lg shadow-red-900/30">
                                        Upload Track
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div
                        class="col-span-full text-center py-20 bg-gray-800/30 rounded-3xl border border-dashed border-gray-700">
                        <i class="fas fa-music text-6xl text-gray-600 mb-4"></i>
                        <h3 class="text-2xl font-bold text-gray-400">No Active Trials</h3>
                        <p class="text-gray-500">Check back soon for new competitions!</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Best Performing Tracks -->
        <div class="bg-gray-900/50 py-16 border-y border-gray-800">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold mb-10 text-center">🔥 Trending on Track Trials</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($bestTracks as $index => $entry)
                        <div
                            class="flex items-center gap-4 bg-black/40 p-4 rounded-xl border border-gray-800 hover:border-gray-600 transition">
                            <div class="relative w-16 h-16 flex-shrink-0">
                                <img src="{{ $entry->cover_image ? Storage::url($entry->cover_image) : 'https://via.placeholder.com/150' }}"
                                    class="w-full h-full object-cover rounded-lg">
                                <div
                                    class="absolute -top-2 -left-2 w-6 h-6 bg-yellow-500 text-black font-bold rounded-full flex items-center justify-center text-xs">
                                    #{{ $index + 1 }}
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold truncate">{{ $entry->track_title }}</h4>
                                <p class="text-sm text-gray-400 truncate">{{ $entry->creator->name }}</p>
                                <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                                    <span><i class="fas fa-play mr-1"></i> {{ number_format($entry->plays) }}</span>
                                    <span><i class="fas fa-heart mr-1 text-red-500"></i>
                                        {{ number_format($entry->votes) }}</span>
                                </div>
                            </div>
                            <button
                                class="w-10 h-10 rounded-full bg-red-600 flex items-center justify-center hover:bg-red-500 transition">
                                <i class="fas fa-play text-white"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
@endsection