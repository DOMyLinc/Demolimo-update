@extends('layouts.app')

@section('title', 'Podcasts')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold mb-2">Podcasts</h1>
            <p class="text-gray-400">Discover and listen to podcasts from creators worldwide.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('podcasts.my') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-full font-bold transition">
                <i class="fas fa-microphone mr-2"></i> My Podcasts
            </a>
            <a href="{{ route('podcasts.create') }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white rounded-full font-bold transition">
                <i class="fas fa-plus mr-2"></i> Create Podcast
            </a>
        </div>
    </div>

    @if($podcasts->isEmpty())
        <div class="text-center py-20 bg-white/5 rounded-2xl border border-white/10">
            <i class="fas fa-podcast text-4xl text-gray-500 mb-4"></i>
            <h3 class="text-xl font-bold mb-2">No podcasts yet</h3>
            <p class="text-gray-400">Be the first to create a podcast!</p>
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            @foreach($podcasts as $podcast)
                <a href="{{ route('podcasts.show', $podcast) }}" class="group">
                    <div class="relative aspect-square mb-3 rounded-xl overflow-hidden bg-white/5 shadow-lg">
                        @if($podcast->cover_image)
                            <img src="{{ Storage::url($podcast->cover_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-purple-700 to-blue-700 flex items-center justify-center">
                                <i class="fas fa-podcast text-6xl text-white/20"></i>
                            </div>
                        @endif
                        @if($podcast->is_explicit)
                            <div class="absolute top-2 left-2 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded">E</div>
                        @endif
                    </div>
                    <h3 class="font-bold truncate group-hover:text-purple-400 transition">{{ $podcast->title }}</h3>
                    <p class="text-sm text-gray-400 truncate">{{ $podcast->user->name }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $podcast->episodes_count }} episodes</p>
                </a>
            @endforeach
        </div>
        <div class="mt-8">
            {{ $podcasts->links() }}
        </div>
    @endif
</div>
@endsection
