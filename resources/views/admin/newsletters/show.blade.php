@extends('layouts.admin')

@section('page-title', 'Newsletter Details')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="card">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-white">{{ $newsletter->subject }}</h2>
                <a href="{{ route('admin.newsletters.index') }}" class="text-gray-400 hover:text-white transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
            </div>

            <div class="space-y-6">
                <!-- Status -->
                <div class="flex items-center gap-4 p-4 bg-white/5 rounded-lg">
                    <div>
                        <div class="text-xs text-gray-400 uppercase tracking-wider">Status</div>
                        <div class="mt-1">
                            @if($newsletter->isSent())
                                <span
                                    class="px-3 py-1 rounded text-sm bg-green-500/20 text-green-400 border border-green-500/30">
                                    <i class="fas fa-check-circle mr-1"></i> Sent
                                </span>
                            @else
                                <span
                                    class="px-3 py-1 rounded text-sm bg-yellow-500/20 text-yellow-400 border border-yellow-500/30">
                                    <i class="fas fa-clock mr-1"></i> Draft
                                </span>
                            @endif
                        </div>
                    </div>
                    @if($newsletter->sent_at)
                        <div class="ml-auto">
                            <div class="text-xs text-gray-400 uppercase tracking-wider">Sent At</div>
                            <div class="mt-1 text-white">{{ $newsletter->sent_at->format('M d, Y H:i') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 uppercase tracking-wider">Recipients</div>
                            <div class="mt-1 text-white font-bold">{{ number_format($newsletter->recipient_count) }}</div>
                        </div>
                    @endif
                </div>

                <!-- Content Preview -->
                <div class="p-6 bg-white/5 rounded-xl border border-white/10">
                    <h3 class="text-lg font-bold text-white mb-4">Content Preview</h3>
                    <div class="prose prose-invert max-w-none">
                        {!! nl2br(e($newsletter->content)) !!}
                    </div>
                </div>

                <!-- Actions -->
                @if(!$newsletter->isSent())
                    <div class="flex justify-end gap-3 pt-6 border-t border-white/10">
                        <form action="{{ route('admin.newsletters.destroy', $newsletter->id) }}" method="POST"
                            onsubmit="return confirm('Delete this newsletter?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold transition">
                                <i class="fas fa-trash mr-2"></i> Delete
                            </button>
                        </form>
                        <form action="{{ route('admin.newsletters.send', $newsletter->id) }}" method="POST"
                            onsubmit="return confirm('Send this newsletter to all verified users? This action cannot be undone.');">
                            @csrf
                            <button type="submit"
                                class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-bold transition">
                                <i class="fas fa-paper-plane mr-2"></i> Send Newsletter
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection