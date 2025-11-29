@extends('layouts.app')

@section('title', 'Upload to ' . $trial->title)

@section('content')
    <div class="min-h-screen bg-black text-white py-20">
        <div class="container mx-auto px-4 max-w-2xl">
            <a href="{{ route('track-trials.index') }}" class="text-gray-400 hover:text-white mb-8 inline-block">
                <i class="fas fa-arrow-left mr-2"></i> Back to Trials
            </a>

            <div class="bg-gray-900 rounded-2xl border border-gray-800 p-8">
                <div class="mb-8">
                    <span class="text-red-500 font-bold tracking-wider text-sm uppercase">Creator Upload</span>
                    <h1 class="text-3xl font-bold mt-2">Submit to {{ $trial->title }}</h1>
                    <p class="text-gray-400 mt-2">Showcase your best work. Make sure you own all rights to this track.</p>
                </div>

                <form action="{{ route('track-trials.store', $trial) }}" method="POST" enctype="multipart/form-data"
                    class="space-y-6">
                    @csrf

                    <!-- Track Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Track Title</label>
                        <input type="text" name="track_title" required
                            class="w-full bg-black border border-gray-700 rounded-xl px-4 py-3 text-white focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none transition">
                    </div>

                    <!-- Audio File -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Audio File (MP3, WAV, FLAC)</label>
                        <div
                            class="border-2 border-dashed border-gray-700 rounded-xl p-8 text-center hover:border-red-500/50 transition cursor-pointer bg-black/50">
                            <input type="file" name="audio_file" required accept=".mp3,.wav,.flac" class="hidden"
                                id="audio-input">
                            <label for="audio-input" class="cursor-pointer">
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-500 mb-3"></i>
                                <p class="text-gray-300 font-medium">Click to upload or drag and drop</p>
                                <p class="text-sm text-gray-500 mt-1">Max size: 50MB</p>
                            </label>
                            <div id="audio-filename" class="mt-4 text-red-400 font-medium hidden"></div>
                        </div>
                    </div>

                    <!-- Cover Image -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Cover Image (Optional)</label>
                        <input type="file" name="cover_image" accept="image/*"
                            class="w-full bg-black border border-gray-700 rounded-xl px-4 py-3 text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-800 file:text-white hover:file:bg-gray-700">
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-500 hover:to-orange-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-red-900/20 transform transition hover:scale-[1.02]">
                        Submit Track <i class="fas fa-paper-plane ml-2"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('audio-input').addEventListener('change', function (e) {
            const fileName = e.target.files[0]?.name;
            const display = document.getElementById('audio-filename');
            if (fileName) {
                display.textContent = 'Selected: ' + fileName;
                display.classList.remove('hidden');
            }
        });
    </script>
@endsection