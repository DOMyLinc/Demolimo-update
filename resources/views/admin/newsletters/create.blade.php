@extends('layouts.admin')

@section('page-title', 'Create Newsletter')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="card">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-white">Create Newsletter</h2>
                <a href="{{ route('admin.newsletters.index') }}" class="text-gray-400 hover:text-white transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
            </div>

            <form action="{{ route('admin.newsletters.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Subject</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 outline-none transition">
                    @error('subject') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Content</label>
                    <textarea name="content" rows="12" required
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 outline-none transition">{{ old('content') }}</textarea>
                    @error('content') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    <p class="text-xs text-gray-500 mt-1">You can use HTML for formatting</p>
                </div>

                <div class="flex justify-end pt-6">
                    <button type="submit" class="btn btn-primary px-8">
                        Create Newsletter
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection