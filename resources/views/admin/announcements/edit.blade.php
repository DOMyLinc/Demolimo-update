@extends('layouts.admin')

@section('page-title', 'Edit Announcement')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="card">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-white">Edit Announcement</h2>
                <a href="{{ route('admin.announcements.index') }}" class="text-gray-400 hover:text-white transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
            </div>

            <form action="{{ route('admin.announcements.update', $announcement->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Title</label>
                    <input type="text" name="title" value="{{ old('title', $announcement->title) }}" required
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 outline-none transition">
                    @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Message</label>
                    <textarea name="message" rows="4" required
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 outline-none transition">{{ old('message', $announcement->message) }}</textarea>
                    @error('message') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Type</label>
                    <select name="type" required
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 outline-none transition">
                        <option value="info" {{ old('type', $announcement->type) == 'info' ? 'selected' : '' }}>Info (Blue)
                        </option>
                        <option value="success" {{ old('type', $announcement->type) == 'success' ? 'selected' : '' }}>Success
                            (Green)</option>
                        <option value="warning" {{ old('type', $announcement->type) == 'warning' ? 'selected' : '' }}>Warning
                            (Yellow)</option>
                        <option value="danger" {{ old('type', $announcement->type) == 'danger' ? 'selected' : '' }}>Danger
                            (Red)</option>
                    </select>
                    @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Start Date
                            (Optional)</label>
                        <input type="datetime-local" name="starts_at"
                            value="{{ old('starts_at', $announcement->starts_at?->format('Y-m-d\TH:i')) }}"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 outline-none transition">
                        @error('starts_at') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">End Date
                            (Optional)</label>
                        <input type="datetime-local" name="ends_at"
                            value="{{ old('ends_at', $announcement->ends_at?->format('Y-m-d\TH:i')) }}"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 outline-none transition">
                        @error('ends_at') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $announcement->is_active) ? 'checked' : '' }} id="is_active"
                        class="w-5 h-5 rounded text-red-500 focus:ring-red-500 bg-gray-700 border-gray-600">
                    <label for="is_active" class="text-white">Active</label>
                </div>

                <div class="flex justify-end pt-6">
                    <button type="submit" class="btn btn-primary px-8">
                        Update Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection