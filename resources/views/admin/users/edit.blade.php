@extends('layouts.admin')

@section('page-title', 'Edit User: ' . $user->name)

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Basic Info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-gray-700">
                <h3 class="text-xl font-bold text-white mb-4">User Information</h3>
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-gray-400 text-sm font-bold mb-2">Name</label>
                            <input type="text" name="name" value="{{ $user->name }}"
                                class="w-full bg-gray-700 text-white border border-gray-600 rounded py-2 px-3 focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-400 text-sm font-bold mb-2">Email</label>
                            <input type="email" name="email" value="{{ $user->email }}"
                                class="w-full bg-gray-700 text-white border border-gray-600 rounded py-2 px-3 focus:outline-none focus:border-blue-500">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Role</label>
                        <select name="role"
                            class="w-full bg-gray-700 text-white border border-gray-600 rounded py-2 px-3 focus:outline-none focus:border-blue-500">
                            <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                            <option value="artist" {{ $user->role === 'artist' ? 'selected' : '' }}>Artist</option>
                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>

                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_verified" value="1" {{ $user->is_verified ? 'checked' : '' }}
                                class="form-checkbox h-5 w-5 text-blue-600 bg-gray-700 border-gray-600 rounded">
                            <span class="ml-2 text-gray-300">Verified User</span>
                        </label>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded transition duration-300">
                            Update Information
                        </button>
                    </div>
                </form>
            </div>

            <!-- Upload Limits -->
            <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-gray-700">
                <h3 class="text-xl font-bold text-white mb-4">Usage Limits</h3>
                <form action="{{ route('admin.users.update_limit', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
@extends('layouts.admin')

@section('page-title', 'Edit User: ' . $user->name)

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Basic Info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-gray-700">
                <h3 class="text-xl font-bold text-white mb-4">User Information</h3>
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-gray-400 text-sm font-bold mb-2">Name</label>
                            <input type="text" name="name" value="{{ $user->name }}"
                                class="w-full bg-gray-700 text-white border border-gray-600 rounded py-2 px-3 focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-400 text-sm font-bold mb-2">Email</label>
                            <input type="email" name="email" value="{{ $user->email }}"
                                class="w-full bg-gray-700 text-white border border-gray-600 rounded py-2 px-3 focus:outline-none focus:border-blue-500">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Role</label>
                        <select name="role"
                            class="w-full bg-gray-700 text-white border border-gray-600 rounded py-2 px-3 focus:outline-none focus:border-blue-500">
                            <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                            <option value="artist" {{ $user->role === 'artist' ? 'selected' : '' }}>Artist</option>
                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>

                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_verified" value="1" {{ $user->is_verified ? 'checked' : '' }}
                                class="form-checkbox h-5 w-5 text-blue-600 bg-gray-700 border-gray-600 rounded">
                            <span class="ml-2 text-gray-300">Verified User</span>
                        </label>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded transition duration-300">
                            Update Information
                        </button>
                    </div>
                </form>
            </div>

            <!-- Upload Limits -->
            <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-gray-700">
                <h3 class="text-xl font-bold text-white mb-4">Usage Limits</h3>
                <form action="{{ route('admin.users.update_limit', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Max Uploads (Songs)</label>
                        <input type="number" name="max_uploads" value="{{ $user->max_uploads }}"
                            class="w-full bg-gray-700 text-white border border-gray-600 rounded py-2 px-3 focus:outline-none focus:border-blue-500">
                        <p class="text-gray-500 text-xs mt-1">Current Usage: {{ $user->tracks()->count() }} songs</p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded transition duration-300">
                            Update Limits
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar Actions -->
        <div class="space-y-6">
            <!-- Growth Tools -->
            <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-gray-700">
                <h3 class="text-xl font-bold text-white mb-4">Growth Tools</h3>
                <form action="{{ route('admin.users.addFollowers', $user) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Add Fake Followers</label>
                        <input type="number" name="count" placeholder="Number of followers" min="1" max="1000"
                            class="w-full bg-gray-700 text-white border border-gray-600 rounded py-2 px-3 focus:outline-none focus:border-blue-500">
                    </div>
                    <button type="submit"
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                        Add Followers
                    </button>
                </form>
            </div>

            <!-- Ban Management -->
            <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-gray-700">
                <h3 class="text-xl font-bold text-white mb-4">Account Status</h3>

                @if($user->is_banned)
                    <div class="bg-red-900 text-red-200 p-4 rounded mb-4">
                        <strong>Banned</strong>
                        @if($user->banned_until)
                            <br>Until: {{ $user->banned_until->format('M d, Y H:i') }}
                        @else
                            <br>Indefinitely
                        @endif
                    </div>
                    <form action="{{ route('admin.users.unban', $user) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                            Unban User
                        </button>
                    </form>
                @else
                    <div class="bg-green-900 text-green-200 p-4 rounded mb-4">
                        <strong>Active</strong>
                    </div>
                    <form action="{{ route('admin.users.ban', $user) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-gray-400 text-sm font-bold mb-2">Ban Duration (Days)</label>
                            <input type="number" name="days" placeholder="Leave empty for indefinite"
                                class="w-full bg-gray-700 text-white border border-gray-600 rounded py-2 px-3 focus:outline-none focus:border-red-500">
                        </div>
                        <button type="submit"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                            Ban User
                        </button>
                    </form>
                @endif
            </div>

            <!-- Delete Account -->
            <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-gray-700">
                <h3 class="text-xl font-bold text-red-500 mb-4">Danger Zone</h3>
                <p class="text-gray-400 text-sm mb-4">Once you delete a user, there is no going back. Please be certain.</p>
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this user?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="w-full border border-red-600 text-red-500 hover:bg-red-600 hover:text-white font-bold py-2 px-4 rounded transition duration-300">
                        Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection