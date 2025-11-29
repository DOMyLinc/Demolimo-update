@extends('layouts.app')

@section('title', 'My Portfolio')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold mb-2">My Portfolio</h1>
                <p class="text-gray-400">Manage your music rights and investments.</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-400">Total Value</p>
                <p class="text-3xl font-bold text-green-400">${{ number_format($portfolioValue, 2) }}</p>
                <p class="text-xs {{ $portfolioValue >= $totalInvested ? 'text-green-500' : 'text-red-500' }}">
                    {{ $portfolioValue >= $totalInvested ? '+' : '' }}{{ number_format($portfolioValue - $totalInvested, 2) }}
                    ({{ number_format($totalInvested > 0 ? (($portfolioValue - $totalInvested) / $totalInvested) * 100 : 0, 1) }}%)
                </p>
            </div>
        </div>

        @if($investments->isEmpty())
            <div class="text-center py-20 bg-white/5 rounded-2xl border border-white/10">
                <i class="fas fa-chart-line text-4xl text-gray-500 mb-4"></i>
                <h3 class="text-xl font-bold mb-2">No investments yet</h3>
                <p class="text-gray-400 mb-6">Start investing in tracks to earn royalties and trade rights.</p>
                <a href="{{ route('discovery.trending') }}"
                    class="px-6 py-2 bg-blue-600 text-white rounded-full font-bold hover:bg-blue-500 transition">Explore
                    Tracks</a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Investment List -->
                <div class="lg:col-span-2 space-y-4">
                    @foreach($investments as $investment)
                        <div
                            class="bg-white/5 rounded-xl p-4 border border-white/10 flex items-center gap-4 hover:bg-white/10 transition">
                            <img src="{{ $investment->track->cover_image }}" class="w-16 h-16 rounded-lg object-cover">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold truncate">{{ $investment->track->title }}</h3>
                                <p class="text-sm text-gray-400">{{ $investment->track->user->name }}</p>
                                <div class="flex gap-4 mt-1 text-xs">
                                    <span class="text-gray-500">Invested:
                                        ${{ number_format($investment->invested_amount, 2) }}</span>
                                    <span class="text-gray-500">Shares: {{ number_format($investment->shares, 4) }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-lg">${{ number_format($investment->current_value, 2) }}</p>
                                <p class="text-xs {{ $investment->roi >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                    {{ $investment->roi >= 0 ? '+' : '' }}{{ number_format($investment->roi, 1) }}%
                                </p>
                                <form action="{{ route('blockchain.sell', $investment->id) }}" method="POST" class="mt-2">
                                    @csrf
                                    <button type="submit"
                                        class="text-xs bg-red-500/20 text-red-400 px-2 py-1 rounded hover:bg-red-500 hover:text-white transition">Sell</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Stats Sidebar -->
                <div class="space-y-6">
                    <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                        <h3 class="font-bold mb-4">Market Overview</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400">Active Investments</span>
                                <span class="font-bold">{{ $investments->count() }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400">Total Invested</span>
                                <span class="font-bold">${{ number_format($totalInvested, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400">Avg. ROI</span>
                                <span
                                    class="font-bold {{ $portfolioValue >= $totalInvested ? 'text-green-400' : 'text-red-400' }}">
                                    {{ number_format($totalInvested > 0 ? (($portfolioValue - $totalInvested) / $totalInvested) * 100 : 0, 1) }}%
                                </span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-gradient-to-br from-blue-900 to-purple-900 rounded-2xl p-6 border border-white/10 relative overflow-hidden">
                        <div class="relative z-10">
                            <h3 class="font-bold text-xl mb-2">Pro Tip</h3>
                            <p class="text-sm text-blue-100 mb-4">Invest in new artists early to maximize your returns as their
                                popularity grows.</p>
                            <a href="{{ route('discovery.new-releases') }}"
                                class="inline-block bg-white text-blue-900 px-4 py-2 rounded-lg font-bold text-sm hover:bg-blue-50 transition">Find
                                New Artists</a>
                        </div>
                        <i class="fas fa-rocket absolute -bottom-4 -right-4 text-9xl text-white/10 transform rotate-12"></i>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection