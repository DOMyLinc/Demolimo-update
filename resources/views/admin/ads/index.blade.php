@extends('layouts.admin')

@section('title', 'Ads & Boosts Manager')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-black text-white mb-2 tracking-tight">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-red-600">Ads & Boosts</span>
                Manager
            </h1>
            <p class="text-gray-400">Manage user campaigns and promoted content</p>
        </div>

        <!-- Stats Cards (Glass Orange Style) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-orange-500/10 backdrop-blur-md border border-orange-500/20 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-orange-200 font-bold">Active Ads</h3>
                    <i class="fas fa-ad text-orange-500 text-2xl"></i>
                </div>
                <p class="text-3xl font-black text-white">{{ $ads->where('status', 'active')->count() }}</p>
            </div>
            <div class="bg-red-500/10 backdrop-blur-md border border-red-500/20 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-red-200 font-bold">Active Boosts</h3>
                    <i class="fas fa-rocket text-red-500 text-2xl"></i>
                </div>
                <p class="text-3xl font-black text-white">{{ $boosts->where('status', 'active')->count() }}</p>
            </div>
            <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-200 font-bold">Pending Review</h3>
                    <i class="fas fa-clock text-yellow-500 text-2xl"></i>
                </div>
                <p class="text-3xl font-black text-white">
                    {{ $ads->where('status', 'pending')->count() + $boosts->where('status', 'pending')->count() }}</p>
            </div>
        </div>

        <!-- Ads Table -->
        <div class="bg-black/40 backdrop-blur-xl border border-white/10 rounded-2xl overflow-hidden mb-8">
            <div class="p-6 border-b border-white/10">
                <h2 class="text-xl font-bold text-white">Advertisements</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-white/5 text-gray-400 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-4">Ad Details</th>
                            <th class="px-6 py-4">User</th>
                            <th class="px-6 py-4">Budget</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($ads as $ad)
                                        <tr class="hover:bg-white/5 transition">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-4">
                                                    <img src="{{ $ad->image_path }}"
                                                        class="w-16 h-16 object-cover rounded-lg border border-white/10">
                                                    <div>
                                                        <h4 class="font-bold text-white">{{ $ad->title }}</h4>
                                                        <a href="{{ $ad->target_url }}" target="_blank"
                                                            class="text-xs text-orange-400 hover:text-orange-300 truncate max-w-[150px] block">{{ $ad->target_url }}</a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-gray-300">{{ $ad->user->name }}</td>
                                            <td class="px-6 py-4 text-white font-mono">${{ number_format($ad->budget, 2) }}</td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="px-3 py-1 rounded-full text-xs font-bold 
                                                    {{ $ad->status === 'active' ? 'bg-green-500/20 text-green-400' :
                            ($ad->status === 'pending' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400') }}">
                                                    {{ ucfirst($ad->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($ad->status === 'pending')
                                                    <div class="flex gap-2">
                                                        <form action="{{ route('admin.ads.approve', $ad) }}" method="POST">
                                                            @csrf
                                                            <button
                                                                class="p-2 bg-green-500/20 text-green-400 rounded-lg hover:bg-green-500 hover:text-white transition"><i
                                                                    class="fas fa-check"></i></button>
                                                        </form>
                                                        <form action="{{ route('admin.ads.reject', $ad) }}" method="POST">
                                                            @csrf
                                                            <button
                                                                class="p-2 bg-red-500/20 text-red-400 rounded-lg hover:bg-red-500 hover:text-white transition"><i
                                                                    class="fas fa-times"></i></button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Boosts Table -->
        <div class="bg-black/40 backdrop-blur-xl border border-white/10 rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <h2 class="text-xl font-bold text-white">Track Boosts</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-white/5 text-gray-400 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-4">Track</th>
                            <th class="px-6 py-4">User</th>
                            <th class="px-6 py-4">Target Views</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($boosts as $boost)
                                        <tr class="hover:bg-white/5 transition">
                                            <td class="px-6 py-4 text-white font-bold">{{ $boost->track->title ?? 'Unknown Track' }}</td>
                                            <td class="px-6 py-4 text-gray-300">{{ $boost->user->name }}</td>
                                            <td class="px-6 py-4 text-white">{{ number_format($boost->target_views) }}</td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="px-3 py-1 rounded-full text-xs font-bold 
                                                    {{ $boost->status === 'active' ? 'bg-green-500/20 text-green-400' :
                            ($boost->status === 'pending' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400') }}">
                                                    {{ ucfirst($boost->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($boost->status === 'pending')
                                                    <form action="{{ route('admin.boosts.approve', $boost) }}" method="POST">
                                                        @csrf
                                                        <button
                                                            class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-bold transition shadow-lg shadow-orange-900/20">
                                                            Approve Boost
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection