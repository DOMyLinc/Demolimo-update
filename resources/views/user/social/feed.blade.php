@extends('layouts.app')

@section('title', 'Social Feed')

@section('content')
    <div class="container mx-auto px-4 py-8 flex gap-8">

        <!-- Main Feed -->
        <div class="w-full lg:w-2/3">
            <!-- Create Post -->
            <div class="bg-white/5 rounded-2xl p-6 border border-white/10 mb-8">
                <form action="{{ route('social.posts.create') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="flex gap-4">
                        <img src="{{ auth()->user()->avatar_url ?? 'https://via.placeholder.com/40' }}"
                            class="w-10 h-10 rounded-full">
                        <div class="flex-1">
                            <textarea name="content" rows="2"
                                class="w-full bg-transparent border-none focus:ring-0 text-white placeholder-gray-500 resize-none"
                                placeholder="What's on your mind?"></textarea>
                            <div class="flex items-center justify-between mt-4 pt-4 border-t border-white/10">
                                <div class="flex gap-4 text-blue-400">
                                    <button type="button" class="hover:text-blue-300"><i class="fas fa-image"></i></button>
                                    <button type="button" class="hover:text-blue-300"><i class="fas fa-video"></i></button>
                                    <button type="button" class="hover:text-blue-300"><i class="fas fa-music"></i></button>
                                </div>
                                <div class="flex items-center gap-4">
                                    <select name="visibility"
                                        class="bg-black/20 border border-white/10 rounded-lg text-xs px-2 py-1 text-gray-300">
                                        <option value="public">Public</option>
                                        <option value="friends">Followers</option>
                                        <option value="private">Private</option>
                                    </select>
                                    <button type="submit"
                                        class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-1.5 rounded-full text-sm font-bold transition">Post</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Posts -->
            <div class="space-y-6">
                @forelse($posts as $post)
                    <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $post->user->avatar_url ?? 'https://via.placeholder.com/40' }}"
                                    class="w-10 h-10 rounded-full">
                                <div>
                                    <h4 class="font-bold text-sm">{{ $post->user->name }}</h4>
                                    <p class="text-xs text-gray-500">{{ $post->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            @if(auth()->id() === $post->user_id)
                                <button class="text-gray-500 hover:text-red-500"><i class="fas fa-trash"></i></button>
                            @endif
                        </div>

                        <p class="text-gray-200 mb-4">{{ $post->content }}</p>

                        @if($post->media_url)
                            <div class="mb-4 rounded-xl overflow-hidden bg-black/50">
                                @if($post->media_type === 'image')
                                    <img src="{{ Storage::url($post->media_url) }}" class="w-full h-auto">
                                @elseif($post->media_type === 'video')
                                    <video src="{{ Storage::url($post->media_url) }}" controls class="w-full"></video>
                                @endif
                            </div>
                        @endif

                        <div class="flex items-center gap-6 text-gray-400 text-sm border-t border-white/10 pt-4">
                            <button class="flex items-center gap-2 hover:text-red-500 transition">
                                <i class="far fa-heart"></i> {{ $post->reactions_count ?? 0 }}
                            </button>
                            <button class="flex items-center gap-2 hover:text-blue-400 transition">
                                <i class="far fa-comment"></i> {{ $post->comments_count ?? 0 }}
                            </button>
                            <button class="flex items-center gap-2 hover:text-white transition ml-auto">
                                <i class="fas fa-share"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-users text-4xl mb-4"></i>
                        <p>No posts yet. Follow people to see their updates!</p>
                    </div>
                @endforelse

                {{ $posts->links() }}
            </div>
        </div>

        <!-- Sidebar -->
        <div class="hidden lg:block w-1/3 space-y-8">
            <!-- Suggested Users -->
            <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                <h3 class="font-bold mb-4">Suggested for You</h3>
                <div class="space-y-4">
                    @foreach($suggestedUsers as $user)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img src="{{ $user->avatar_url ?? 'https://via.placeholder.com/40' }}"
                                    class="w-10 h-10 rounded-full">
                                <div>
                                    <p class="font-bold text-sm">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $user->followers_count }} followers</p>
                                </div>
                            </div>
                            <button onclick="followUser({{ $user->id }})"
                                class="text-blue-400 text-sm font-bold hover:text-blue-300">Follow</button>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Trending Tracks -->
            <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                <h3 class="font-bold mb-4">Trending This Week</h3>
                <div class="space-y-3">
                    @foreach($trendingTracks as $track)
                        <div class="flex items-center gap-3 group cursor-pointer hover:bg-white/5 p-2 rounded-lg transition">
                            <img src="{{ $track->cover_image }}" class="w-10 h-10 rounded object-cover">
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-sm truncate">{{ $track->title }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ $track->user->name }}</p>
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

    @push('scripts')
        <script>
            function followUser(id) {
                fetch(`/users/${id}/follow`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) location.reload();
                    });
            }
        </script>
    @endpush
@endsection