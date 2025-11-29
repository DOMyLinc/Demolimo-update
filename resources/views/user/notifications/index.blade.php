@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold">Notifications</h1>
            @if($notifications->total() > 0)
                <div class="flex gap-3">
                    <form action="{{ route('notifications.readAll') }}" method="POST">
                        @csrf
                        <button class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-full font-bold transition">
                            <i class="fas fa-check-double mr-2"></i> Mark All Read
                        </button>
                    </form>
                    <form action="{{ route('notifications.deleteAll') }}" method="POST"
                        onsubmit="return confirm('Delete all notifications?')">
                        @csrf
                        @method('DELETE')
                        <button
                            class="px-4 py-2 bg-red-600/20 hover:bg-red-600/30 text-red-400 rounded-full font-bold transition">
                            <i class="fas fa-trash mr-2"></i> Clear All
                        </button>
                    </form>
                </div>
            @endif
        </div>

        @if($notifications->isEmpty())
            <div class="text-center py-20 bg-white/5 rounded-2xl border border-white/10">
                <i class="fas fa-bell-slash text-4xl text-gray-500 mb-4"></i>
                <h3 class="text-xl font-bold mb-2">No notifications</h3>
                <p class="text-gray-400">You're all caught up!</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($notifications as $notification)
                    <div
                        class="bg-white/5 rounded-xl p-4 border border-white/10 {{ is_null($notification->read_at) ? 'bg-purple-600/10 border-purple-500/30' : '' }} flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-purple-600/20 flex items-center justify-center flex-shrink-0">
                            @if($notification->type === 'like')
                                <i class="fas fa-heart text-red-400"></i>
                            @elseif($notification->type === 'comment')
                                <i class="fas fa-comment text-blue-400"></i>
                            @elseif($notification->type === 'follow')
                                <i class="fas fa-user-plus text-green-400"></i>
                            @elseif($notification->type === 'purchase')
                                <i class="fas fa-shopping-cart text-yellow-400"></i>
                            @else
                                <i class="fas fa-bell text-purple-400"></i>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-white">{{ $notification->message }}</p>
                            <p class="text-sm text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            @if(is_null($notification->read_at))
                                <button onclick="markAsRead({{ $notification->id }})"
                                    class="px-3 py-1 bg-purple-600 hover:bg-purple-500 rounded-full text-sm font-bold transition">
                                    <i class="fas fa-check"></i>
                                </button>
                            @endif
                            <button onclick="deleteNotification({{ $notification->id }})"
                                class="px-3 py-1 bg-red-600/20 hover:bg-red-600/30 text-red-400 rounded-full text-sm font-bold transition">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            function markAsRead(id) {
                fetch(`/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                }).then(() => location.reload());
            }

            function deleteNotification(id) {
                if (confirm('Delete this notification?')) {
                    fetch(`/notifications/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    }).then(() => location.reload());
                }
            }
        </script>
    @endpush
@endsection