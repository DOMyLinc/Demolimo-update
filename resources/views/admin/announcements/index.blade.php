@extends('layouts.admin')

@section('page-title', 'Announcements')

@section('content')
    <div class="card">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-white">Site Announcements</h2>
            <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i> New Announcement
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-gray-400 border-b border-white/10">
                        <th class="pb-3 pl-4">Title</th>
                        <th class="pb-3">Type</th>
                        <th class="pb-3">Status</th>
                        <th class="pb-3">Schedule</th>
                        <th class="pb-3 pr-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-300">
                    @forelse($announcements as $announcement)
                        <tr class="border-b border-white/5 hover:bg-white/5 transition">
                            <td class="py-4 pl-4">
                                <div class="font-medium text-white">{{ $announcement->title }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ Str::limit($announcement->message, 60) }}</div>
                            </td>
                            <td class="py-4">
                                @php
                                    $colors = [
                                        'info' => 'blue',
                                        'warning' => 'yellow',
                                        'success' => 'green',
                                        'danger' => 'red'
                                    ];
                                    $color = $colors[$announcement->type] ?? 'gray';
                                @endphp
                                <span
                                    class="px-2 py-1 rounded text-xs bg-{{ $color }}-500/20 text-{{ $color }}-400 border border-{{ $color }}-500/30">
                                    {{ ucfirst($announcement->type) }}
                                </span>
                            </td>
                            <td class="py-4">
                                @if($announcement->isCurrentlyActive())
                                    <span
                                        class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-400 border border-green-500/30">Active</span>
                                @else
                                    <span
                                        class="px-2 py-1 rounded text-xs bg-gray-500/20 text-gray-400 border border-gray-500/30">Inactive</span>
                                @endif
                            </td>
                            <td class="py-4 text-sm">
                                @if($announcement->starts_at)
                                    <div>From: {{ $announcement->starts_at->format('M d, Y') }}</div>
                                @endif
                                @if($announcement->ends_at)
                                    <div>Until: {{ $announcement->ends_at->format('M d, Y') }}</div>
                                @endif
                                @if(!$announcement->starts_at && !$announcement->ends_at)
                                    <span class="text-gray-500">Always</span>
                                @endif
                            </td>
                            <td class="py-4 pr-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <form action="{{ route('admin.announcements.toggle', $announcement->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="p-2 rounded hover:bg-white/10 transition"
                                            title="{{ $announcement->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i
                                                class="fas fa-{{ $announcement->is_active ? 'eye-slash' : 'eye' }} text-{{ $announcement->is_active ? 'yellow' : 'green' }}-400"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.announcements.edit', $announcement->id) }}"
                                        class="p-2 rounded hover:bg-white/10 text-blue-400 transition">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.announcements.destroy', $announcement->id) }}" method="POST"
                                        onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded hover:bg-white/10 text-red-400 transition">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-500">
                                No announcements found. Create one to get started!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $announcements->links() }}
        </div>
    </div>
@endsection