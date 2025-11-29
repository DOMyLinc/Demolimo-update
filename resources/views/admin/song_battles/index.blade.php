@extends('layouts.admin')

@section('page-title', 'Track Trials Management')

@section('content')
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">All Track Trials</h2>
            <!-- Search could go here -->
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Artist</th>
                        <th>Versions</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($battles as $battle)
                        <tr>
                            <td>#{{ $battle->id }}</td>
                            <td>
                                <div class="font-bold text-white">{{ $battle->title }}</div>
                                <div class="text-xs text-gray-400 truncate w-48">{{ $battle->description }}</div>
                            </td>
                            <td>
                                <a href="{{ route('admin.users.edit', $battle->user) }}" class="text-blue-400 hover:underline">
                                    {{ $battle->user->name }}
                                </a>
                            </td>
                            <td>{{ $battle->versions_count }}</td>
                            <td>
                                <span class="badge badge-{{ $battle->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($battle->status) }}
                                </span>
                            </td>
                            <td>{{ $battle->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('song_battles.show', $battle) }}" target="_blank"
                                    class="btn btn-primary btn-sm">Watch</a>
                                <form action="{{ route('admin.song_battles.destroy', $battle) }}" method="POST"
                                    style="display: inline;" onsubmit="return confirm('Delete this battle?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                No track trials found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1.5rem;">
            {{ $battles->links() }}
        </div>
    </div>
@endsection