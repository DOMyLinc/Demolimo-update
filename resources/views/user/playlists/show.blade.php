@extends('layouts.user')

@section('title', $playlist->name)

@section('content')
    <div class="container-fluid">
        <!-- Playlist Header -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        @if($playlist->cover_image)
                            <img src="{{ Storage::url($playlist->cover_image) }}" alt="{{ $playlist->name }}"
                                class="img-fluid rounded shadow-sm" style="max-height: 250px; width: 100%; object-fit: cover;">
                        @else
                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center shadow-sm"
                                style="height: 250px; width: 100%;">
                                <i class="fas fa-music fa-5x text-white"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-9">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="text-uppercase text-muted mb-1 small font-weight-bold">Playlist</h5>
                                <h1 class="font-weight-bold text-gray-900 mb-2">{{ $playlist->name }}</h1>
                                <p class="mb-3">
                                    Created by <a href="#"
                                        class="font-weight-bold text-primary">{{ $playlist->user->name }}</a>
                                    • {{ $playlist->tracks->count() }} tracks
                                    @if(!$playlist->is_public) <span class="badge badge-secondary ml-2"><i
                                    class="fas fa-lock mr-1"></i> Private</span> @endif
                                </p>
                                <p class="text-gray-600">{{ $playlist->description }}</p>
                            </div>
                            <div>
                                @if(Auth::id() === $playlist->user_id)
                                    <a href="{{ route('playlists.edit', $playlist) }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </a>
                                @else
                                    @if(Auth::user()->followedPlaylists->contains($playlist->id))
                                        <form action="{{ route('playlists.unfollow', $playlist) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-check mr-1"></i> Following
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('playlists.follow', $playlist) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-heart mr-1"></i> Follow
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-success btn-lg rounded-pill px-4 mr-2"
                                onclick="playPlaylist({{ $playlist->id }})">
                                <i class="fas fa-play mr-2"></i> Play
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Track List -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tracks</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th style="width: 60px;"></th>
                                <th>Title</th>
                                <th>Artist</th>
                                <th>Album</th>
                                <th class="text-center"><i class="far fa-clock"></i></th>
                                @if(Auth::id() === $playlist->user_id)
                                    <th class="text-right">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="playlist-tracks">
                            @forelse($playlist->tracks as $index => $track)
                                <tr class="track-row" data-id="{{ $track->id }}">
                                    <td class="align-middle text-muted">{{ $index + 1 }}</td>
                                    <td class="align-middle">
                                        <img src="{{ $track->cover_image }}" alt="" class="rounded"
                                            style="width: 40px; height: 40px; object-fit: cover;">
                                    </td>
                                    <td class="align-middle">
                                        <a href="#"
                                            class="text-dark font-weight-bold text-decoration-none">{{ $track->title }}</a>
                                    </td>
                                    <td class="align-middle">
                                        <a href="#" class="text-muted text-decoration-none">{{ $track->user->name }}</a>
                                    </td>
                                    <td class="align-middle text-muted">
                                        {{ $track->album ? $track->album->title : '-' }}
                                    </td>
                                    <td class="align-middle text-center text-muted">
                                        {{ gmdate("i:s", $track->duration) }}
                                    </td>
                                    @if(Auth::id() === $playlist->user_id)
                                        <td class="align-middle text-right">
                                            <form action="{{ route('playlists.removeTrack', [$playlist, $track]) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0"
                                                    title="Remove from playlist">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-music fa-3x mb-3 text-gray-300"></i>
                                        <p class="mb-0">This playlist is empty.</p>
                                        @if(Auth::id() === $playlist->user_id)
                                            <a href="{{ route('discovery.index') }}" class="btn btn-sm btn-primary mt-2">Find Tracks
                                                to Add</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function playPlaylist(id) {
                // Placeholder for global player integration
                console.log('Playing playlist ' + id);
                // If you have a global player instance, call it here
                // window.player.playPlaylist(id);
                alert('Starting playlist playback...');
            }
        </script>
    @endpush
@endsection