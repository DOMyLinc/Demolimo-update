@extends('layouts.app')

@section('title', 'Followers')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold">Followers</h1>
            <div class="flex gap-2">
                <a href="{{ route('social.followers') }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-full font-bold">Followers</a>
                <a href="{{ route('social.following') }}"
                    class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-full font-bold transition">Following</a>
            </div>
        </div>

        @if($followers->isEmpty())
            <div class="text-center py-20 bg-white/5 rounded-2xl border border-white/10">
                <i class="fas fa-users text-4xl text-gray-500 mb-4"></i>
                <h3 class="text-xl font-bold mb-2">No followers yet</h3>
                <p class="text-gray-400">Share your profile to get more followers!</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($followers as $user)
                    <div class="bg-white/5 rounded-2xl p-6 border border-white/10 flex items-center gap-4">
                        <img src="{{ $user->avatar_url ?? 'https://via.placeholder.com/60' }}"
                            class="w-16 h-16 rounded-full object-cover">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-lg truncate">{{ $user->name }}</h4>
                            <div class="flex gap-4 text-sm text-gray-400 mt-1">
                                <span>{{ $user->tracks_count }} tracks</span>
                                <span>{{ $user->followers_count }} followers</span>
                            </div>
                        </div>
                        @if(auth()->user()->isFollowing($user))
                            <button onclick="unfollowUser({{ $user->id }})"
                                class="px-4 py-1.5 border border-white/20 rounded-full text-sm font-bold hover:bg-white/10 transition">Following</button>
                        @else
                            <button onclick="followUser({{ $user->id }})"
                                class="px-4 py-1.5 bg-blue-600 rounded-full text-sm font-bold hover:bg-blue-500 transition">Follow</button>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="mt-8">
                {{ $followers->links() }}
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            function followUser(id) {
                fetch(`/users/${id}/follow`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
            }

            function unfollowUser(id) {
                fetch(`/users/${id}/follow`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
            }
        </script>
    @endpush
@endsection