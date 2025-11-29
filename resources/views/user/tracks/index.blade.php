<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Tracks') }}
            </h2>
            <a href="{{ route('user.tracks.create') }}"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Upload New Track
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                            role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

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
                                    <div
                                        class="absolute bottom-2 right-2 bg-black bg-opacity-50 text-white px-2 py-1 rounded text-xs">
                                        {{ gmdate("i:s", $track->duration) }}
                                    </div>
                                </div>
                                <h3 class="font-bold text-lg truncate"><a href="{{ route('user.tracks.show', $track) }}"
                                        class="hover:text-blue-500">{{ $track->title }}</a></h3>
                        {{ $tracks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>