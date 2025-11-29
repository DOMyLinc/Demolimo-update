@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-white">Track Trials</h1>
            <div>
                <a href="{{ route('song_battles.hall_of_fame') }}"
                    class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded mr-4 transition duration-300 shadow-lg flex items-center inline-flex">
                    <span class="mr-2">🏆</span> Hall of Fame
                </a>
                <a href="{{ route('song_battles.create') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-300 shadow-lg">
                    Create New Trial
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-500 text-white p-4 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($battles as $battle)
                <div
                    class="bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-700 hover:border-blue-500 transition duration-300">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-white mb-2 truncate">{{ $battle->title }}</h2>
                        <p class="text-gray-400 text-sm mb-4 line-clamp-2">{{ $battle->description }}</p>

                        <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
                            <span>By {{ $battle->user->name }}</span>
                            <span>{{ $battle->created_at->diffForHumans() }}</span>
                        </div>

                        <div class="flex justify-between items-center border-t border-gray-700 pt-4">
                            <div class="text-blue-400">
                                <span class="font-bold">{{ $battle->versions->sum('votes_count') }}</span> Votes
                            </div>
                            <a href="{{ route('song_battles.show', $battle) }}"
                                class="text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-sm transition duration-300">
                                View Battle
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $battles->links() }}
        </div>
    </div>
@endsection