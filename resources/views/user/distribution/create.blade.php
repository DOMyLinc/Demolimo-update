@extends('layouts.app')

@section('content')
<div class="create-distribution">
    <div class="page-header">
        <h1>Submit Distribution Request</h1>
        <a href="{{ route('distribution.index') }}" class="btn btn-secondary">
            ← Back to My Distributions
        </a>
    </div>

    <div class="form-container">
        <form method="POST" action="{{ route('distribution.store') }}">
            @csrf

            <!-- Content Selection -->
            <div class="form-section">
                <h3>Select Content to Distribute</h3>
                
                <div class="form-group">
                    <label for="type">Content Type *</label>
                    <select name="type" id="type" class="form-control" required>
                        <option value="">-- Select Type --</option>
                        <option value="track" {{ old('type') == 'track' ? 'selected' : '' }}>Single Track</option>
                        <option value="album" {{ old('type') == 'album' ? 'selected' : '' }}>Album</option>
                    </select>
                    @error('type')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" id="track-select" style="display:none;">
                    <label for="track_id">Select Track *</label>
                    <select name="content_id" id="track_id" class="form-control">
                        <option value="">-- Select Track --</option>
                        @foreach($tracks as $track)
                        <option value="{{ $track->id }}" {{ old('content_id') == $track->id ? 'selected' : '' }}>
                            {{ $track->title }}
                        </option>
                        @endforeach
                    </select>
                    @if($tracks->isEmpty())
                        <p class="help-text">You don't have any tracks available for distribution.</p>
                    @endif
                </div>

                <div class="form-group" id="album-select" style="display:none;">
                    <label for="album_id">Select Album *</label>
                    <select name="content_id" id="album_id" class="form-control">
                        <option value="">-- Select Album --</option>
                        @foreach($albums as $album)
                        <option value="{{ $album->id }}" {{ old('content_id') == $album->id ? 'selected' : '' }}>
                            {{ $album->title }}
                        </option>
                        @endforeach
                    </select>
                    @if($albums->isEmpty())
                        <p class="help-text">You don't have any albums available for distribution.</p>
                    @endif
                </div>
            </div>

            <!-- Platform Selection -->
            <div class="form-section">
                <h3>Select Distribution Platforms *</h3>
                <p class="section-description">Choose one or more platforms where you want your music distributed.</p>
                
                <div class="platforms-grid">
                    @foreach($platforms as $key => $name)
                    <label class="platform-checkbox">
                        <input type="checkbox" name="platforms[]" value="{{ $key }}" 
                               {{ is_array(old('platforms')) && in_array($key, old('platforms')) ? 'checked' : '' }}>
                        <div class="platform-card">
                            <div class="platform-icon">🎵</div>
                            <div class="platform-name">{{ $name }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('platforms')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <!-- Release Information -->
            <div class="form-section">
                <h3>Release Information</h3>
                
                <div class="form-group">
                    <label for="release_date">Release Date *</label>
                    <input type="date" name="release_date" id="release_date" 
                           class="form-control" value="{{ old('release_date') }}" 
                           min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                    <p class="help-text">The date when your music will be available on the selected platforms.</p>
                    @error('release_date')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="upc">UPC Code (Optional)</label>
                        <input type="text" name="upc" id="upc" class="form-control" 
                               value="{{ old('upc') }}" placeholder="e.g., 123456789012">
                        <p class="help-text">Universal Product Code for your release.</p>
                        @error('upc')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="isrc">ISRC Code (Optional)</label>
                        <input type="text" name="isrc" id="isrc" class="form-control" 
                               value="{{ old('isrc') }}" placeholder="e.g., USRC17607839">
                        <p class="help-text">International Standard Recording Code.</p>
                        @error('isrc')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Important Notice -->
            <div class="notice-box">
                <h4>⚠️ Important Information</h4>
                <ul>
                    <li>Your distribution request will be reviewed by our team before processing.</li>
                    <li>Processing time typically takes 3-5 business days.</li>
                    <li>Ensure all metadata and audio quality meet platform requirements.</li>
                    <li>You'll be notified via email once your distribution is approved.</li>
                </ul>
            </div>

            <!-- Submit Button -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">
                    Submit Distribution Request
                </button>
                <a href="{{ route('distribution.index') }}" class="btn btn-outline btn-lg">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const trackSelect = document.getElementById('track-select');
    const albumSelect = document.getElementById('album-select');

    typeSelect.addEventListener('change', function() {
        if (this.value === 'track') {
            trackSelect.style.display = 'block';
            albumSelect.style.display = 'none';
            document.getElementById('track_id').required = true;
            document.getElementById('album_id').required = false;
        } else if (this.value === 'album') {
            trackSelect.style.display = 'none';
            albumSelect.style.display = 'block';
            document.getElementById('track_id').required = false;
            document.getElementById('album_id').required = true;
        } else {
            trackSelect.style.display = 'none';
            albumSelect.style.display = 'none';
        }
    });

    // Trigger on page load if old value exists
    if (typeSelect.value) {
        typeSelect.dispatchEvent(new Event('change'));
    }
});
</script>

<style>
.create-distribution {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.page-header h1 {
    margin: 0;
    font-size: 2rem;
}

.form-container {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.form-section {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 1px solid #e5e7eb;
}

.form-section:last-of-type {
    border-bottom: none;
}

.form-section h3 {
    margin: 0 0 10px 0;
    font-size: 1.5rem;
}

.section-description {
    color: #6b7280;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 1rem;
}

.form-control:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.help-text {
    margin-top: 5px;
    font-size: 0.875rem;
    color: #6b7280;
}

.error {
    display: block;
    margin-top: 5px;
    color: #ef4444;
    font-size: 0.875rem;
}

.platforms-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
}

.platform-checkbox {
    cursor: pointer;
}

.platform-checkbox input[type="checkbox"] {
    display: none;
}

.platform-card {
    padding: 20px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    text-align: center;
    transition: all 0.2s;
}

.platform-checkbox input[type="checkbox"]:checked + .platform-card {
    border-color: #4f46e5;
    background: #eef2ff;
}

.platform-card:hover {
    border-color: #4f46e5;
    transform: translateY(-2px);
}

.platform-icon {
    font-size: 2rem;
    margin-bottom: 10px;
}

.platform-name {
    font-weight: 600;
    color: #374151;
}

.notice-box {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.notice-box h4 {
    margin: 0 0 15px 0;
    color: #92400e;
}

.notice-box ul {
    margin: 0;
    padding-left: 20px;
}

.notice-box li {
    margin-bottom: 8px;
    color: #78350f;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-lg {
    padding: 14px 32px;
    font-size: 1.125rem;
}

.btn-primary {
    background: #4f46e5;
    color: white;
}

.btn-primary:hover {
    background: #4338ca;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-outline {
    background: white;
    border: 2px solid #d1d5db;
    color: #374151;
}

.btn-outline:hover {
    background: #f9fafb;
}
</style>
@endsection
