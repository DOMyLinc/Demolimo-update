@extends('layouts.admin')

@section('page-title', 'Edit Admin')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-white">Edit Administrator</h2>
            <a href="{{ route('admin.admins.index') }}" class="text-gray-400 hover:text-white transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>

        <form action="{{ route('admin.admins.update', $admin->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- User Details -->
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $admin->name) }}" required
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 outline-none transition">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $admin->email) }}" required
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 outline-none transition">
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Password (Leave blank to keep)</label>
                        <input type="password" name="password"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 outline-none transition">
                        @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Confirm Password</label>
                        <input type="password" name="password_confirmation"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 outline-none transition">
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            @if($admin->id !== 1)
            <div class="pt-6 border-t border-white/10">
                <h3 class="text-lg font-bold text-white mb-4">Assign Permissions</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @php
                        $permissions = [
                            'manage_users' => ['Manage Users', 'View, edit, ban, and verify users'],
                            'manage_content' => ['Manage Content', 'Manage tracks, albums, and playlists'],
                            'manage_finance' => ['Manage Finance', 'View sales, process withdrawals'],
                            'manage_settings' => ['System Settings', 'Configure site settings and features'],
                            'manage_admins' => ['Manage Admins', 'Create and manage other administrators'],
                        ];
                    @endphp

                    @foreach($permissions as $key => $label)
                    <label class="flex items-start gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                        <input type="checkbox" name="permissions[]" value="{{ $key }}" 
                            {{ in_array($key, $admin->permissions ?? []) ? 'checked' : '' }}
                            class="w-5 h-5 rounded mt-1 text-red-500 focus:ring-red-500 bg-gray-700 border-gray-600">
                        <div>
                            <div class="font-medium text-white">{{ $label[0] }}</div>
                            <div class="text-xs text-gray-400">{{ $label[1] }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            @else
            <div class="pt-6 border-t border-white/10">
                <div class="p-4 bg-blue-500/10 border border-blue-500/20 rounded-lg text-blue-400 text-sm">
                    <i class="fas fa-info-circle mr-2"></i> Super Admin has all permissions by default and cannot be restricted.
                </div>
            </div>
            @endif

            <div class="flex justify-end pt-6">
                <button type="submit" class="btn btn-primary px-8">
                    Update Admin
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
