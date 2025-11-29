@extends('layouts.app')

@section('title', 'Upload Track')

@section('content')
    <style>
        .upload-page {
            padding: 4rem 0;
            min-height: 100vh;
        }

        .upload-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-title {
            font-size: 3rem;
            font-weight: 900;
            background: linear-gradient(135deg, #fff 0%, #667eea 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            font-size: 1.125rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .upload-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 3rem;
        }

        .form-section {
            margin-bottom: 2.5rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #fff;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 0.875rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.08);
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .file-upload-zone {
            border: 2px dashed rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 3rem 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .file-upload-zone:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }

        .file-upload-zone.dragover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .upload-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #667eea;
        }

        .upload-text {
            font-size: 1.125rem;
            margin-bottom: 0.5rem;
        }

        .upload-hint {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.5);
        }

        .file-input {
            display: none;
        }

        .file-preview {
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .file-info {
            flex: 1;
        }

        .file-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .file-size {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .remove-file {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .remove-file:hover {
            background: rgba(239, 68, 68, 0.3);
        }

        .image-upload-preview {
            width: 200px;
            height: 200px;
            border-radius: 12px;
            object-fit: cover;
            margin-top: 1rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .submit-btn {
            width: 100%;
            padding: 1.125rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1.125rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 2rem;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .upload-card {
                padding: 2rem 1.5rem;
            }

            .page-title {
                font-size: 2rem;
            }
        }
    </style>

    <div class="upload-page">
        <div class="container upload-container">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">🎵 Upload Your Track</h1>
                <p class="page-subtitle">Share your music with the world</p>
            </div>

            <!-- Upload Form -->
            <div class="upload-card">
                <form action="{{ route('user.tracks.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf

                    <!-- Audio File Upload -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-music"></i> Audio File
                        </h3>
                        <div class="file-upload-zone" id="audioDropZone">
                            <i class="fas fa-cloud-upload-alt upload-icon"></i>
                            <p class="upload-text">Drag & drop your audio file here</p>
                            <p class="upload-hint">or click to browse (MP3, WAV, FLAC - Max 50MB)</p>
                            <input type="file" name="audio_file" id="audioFile" class="file-input" accept="audio/*"
                                required>
                        </div>
                        <div id="audioPreview" style="display: none;"></div>
                        @error('audio_file')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Track Information -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-info-circle"></i> Track Information
                        </h3>

                        <div class="form-group">
                            <label for="title" class="form-label">Track Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title" id="title" class="form-input" placeholder="Enter track title"
                                required value="{{ old('title') }}">
                            @error('title')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-textarea"
                                placeholder="Tell us about your track...">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <x-genre-select name="genre_id" :selected="old('genre_id')" :required="true" />
                                @error('genre_id')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="album_id" class="form-label">Album (Optional)</label>
                                <select name="album_id" id="album_id" class="form-select">
                                    <option value="">No Album</option>
                                    @foreach(auth()->user()->albums ?? [] as $album)
                                        <option value="{{ $album->id }}" {{ old('album_id') == $album->id ? 'selected' : '' }}>
                                            {{ $album->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="release_date" class="form-label">Release Date</label>
                                <input type="date" name="release_date" id="release_date" class="form-input"
                                    value="{{ old('release_date', date('Y-m-d')) }}">
                            </div>

                            <div class="form-group">
                                <label for="tags" class="form-label">Tags (comma separated)</label>
                                <input type="text" name="tags" id="tags" class="form-input"
                                    placeholder="e.g., chill, summer, vibes" value="{{ old('tags') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Cover Image -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-image"></i> Cover Image
                        </h3>
                        <div class="file-upload-zone" id="imageDropZone">
                            <i class="fas fa-image upload-icon"></i>
                            <p class="upload-text">Upload cover image</p>
                            <p class="upload-hint">JPG, PNG, WEBP - Max 5MB (Recommended: 1000x1000px)</p>
                            <input type="file" name="cover_image" id="coverImage" class="file-input" accept="image/*">
                        </div>
                        <img id="imagePreview" class="image-upload-preview" style="display: none;">
                        @error('cover_image')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Settings -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-cog"></i> Settings
                        </h3>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="privacy" class="form-label">Privacy</label>
                                <select name="privacy" id="privacy" class="form-select">
                                    <option value="public" {{ old('privacy') == 'public' ? 'selected' : '' }}>Public</option>
                                    <option value="private" {{ old('privacy') == 'private' ? 'selected' : '' }}>Private
                                    </option>
                                    <option value="unlisted" {{ old('privacy') == 'unlisted' ? 'selected' : '' }}>Unlisted
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="price" class="form-label">Price (Leave 0 for free)</label>
                                <input type="number" name="price" id="price" class="form-input" min="0" step="0.01"
                                    value="{{ old('price', 0) }}">
                            </div>
                        </div>

                        <div class="checkbox-group">
                            <input type="checkbox" name="allow_downloads" id="allow_downloads" class="checkbox" {{ old('allow_downloads') ? 'checked' : '' }}>
                            <label for="allow_downloads" class="form-label" style="margin: 0;">Allow downloads</label>
                        </div>

                        <div class="checkbox-group">
                            <input type="checkbox" name="explicit_content" id="explicit_content" class="checkbox" {{ old('explicit_content') ? 'checked' : '' }}>
                            <label for="explicit_content" class="form-label" style="margin: 0;">Explicit content</label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <i class="fas fa-upload"></i> Upload Track
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Audio file upload
        const audioDropZone = document.getElementById('audioDropZone');
        const audioFile = document.getElementById('audioFile');
        const audioPreview = document.getElementById('audioPreview');

        audioDropZone.addEventListener('click', () => audioFile.click());

        audioDropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            audioDropZone.classList.add('dragover');
        });

        audioDropZone.addEventListener('dragleave', () => {
            audioDropZone.classList.remove('dragover');
        });

        audioDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            audioDropZone.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                audioFile.files = files;
                showAudioPreview(files[0]);
            }
        });

        audioFile.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                showAudioPreview(e.target.files[0]);
            }
        });

        function showAudioPreview(file) {
            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            audioPreview.innerHTML = `
                <div class="file-preview">
                    <i class="fas fa-file-audio" style="font-size: 2rem; color: #667eea;"></i>
                    <div class="file-info">
                        <div class="file-name">${file.name}</div>
                        <div class="file-size">${sizeMB} MB</div>
                    </div>
                    <button type="button" class="remove-file" onclick="removeAudioFile()">
                        <i class="fas fa-times"></i> Remove
                    </button>
                </div>
            `;
            audioPreview.style.display = 'block';
        }

        function removeAudioFile() {
            audioFile.value = '';
            audioPreview.style.display = 'none';
        }

        // Image file upload
        const imageDropZone = document.getElementById('imageDropZone');
        const coverImage = document.getElementById('coverImage');
        const imagePreview = document.getElementById('imagePreview');

        imageDropZone.addEventListener('click', () => coverImage.click());

        imageDropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            imageDropZone.classList.add('dragover');
        });

        imageDropZone.addEventListener('dragleave', () => {
            imageDropZone.classList.remove('dragover');
        });

        imageDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            imageDropZone.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                coverImage.files = files;
                showImagePreview(files[0]);
            }
        });

        coverImage.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                showImagePreview(e.target.files[0]);
            }
        });

        function showImagePreview(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }

        // Form validation
        document.getElementById('uploadForm').addEventListener('submit', (e) => {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        });
    </script>
@endsection