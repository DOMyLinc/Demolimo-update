@props(['name' => 'genre_id', 'selected' => null, 'required' => false])

@php
    $genres = \App\Models\Genre::where('is_active', true)
        ->orderBy('sort_order')
        ->get();
@endphp

<div class="genre-select-wrapper">
    <label for="{{ $name }}" class="form-label">
        Genre @if($required)<span class="text-red-500">*</span>@endif
    </label>
    <select name="{{ $name }}" id="{{ $name }}" class="genre-select" @if($required) required @endif>
        <option value="">Select a genre...</option>
        @foreach($genres as $genre)
            <option value="{{ $genre->id }}" @if($selected == $genre->id) selected @endif data-icon="{{ $genre->icon }}"
                data-color="{{ $genre->color }}">
                {{ $genre->icon }} {{ $genre->name }}
            </option>
        @endforeach
    </select>
</div>

<style>
    .genre-select-wrapper {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: rgba(255, 255, 255, 0.9);
    }

    .genre-select {
        width: 100%;
        padding: 0.875rem 1rem;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        color: #fff;
        font-size: 1rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .genre-select:hover {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(102, 126, 234, 0.3);
    }

    .genre-select:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.08);
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .genre-select option {
        background: #1a1a2e;
        color: #fff;
        padding: 0.5rem;
    }
</style>