@extends('layouts.admin')

@section('page-title', 'Update Details')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('admin.updates.index') }}" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-1"></i> Back to Updates
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="md:col-span-2 space-y-6">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">{{ $update->name }} <span
                                class="text-gray-500 text-sm ml-2">v{{ $update->version }}</span></h2>
                    </div>
                    <div class="card-body">
                        <div class="mb-6">
                            <h3 class="text-sm font-bold text-gray-700 uppercase mb-2">Description</h3>
                            <p class="text-gray-600">{{ $update->description ?: 'No description provided.' }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-bold text-gray-700 uppercase mb-2">Changelog</h3>
                            <div class="bg-gray-50 p-4 rounded-md text-sm font-mono whitespace-pre-wrap">
                                {{ $update->changelog ?: 'No changelog available.' }}</div>
                        </div>
                    </div>
                </div>

                @if($update->status === 'completed' && $update->files_modified)
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Modified Files</h2>
                        </div>
                        <div class="card-body">
                            <ul class="list-disc list-inside text-sm text-gray-600 max-h-60 overflow-y-auto">
                                @foreach($update->files_modified as $file)
                                    <li>{{ $file }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar Actions -->
            <div class="space-y-6">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Status</h2>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <span class="block text-center px-4 py-2 rounded text-sm font-bold w-full
                                @if($update->status === 'completed') bg-green-100 text-green-800
                                @elseif($update->status === 'failed') bg-red-100 text-red-800
                                @elseif($update->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($update->status === 'installing') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($update->status) }}
                            </span>
                        </div>

                        @if($update->status === 'pending')
                            <form action="{{ route('admin.updates.apply', $update) }}" method="POST"
                                onsubmit="return confirm('Are you sure? This will put the site in maintenance mode.');">
                                @csrf
                                <button type="submit" class="btn btn-success w-full mb-2">
                                    <i class="fas fa-play mr-2"></i> Install Update
                                </button>
                            </form>
                        @endif

                        @if($update->status === 'completed' && $update->backup_path)
                            <div class="border-t pt-4 mt-4">
                                <h4 class="text-sm font-bold text-gray-700 mb-2">Rollback</h4>
                                <p class="text-xs text-gray-500 mb-3">Restore the system to the state before this update.</p>
                                <button type="button" class="btn btn-outline-danger w-full" disabled
                                    title="Rollback via CLI recommended for safety">
                                    <i class="fas fa-undo mr-2"></i> Rollback
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Details</h2>
                    </div>
                    <div class="card-body text-sm">
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-500">Uploaded</span>
                            <span class="font-medium">{{ $update->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-500">Size</span>
                            <span
                                class="font-medium">{{ number_format(Storage::disk('local')->size($update->file_path) / 1024 / 1024, 2) }}
                                MB</span>
                        </div>
                        @if($update->installed_at)
                            <div class="flex justify-between py-2 border-b">
                                <span class="text-gray-500">Installed</span>
                                <span class="font-medium">{{ $update->installed_at->format('M d, Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection