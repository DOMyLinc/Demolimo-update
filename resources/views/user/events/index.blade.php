@extends('layouts.app')

@section('title', 'Events')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold mb-2">Events</h1>
            <p class="text-gray-400">Discover live music events, concerts, and festivals near you.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('events.myEvents') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-full font-bold transition">
                <i class="fas fa-calendar-alt mr-2"></i> My Events
            </a>
            <a href="{{ route('events.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-full font-bold transition">
                <i class="fas fa-plus mr-2"></i> Create Event
            </a>
        </div>
    </div>

    <!-- Featured Events -->
    @if($featuredEvents->isNotEmpty())
        <section class="mb-12">
            <h2 class="text-2xl font-bold mb-6">Featured Events</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($featuredEvents as $event)
                    <a href="{{ route('events.show', $event) }}" class="group">
                        <div class="relative aspect-video mb-3 rounded-xl overflow-hidden bg-white/5 shadow-lg">
                            @if($event->cover_image)
                                <img src="{{ Storage::url($event->cover_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-blue-700 to-purple-700 flex items-center justify-center">
                                    <i class="fas fa-calendar-star text-6xl text-white/20"></i>
                                </div>
                            @endif
                            <div class="absolute top-3 left-3 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full">
                                FEATURED
                            </div>
                        </div>
                        <div class="mb-2">
                            <h3 class="font-bold text-lg truncate group-hover:text-blue-400 transition">{{ $event->title }}</h3>
                            <p class="text-sm text-gray-400">{{ $event->venue }}</p>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-400"><i class="far fa-calendar mr-2"></i> {{ $event->start_date->format('M d, Y') }}</span>
                            <span class="text-green-400 font-bold">From ${{ number_format($event->ticketTypes->min('price'), 2) }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Upcoming Events -->
    <section>
        <h2 class="text-2xl font-bold mb-6">Upcoming Events</h2>
        @if($upcomingEvents->isEmpty())
            <div class="text-center py-20 bg-white/5 rounded-2xl border border-white/10">
                <i class="fas fa-calendar-times text-4xl text-gray-500 mb-4"></i>
                <h3 class="text-xl font-bold mb-2">No upcoming events</h3>
                <p class="text-gray-400">Check back later or create your own event!</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($upcomingEvents as $event)
                    <a href="{{ route('events.show', $event) }}" class="bg-white/5 rounded-xl border border-white/10 overflow-hidden hover:bg-white/10 transition group">
                        <div class="aspect-video bg-gray-900 relative">
                            @if($event->cover_image)
                                <img src="{{ Storage::url($event->cover_image) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-gray-700 to-gray-900 flex items-center justify-center">
                                    <i class="fas fa-music text-4xl text-white/20"></i>
                                </div>
                            @endif
                            @if($event->is_online)
                                <div class="absolute top-2 right-2 bg-green-600 text-white text-xs font-bold px-2 py-1 rounded">ONLINE</div>
                            @endif
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold truncate group-hover:text-blue-400 transition mb-1">{{ $event->title }}</h3>
                            <p class="text-sm text-gray-400 truncate mb-3"><i class="fas fa-map-marker-alt mr-2"></i> {{ $event->city }}, {{ $event->country }}</p>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-400">{{ $event->start_date->format('M d, g:i A') }}</span>
                                @if($event->ticketTypes->isNotEmpty())
                                    <span class="text-green-400 font-bold">From ${{ number_format($event->ticketTypes->min('price'), 2) }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mt-8">
                {{ $upcomingEvents->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
