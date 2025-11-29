@extends('layouts.app')

@section('title', 'Activity Feed')

@section('content')
    <div class="container mx-auto px-4 py-8 flex gap-8">

        <!-- Main Feed -->
        <div class="w-full lg:w-2/3">
            <h1 class="text-3xl font-bold mb-8">Activity Feed</h1>

            @if($tracks->isEmpty())
                <div class="bg-white/5 rounded-2xl p-12 text-center border border-white/10">
                    <div class="w-20 h-20 bg-white/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-stream text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Your feed is quiet</h3>
                    <p class="text-gray-400 mb-6">Follow artists and friends to see their latest tracks and activity here.</p>
                    <a href="{{ route('user.discovery.index') }}" class="btn btn-primary">Find People to Follow</a>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($tracks as $track)
                        <div class="bg-white/5 rounded-2xl p-6 border border-white/10 hover:border-white/20 transition group">
                            <!-- Header -->
                            <div class="flex items-center mb-4">
                                <img src="{{ $track->user->avatar_url ?? 'https://via.placeholder.com/40' }}"
                                    class="w-10 h-10 rounded-full mr-3">
                                <div>
                                    <p class="font-bold text-sm">
                                        <a href="#" class="hover:text-blue-400">{{ $track->user->name }}</a>
                                        <span class="text-gray-400 font-normal">uploaded a new track</span>
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $track->created_at->diffForHumans() }}</p>
                                </div>
                            </div>

                            <!-- Track Card -->
                            <div class="flex gap-4 bg-black/20 rounded-xl p-4 hover:bg-black/30 transition">
                                <div class="relative w-24 h-24 flex-shrink-0">
                                    <img src="{{ $track->cover_image ?? 'https://via.placeholder.com/150' }}"
                                        class="w-full h-full object-cover rounded-lg">
                                    <button wire:click="$dispatch('playTrack', { trackId: {{ $track->id }} })"
                                        class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                        <div
                                            class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-black shadow-lg">
                                            <i class="fas fa-play pl-1"></i>
                                        </div>
                                    </button>
                                </div>

                                <div class="flex-1 min-w-0 flex flex-col justify-center">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-bold text-lg truncate hover:text-blue-400 cursor-pointer">
                                                {{ $track->title }}</h3>
                                            <p class="text-gray-400 text-sm">{{ $track->user->name }}</p>
                                        </div>
                                        <span class="bg-white/10 text-xs px-2 py-1 rounded text-gray-300">{{ $track->genre }}</span>
                                    </div>

                                    <!-- Waveform Placeholder -->
                                    <div class="h-8 mt-2 flex items-end gap-0.5 opacity-50">
                                        @for($i = 0; $i < 40; $i++)
                                            <div class="w-1 bg-gray-500 rounded-t" style="height: {{ rand(20, 100) }}%"></div>
                                        @endfor
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-6 mt-4 text-gray-400 text-sm">
                                <button class="flex items-center gap-2 hover:text-red-500 transition">
                                    <i class="far fa-heart"></i> {{ $track->likes_count ?? 0 }}
                                </button>
                                <button class="flex items-center gap-2 hover:text-blue-400 transition">
                                    <i class="far fa-comment"></i> {{ $track->comments_count ?? 0 }}
                                </button>
                                <button class="flex items-center gap-2 hover:text-green-400 transition">
                                    <i class="fas fa-retweet"></i> Repost
                                </button>
                                <button class="flex items-center gap-2 hover:text-white transition ml-auto">
                                    <i class="fas fa-share-alt"></i> Share
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $tracks->links() }}
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="hidden lg:block w-1/3">
            <div class="sticky top-24 space-y-8">

                <!-- Suggested Artists -->
                <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                    <h3 class="font-bold mb-4">Who to Follow</h3>
                    <div class="space-y-4">
                        @foreach(range(1, 3) as $i)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full"></div>
                                    <div>
                                        <p class="font-bold text-sm">Artist Name</p>
                                        <p class="text-xs text-gray-400">Hip Hop • 12k followers</p>
                                    </div>
                                </div>
                                <button class="text-blue-400 text-sm font-bold hover:text-blue-300">Follow</button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Listening History -->
                <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                    <h3 class="font-bold mb-4">Recently Played</h3>
                    <div class="space-y-3">
                        @foreach(range(1, 3) as $i)
                            <div
                                class="flex items-center gap-3 group cursor-pointer hover:bg-white/5 p-2 rounded-lg transition">
                                <img src="https://via.placeholder.com/40" class="rounded">
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-sm truncate">Track Title</p>
                                    <p class="text-xs text-gray-400 truncate">Artist Name</p>
                                </div>
                                <button class="opacity-0 group-hover:opacity-100 text-white">
                                    <i class="fas fa-play text-xs"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection