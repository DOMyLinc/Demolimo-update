@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-[#0f0f13] text-white p-8 pt-24 flex items-center justify-center">
        <div class="w-full max-w-2xl">
            <div class="mb-8">
                <a href="{{ route('user.boost.index') }}"
                    class="text-gray-400 hover:text-white transition mb-4 inline-block">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                </a>
                <h1 class="text-3xl font-black tracking-tight">Boost Your <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-red-600">Track</span></h1>
                <p class="text-gray-400">Get more plays and exposure for your music.</p>
            </div>

            <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-8 shadow-2xl">
                <form action="{{ route('user.boost.store-boost') }}" method="POST" class="space-y-6"
                    x-data="{ views: 1000, costPerView: 0.01 }">
                    @csrf

                    <div>
                        <label class="block text-sm font-bold text-gray-300 mb-2">Select Track</label>
                        <select name="track_id" required
                            class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none transition">
                            @foreach($tracks as $track)
                                <option value="{{ $track->id }}">{{ $track->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-300 mb-2">Target Views</label>
                        <div class="relative">
                            <i class="fas fa-eye absolute left-4 top-3.5 text-gray-500"></i>
                            <input type="number" name="target_views" x-model="views" required min="100" step="100"
                                class="w-full bg-black/40 border border-white/10 rounded-xl pl-10 pr-4 py-3 text-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none transition">
                        </div>
                    </div>

                    <div
                        class="p-4 bg-orange-500/10 border border-orange-500/20 rounded-xl flex justify-between items-center">
                        <div>
                            <p class="text-sm text-orange-200 font-bold">Estimated Cost</p>
                            <p class="text-xs text-orange-300/70">$0.01 per view</p>
                        </div>
                        <div class="text-2xl font-black text-white">
                            $<span x-text="(views * costPerView).toFixed(2)"></span>
                        </div>
                    </div>

                    <input type="hidden" name="budget" :value="(views * costPerView).toFixed(2)">

                    <button type="submit"
                        class="w-full py-4 bg-gradient-to-r from-orange-500 to-red-600 hover:brightness-110 text-white font-bold rounded-xl shadow-lg shadow-orange-900/30 transition transform hover:-translate-y-0.5 text-lg">
                        Launch Boost Campaign
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection