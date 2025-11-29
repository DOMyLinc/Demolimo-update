@extends('layouts.app')

@section('title', 'Radio Stations')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-2">Radio Stations</h1>
            <p class="text-gray-400">Listen to curated music channels and discover new tracks 24/7.</p>
        </div>

        <!-- Featured Stations -->
        @if($featured->isNotEmpty())
            <section class="mb-12">
                <h2 class="text-2xl font-bold mb-6">Featured Stations</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($featured as $station)
                        <a href="{{ route('radio.show', $station->slug) }}" class="group">
                            <div
                                class="relative aspect-square mb-3 rounded-2xl overflow-hidden bg-gradient-to-br from-red-900 to-orange-900 shadow-lg">
                                @if($station->cover_image)
                                    <img src="{{ Storage::url($station->cover_image) }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-broadcast-tower text-8xl text-white/20"></i>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                                <div class="absolute bottom-4 left-4 right-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                                        <span class="text-white text-xs font-bold uppercase">Live</span>
                                    </div>
                                    <h3 class="text-white font-bold text-xl">{{ $station->name }}</h3>
                                    <p class="text-white/80 text-sm"><i class="fas fa-headphones mr-2"></i>
                                        {{ number_format($station->listeners_count) }} listening</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- All Stations -->
        <section>
            <h2 class="text-2xl font-bold mb-6">All Stations</h2>
            @if($stations->isEmpty())
                <div class="text-center py-20 bg-white/5 rounded-2xl border border-white/10">
                    <i class="fas fa-radio text-4xl text-gray-500 mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">No radio stations available</h3>
                    <p class="text-gray-400">Check back later for new stations!</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($stations as $station)
                        <a href="{{ route('radio.show', $station->slug) }}"
                            class="bg-white/5 rounded-xl border border-white/10 overflow-hidden hover:bg-white/10 transition group">
                            <div class="aspect-square bg-gradient-to-br from-gray-700 to-gray-900 relative">
                                @if($station->cover_image)
                                    <img src="{{ Storage::url($station->cover_image) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-radio text-5xl text-white/20"></i>
                                    </div>
                                @endif
                                @if($station->is_live)
                                    <div
                                        class="absolute top-2 right-2 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded-full flex items-center">
                                        <span class="w-1.5 h-1.5 bg-white rounded-full mr-1.5 animate-pulse"></span>
                                        LIVE
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="font-bold truncate group-hover:text-red-400 transition mb-1">{{ $station->name }}</h3>
                                <p class="text-sm text-gray-400 truncate mb-2">{{ $station->description }}</p>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-500"><i class="fas fa-music mr-1"></i>
                                        {{ ucfirst($station->genre) }}</span>
                                    <span class="text-gray-500"><i class="fas fa-headphones mr-1"></i>
                                        {{ number_format($station->listeners_count) }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="mt-8">
                    {{ $stations->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection