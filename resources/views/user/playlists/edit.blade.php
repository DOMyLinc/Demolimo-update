@extends('layouts.user')

@section('title', 'Edit Playlist')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Edit Playlist: {{ $playlist->name }}</h6>
                        <a href="{{ route('playlists.show', $playlist) }}" class="btn btn-sm btn-secondary">Back</a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('playlists.update', $playlist) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="name">Playlist Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                    name="name" value="{{ old('name', $playlist->name) }}" required>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                                    name="description" rows="3">{{ old('description', $playlist->description) }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="cover_image">Cover Image</label>
                                @if($playlist->cover_image)
                                    <div class="mb-2">
                                        <img src="{{ Storage::url($playlist->cover_image) }}" alt="Current Cover"
                                            style="height: 100px; width: 100px; object-fit: cover; border-radius: 5px;">
                                    </div>
                                @endif
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('cover_image') is-invalid @enderror"
                                        id="cover_image" name="cover_image" accept="image/*">
                                    <label class="custom-file-label" for="cover_image">Choose new file...</label>
                                </div>
                                <small class="form-text text-muted">Leave empty to keep current image.</small>
                                @error('cover_image')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="hidden" name="is_public" value="0">
                                    <input type="checkbox" class="custom-control-input" id="is_public" name="is_public"
                                        value="1" {{ old('is_public', $playlist->is_public) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_public">Public Playlist</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="hidden" name="is_collaborative" value="0">
                                    <input type="checkbox" class="custom-control-input" id="is_collaborative"
                                        name="is_collaborative" value="1" {{ old('is_collaborative', $playlist->is_collaborative) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_collaborative">Collaborative</label>
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete
                                    Playlist</button>
                                <button type="submit" class="btn btn-primary">Update Playlist</button>
                            </div>
                        </form>

                        <form id="delete-form" action="{{ route('playlists.destroy', $playlist) }}" method="POST"
                            style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $('.custom-file-input').on('change', function () {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

            function confirmDelete() {
                if (confirm('Are you sure you want to delete this playlist? This action cannot be undone.')) {
                    document.getElementById('delete-form').submit();
                }
            }
        </script>
    @endpush
@endsection