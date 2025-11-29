@extends('layouts.admin')

@section('page-title', 'Newsletters')

@section('content')
    <div class="card">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-white">Newsletters</h2>
            <a href="{{ route('admin.newsletters.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i> Create Newsletter
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-gray-400 border-b border-white/10">
                        <th class="pb-3 pl-4">Subject</th>
                        <th class="pb-3">Status</th>
                        <th class="pb-3">Recipients</th>
                        <th class="pb-3">Sent At</th>
                        <th class="pb-3 pr-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-300">
                    @forelse($newsletters as $newsletter)
                        <tr class="border-b border-white/5 hover:bg-white/5 transition">
                            <td class="py-4 pl-4">
                                <div class="font-medium text-white">{{ $newsletter->subject }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ Str::limit(strip_tags($newsletter->content), 60) }}
                                </div>
                            </td>
                            <td class="py-4">
                                @if($newsletter->isSent())
                                    <span
                                        class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-400 border border-green-500/30">Sent</span>
                                @else
                                    <span
                                        class="px-2 py-1 rounded text-xs bg-yellow-500/20 text-yellow-400 border border-yellow-500/30">Draft</span>
                                @endif
                            </td>
                            <td class="py-4">
                                {{ number_format($newsletter->recipient_count) }}
                            </td>
                            <td class="py-4 text-sm">
                                @if($newsletter->sent_at)
                                    {{ $newsletter->sent_at->format('M d, Y H:i') }}
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="py-4 pr-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.newsletters.show', $newsletter->id) }}"
                                        class="p-2 rounded hover:bg-white/10 text-blue-400 transition">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(!$newsletter->isSent())
                                        <form action="{{ route('admin.newsletters.destroy', $newsletter->id) }}" method="POST"
                                            onsubmit="return confirm('Delete this newsletter?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded hover:bg-white/10 text-red-400 transition">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-500">
                                No newsletters yet. Create one to get started!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $newsletters->links() }}
        </div>
    </div>
@endsection