@extends('layouts.admin')

@section('page-title', 'Track Management')

@section('content')
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Tracks</div>
            <div class="stat-value">{{ number_format($stats['total_tracks']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Plays</div>
            <div class="stat-value">{{ number_format($stats['total_plays']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Public Tracks</div>
            <div class="stat-value">{{ number_format($stats['public_tracks']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Private Tracks</div>
            <div class="stat-value">{{ number_format($stats['private_tracks']) }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">All Tracks</h2>
            <form action="{{ route('admin.tracks.index') }}" method="GET" class="flex gap-3">
                <select name="status"
                    class="bg-white/10 border border-white/20 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-red-500">
                    <option value="" class="text-gray-900">All Status</option>
                    <option value="pending" class="text-gray-900" {{ request('status') == 'pending' ? 'selected' : '' }}>
                        Pending</option>
                    <option value="approved" class="text-gray-900" {{ request('status') == 'approved' ? 'selected' : '' }}>
                        Approved</option>
                    <option value="rejected" class="text-gray-900" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                        Rejected</option>
                </select>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tracks..."
                    style="padding: 0.5rem 1rem; border-radius: 8px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #fff;">
                <button type="submit"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-bold transition">Filter</button>
            </form>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Artist</th>
                        <th>Plays</th>
                        <th>Views</th>
                        <th>Likes</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tracks as $track)
                        <tr>
                            <td>#{{ $track->id }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    @if($track->cover_art)
                                        <img src="{{ Storage::url($track->cover_art) }}" alt=""
                                            class="w-8 h-8 rounded object-cover">
                                    @endif
                                    <span>{{ $track->title }}</span>
                                </div>
                            </td>
                            <td>{{ $track->user->name ?? 'Unknown' }}</td>
                            <td>{{ number_format($track->plays) }}</td>
                            <td>{{ number_format($track->views) }}</td>
                            <td>{{ number_format($track->likes) }}</td>
                            <td>
                                <span
                                    class="badge badge-{{ $track->status === 'approved' ? 'success' : ($track->status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($track->status) }}
                                </span>
                            </td>
                            <td>
                                <button onclick="openBoostModal({{ $track->id }}, '{{ addslashes($track->title) }}')"
                                    class="btn btn-primary btn-sm bg-purple-600 hover:bg-purple-700">
                                    Boost
                                </button>
                                @if($track->status !== 'approved')
                                    <form action="{{ route('admin.tracks.approve', $track) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                @endif
                                @if($track->status !== 'rejected')
                                    <form action="{{ route('admin.tracks.reject', $track) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.tracks.destroy', $track) }}" method="POST" class="inline-block"
                                    onsubmit="return confirm('Delete this track?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem;">
                                No tracks found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1.5rem;">
            {{ $tracks->links() }}
        </div>
    </div>

    <!-- Boost Modal -->
    <div id="boostModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50">
        <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md border border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-white">Boost Track: <span id="modalTrackTitle"></span></h3>
                <button onclick="closeBoostModal()" class="text-gray-400 hover:text-white">&times;</button>
            </div>
            <form id="boostForm" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-400 text-sm font-bold mb-2">Add Plays</label>
                        <input type="number" name="plays"
                            class="w-full bg-gray-700 text-white border border-gray-600 rounded py-2 px-3 focus:outline-none focus:border-purple-500"
                            min="0">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm font-bold mb-2">Add Views</label>
                        <input type="number" name="views"
                            class="w-full bg-gray-700 text-white border border-gray-600 rounded py-2 px-3 focus:outline-none focus:border-purple-500"
                            min="0">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm font-bold mb-2">Add Likes</label>
                        <input type="number" name="likes"
                            class="w-full bg-gray-700 text-white border border-gray-600 rounded py-2 px-3 focus:outline-none focus:border-purple-500"
                            min="0">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm font-bold mb-2">Add Downloads</label>
                        <input type="number" name="downloads"
                            class="w-full bg-gray-700 text-white border border-gray-600 rounded py-2 px-3 focus:outline-none focus:border-purple-500"
                            min="0">
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="closeBoostModal()"
                            class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-500">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-500 font-bold">Boost
                            Stats</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openBoostModal(trackId, trackTitle) {
            document.getElementById('boostModal').classList.remove('hidden');
            document.getElementById('boostModal').classList.add('flex');
            document.getElementById('modalTrackTitle').textContent = trackTitle;
            document.getElementById('boostForm').action = "/admin/tracks/" + trackId + "/add-interactions";
        }

        function closeBoostModal() {
            document.getElementById('boostModal').classList.add('hidden');
            document.getElementById('boostModal').classList.remove('flex');
        }
    </script>
@endsection