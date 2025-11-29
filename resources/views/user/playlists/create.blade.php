@extends('layouts.user')

@section('title', 'Create Playlist')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Create New Playlist</h6>
                        <a href="{{ route('playlists.index') }}" class="btn btn-sm btn-secondary">Back</a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('playlists.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label for="name">Playlist Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                    name="name" value="{{ old('name') }}" required autofocus>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                                    name="description" rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="cover_image">Cover Image</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('cover_image') is-invalid @enderror"
                                        id="cover_image" name="cover_image" accept="image/*">
                                    <label class="custom-file-label" for="cover_image">Choose file...</label>
                                </div>
                                <small class="form-text text-muted">Recommended size: 500x500px. Max size: 2MB.</small>
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
                                        value="1" {{ old('is_public', 1) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_public">Public Playlist</label>
                                </div>
                                <small class="form-text text-muted">Public playlists are visible to everyone on the
                                    platform.</small>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="hidden" name="is_collaborative" value="0">
                                    <input type="checkbox" class="custom-control-input" id="is_collaborative"
                                        name="is_collaborative" value="1" {{ old('is_collaborative') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_collaborative">Collaborative</label>
                                </div>
                                <small class="form-text text-muted">Allow other users to add tracks to this
                                    playlist.</small>
                            </div>

                            <hr>

                            <button type="submit" class="btn btn-primary btn-block">Create Playlist</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Custom file input label update
            $('.custom-file-input').on('change', function () {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });
        </script>
    @endpush
@endsection