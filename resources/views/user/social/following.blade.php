@extends('layouts.app')

@section('title', 'Following')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold">Following</h1>
            <div class="flex gap-2">
                <a href="{{ route('social.followers') }}"
                    class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-full font-bold transition">Followers</a>
                <a href="{{ route('social.following') }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-full font-bold">Following</a>
            </div>
        </div>

        @if($following->isEmpty())
            <div class="text-center py-20 bg-white/5 rounded-2xl border border-white/10">
                <i class="fas fa-user-plus text-4xl text-gray-500 mb-4"></i>
                <h3 class="text-xl font-bold mb-2">Not following anyone</h3>
                <p class="text-gray-400 mb-6">Find artists and friends to follow to see their activity.</p>
                <a href="{{ route('discovery.index') }}"
                    class="px-6 py-2 bg-white text-black rounded-full font-bold hover:bg-gray-200 transition">Find People</a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($following as $user)
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
                        <button onclick="unfollowUser({{ $user->id }})"
                            class="px-4 py-1.5 border border-white/20 rounded-full text-sm font-bold hover:bg-white/10 transition">Following</button>
                    </div>
                @endforeach
            </div>
            <div class="mt-8">
                {{ $following->links() }}
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            function unfollowUser(id) {
                if (confirm('Are you sure you want to unfollow this user?')) {
                    fetch(`/users/${id}/follow`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                    }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
                }
            }
        </script>
    @endpush
@endsection