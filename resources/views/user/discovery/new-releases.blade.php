@extends('layouts.app')

@section('title', 'New Releases')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">New Releases</h1>

        <!-- New Albums -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold mb-6">Latest Albums</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                @foreach($albums as $album)
                    <div class="group">
                        <div class="relative aspect-square mb-3 rounded-xl overflow-hidden bg-white/5 shadow-lg">
                            <img src="{{ $album->cover_image ?? 'https://via.placeholder.com/300' }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            <button
                                class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                <div
                                    class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-black shadow-lg transform scale-90 group-hover:scale-100 transition">
                                    <i class="fas fa-play pl-1"></i>
                                </div>
                            </button>
                        </div>
                        <h3 class="font-bold truncate hover:text-blue-400 cursor-pointer">{{ $album->title }}</h3>
                        <p class="text-sm text-gray-400 truncate">{{ $album->user->name }}</p>
                    </div>
                @endforeach
            </div>
            <div class="mt-6">
                {{ $albums->links() }}
            </div>
        </section>

        <!-- New Tracks -->
        <section>
            <h2 class="text-2xl font-bold mb-6">Latest Tracks</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($tracks as $track)
                    <div
                        class="flex items-center gap-4 p-3 rounded-xl bg-white/5 hover:bg-white/10 transition group border border-white/5">
                        <div class="relative w-16 h-16 flex-shrink-0">
                            <img src="{{ $track->cover_image }}" class="w-full h-full rounded-lg object-cover">
                            <button
                                class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition rounded-lg">
                                <i class="fas fa-play text-white"></i>
                            </button>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold truncate hover:text-blue-400 cursor-pointer">{{ $track->title }}</h3>
                            <p class="text-sm text-gray-400 truncate">{{ $track->user->name }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $track->created_at->diffForHumans() }}</p>
                        </div>
                        <button class="text-gray-500 hover:text-red-500 px-2"><i class="far fa-heart"></i></button>
                    </div>
                @endforeach
            </div>
            <div class="mt-6">
                {{ $tracks->links() }}
            </div>
        </section>
    </div>
@endsection