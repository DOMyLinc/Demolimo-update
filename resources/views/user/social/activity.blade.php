@extends('layouts.app')

@section('title', 'My Activity')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">My Activity</h1>

        <div class="max-w-3xl mx-auto">
            @if($activities->isEmpty())
                <div class="text-center py-20 bg-white/5 rounded-2xl border border-white/10">
                    <i class="fas fa-history text-4xl text-gray-500 mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">No recent activity</h3>
                    <p class="text-gray-400">Your recent actions and notifications will appear here.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($activities as $activity)
                        <div class="bg-white/5 rounded-xl p-4 border border-white/10 flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-xl">
                                {{ $activity['icon'] }}
                            </div>
                            <div class="flex-1">
                                <p class="text-white">{{ $activity['message'] }}</p>
                                <p class="text-xs text-gray-400">{{ $activity['time']->diffForHumans() }}</p>
                            </div>
                            @if($activity['type'] === 'upload')
                                <a href="{{ route('tracks.show', $activity['data']->id) }}"
                                    class="text-blue-400 hover:text-blue-300 text-sm font-bold">View Track</a>
                            @elseif($activity['type'] === 'follower')
                                <a href="{{ route('users.show', $activity['data']->id) }}"
                                    class="text-blue-400 hover:text-blue-300 text-sm font-bold">View Profile</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection