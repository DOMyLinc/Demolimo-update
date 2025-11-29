@extends('layouts.admin')

@section('page-title', 'Manage Admins')

@section('content')
    <div class="card">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-white">Administrators & Moderators</h2>
            <a href="{{ route('admin.admins.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i> Add New Admin
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-gray-400 border-b border-white/10">
                        <th class="pb-3 pl-4">Name</th>
                        <th class="pb-3">Email</th>
                        <th class="pb-3">Role</th>
                        <th class="pb-3">Permissions</th>
                        <th class="pb-3">Created</th>
                        <th class="pb-3 pr-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-300">
                    @foreach($admins as $admin)
                        <tr class="border-b border-white/5 hover:bg-white/5 transition">
                            <td class="py-4 pl-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-gradient-to-br from-red-500 to-orange-500 flex items-center justify-center text-white font-bold text-xs">
                                        {{ strtoupper(substr($admin->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-white">{{ $admin->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $admin->username }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4">{{ $admin->email }}</td>
                            <td class="py-4">
                                @if($admin->id === 1)
                                    <span
                                        class="px-2 py-1 rounded text-xs bg-red-500/20 text-red-400 border border-red-500/30">Super
                                        Admin</span>
                                @else
                                    <span
                                        class="px-2 py-1 rounded text-xs bg-blue-500/20 text-blue-400 border border-blue-500/30">Admin</span>
                                @endif
                            </td>
                            <td class="py-4">
                                @if($admin->id === 1)
                                    <span class="text-xs text-gray-500">All Permissions</span>
                                @elseif(empty($admin->permissions))
                                    <span class="text-xs text-gray-500">None</span>
                                @else
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($admin->permissions as $perm)
                                            <span
                                                class="px-1.5 py-0.5 rounded text-[10px] bg-white/10 text-gray-300">{{ str_replace('_', ' ', $perm) }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 text-sm text-gray-500">{{ $admin->created_at->format('M d, Y') }}</td>
                            <td class="py-4 pr-4 text-right">
                                @if($admin->id !== 1 && $admin->id !== auth()->id())
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.admins.edit', $admin->id) }}"
                                            class="p-2 rounded hover:bg-white/10 text-blue-400 transition" title="Edit Permissions">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded hover:bg-white/10 text-red-400 transition"
                                                title="Remove Admin">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection