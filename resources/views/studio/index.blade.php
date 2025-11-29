@extends('layouts.app')

@section('title', 'Studio Dashboard')

@section('content')
    <div class="container mx-auto px-4 py-8">

        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white">Studio Dashboard</h1>
                <p class="text-gray-400">Manage your music and analyze your performance.</p>
            </div>
            <div class="flex gap-4">
                <a href="{{ route('user.albums.create') }}" class="btn btn-outline">
                    <i class="fas fa-compact-disc mr-2"></i> New Album
                </a>
                <a href="{{ route('user.tracks.create') }}" class="btn btn-primary">
                    <i class="fas fa-cloud-upload-alt mr-2"></i> Upload Track
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Plays -->
            <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-gray-400 text-sm font-medium">Total Plays</p>
                        <h3 class="text-3xl font-bold text-white mt-1">{{ number_format($stats['total_plays']) }}</h3>
                    </div>
                    <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center text-blue-400">
                        <i class="fas fa-play"></i>
                    </div>
                </div>
                <div class="flex items-center text-sm text-green-400">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span>12% from last month</span>
                </div>
            </div>

            <!-- Total Likes -->
            <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-gray-400 text-sm font-medium">Total Likes</p>
                        <h3 class="text-3xl font-bold text-white mt-1">{{ number_format($stats['total_likes']) }}</h3>
                    </div>
                    <div class="w-10 h-10 bg-red-500/20 rounded-lg flex items-center justify-center text-red-400">
                        <i class="fas fa-heart"></i>
                    </div>
                </div>
                <div class="flex items-center text-sm text-green-400">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span>5% from last month</span>
                </div>
            </div>

            <!-- Followers -->
            <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-gray-400 text-sm font-medium">Followers</p>
                        <h3 class="text-3xl font-bold text-white mt-1">{{ number_format($stats['followers']) }}</h3>
                    </div>
                    <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center text-purple-400">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="flex items-center text-sm text-green-400">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span>24 new this week</span>
                </div>
            </div>

            <!-- Revenue -->
            <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-gray-400 text-sm font-medium">Estimated Revenue</p>
                        <h3 class="text-3xl font-bold text-white mt-1">${{ number_format($stats['revenue'], 2) }}</h3>
                    </div>
                    <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center text-green-400">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
                <div class="flex items-center text-sm text-gray-400">
                    <span>Available for withdrawal</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Recent Tracks -->
            <div class="lg:col-span-2 bg-white/5 rounded-2xl border border-white/10 overflow-hidden">
                <div class="p-6 border-b border-white/10 flex justify-between items-center">
                    <h3 class="font-bold text-lg">Recent Tracks</h3>
                    <a href="{{ route('user.tracks.index') }}" class="text-sm text-blue-400 hover:text-blue-300">View
                        All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-white/5 text-gray-400 text-xs uppercase">
                            <tr>
                                <th class="px-6 py-3">Track</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Plays</th>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($recentTracks as $track)
                                <tr class="hover:bg-white/5 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <img src="{{ $track->cover_image ?? 'https://via.placeholder.com/40' }}"
                                                class="w-10 h-10 rounded object-cover mr-3">
                                            <div>
                                                <p class="font-bold text-sm text-white">{{ $track->title }}</p>
                                                <p class="text-xs text-gray-400">{{ $track->genre }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="px-2 py-1 text-xs rounded-full {{ $track->is_public ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400' }}">
                                            {{ $track->is_public ? 'Public' : 'Private' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-300">
                                        {{ number_format($track->plays) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-400">
                                        {{ $track->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2">
                                            <a href="{{ route('user.tracks.edit', $track) }}"
                                                class="text-gray-400 hover:text-white">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="text-gray-400 hover:text-red-500">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                                        No tracks uploaded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions & Albums -->
            <div class="space-y-8">

                <!-- Albums -->
                <div class="bg-white/5 rounded-2xl border border-white/10 overflow-hidden">
                    <div class="p-6 border-b border-white/10 flex justify-between items-center">
                        <h3 class="font-bold text-lg">Recent Albums</h3>
                        <a href="{{ route('user.albums.index') }}" class="text-sm text-blue-400 hover:text-blue-300">View
                            All</a>
                    </div>
                    <div class="p-4 space-y-4">
                        @forelse($recentAlbums as $album)
                            <div class="flex items-center gap-4 p-2 rounded-lg hover:bg-white/5 transition">
                                <img src="{{ $album->cover_image ?? 'https://via.placeholder.com/50' }}"
                                    class="w-12 h-12 rounded object-cover">
                                <div class="flex-1">
                                    <h4 class="font-bold text-sm text-white">{{ $album->title }}</h4>
                                    <p class="text-xs text-gray-400">{{ $album->tracks_count }} tracks</p>
                                </div>
                                <a href="{{ route('user.albums.show', $album) }}" class="text-gray-400 hover:text-white">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        @empty
                            <div class="text-center py-4 text-gray-400 text-sm">
                                No albums created yet.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Monetization Card -->
                <div
                    class="bg-gradient-to-br from-green-900/50 to-emerald-900/50 rounded-2xl p-6 border border-green-500/20">
                    <h3 class="font-bold text-lg text-white mb-2">Monetization</h3>
                    <p class="text-sm text-gray-300 mb-4">You have earned ${{ number_format($stats['revenue'], 2) }} so far.
                    </p>
                    <button class="w-full bg-green-600 hover:bg-green-500 text-white font-bold py-2 rounded-lg transition">
                        Withdraw Funds
                    </button>
                </div>

            </div>
        </div>

    </div>
@endsection