@extends('layouts.user')

@section('title', 'My Playlists')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">My Playlists</h1>
            <a href="{{ route('playlists.create') }}" class="btn btn-primary">
                <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Create Playlist
            </a>
        </div>

        <!-- My Playlists -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Created by Me</h6>
            </div>
            <div class="card-body">
                @if($playlists->count() > 0)
                    <div class="row">
                        @foreach($playlists as $playlist)
                            <div class="col-md-3 mb-4">
                                <div class="card h-100">
                                    <a href="{{ route('playlists.show', $playlist) }}">
                                        @if($playlist->cover_image)
                                            <img src="{{ Storage::url($playlist->cover_image) }}" class="card-img-top"
                                                alt="{{ $playlist->name }}" style="height: 200px; object-fit: cover;">
                                        @else
                                            <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center"
                                                style="height: 200px;">
                                                <i class="fas fa-music fa-3x text-white"></i>
                                            </div>
                                        @endif
                                    </a>
                                    <div class="card-body">
                                        <h5 class="card-title text-truncate">
                                            <a href="{{ route('playlists.show', $playlist) }}"
                                                class="text-dark text-decoration-none">{{ $playlist->name }}</a>
                                        </h5>
                                        <p class="card-text text-muted small">
                                            {{ $playlist->tracks_count }} tracks
                                            @if(!$playlist->is_public) <i class="fas fa-lock ml-1" title="Private"></i> @endif
                                        </p>
                                    </div>
                                    <div class="card-footer bg-transparent border-top-0">
                                        <a href="{{ route('playlists.edit', $playlist) }}"
                                            class="btn btn-sm btn-outline-secondary btn-block">Edit</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center py-4">You haven't created any playlists yet.</p>
                @endif
            </div>
        </div>

        <!-- Followed Playlists -->
        @if($followedPlaylists->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Followed Playlists</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($followedPlaylists as $playlist)
                            <div class="col-md-3 mb-4">
                                <div class="card h-100">
                                    <a href="{{ route('playlists.show', $playlist) }}">
                                        @if($playlist->cover_image)
                                            <img src="{{ Storage::url($playlist->cover_image) }}" class="card-img-top"
                                                alt="{{ $playlist->name }}" style="height: 200px; object-fit: cover;">
                                        @else
                                            <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center"
                                                style="height: 200px;">
                                                <i class="fas fa-music fa-3x text-white"></i>
                                            </div>
                                        @endif
                                    </a>
                                    <div class="card-body">
                                        <h5 class="card-title text-truncate">
                                            <a href="{{ route('playlists.show', $playlist) }}"
                                                class="text-dark text-decoration-none">{{ $playlist->name }}</a>
                                        </h5>
                                        <p class="card-text text-muted small">
                                            by {{ $playlist->user->name }} • {{ $playlist->tracks_count }} tracks
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection