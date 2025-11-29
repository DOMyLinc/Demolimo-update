@extends('layouts.app')

@section('title', $podcast->title)

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-5xl mx-auto">
            <!-- Podcast Header -->
            <div class="flex gap-8 mb-12">
                <div class="w-64 h-64 rounded-2xl overflow-hidden shadow-2xl flex-shrink-0">
                    @if($podcast->cover_image)
                        <img src="{{ Storage::url($podcast->cover_image) }}" class="w-full h-full object-cover">
                    @else
                        <div
                            class="w-full h-full bg-gradient-to-br from-purple-700 to-blue-700 flex items-center justify-center">
                            <i class="fas fa-podcast text-8xl text-white/20"></i>
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <p class="text-sm text-purple-400 font-bold uppercase mb-2">Podcast</p>
                            <h1 class="text-4xl font-bold mb-2">{{ $podcast->title }}</h1>
                            <p class="text-gray-400">by <span class="text-white font-bold">{{ $podcast->user->name }}</span>
                            </p>
                        </div>
                        @if(auth()->check())
                            @if(auth()->user()->podcastSubscriptions->contains($podcast->id))
                                <form action="{{ route('podcasts.unsubscribe', $podcast) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        class="px-6 py-2 border border-white/20 rounded-full font-bold hover:bg-white/10 transition">Subscribed</button>
                                </form>
                            @else
                                <form action="{{ route('podcasts.subscribe', $podcast) }}" method="POST">
                                    @csrf
                                    <button
                                        class="px-6 py-2 bg-purple-600 rounded-full font-bold hover:bg-purple-500 transition">Subscribe</button>
                                </form>
                            @endif
                        @endif
                    </div>
                    <p class="text-gray-300 mb-6">{{ $podcast->description }}</p>
                    <div class="flex gap-6 text-sm text-gray-400">
                        <span><i class="fas fa-list mr-2"></i> {{ $podcast->episodes->count() }} episodes</span>
                        <span><i class="fas fa-users mr-2"></i> {{ number_format($podcast->subscribers) }}
                            subscribers</span>
                        <span><i class="fas fa-globe mr-2"></i> {{ $podcast->language }}</span>
                    </div>
                </div>
            </div>

            <!-- Episodes List -->
            <div>
                <h2 class="text-2xl font-bold mb-6">Episodes</h2>
                @if($podcast->episodes->isEmpty())
                    <div class="text-center py-12 bg-white/5 rounded-xl border border-white/10">
                        <p class="text-gray-400">No episodes published yet.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($podcast->episodes as $episode)
                            <div
                                class="bg-white/5 rounded-xl p-4 border border-white/10 hover:bg-white/10 transition flex items-center gap-4">
                                <button
                                    class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center hover:bg-purple-500 transition flex-shrink-0">
                                    <i class="fas fa-play pl-1"></i>
                                </button>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold truncate">{{ $episode->title }}</h3>
                                    <p class="text-sm text-gray-400 truncate">{{ $episode->description }}</p>
                                    <div class="flex gap-4 text-xs text-gray-500 mt-1">
                                        <span>{{ $episode->published_at->format('M d, Y') }}</span>
                                        <span>{{ gmdate("H:i:s", $episode->duration) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection