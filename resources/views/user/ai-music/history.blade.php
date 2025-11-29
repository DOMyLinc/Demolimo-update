@extends('layouts.app')

@section('title', 'Generation History')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold">Generation History</h1>
        <a href="{{ route('ai-music.index') }}" class="px-6 py-2 bg-purple-600 text-white rounded-full font-bold hover:bg-purple-500 transition">
            <i class="fas fa-plus mr-2"></i> New Generation
        </a>
    </div>

    @if($generations->isEmpty())
        <div class="text-center py-20 bg-white/5 rounded-2xl border border-white/10">
            <i class="fas fa-music text-4xl text-gray-500 mb-4"></i>
            <h3 class="text-xl font-bold mb-2">No generations yet</h3>
            <p class="text-gray-400 mb-6">Start creating music with AI today!</p>
            <a href="{{ route('ai-music.index') }}" class="text-purple-400 hover:text-purple-300 font-bold">Try Generator</a>
        </div>
    @else
        <div class="bg-white/5 rounded-2xl border border-white/10 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-white/5 text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Prompt</th>
                        <th class="px-6 py-4">Model</th>
                        <th class="px-6 py-4 text-center">Duration</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($generations as $gen)
                        <tr class="hover:bg-white/5 transition">
                            <td class="px-6 py-4 text-gray-400 text-sm">{{ $gen->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-4">
                                <p class="font-bold text-white truncate max-w-xs" title="{{ $gen->prompt }}">{{ $gen->prompt }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs bg-white/10 px-2 py-1 rounded">{{ $gen->model->name }}</span>
                            </td>
                            <td class="px-6 py-4 text-center text-gray-400">{{ $gen->duration }}s</td>
                            <td class="px-6 py-4 text-center">
                                @if($gen->status === 'completed')
                                    <span class="text-green-400 font-bold text-xs uppercase">Ready</span>
                                @elseif($gen->status === 'failed')
                                    <span class="text-red-400 font-bold text-xs uppercase">Failed</span>
                                @else
                                    <span class="text-yellow-400 font-bold text-xs uppercase">Processing</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($gen->status === 'completed')
                                    <button class="text-white hover:text-purple-400 mr-3" title="Play">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <a href="#" class="text-gray-400 hover:text-white" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-8">
            {{ $generations->links() }}
        </div>
    @endif
</div>
@endsection
