@extends('layouts.admin')

@section('page-title', 'Edit Sound')

@section('content')
    <div class="sound-edit">
        <div class="form-container">
            <form action="{{ route('admin.daw-sounds.update', $dawSound) }}" method="POST">
                @csrf @method('PUT')
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="name" value="{{ old('name', $dawSound->name) }}" required class="form-control">
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" required class="form-control">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $dawSound->category_id == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3"
                        class="form-control">{{ old('description', $dawSound->description) }}</textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>BPM</label>
                        <input type="number" name="bpm" value="{{ old('bpm', $dawSound->bpm) }}" min="1" max="300"
                            class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Key</label>
                        <input type="text" name="key" value="{{ old('key', $dawSound->key) }}" maxlength="10"
                            class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label>Tags (comma separated)</label>
                    <input type="text" name="tags"
                        value="{{ old('tags', is_array($dawSound->tags) ? implode(', ', $dawSound->tags) : '') }}"
                        class="form-control">
                </div>
                <div class="form-group">
                    <label class="checkbox-label"><input type="checkbox" name="is_active" value="1" {{ $dawSound->is_active ? 'checked' : '' }}><span>Active</span></label>
                </div>
                <div class="form-group">
                    <label class="checkbox-label"><input type="checkbox" name="is_premium" value="1" {{ $dawSound->is_premium ? 'checked' : '' }}><span>Premium Only</span></label>
                </div>
                <div class="form-actions">
                    <a href="{{ route('admin.daw-sounds.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Sound</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .sound-edit {
            max-width: 700px;
            margin: 0 auto;
            padding: 20px
        }

        .form-container {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1)
        }

        .form-group {
            margin-bottom: 20px
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem
        }

        .form-control:focus {
            outline: none;
            border-color: #4f46e5
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer
        }

        .checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none
        }

        .btn-primary {
            background: #4f46e5;
            color: #fff
        }

        .btn-primary:hover {
            background: #4338ca
        }

        .btn-secondary {
            background: #6b7280;
            color: #fff
        }

        .btn-secondary:hover {
            background: #4b5563
        }
    </style>
@endsection