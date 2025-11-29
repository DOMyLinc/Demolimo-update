@extends('layouts.admin')

@section('page-title', 'Add New Admin')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="card">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-white">Create Administrator</h2>
                <a href="{{ route('admin.admins.index') }}" class="text-gray-400 hover:text-white transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
            </div>

            <form action="{{ route('admin.admins.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- User Details -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Full
                            Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 outline-none transition">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Email
                            Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 outline-none transition">
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Password</label>
                            <input type="password" name="password" required
                                class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 outline-none transition">
                            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Confirm
                                Password</label>
                            <input type="password" name="password_confirmation" required
                                class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 outline-none transition">
                        </div>
                    </div>
                </div>

                <!-- Permissions -->
                <div class="pt-6 border-t border-white/10">
                    <h3 class="text-lg font-bold text-white mb-4">Assign Permissions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label
                            class="flex items-start gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                            <input type="checkbox" name="permissions[]" value="manage_users"
                                class="w-5 h-5 rounded mt-1 text-red-500 focus:ring-red-500 bg-gray-700 border-gray-600">
                            <div>
                                <div class="font-medium text-white">Manage Users</div>
                                <div class="text-xs text-gray-400">View, edit, ban, and verify users</div>
                            </div>
                        </label>

                        <label
                            class="flex items-start gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                            <input type="checkbox" name="permissions[]" value="manage_content"
                                class="w-5 h-5 rounded mt-1 text-red-500 focus:ring-red-500 bg-gray-700 border-gray-600">
                            <div>
                                <div class="font-medium text-white">Manage Content</div>
                                <div class="text-xs text-gray-400">Manage tracks, albums, and playlists</div>
                            </div>
                        </label>

                        <label
                            class="flex items-start gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                            <input type="checkbox" name="permissions[]" value="manage_finance"
                                class="w-5 h-5 rounded mt-1 text-red-500 focus:ring-red-500 bg-gray-700 border-gray-600">
                            <div>
                                <div class="font-medium text-white">Manage Finance</div>
                                <div class="text-xs text-gray-400">View sales, process withdrawals</div>
                            </div>
                        </label>

                        <label
                            class="flex items-start gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                            <input type="checkbox" name="permissions[]" value="manage_settings"
                                class="w-5 h-5 rounded mt-1 text-red-500 focus:ring-red-500 bg-gray-700 border-gray-600">
                            <div>
                                <div class="font-medium text-white">System Settings</div>
                                <div class="text-xs text-gray-400">Configure site settings and features</div>
                            </div>
                        </label>

                        <label
                            class="flex items-start gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                            <input type="checkbox" name="permissions[]" value="manage_admins"
                                class="w-5 h-5 rounded mt-1 text-red-500 focus:ring-red-500 bg-gray-700 border-gray-600">
                            <div>
                                <div class="font-medium text-white">Manage Admins</div>
                                <div class="text-xs text-gray-400">Create and manage other administrators</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end pt-6">
                    <button type="submit" class="btn btn-primary px-8">
                        Create Admin
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection