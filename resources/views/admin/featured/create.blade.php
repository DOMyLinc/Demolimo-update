@extends('layouts.admin')

@section('page-title', 'Feature Content')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="card">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-white">Feature Content</h2>
                <a href="{{ route('admin.featured.index') }}" class="text-gray-400 hover:text-white transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
            </div>

            <form action="{{ route('admin.featured.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Content
                        Type</label>
                    <select name="type" id="type" required
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 outline-none transition">
                        <option value="">Select Type</option>
                        <option value="track">Track</option>
                        <option value="album">Album</option>
                        <option value="artist">Artist</option>
                    </select>
                    @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Content ID</label>
                    <input type="number" name="id" required
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 outline-none transition"
                        placeholder="Enter the ID of the track/album/artist">
                    @error('id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    <p class="text-xs text-gray-500 mt-1">You can find the ID in the URL when viewing the content</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Expires At
                        (Optional)</label>
                    <input type="datetime-local" name="expires_at"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 outline-none transition">
                    @error('expires_at') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end pt-6">
                    <button type="submit" class="btn btn-primary px-8">
                        Feature Content
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection