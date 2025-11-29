@extends('layouts.app')

@section('title', 'Live Streams')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold mb-2">Live Streams</h1>
            <p class="text-gray-400">Watch live performances and connect with artists in real-time.</p>
        </div>
        <a href="{{ route('livestreams.create') }}" class="px-6 py-2 bg-red-600 hover:bg-red-500 text-white rounded-full font-bold transition">
            <i class="fas fa-broadcast-tower mr-2"></i> Go Live
        </a>
    </div>

    <!-- Live Now -->
    @if($liveStreams->isNotEmpty())
        <section class="mb-12">
            <h2 class="text-2xl font-bold mb-6 flex items-center">
                <span class="w-3 h-3 bg-red-500 rounded-full mr-3 animate-pulse"></span>
                Live Now
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($liveStreams as $stream)
                    <a href="{{ route('livestreams.show', $stream) }}" class="group">
                        <div class="relative aspect-video mb-3 rounded-xl overflow-hidden bg-black shadow-lg">
                            @if($stream->thumbnail)
                                <img src="{{ Storage::url($stream->thumbnail) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-red-900 to-purple-900 flex items-center justify-center">
                                    <i class="fas fa-video text-6xl text-white/20"></i>
                                </div>
                            @endif
                            <div class="absolute top-3 left-3 bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-full flex items-center">
                                <span class="w-2 h-2 bg-white rounded-full mr-2 animate-pulse"></span>
                                LIVE
                            </div>
                            <div class="absolute bottom-3 right-3 bg-black/70 text-white text-xs font-bold px-2 py-1 rounded">
                                <i class="fas fa-eye mr-1"></i> {{ number_format($stream->current_viewers) }}
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <img src="{{ $stream->user->avatar_url ?? 'https://via.placeholder.com/40' }}" class="w-10 h-10 rounded-full">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold truncate group-hover:text-red-400 transition">{{ $stream->title }}</h3>
                                <p class="text-sm text-gray-400 truncate">{{ $stream->user->name }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Scheduled Streams -->
    @if($scheduled->isNotEmpty())
        <section>
            <h2 class="text-2xl font-bold mb-6">Upcoming Streams</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($scheduled as $stream)
                    <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                        <div class="flex items-center gap-3 mb-3">
                            <img src="{{ $stream->user->avatar_url ?? 'https://via.placeholder.com/40' }}" class="w-10 h-10 rounded-full">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold truncate">{{ $stream->title }}</h3>
                                <p class="text-sm text-gray-400 truncate">{{ $stream->user->name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-400"><i class="far fa-clock mr-2"></i> {{ $stream->scheduled_at->format('M d, g:i A') }}</span>
                            <button class="text-purple-400 hover:text-purple-300 font-bold">Set Reminder</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if($liveStreams->isEmpty() && $scheduled->isEmpty())
        <div class="text-center py-20 bg-white/5 rounded-2xl border border-white/10">
            <i class="fas fa-broadcast-tower text-4xl text-gray-500 mb-4"></i>
            <h3 class="text-xl font-bold mb-2">No live streams right now</h3>
            <p class="text-gray-400">Check back later or start your own stream!</p>
        </div>
    @endif
</div>
@endsection
