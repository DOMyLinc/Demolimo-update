@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <a href="{{ route('zipcodes.show', $zipcode) }}"
                class="text-blue-400 hover:text-blue-300 mb-4 inline-block">&larr; Back to Zipcode</a>
            <h1 class="text-3xl font-bold text-white">Create Group in {{ $zipcode->code }}</h1>
        </div>

        <div class="bg-gray-800 rounded-lg shadow-lg p-6 max-w-2xl mx-auto">
            <form action="{{ route('zipcodes.groups.store', $zipcode) }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label for="name" class="block text-gray-300 text-sm font-bold mb-2">Group Name</label>
                    <input type="text" name="name" id="name"
                        class="w-full bg-gray-700 text-white border border-gray-600 rounded py-2 px-3 focus:outline-none focus:border-blue-500"
                        required>
                </div>

                <div class="mb-6">
                    <label for="description" class="block text-gray-300 text-sm font-bold mb-2">Description</label>
                    <textarea name="description" id="description" rows="4"
                        class="w-full bg-gray-700 text-white border border-gray-600 rounded py-2 px-3 focus:outline-none focus:border-blue-500"></textarea>
                </div>

                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_private"
                            class="form-checkbox h-5 w-5 text-blue-600 bg-gray-700 border-gray-600 rounded">
                        <span class="ml-2 text-gray-300">Private Group</span>
                    </label>
                    <p class="text-gray-500 text-xs mt-1">Private groups are invite-only and content is hidden from
                        non-members.</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                        Create Group
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection