@extends('layouts.admin')

@section('page-title', 'Edit Gift')

@section('content')
    <div class="gift-edit">
        <div class="form-container">
            <form action="{{ route('admin.gifts.update', $gift) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Gift Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $gift->name) }}" required
                        class="form-control @error('name') is-invalid @enderror">
                    @error('name')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="icon">Icon (Emoji) *</label>
                    <input type="text" name="icon" id="icon" value="{{ old('icon', $gift->icon) }}" required maxlength="10"
                        class="form-control @error('icon') is-invalid @enderror">
                    @error('icon')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="price">Price (USD) *</label>
                    <input type="number" name="price" id="price" value="{{ old('price', $gift->price) }}" required
                        step="0.01" min="0.01" class="form-control @error('price') is-invalid @enderror">
                    @error('price')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="3"
                        class="form-control @error('description') is-invalid @enderror">{{ old('description', $gift->description) }}</textarea>
                    @error('description')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="sort_order">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order"
                        value="{{ old('sort_order', $gift->sort_order) }}"
                        class="form-control @error('sort_order') is-invalid @enderror">
                    @error('sort_order')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $gift->is_active) ? 'checked' : '' }}>
                        <span>Active</span>
                    </label>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.gifts.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Gift</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .gift-edit {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .form-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
            padding: 10px 15px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #4f46e5;
        }

        .form-control.is-invalid {
            border-color: #ef4444;
        }

        .error {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 5px;
            display: block;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
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

        .btn-secondary:hover {
            background: #4b5563;
        }
    </style>
@endsection