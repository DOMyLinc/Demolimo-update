<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Landing Page Builder
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6 flex justify-between items-center">
                        <h3 class="text-lg font-bold">Page Blocks</h3>
                        <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Add New
                            Block</button>
                    </div>

                    <div class="space-y-4">
                        @forelse($blocks as $block)
                            <div class="border rounded p-4 bg-gray-50">
                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="font-bold uppercase">{{ $block->key }}</h4>
                                    <span class="text-xs bg-gray-200 px-2 py-1 rounded">Order: {{ $block->order }}</span>
                                </div>
                                <form action="{{ route('admin.landing.update', $block->key) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="grid grid-cols-1 gap-4">
                                        @foreach($block->content as $key => $value)
                                            <div>
                                                <label
                                                    class="block text-xs font-bold text-gray-500 uppercase">{{ $key }}</label>
                                                <input type="text" name="content[{{ $key }}]" value="{{ $value }}"
                                                    class="w-full border rounded px-2 py-1">
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="mt-4 flex justify-end gap-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="is_visible" value="1" {{ $block->is_visible ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm">Visible</span>
                                        </label>
                                        <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">Save
                                            Changes</button>
                                    </div>
                                </form>
                            </div>
                        @empty
                            <div class="text-center text-gray-500 py-8">
                                No blocks found. Run seeder or add manually.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>