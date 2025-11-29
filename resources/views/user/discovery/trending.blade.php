@extends('layouts.app')

@section('title', 'Trending Music')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold">Trending</h1>
            <div class="flex bg-white/10 rounded-lg p-1">
                @foreach($periods as $p)
                    <a href="{{ route('discovery.trending', ['period' => $p]) }}"
                        class="px-4 py-1.5 rounded-md text-sm font-bold capitalize transition {{ $period === $p ? 'bg-blue-600 text-white shadow' : 'text-gray-400 hover:text-white' }}">
                        {{ str_replace('_', ' ', $p) }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="bg-white/5 rounded-2xl border border-white/10 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-white/5 text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="px-6 py-4 w-16 text-center">#</th>
                        <th class="px-6 py-4">Track</th>
                        <th class="px-6 py-4">Artist</th>
                        <th class="px-6 py-4 text-center">Plays</th>
                        <th class="px-6 py-4 text-center">Duration</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($tracks as $index => $track)
                        <tr class="hover:bg-white/5 transition group">
                            <td class="px-6 py-4 text-center text-gray-500 font-bold">{{ $index + 1 }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="relative w-10 h-10 flex-shrink-0">
                                        <img src="{{ $track->cover_image }}" class="w-full h-full rounded object-cover">
                                        <button
                                            class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                            <i class="fas fa-play text-white text-xs"></i>
                                        </button>
                                    </div>
                                    <span
                                        class="font-bold text-white group-hover:text-blue-400 transition cursor-pointer">{{ $track->title }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-400">{{ $track->user->name }}</td>
                            <td class="px-6 py-4 text-center text-gray-400">{{ number_format($track->plays) }}</td>
                            <td class="px-6 py-4 text-center text-gray-400">{{ gmdate("i:s", $track->duration) }}</td>
                            <td class="px-6 py-4 text-right">
                                <button class="text-gray-500 hover:text-red-500 mr-3"><i class="far fa-heart"></i></button>
                                <button class="text-gray-500 hover:text-white"><i class="fas fa-ellipsis-h"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection