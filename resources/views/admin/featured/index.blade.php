@extends('layouts.admin')

@section('page-title', 'Featured Content')

@section('content')
    <div class="card">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-white">Featured Content</h2>
            <a href="{{ route('admin.featured.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i> Feature Content
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-gray-400 border-b border-white/10">
                        <th class="pb-3 pl-4">Position</th>
                        <th class="pb-3">Type</th>
                        <th class="pb-3">Title</th>
                        <th class="pb-3">Expires</th>
                        <th class="pb-3 pr-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-300">
                    @forelse($featured as $item)
                        <tr class="border-b border-white/5 hover:bg-white/5 transition">
                            <td class="py-4 pl-4">
                                <span
                                    class="px-2 py-1 rounded text-xs bg-white/10 text-white font-bold">#{{ $item->position }}</span>
                            </td>
                            <td class="py-4">
                                <span
                                    class="px-2 py-1 rounded text-xs bg-purple-500/20 text-purple-400 border border-purple-500/30">
                                    {{ class_basename($item->featurable_type) }}
                                </span>
                            </td>
                            <td class="py-4">
                                <div class="font-medium text-white">
                                    {{ $item->featurable->title ?? $item->featurable->name ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="py-4 text-sm">
                                @if($item->expires_at)
                                    {{ $item->expires_at->format('M d, Y') }}
                                @else
                                    <span class="text-gray-500">Never</span>
                                @endif
                            </td>
                            <td class="py-4 pr-4 text-right">
                                <form action="{{ route('admin.featured.destroy', $item->id) }}" method="POST"
                                    onsubmit="return confirm('Remove from featured?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded hover:bg-white/10 text-red-400 transition">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-500">
                                No featured content yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $featured->links() }}
        </div>
    </div>
@endsection