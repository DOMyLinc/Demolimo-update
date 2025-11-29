@extends('layouts.app')

@section('title', 'Fan Clubs')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold mb-2">Fan Clubs</h1>
            <p class="text-gray-400">Join exclusive communities and get access to premium content from your favorite artists.</p>
        </div>
        <a href="{{ route('fan-clubs.create') }}" class="px-6 py-2 bg-pink-600 hover:bg-pink-500 text-white rounded-full font-bold transition">
            <i class="fas fa-plus mr-2"></i> Create Fan Club
        </a>
    </div>

    @if($fanClubs->isEmpty())
        <div class="text-center py-20 bg-white/5 rounded-2xl border border-white/10">
            <i class="fas fa-users text-4xl text-gray-500 mb-4"></i>
            <h3 class="text-xl font-bold mb-2">No fan clubs yet</h3>
            <p class="text-gray-400">Be the first to create a fan club!</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($fanClubs as $fanClub)
                <a href="{{ route('fan-clubs.show', $fanClub) }}" class="bg-white/5 rounded-2xl border border-white/10 overflow-hidden hover:bg-white/10 transition group">
                    <div class="aspect-video bg-gradient-to-br from-pink-900 to-purple-900 relative">
                        @if($fanClub->cover_image)
                            <img src="{{ Storage::url($fanClub->cover_image) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fas fa-heart text-6xl text-white/20"></i>
                            </div>
                        @endif
                        <div class="absolute bottom-3 left-3 flex items-center gap-2">
                            <img src="{{ $fanClub->artist->avatar_url ?? 'https://via.placeholder.com/40' }}" class="w-10 h-10 rounded-full border-2 border-white">
                            <div>
                                <p class="text-white font-bold text-sm">{{ $fanClub->artist->name }}</p>
                                <p class="text-white/80 text-xs"><i class="fas fa-users mr-1"></i> {{ number_format($fanClub->members_count) }} members</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-lg truncate group-hover:text-pink-400 transition mb-2">{{ $fanClub->name }}</h3>
                        <p class="text-sm text-gray-400 line-clamp-2 mb-3">{{ $fanClub->description }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-green-400 font-bold">${{ number_format($fanClub->monthly_price, 2) }}/mo</span>
                            <span class="text-xs bg-pink-600/20 text-pink-400 px-3 py-1 rounded-full font-bold">Join Now</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-8">
            {{ $fanClubs->links() }}
        </div>
    @endif
</div>
@endsection
