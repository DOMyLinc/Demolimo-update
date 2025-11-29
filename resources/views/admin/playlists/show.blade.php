@extends('layouts.admin')

@section('title', 'Playlist Details')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Playlist Details</h1>
            <a href="{{ route('admin.playlists.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Back to List
            </a>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Info</h6>
                    </div>
                    <div class="card-body text-center">
                        @if($playlist->cover_image)
                            <img src="{{ Storage::url($playlist->cover_image) }}" alt="{{ $playlist->name }}"
                                class="img-fluid rounded mb-3 shadow-sm" style="max-height: 200px;">
                        @else
                            <div class="bg-secondary rounded mb-3 d-flex align-items-center justify-content-center mx-auto shadow-sm"
                                style="height: 200px; width: 200px;">
                                <i class="fas fa-music fa-5x text-white"></i>
                            </div>
                        @endif

                        <h4 class="font-weight-bold">{{ $playlist->name }}</h4>
                        <p class="text-muted mb-1">by <a
                                href="{{ route('admin.users.show', $playlist->user) }}">{{ $playlist->user->name }}</a></p>

                        <div class="mb-3">
                            @if($playlist->is_public)
                                <span class="badge badge-success px-3 py-2">Public</span>
                            @else
                                <span class="badge badge-secondary px-3 py-2">Private</span>
                            @endif
                        </div>

                        <p class="small text-muted">{{ $playlist->description }}</p>

                        <hr>

                        <div class="d-flex justify-content-center">
                            <form action="{{ route('admin.playlists.toggleVisibility', $playlist) }}" method="POST"
                                class="mr-2">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm">
                                    <i class="fas fa-eye-slash mr-1"></i> Toggle Visibility
                                </button>
                            </form>

                            <form action="{{ route('admin.playlists.destroy', $playlist) }}" method="POST"
                                onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash mr-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Tracks ({{ $playlist->tracks->count() }})</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Title</th>
                                        <th>Artist</th>
                                        <th>Duration</th>
                                        <th>Added</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($playlist->tracks as $index => $track)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <a href="{{ route('admin.tracks.show', $track) }}">{{ $track->title }}</a>
                                            </td>
                                            <td>{{ $track->user->name }}</td>
                                            <td>{{ gmdate("i:s", $track->duration) }}</td>
                                            <td>{{ $track->pivot->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection