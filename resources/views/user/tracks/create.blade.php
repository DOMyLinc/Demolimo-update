@extends('layouts.app')

@section('title', 'Upload Track')

@section('content')
    <style>
        .upload-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        .upload-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .upload-header h1 {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #fff 0%, #667eea 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .upload-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 3rem;
        }

        /* Drop Zone */
        .drop-zone {
            border: 2px dashed rgba(102, 126, 234, 0.3);
            border-radius: 20px;
            padding: 4rem 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            margin-bottom: 2rem;
        }

        .drop-zone:hover,
        .drop-zone.dragover {
            border-color: rgba(102, 126, 234, 0.6);
            background: rgba(102, 126, 234, 0.05);
        }

        .drop-zone-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .drop-zone-text {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .drop-zone-hint {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.875rem;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 2rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 1rem 1.5rem;
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
            border-color: rgba(102, 126, 234, 0.5);
            background: rgba(255, 255, 255, 0.08);
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        /* Cover Upload */
        .cover-upload {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .cover-preview {
            width: 150px;
            height: 150px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.2), rgba(118, 75, 162, 0.2));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .cover-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 16px;
        }

        /* Submit Button */
        .submit-section {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>

    <div class="upload-container">
        <div class="upload-header">
            <h1>Upload Your Track 🎵</h1>
            <p style="color: rgba(255, 255, 255, 0.7);">Share your music with the world</p>
        </div>

        <div class="upload-card">
            <form action="{{ route('user.tracks.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Drop Zone -->
                <div class="drop-zone" onclick="document.getElementById('audioFile').click()">
                    <div class="drop-zone-icon">🎵</div>
                    <div class="drop-zone-text">Drag & drop your audio file here</div>
                    <div class="drop-zone-hint">or click to browse (MP3, WAV, FLAC - Max 100MB)</div>
                    <input type="file" name="audio_file" id="audioFile" accept="audio/*" style="display: none;" required>
                </div>

                <!-- Track Title -->
                <div class="form-group">
                    <label class="form-label">Track Title *</label>
                    <input type="text" name="title" class="form-input" placeholder="Enter track title" required>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" placeholder="Tell us about your track..."></textarea>
                </div>

                <!-- Genre -->
                <div class="form-group">
                    <label class="form-label">Genre *</label>
                    <select name="genre" class="form-select" required>
                        <option value="">Select a genre</option>
                        <option value="electronic">Electronic</option>
                        <option value="hip-hop">Hip Hop</option>
                        <option value="rock">Rock</option>
                        <option value="pop">Pop</option>
                        <option value="jazz">Jazz</option>
                        <option value="classical">Classical</option>
                        <option value="r&b">R&B</option>
                        <option value="country">Country</option>
                        <option value="indie">Indie</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <!-- Cover Art -->
                <div class="form-group">
                    <label class="form-label">Cover Art</label>
                    <div class="cover-upload">
                        <div class="cover-preview" id="coverPreview">🎨</div>
                        <div style="flex: 1;">
                            <input type="file" name="cover_art" id="coverArt" accept="image/*" style="display: none;">
                            <button type="button" class="btn btn-outline"
                                onclick="document.getElementById('coverArt').click()">
                                Choose Image
                            </button>
                            <p style="color: rgba(255, 255, 255, 0.6); font-size: 0.875rem; margin-top: 0.5rem;">
                                Recommended: 1000x1000px, JPG or PNG
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Visibility -->
                <div class="form-group">
                    <label class="form-label">Visibility</label>
                    <select name="visibility" class="form-select">
                        <option value="public">Public - Anyone can listen</option>
                        <option value="private">Private - Only you can listen</option>
                        <option value="unlisted">Unlisted - Only people with the link</option>
                    </select>
                </div>

                <!-- Submit -->
                <div class="submit-section">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">Upload Track</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            // Cover art preview
            document.getElementById('coverArt').addEventListener('change', (e) => {
                if (e.target.files.length) {
                    const file = e.target.files[0];
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        document.getElementById('coverPreview').innerHTML = `<img src="${e.target.result}" alt="Cover">`;
                    };
                    reader.readAsDataURL(file);
                }
            });
        </script>
    @endpush
@endsection