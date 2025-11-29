@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-[#0f0f13] text-white p-8 pt-24 flex items-center justify-center">
        <div class="w-full max-w-2xl">
            <div class="mb-8">
                <a href="{{ route('user.boost.index') }}"
                    class="text-gray-400 hover:text-white transition mb-4 inline-block">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                </a>
                <h1 class="text-3xl font-black tracking-tight">Create New <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-red-600">Advertisement</span>
                </h1>
                <p class="text-gray-400">Promote your external links or content across the platform.</p>
            </div>

            <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-8 shadow-2xl">
                <form action="{{ route('user.boost.store-ad') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-bold text-gray-300 mb-2">Ad Title</label>
                        <input type="text" name="title" required
                            class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none transition"
                            placeholder="Check out my new merch!">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-300 mb-2">Ad Image (Banner)</label>
                        <div
                            class="relative border-2 border-dashed border-white/10 rounded-xl p-8 text-center hover:border-orange-500/50 transition cursor-pointer group">
                            <input type="file" name="image" required accept="image/*"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <i
                                class="fas fa-cloud-upload-alt text-3xl text-gray-500 group-hover:text-orange-400 mb-2 transition"></i>
                            <p class="text-sm text-gray-400 group-hover:text-white transition">Click to upload or drag and
                                drop</p>
                            <p class="text-xs text-gray-600 mt-1">Recommended size: 1200x400px</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-300 mb-2">Target URL</label>
                        <div class="relative">
                            <i class="fas fa-link absolute left-4 top-3.5 text-gray-500"></i>
                            <input type="url" name="target_url" required
                                class="w-full bg-black/40 border border-white/10 rounded-xl pl-10 pr-4 py-3 text-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none transition"
                                placeholder="https://myshop.com">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-300 mb-2">Budget ($)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-gray-500">$</span>
                            <input type="number" name="budget" required min="5" step="0.01"
                                class="w-full bg-black/40 border border-white/10 rounded-xl pl-8 pr-4 py-3 text-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500 outline-none transition"
                                placeholder="50.00">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Minimum budget: $5.00</p>
                    </div>

                    <button type="submit"
                        class="w-full py-4 bg-gradient-to-r from-orange-500 to-red-600 hover:brightness-110 text-white font-bold rounded-xl shadow-lg shadow-orange-900/30 transition transform hover:-translate-y-0.5 text-lg">
                        Submit Advertisement
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection