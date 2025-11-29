<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $user->name }}'s Profile
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Profile Header -->
                    <div class="flex items-center gap-6 mb-8">
                        <div class="w-24 h-24 bg-gray-200 rounded-full overflow-hidden">
                            @if($user->avatar)
                                <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}"
                                    class="w-full h-full object-cover">
                            @else
                                <div class="flex items-center justify-center h-full text-gray-400 text-2xl font-bold">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold">{{ $user->name }}</h1>
                            <div class="flex gap-4 text-gray-600 mt-2">
                                <span><strong>{{ $user->followers_count }}</strong> Followers</span>
                                <span><strong>{{ $user->following_count }}</strong> Following</span>
                                <span><strong>{{ $user->tracks_count }}</strong> Tracks</span>
                            </div>
                            @if(auth()->id() !== $user->id)
                                <button class="mt-4 bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700 transition">
                                    Follow
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Tracks List -->
                    <h3 class="text-xl font-bold mb-4">Tracks</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($tracks as $track)
                            <div class="border rounded-lg p-4 hover:shadow-lg transition">
                                <div class="relative h-48 bg-gray-200 rounded-md overflow-hidden mb-4">
                                    @if($track->image_path)
                                        <img src="{{ Storage::url($track->image_path) }}" alt="{{ $track->title }}"
                                            class="w-full h-full object-cover">
                                    @else
                                        <div class="flex items-center justify-center h-full text-gray-400">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3">
                                                </path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <h3 class="font-bold text-lg truncate"><a href="{{ route('user.tracks.show', $track) }}"
                                        class="hover:text-blue-500">{{ $track->title }}</a></h3>
                                <p class="text-sm text-gray-500 mb-2">{{ $track->created_at->diffForHumans() }}</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        {{ $tracks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>