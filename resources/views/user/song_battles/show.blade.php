@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-bold text-white mb-2">{{ $songBattle->title }}</h1>
            <p class="text-gray-400 mb-6">{{ $songBattle->description }}</p>

            <div class="flex justify-center space-x-4 mb-8">
                <form action="{{ route('song_battles.share.feed', $songBattle) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                        Share to Feed
                    </button>
                </form>

                <form action="{{ route('song_battles.share.zipcode', $songBattle) }}" method="POST"
                    class="flex items-center bg-gray-800 rounded-full px-4 py-1">
                    @csrf
                    <input type="text" name="zipcode_id" placeholder="Zip ID"
                        class="bg-transparent text-white border-none focus:ring-0 w-20 text-center" required>
                    <button type="submit" class="text-green-400 hover:text-green-300 font-bold ml-2">
                        Share to Zip
                    </button>
                </form>
            </div>

            @if(session('success'))
                <div class="bg-green-500 text-white p-4 rounded mb-6 inline-block">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-500 text-white p-4 rounded mb-6 inline-block">
                    {{ session('error') }}
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($songBattle->versions as $version)
                <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-700 flex flex-col">
                    <div class="p-6 flex-grow">
                        <h2 class="text-2xl font-bold text-white mb-2">Version {{ $version->version_number }}</h2>
                        <p class="text-blue-400 mb-4">{{ $version->style_name }}</p>

                        <div class="mb-6">
                            <audio controls class="w-full" data-version-id="{{ $version->id }}">
                                <source src="{{ Storage::url($version->file_path) }}" type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>
                            <p class="text-xs text-gray-500 mt-2 text-center"><span
                                    class="play-count-{{ $version->id }}">{{ $version->play_count }}</span> Plays</p>
                        </div>

                        <form action="{{ route('song_battles.vote', $version) }}" method="POST" class="mb-6 text-center">
                            @csrf
                            <button type="submit"
                                class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-3 px-8 rounded-full transition duration-300 w-full shadow-lg transform hover:scale-105">
                                Vote ({{ $version->votes_count }})
                            </button>
                        </form>

                        <div class="border-t border-gray-700 pt-4">
                            <h3 class="text-white font-bold mb-4">Comments</h3>
                            <div class="space-y-4 max-h-60 overflow-y-auto mb-4 custom-scrollbar pr-2">
                                @foreach($version->comments as $comment)
                                    <div class="bg-gray-700 p-3 rounded">
                                        <div class="flex justify-between items-start mb-1">
                                            <span class="font-bold text-sm text-blue-300">{{ $comment->user->name }}</span>
                                            <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-gray-300 text-sm">{{ $comment->content }}</p>
                                    </div>
                                @endforeach
                            </div>

                            <form action="{{ route('song_battles.comment', $version) }}" method="POST">
                                @csrf
                                <textarea name="content"
                                    class="w-full bg-gray-700 text-white rounded px-3 py-2 text-sm mb-2 border border-gray-600 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                                    rows="2" placeholder="Add a comment..." required></textarea>
                                <button type="submit"
                                    class="bg-gray-600 hover:bg-gray-500 text-white text-sm font-bold py-1 px-4 rounded transition duration-300 w-full">
                                    Post Comment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const audios = document.querySelectorAll('audio');
            audios.forEach(audio => {
                audio.addEventListener('play', function () {
                    const versionId = this.dataset.versionId;
                    if (!versionId) return;

                    // Simple debounce/check to prevent spamming plays on seek/pause-play
                    if (this.dataset.played) return;

                    fetch(`/song-battles/versions/${versionId}/play`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.dataset.played = true;
                                // Update play count in UI
                                const countSpan = document.querySelector(`.play-count-${versionId}`);
                                if (countSpan) {
                                    countSpan.textContent = data.plays;
                                }
                            }
                        })
                        .catch(error => console.error('Error registering play:', error));
                });

                // Reset played flag when audio ends so they can play again for credit? 
                // Or maybe only once per page load? Let's stick to once per page load for now to prevent abuse.
            });
        });
    </script>
@endsection