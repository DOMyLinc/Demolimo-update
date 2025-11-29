@extends('layouts.admin')

@section('page-title', 'User Management')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Users</div>
        <div class="stat-value">{{ number_format($stats['total_users']) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Verified Users</div>
        <div class="stat-value">{{ number_format($stats['verified_users']) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Artists</div>
        <div class="stat-value">{{ number_format($stats['artist_users']) }}</div>
    </div>
    @extends('layouts.admin')

    @section('page-title', 'User Management')

    @section('content')
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Users</div>
                <div class="stat-value">{{ number_format($stats['total_users']) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Verified Users</div>
                <div class="stat-value">{{ number_format($stats['verified_users']) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Artists</div>
                <div class="stat-value">{{ number_format($stats['artist_users']) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Banned Users</div>
                <div class="stat-value">{{ number_format($stats['banned_users']) }}</div>
            </div>
        </div>

        <div class="card">
            <form action="{{ route('admin.users.bulk') }}" method="POST" id="bulkForm">
                @csrf
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2 class="card-title">All Users</h2>
                    <div class="flex gap-3">
                        <div class="flex gap-2">
                            <select name="action"
                                class="bg-white/10 border border-white/20 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-red-500">
                                <option value="" class="text-gray-900">Bulk Actions</option>
                                <option value="delete" class="text-gray-900">Delete Selected</option>
                                <option value="ban" class="text-gray-900">Ban Selected</option>
                                <option value="verify" class="text-gray-900">Verify Selected</option>
                            </select>
                            <button type="submit" onclick="return confirm('Are you sure you want to apply this action?')"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-bold transition">
                                Apply
                            </button>
                        </div>
                        <input type="text" placeholder="Search users..."
                            style="padding: 0.5rem 1rem; border-radius: 8px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #fff;">
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th class="w-10">
                                    <input type="checkbox" onclick="toggleAll(this)"
                                        class="rounded bg-white/10 border-white/20 text-red-600 focus:ring-red-500">
                                </th>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="ids[]" value="{{ $user->id }}"
                                            class="user-checkbox rounded bg-white/10 border-white/20 text-red-600 focus:ring-red-500">
                                    </td>
                                    <td>#{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge badge-{{ $user->role === 'admin' ? 'danger' : 'success' }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($user->is_banned)
                                            <span class="badge badge-danger">Banned</span>
                                        @elseif($user->is_verified)
                                            <span class="badge badge-success">Verified</span>
                                        @else
                                            <span class="badge badge-warning">Unverified</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-primary btn-sm">View</a>
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-success btn-sm">Edit</a>
                                        @if($user->is_banned)
                                            <button type="button"
                                                onclick="document.getElementById('unban-{{ $user->id }}').submit()"
                                                class="btn btn-success btn-sm">Unban</button>
                                        @else
                                            <button type="button" onclick="document.getElementById('ban-{{ $user->id }}').submit()"
                                                class="btn btn-danger btn-sm">Ban</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 2rem;">
                                        No users found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            <!-- Hidden forms for individual actions to prevent nesting -->
            @foreach($users as $user)
                @if($user->is_banned)
                    <form id="unban-{{ $user->id }}" action="{{ route('admin.users.unban', $user) }}" method="POST"
                        style="display: none;">
                        @csrf
                    </form>
                @else
                    <form id="ban-{{ $user->id }}" action="{{ route('admin.users.ban', $user) }}" method="POST"
                        style="display: none;">
                        @csrf
                    </form>
                @endif
            @endforeach

            <div style="margin-top: 1.5rem;">
                {{ $users->links() }}
            </div>
        </div>

        <script>
            function toggleAll(source) {
                checkboxes = document.getElementsByClassName('user-checkbox');
                for (var i = 0, n = checkboxes.length; i < n; i++) {
                    checkboxes[i].checked = source.checked;
                }
            }
        </script>
    @endsection