@extends('layouts.admin')

@section('page-title', 'System Updates')

@section('content')
    <div class="card">
        <div class="card-header flex justify-between items-center">
            <h2 class="card-title">System Updates</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.updates.create') }}" class="btn btn-primary">
                    <i class="fas fa-upload mr-2"></i> Upload Update
                </a>
            </div>
        </div>

        <div class="card-body">
            @if($updates->isEmpty())
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-history text-4xl mb-3"></i>
                    <p>No updates found.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th class="text-left">Version</th>
                                <th class="text-left">Name</th>
                                <th class="text-left">Status</th>
                                <th class="text-left">Installed At</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($updates as $update)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4 font-medium">{{ $update->version }}</td>
                                    <td class="py-3 px-4">{{ $update->name }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded text-xs font-bold
                                                    @if($update->status === 'completed') bg-green-100 text-green-800
                                                    @elseif($update->status === 'failed') bg-red-100 text-red-800
                                                    @elseif($update->status === 'pending') bg-yellow-100 text-yellow-800
                                                    @elseif($update->status === 'installing') bg-blue-100 text-blue-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($update->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $update->installed_at ? $update->installed_at->format('M d, Y H:i') : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('admin.updates.show', $update) }}" class="btn btn-sm btn-secondary mr-2">
                                            View
                                        </a>
                                        @if($update->status === 'pending')
                                            <form action="{{ route('admin.updates.apply', $update) }}" method="POST"
                                                class="inline-block"
                                                onsubmit="return confirm('Are you sure you want to install this update? This will put the site in maintenance mode.');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success mr-2">
                                                    Install
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('admin.updates.destroy', $update) }}" method="POST"
                                            class="inline-block" onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection