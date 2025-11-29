<div x-data="{ 
        audio: null,
        isPlaying: @entangle('isPlaying'),
        volume: @entangle('volume'),
        progress: 0,
        duration: 0,
        isMinimized: false,
        isClosed: false,
        init() {
            this.audio = new Audio();
            
            this.audio.addEventListener('timeupdate', () => {
                this.progress = (this.audio.currentTime / this.audio.duration) * 100;
            });
            
            this.audio.addEventListener('ended', () => {
                this.isPlaying = false;
                $wire.next();
            });

            $wire.on('audio-play', (data) => {
                this.audio.src = data.url;
                this.audio.play();
                this.isPlaying = true;
                this.isClosed = false;
            });

            $wire.on('audio-toggle', () => {
                if (this.isPlaying) {
                    this.audio.play();
                } else {
                    this.audio.pause();
                }
            });
            
            this.$watch('volume', (value) => {
                this.audio.volume = value / 100;
            });
        },
        seek(event) {
            const rect = event.target.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const percentage = x / rect.width;
            this.audio.currentTime = percentage * this.audio.duration;
        },
        formatTime(seconds) {
            if (!seconds) return '0:00';
            const m = Math.floor(seconds / 60);
            const s = Math.floor(seconds % 60);
            return `${m}:${s < 10 ? '0' : ''}${s}`;
        },
        closePlayer() {
            this.audio.pause();
            this.isPlaying = false;
            this.isClosed = true;
        },
        toggleMinimize() {
            this.isMinimized = !this.isMinimized;
        }
    }" x-show="!isClosed && @js($currentTrack !== null)" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
    x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-y-0"
    x-transition:leave-end="translate-y-full"
    class="fixed bottom-0 left-0 right-0 bg-[#1a1a2e] border-t border-white/10 text-white z-50"
    :class="isMinimized ? 'h-16' : 'h-24'" style="backdrop-filter: blur(20px);">

    @if($currentTrack)
        <div class="container mx-auto px-4 h-full relative">
            <!-- Close and Minimize Buttons -->
            <div class="absolute top-2 right-4 flex gap-3 z-10">
                <button @click="toggleMinimize()" class="text-gray-400 hover:text-white transition-colors" title="Minimize">
                    <i class="fas" :class="isMinimized ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </button>
                <button @click="closePlayer()" class="text-gray-400 hover:text-red-500 transition-colors"
                    title="Close Player">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Minimized View -->
            <div x-show="isMinimized" class="flex items-center h-full gap-4 pr-20">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <img src="{{ $currentTrack['cover_image'] ?? 'https://via.placeholder.com/60' }}"
                        class="w-12 h-12 rounded object-cover">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold truncate text-sm">{{ $currentTrack['title'] }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $currentTrack['artist']['name'] ?? 'Unknown Artist' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button wire:click="previous" class="hover:text-purple-400 transition-colors">
                        <i class="fas fa-step-backward"></i>
                    </button>
                    <button wire:click="togglePlay"
                        class="bg-white text-black rounded-full w-10 h-10 flex items-center justify-center hover:scale-105 transition">
                        <i class="fas" :class="isPlaying ? 'fa-pause' : 'fa-play pl-1'"></i>
                    </button>
                    <button wire:click="next" class="hover:text-purple-400 transition-colors">
                        <i class="fas fa-step-forward"></i>
                    </button>
                </div>
            </div>

            <!-- Full View -->
            <div x-show="!isMinimized" class="flex items-center h-full gap-4 pr-20">
                <!-- Track Info -->
                <div class="flex items-center w-1/4 min-w-[200px]">
                    <img src="{{ $currentTrack['cover_image'] ?? 'https://via.placeholder.com/60' }}"
                        class="w-14 h-14 rounded-md object-cover shadow-lg mr-4 animate-spin-slow"
                        :class="{ 'paused': !isPlaying }" style="animation-duration: 10s;">
                    <div class="overflow-hidden">
                        <h4 class="font-bold text-sm truncate">{{ $currentTrack['title'] }}</h4>
                        <p class="text-xs text-gray-400 truncate">{{ $currentTrack['artist']['name'] ?? 'Unknown Artist' }}
                        </p>
                    </div>
                    <button class="ml-4 text-gray-400 hover:text-red-500 transition">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>

                <!-- Player Controls -->
                <div class="flex flex-col items-center w-2/4 max-w-2xl px-4">
                    <div class="flex items-center gap-6 mb-2">
                        <button class="transition {{ $shuffle ? 'text-purple-500' : 'text-gray-400 hover:text-white' }}"
                            wire:click="toggleShuffle" title="{{ $shuffle ? 'Shuffle On' : 'Shuffle Off' }}">
                            <i class="fas fa-random text-sm"></i>
                        </button>
                        <button class="text-gray-300 hover:text-white transition" wire:click="previous">
                            <i class="fas fa-step-backward text-lg"></i>
                        </button>
                        <button
                            class="w-10 h-10 rounded-full bg-white text-black flex items-center justify-center hover:scale-105 transition shadow-lg shadow-white/20"
                            wire:click="togglePlay">
                            <i class="fas" :class="isPlaying ? 'fa-pause' : 'fa-play pl-1'"></i>
                        </button>
                        <button class="text-gray-300 hover:text-white transition" wire:click="next">
                            <i class="fas fa-step-forward text-lg"></i>
                        </button>
                        <button class="transition {{ $repeat ? 'text-purple-500' : 'text-gray-400 hover:text-white' }}"
                            wire:click="toggleRepeat" title="{{ $repeat ? 'Repeat On' : 'Repeat Off' }}">
                            <i class="fas fa-redo text-sm"></i>
                        </button>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full flex items-center gap-3 text-xs text-gray-400 font-mono">
                        <span x-text="formatTime(audio.currentTime)">0:00</span>
                        <div class="flex-1 h-1 bg-gray-700 rounded-full cursor-pointer group relative" @click="seek">
                            <div class="absolute h-full bg-gradient-to-r from-blue-500 to-purple-500 rounded-full group-hover:from-blue-400 group-hover:to-purple-400"
                                :style="`width: ${progress}%`"></div>
                            <div class="absolute h-3 w-3 bg-white rounded-full -top-1 shadow-md opacity-0 group-hover:opacity-100 transition-opacity"
                                :style="`left: ${progress}%`"></div>
                        </div>
                        <span x-text="formatTime(audio.duration)">0:00</span>
                    </div>
                </div>

                <!-- Volume & Extras -->
                <div class="flex items-center justify-end w-1/4 gap-4 min-w-[200px]">
                    <button class="text-gray-400 hover:text-white" @click="$wire.set('showQueue', !$wire.showQueue)">
                        <i class="fas fa-list"></i>
                    </button>

                    <div class="flex items-center gap-2 w-32 group">
                        <i class="fas fa-volume-up text-gray-400 text-xs"></i>
                        <input type="range" min="0" max="100" x-model="volume"
                            class="w-full h-1 bg-gray-700 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-3 [&::-webkit-slider-thumb]:h-3 [&::-webkit-slider-thumb]:bg-white [&::-webkit-slider-thumb]:rounded-full">
                    </div>

                    <button class="text-gray-400 hover:text-white">
                        <i class="fas fa-expand-alt"></i>
                    </button>
                </div>
            </div>
            <!-- Queue Panel -->
            <div x-show="$wire.showQueue" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-4"
                class="absolute bottom-full right-4 mb-4 w-80 bg-[#1a1a2e]/95 backdrop-blur-xl border border-white/10 rounded-xl shadow-2xl overflow-hidden z-50 max-h-[60vh] flex flex-col"
                @click.away="$wire.set('showQueue', false)">

                <!-- Header -->
                <div class="p-4 border-b border-white/10 flex items-center justify-between bg-white/5">
                    <h3 class="font-bold text-white">Queue</h3>
                    <button wire:click="clearQueue" class="text-xs text-red-400 hover:text-red-300 transition">
                        Clear All
                    </button>
                </div>

                <!-- List -->
                <div class="overflow-y-auto flex-1 p-2 space-y-1 custom-scrollbar">
                    @if(count($queue) > 0)
                        @foreach($queue as $index => $track)
                            <div class="group flex items-center gap-3 p-2 rounded-lg hover:bg-white/5 transition cursor-pointer {{ $currentIndex === $index ? 'bg-white/10 border border-purple-500/30' : '' }}"
                                wire:click="playFromQueue({{ $index }})">

                                <!-- Playing Indicator / Number -->
                                <div class="w-6 text-center text-xs text-gray-400 flex justify-center">
                                    @if($currentIndex === $index && $isPlaying)
                                        <div class="flex gap-0.5 justify-center items-end h-3">
                                            <div class="w-0.5 bg-purple-500 animate-music-bar-1"></div>
                                            <div class="w-0.5 bg-purple-500 animate-music-bar-2"></div>
                                            <div class="w-0.5 bg-purple-500 animate-music-bar-3"></div>
                                        </div>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </div>

                                <img src="{{ $track['cover_image'] ?? 'https://via.placeholder.com/40' }}"
                                    class="w-10 h-10 rounded object-cover">

                                <div class="flex-1 min-w-0">
                                    <p
                                        class="text-sm font-medium text-white truncate {{ $currentIndex === $index ? 'text-purple-400' : '' }}">
                                        {{ $track['title'] }}
                                    </p>
                                    <p class="text-xs text-gray-400 truncate">
                                        {{ $track['artist']['name'] ?? 'Unknown Artist' }}
                                    </p>
                                </div>

                                <button wire:click.stop="removeFromQueue({{ $index }})"
                                    class="opacity-0 group-hover:opacity-100 p-1 text-gray-400 hover:text-red-400 transition">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        @endforeach
                    @else
                        <div class="p-8 text-center text-gray-500 text-sm">
                            Queue is empty
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .animate-spin-slow {
        animation: spin 10s linear infinite;
    }

    .paused {
        animation-play-state: paused;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    .animate-music-bar-1 {
        animation: music-bar 0.6s ease-in-out infinite alternate;
    }

    .animate-music-bar-2 {
        animation: music-bar 0.6s ease-in-out infinite alternate 0.2s;
    }

    .animate-music-bar-3 {
        animation: music-bar 0.6s ease-in-out infinite alternate 0.4s;
    }

    @keyframes music-bar {
        0% {
            height: 30%;
        }

        100% {
            height: 100%;
        }
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 2px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }
</style>