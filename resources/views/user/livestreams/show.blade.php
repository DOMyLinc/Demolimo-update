@extends('layouts.app')

@section('title', $stream->title)

@section('content')
    <div class="min-h-screen bg-black">
        <div class="container mx-auto px-4 py-6">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Video Player -->
                <div class="lg:col-span-3">
                    <div class="aspect-video bg-gray-900 rounded-xl overflow-hidden mb-4 relative">
                        @if($stream->status === 'live')
                            <!-- Video Player Placeholder -->
                            <div
                                class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-800 to-gray-900">
                                <div class="text-center">
                                    <i class="fas fa-video text-6xl text-gray-600 mb-4"></i>
                                    <p class="text-gray-400">Live Stream Player</p>
                                    <p class="text-xs text-gray-500 mt-2">Stream URL: {{ $stream->stream_url }}</p>
                                </div>
                            </div>
                            <div
                                class="absolute top-4 left-4 bg-red-600 text-white text-sm font-bold px-4 py-2 rounded-full flex items-center">
                                <span class="w-2 h-2 bg-white rounded-full mr-2 animate-pulse"></span>
                                LIVE
                            </div>
                            <div class="absolute top-4 right-4 bg-black/70 text-white text-sm font-bold px-3 py-2 rounded">
                                <i class="fas fa-eye mr-2"></i> {{ number_format($stream->current_viewers) }} watching
                            </div>
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <div class="text-center">
                                    <i class="fas fa-clock text-4xl text-gray-600 mb-4"></i>
                                    <p class="text-gray-400">Stream {{ $stream->status }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Stream Info -->
                    <div class="bg-white/5 rounded-xl p-6 border border-white/10">
                        <div class="flex items-center gap-4 mb-4">
                            <img src="{{ $stream->user->avatar_url ?? 'https://via.placeholder.com/50' }}"
                                class="w-12 h-12 rounded-full">
                            <div class="flex-1">
                                <h1 class="text-2xl font-bold">{{ $stream->title }}</h1>
                                <p class="text-gray-400">{{ $stream->user->name }}</p>
                            </div>
                            @if(auth()->check() && $stream->enable_donations)
                                <button
                                    class="px-6 py-2 bg-yellow-600 hover:bg-yellow-500 text-white rounded-full font-bold transition">
                                    <i class="fas fa-heart mr-2"></i> Donate
                                </button>
                            @endif
                        </div>
                        @if($stream->description)
                            <p class="text-gray-300">{{ $stream->description }}</p>
                        @endif
                    </div>
                </div>

                <!-- Chat Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white/5 rounded-xl border border-white/10 h-[600px] flex flex-col">
                        <div class="p-4 border-b border-white/10">
                            <h3 class="font-bold">Live Chat</h3>
                        </div>

                        @if($stream->enable_chat)
                            <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-3">
                                <!-- Messages will be loaded via JavaScript -->
                                <p class="text-gray-500 text-sm text-center">Loading chat...</p>
                            </div>

                            @auth
                                <div class="p-4 border-t border-white/10">
                                    <form id="chat-form" class="flex gap-2">
                                        <input type="text" id="chat-input" placeholder="Send a message..."
                                            class="flex-1 bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition">
                                        <button type="submit"
                                            class="px-4 py-2 bg-purple-600 hover:bg-purple-500 rounded-lg font-bold transition">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="p-4 border-t border-white/10 text-center">
                                    <p class="text-gray-400 text-sm mb-2">Sign in to chat</p>
                                    <a href="{{ route('login') }}"
                                        class="text-purple-400 hover:text-purple-300 font-bold text-sm">Login</a>
                                </div>
                            @endauth
                        @else
                            <div class="flex-1 flex items-center justify-center">
                                <p class="text-gray-500">Chat is disabled</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Mock chat functionality
            document.getElementById('chat-form')?.addEventListener('submit', function (e) {
                e.preventDefault();
                const input = document.getElementById('chat-input');
                if (input.value.trim()) {
                    // Would send via AJAX in production
                    console.log('Sending message:', input.value);
                    input.value = '';
                }
            });
        </script>
    @endpush
@endsection