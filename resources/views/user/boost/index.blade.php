@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-[#0f0f13] text-white p-8 pt-24">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6">
                <div>
                    <h1 class="text-4xl font-black mb-2 tracking-tight">
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-red-600">Promote</span>
                        Your Music
                    </h1>
                    <p class="text-gray-400">Reach more listeners with Ads and Boosts</p>
                </div>
                <div class="flex gap-4">
                    <a href="{{ route('user.boost.create-ad') }}"
                        class="px-6 py-3 bg-orange-500/10 hover:bg-orange-500/20 border border-orange-500/50 text-orange-400 font-bold rounded-xl transition flex items-center gap-2">
                        <i class="fas fa-ad"></i> Create Ad
                    </a>
                    <a href="{{ route('user.boost.create-boost') }}"
                        class="px-6 py-3 bg-gradient-to-r from-orange-500 to-red-600 hover:brightness-110 text-white font-bold rounded-xl shadow-lg shadow-orange-900/30 transition transform hover:-translate-y-0.5 flex items-center gap-2">
                        <i class="fas fa-rocket"></i> Boost Track
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <div class="bg-orange-500/10 backdrop-blur-md border border-orange-500/20 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-orange-200 font-bold">Total Spent</h3>
                        <i class="fas fa-wallet text-orange-500 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-black text-white">
                        ${{ number_format($ads->sum('budget') + $boosts->sum('budget'), 2) }}</p>
                </div>
                <div class="bg-red-500/10 backdrop-blur-md border border-red-500/20 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-red-200 font-bold">Active Campaigns</h3>
                        <i class="fas fa-chart-line text-red-500 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-black text-white">
                        {{ $ads->where('status', 'active')->count() + $boosts->where('status', 'active')->count() }}</p>
                </div>
                <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-gray-200 font-bold">Pending Review</h3>
                        <i class="fas fa-clock text-yellow-500 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-black text-white">
                        {{ $ads->where('status', 'pending')->count() + $boosts->where('status', 'pending')->count() }}
                    </p>
                </div>
            </div>

            <!-- Active Campaigns -->
            <div class="space-y-8">
                <!-- Ads Section -->
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl overflow-hidden">
                    <div class="p-6 border-b border-white/10 flex justify-between items-center">
                        <h2 class="text-xl font-bold">Your Advertisements</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-white/5 text-gray-400 uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-4">Ad Details</th>
                                    <th class="px-6 py-4">Budget</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @forelse($ads as $ad)
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
                                                            <td class="px-6 py-4 text-white font-mono">${{ number_format($ad->budget, 2) }}</td>
                                                            <td class="px-6 py-4">
                                                                <span
                                                                    class="px-3 py-1 rounded-full text-xs font-bold 
                                                                        {{ $ad->status === 'active' ? 'bg-green-500/20 text-green-400' :
                                    ($ad->status === 'pending' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400') }}">
                                                                    {{ ucfirst($ad->status) }}
                                                                </span>
                                                            </td>
                                                            <td class="px-6 py-4 text-gray-400 text-sm">{{ $ad->created_at->format('M d, Y') }}</td>
                                                        </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                            No advertisements found. Create one to get started!
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Boosts Section -->
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl overflow-hidden">
                    <div class="p-6 border-b border-white/10 flex justify-between items-center">
                        <h2 class="text-xl font-bold">Track Boosts</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-white/5 text-gray-400 uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-4">Track</th>
                                    <th class="px-6 py-4">Target Views</th>
                                    <th class="px-6 py-4">Progress</th>
                                    <th class="px-6 py-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @forelse($boosts as $boost)
                                                        <tr class="hover:bg-white/5 transition">
                                                            <td class="px-6 py-4">
                                                                <div class="flex items-center gap-3">
                                                                    <img src="{{ $boost->track->cover_image ?? 'https://via.placeholder.com/40' }}"
                                                                        class="w-10 h-10 rounded object-cover">
                                                                    <span
                                                                        class="font-bold text-white">{{ $boost->track->title ?? 'Unknown Track' }}</span>
                                                                </div>
                                                            </td>
                                                            <td class="px-6 py-4 text-white">{{ number_format($boost->target_views) }}</td>
                                                            <td class="px-6 py-4">
                                                                <div class="w-full bg-gray-700 rounded-full h-2 max-w-[100px]">
                                                                    <div class="bg-orange-500 h-2 rounded-full"
                                                                        style="width: {{ min(100, ($boost->current_views / $boost->target_views) * 100) }}%">
                                                                    </div>
                                                                </div>
                                                                <span
                                                                    class="text-xs text-gray-400 mt-1 block">{{ number_format($boost->current_views) }}
                                                                    / {{ number_format($boost->target_views) }}</span>
                                                            </td>
                                                            <td class="px-6 py-4">
                                                                <span
                                                                    class="px-3 py-1 rounded-full text-xs font-bold 
                                                                        {{ $boost->status === 'active' ? 'bg-green-500/20 text-green-400' :
                                    ($boost->status === 'pending' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400') }}">
                                                                    {{ ucfirst($boost->status) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                            No track boosts found. Boost a track to get more views!
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection