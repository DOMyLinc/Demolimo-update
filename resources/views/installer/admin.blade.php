@extends('installer.layout', ['step' => 3])

@section('title', 'Admin Setup - DemoLimo Installer')

@section('content')
<div class="flex-1">
    <h2 class="text-2xl font-bold text-white mb-6">Create Admin Account</h2>

    <form action="{{ route('installer.admin.post') }}" method="POST" class="space-y-5">
        @csrf
        <div>
            <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Full Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required placeholder="John Doe"
                class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none transition">
            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}" required placeholder="admin@example.com"
                class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none transition">
            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            @extends('installer.layout', ['step' => 3])

            @section('title', 'Admin Setup - DemoLimo Installer')

            @section('content')
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-white mb-6">Create Admin Account</h2>

                    <form action="{{ route('installer.admin.post') }}" method="POST" class="space-y-5">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Full
                                Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="John Doe"
                                class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none transition">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Email
                                Address</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                placeholder="admin@example.com"
                                class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none transition">
                            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label
                                class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Password</label>
                            <input type="password" name="password" required placeholder="••••••••"
                                class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none transition">
                            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Confirm
                                Password</label>
                            <input type="password" name="password_confirmation" required placeholder="••••••••"
                                class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none transition">
                        </div>

                        <!-- Footer Actions -->
                        <div class="mt-8 pt-6 border-t border-white/10 flex justify-between items-center">
                            <a href="{{ route('installer.database') }}"
                                class="px-6 py-3 bg-white/5 hover:bg-white/10 text-white font-bold rounded-lg transition flex items-center">
                                <i class="fas fa-arrow-left mr-2"></i> Back
                            </a>
                            <button type="submit"
                                class="px-8 py-3 lava-gradient hover:brightness-110 text-white font-bold rounded-lg shadow-lg shadow-red-900/30 transition transform hover:-translate-y-0.5 flex items-center">
                                {{ __('installer.continue_features') }} <i class="fas fa-arrow-right ml-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            @endsection