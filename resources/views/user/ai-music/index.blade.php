@extends('layouts.app')

@section('title', 'AI Music Generator')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-12">
                <h1
                    class="text-4xl font-bold mb-4 bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-pink-600">
                    AI Music Generator</h1>
                <p class="text-gray-400 text-lg">Create unique, royalty-free music in seconds using advanced AI models.</p>
            </div>

            <div class="bg-white/5 rounded-2xl p-8 border border-white/10 shadow-xl">
                <form action="{{ route('ai-music.generate') }}" method="POST">
                    @csrf

                    <!-- Model Selection -->
                    <div class="mb-8">
                        <label class="block text-lg font-bold mb-4">Select AI Model</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($providers as $provider)
                                @foreach($provider->models as $model)
                                    <label class="relative cursor-pointer group">
                                        <input type="radio" name="model_id" value="{{ $model->id }}" class="peer sr-only" required>
                                        <div
                                            class="p-4 rounded-xl border border-white/10 bg-white/5 peer-checked:bg-purple-600 peer-checked:border-purple-500 transition hover:bg-white/10">
                                            <div class="flex justify-between items-start mb-2">
                                                <h3 class="font-bold">{{ $model->name }}</h3>
                                                <span class="text-xs bg-black/30 px-2 py-1 rounded">{{ $provider->name }}</span>
                                            </div>
                                            <p class="text-sm text-gray-400 mb-3">{{ $model->description }}</p>
                                            <div class="flex justify-between items-center text-xs font-bold">
                                                <span>Max {{ gmdate("i:s", $model->max_duration) }}</span>
                                                <span
                                                    class="text-green-400">{{ $model->price_per_generation > 0 ? $model->price_per_generation . ' ' . strtoupper($model->currency) : 'FREE' }}</span>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            @endforeach
                        </div>
                    </div>

                    <!-- Prompt Input -->
                    <div class="mb-8">
                        <label for="prompt" class="block text-lg font-bold mb-2">Describe your music</label>
                        <p class="text-sm text-gray-400 mb-4">Be specific about genre, mood, instruments, and tempo.</p>
                        <textarea name="prompt" id="prompt" rows="4"
                            class="w-full bg-black/20 border border-white/10 rounded-xl p-4 text-white placeholder-gray-500 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition"
                            placeholder="A lo-fi hip hop beat with jazzy piano chords, rain sounds in background, relaxed tempo..."
                            required></textarea>
                    </div>

                    <!-- Duration Slider -->
                    <div class="mb-8">
                        <label for="duration" class="block text-lg font-bold mb-2">Duration (seconds)</label>
                        <div class="flex items-center gap-4">
                            <input type="range" name="duration" id="duration" min="10" max="300" value="60"
                                class="w-full h-2 bg-white/10 rounded-lg appearance-none cursor-pointer accent-purple-600"
                                oninput="document.getElementById('durationValue').innerText = this.value + 's'">
                            <span id="durationValue" class="font-bold w-12 text-right">60s</span>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl font-bold text-lg hover:scale-105 transition shadow-lg shadow-purple-600/20">
                            <i class="fas fa-magic mr-2"></i> Generate Music
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('ai-music.history') }}" class="text-gray-400 hover:text-white font-bold">View Generation
                    History</a>
            </div>
        </div>
    </div>
@endsection