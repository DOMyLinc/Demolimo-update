@extends('layouts.admin')

@section('title', 'Manage Playlists')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Playlists</h1>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Playlists</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>User</th>
                                <th>Tracks</th>
                                <th>Visibility</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($playlists as $playlist)
                                <tr>
                                    <td>{{ $playlist->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($playlist->cover_image)
                                                <img src="{{ Storage::url($playlist->cover_image) }}" alt="" class="rounded mr-2"
                                                    style="width: 30px; height: 30px; object-fit: cover;">
                                            @else
                                                <div class="bg-secondary rounded mr-2 d-flex align-items-center justify-content-center"
                                                    style="width: 30px; height: 30px;">
                                                    <i class="fas fa-music text-white small"></i>
                                                </div>
                                            @endif
                                            {{ $playlist->name }}
                                        </div>
                                    </td>
                                    <td>
                                        <a
                                            href="{{ route('admin.users.show', $playlist->user) }}">{{ $playlist->user->name }}</a>
                                    </td>
                                    <td>{{ $playlist->tracks_count }}</td>
                                    <td>
                                        @if($playlist->is_public)
                                            <span class="badge badge-success">Public</span>
                                        @else
                                            <span class="badge badge-secondary">Private</span>
                                        @endif
                                    </td>
                                    <td>{{ $playlist->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.playlists.show', $playlist) }}"
                                            class="btn btn-info btn-sm btn-circle" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('admin.playlists.destroy', $playlist) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this playlist?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm btn-circle" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $playlists->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection