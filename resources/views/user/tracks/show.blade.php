<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $track->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col md:flex-row gap-8">
                        <!-- Cover Art -->
                        <div class="w-full md:w-1/3">
                            <div class="aspect-square bg-gray-200 rounded-lg overflow-hidden shadow-md">
                                @if($track->image_path)
                                    <img src="{{ Storage::url($track->image_path) }}" alt="{{ $track->title }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <div class="flex items-center justify-center h-full text-gray-400">
                                        <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3">
                                            </path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Track Info & Player -->
                        <div class="w-full md:w-2/3">
                            <h1 class="text-3xl font-bold mb-2">{{ $track->title }}</h1>
                            <div class="flex items-center gap-2 text-gray-600 mb-4">
                                <span>By {{ $track->user->name }}</span>
                                <span>•</span>
                                <span>{{ $track->created_at->format('M d, Y') }}</span>
                            </div>

                            <!-- Audio Player -->
                            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                                <audio controls class="w-full">
                                    <source src="{{ Storage::url($track->audio_path) }}" type="audio/mpeg">
                                    Your browser does not support the audio element.
                                </audio>
                                <!-- Waveform Visualization (Placeholder) -->
                                <div class="h-16 bg-gray-200 mt-4 rounded flex items-end gap-1 overflow-hidden px-1">
                                    @if($track->waveform_data)
                                        @foreach($track->waveform_data as $point)
                                            <div class="flex-1 bg-blue-500" style="height: {{ $point * 100 }}%"></div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <div class="prose max-w-none mb-6">
                                <h3 class="text-lg font-semibold mb-2">Description</h3>
                                <p>{{ $track->description ?? 'No description provided.' }}</p>
                            </div>

                            <div class="flex gap-4">
                                <button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                                    Like ({{ $track->likes }})
                                </button>
                                <button class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700 transition">
                                    Share
                                </button>
                                @if($track->price > 0)
                                    <button class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition">
                                        Buy for ${{ $track->price }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>