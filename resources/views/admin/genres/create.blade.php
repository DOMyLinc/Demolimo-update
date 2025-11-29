@extends('layouts.admin')

@section('page-title', 'Create Genre')

@section('content')
    <style>
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 2.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .color-picker-wrapper {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .color-preview {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .btn {
            padding: 0.75rem 2rem;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            margin-right: 1rem;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .toggle-wrapper {
            display: flex;
            align-items: center;
            gap: 1rem;
            cursor: pointer;
        }

        .toggle-switch {
            position: relative;
            width: 50px;
            height: 26px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 13px;
            transition: background 0.3s;
        }

        .toggle-checkbox:checked+.toggle-switch {
            background: #10b981;
        }

        .toggle-switch::after {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 20px;
            height: 20px;
            background: #fff;
            border-radius: 50%;
            transition: transform 0.3s;
        }

        .toggle-checkbox:checked+.toggle-switch::after {
            transform: translateX(24px);
        }
    </style>

    <div class="form-container">
        <h1 style="font-size: 2rem; font-weight: 800; margin-bottom: 2rem;">Create New Genre</h1>

        <form action="{{ route('admin.genres.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">Genre Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Hip Hop" required
                    value="{{ old('name') }}">
                @error('name')
                    <span
                        style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"
                    placeholder="Brief description of the genre">{{ old('description') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Icon (Emoji)</label>
                <input type="text" name="icon" class="form-control" placeholder="e.g. 🎤" value="{{ old('icon', '🎵') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Color</label>
                <div class="color-picker-wrapper">
                    <input type="color" name="color" id="colorInput" value="{{ old('color', '#667eea') }}"
                        style="opacity: 0; width: 0; height: 0;">
                    <div class="color-preview" id="colorPreview" style="background: {{ old('color', '#667eea') }}"
                        onclick="document.getElementById('colorInput').click()"></div>
                    <input type="text" class="form-control" id="colorText" value="{{ old('color', '#667eea') }}" readonly
                        onclick="document.getElementById('colorInput').click()">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Status</label>
                <label class="toggle-wrapper">
                    <input type="checkbox" name="is_active" class="toggle-checkbox" value="1" checked
                        style="display: none;">
                    <div class="toggle-switch"></div>
                    <span>Active</span>
                </label>
            </div>

            <div style="margin-top: 2.5rem;">
                <a href="{{ route('admin.genres.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Genre</button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            const colorInput = document.getElementById('colorInput');
            const colorPreview = document.getElementById('colorPreview');
            const colorText = document.getElementById('colorText');

            colorInput.addEventListener('input', (e) => {
                colorPreview.style.background = e.target.value;
                colorText.value = e.target.value;
            });
        </script>
    @endpush
@endsection