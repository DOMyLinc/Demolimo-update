@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <a href="{{ route('zipcodes.show', $zipcode) }}"
                class="text-blue-400 hover:text-blue-300 mb-4 inline-block">&larr; Back to Zipcode</a>
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-4xl font-bold text-white mb-2">{{ $group->name }}</h1>
                    <p class="text-gray-400 mb-4">{{ $group->description }}</p>
                    <div class="flex items-center text-sm text-gray-500">
                        <span class="mr-4">Created by {{ $group->creator->name }}</span>
                        <span>{{ $group->members->count() }} Members</span>
                        @if($group->is_private)
                            <span class="ml-4 px-2 py-1 bg-red-900 text-red-300 rounded text-xs">Private</span>
                        @else
                            <span class="ml-4 px-2 py-1 bg-green-900 text-green-300 rounded text-xs">Public</span>
                        @endif
                    </div>
                </div>

                <div>
                    @if($group->members->contains(auth()->user()))
                        <form action="{{ route('zipcodes.groups.leave', [$zipcode, $group]) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                                Leave Group
                            </button>
                        </form>
                    @else
                        <form action="{{ route('zipcodes.groups.join', [$zipcode, $group]) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                                Join Group
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Feed Area -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Post Creation (Placeholder) -->
                @if($group->members->contains(auth()->user()))
                    <div class="bg-gray-800 rounded-lg p-6 shadow-lg">
                        <textarea
                            class="w-full bg-gray-700 text-white rounded p-3 mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Write something..."></textarea>
                        <div class="flex justify-end">
                            <button
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                                Post
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Posts (Placeholder) -->
                <div class="bg-gray-800 rounded-lg p-6 shadow-lg text-center text-gray-500 py-12">
                    No posts yet. Be the first to share something!
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Members List -->
                <div class="bg-gray-800 rounded-lg p-6 shadow-lg">
                    <h3 class="text-xl font-bold text-white mb-4">Members</h3>
                    <div class="space-y-3">
                        @foreach($group->members->take(5) as $member)
                            <div class="flex items-center">
                                <div
                                    class="w-8 h-8 bg-gray-600 rounded-full mr-3 flex items-center justify-center text-xs font-bold text-white">
                                    {{ substr($member->name, 0, 1) }}
                                </div>
                                <span class="text-gray-300">{{ $member->name }}</span>
                                @if($member->pivot->role === 'admin')
                                    <span class="ml-auto text-xs bg-blue-900 text-blue-300 px-2 py-1 rounded">Admin</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @if($group->members->count() > 5)
                        <div class="mt-4 text-center">
                            <a href="#" class="text-blue-400 hover:text-blue-300 text-sm">View all members</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection