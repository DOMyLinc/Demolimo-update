@extends('layouts.admin')

@section('title', 'Language Manager')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">🌍 Language Manager</h1>
                <p class="text-gray-400">Manage global languages and translations</p>
            </div>
            <button onclick="document.getElementById('addLanguageModal').classList.remove('hidden')"
                class="px-6 py-2 bg-purple-600 hover:bg-purple-700 rounded-lg text-white font-bold transition">
                <i class="fas fa-plus mr-2"></i> Add Language
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($languages as $language)
                <div
                    class="bg-white/5 border border-white/10 rounded-xl p-6 hover:border-purple-500/50 transition relative overflow-hidden">
                    @if($language->is_default)
                        <div class="absolute top-0 right-0 bg-purple-600 text-xs text-white px-3 py-1 rounded-bl-lg font-bold">
                            DEFAULT
                        </div>
                    @endif

                    <div class="flex items-center gap-4 mb-4">
                        <span class="text-4xl">{{ $language->flag ?? '🏳️' }}</span>
                        <div>
                            <h3 class="text-xl font-bold text-white">{{ $language->name }}</h3>
                            <p class="text-gray-400 text-sm">{{ strtoupper($language->code) }} •
                                {{ $language->is_rtl ? 'RTL' : 'LTR' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm text-gray-500 mb-6">
                        <span><i class="fas fa-language mr-2"></i> {{ $language->translations_count }} Strings</span>
                        <span class="{{ $language->is_active ? 'text-green-500' : 'text-red-500' }}">
                            <i class="fas fa-circle text-[10px] mr-1"></i> {{ $language->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('admin.languages.translations', $language) }}"
                            class="flex-1 text-center px-4 py-2 bg-white/10 hover:bg-white/20 rounded-lg text-white transition">
                            <i class="fas fa-edit mr-2"></i> Translate
                        </a>

                        @if(!$language->is_default)
                            <form action="{{ route('admin.languages.default', $language) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="px-3 py-2 bg-white/5 hover:bg-purple-600/20 text-gray-400 hover:text-purple-400 rounded-lg transition"
                                    title="Make Default">
                                    <i class="fas fa-star"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.languages.toggle', $language) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="px-3 py-2 bg-white/5 hover:bg-red-600/20 text-gray-400 hover:text-red-400 rounded-lg transition"
                                    title="Toggle Active">
                                    <i class="fas fa-power-off"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Add Language Modal -->
    <div id="addLanguageModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-gray-900 rounded-xl p-8 max-w-md w-full mx-4 border border-white/10">
            <h3 class="text-xl font-bold text-white mb-6">Add New Language</h3>

            <form action="{{ route('admin.languages.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Name</label>
                    <input type="text" name="name" placeholder="e.g. Spanish" required
                        class="w-full bg-black/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-purple-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Code (ISO 639-1)</label>
                    <input type="text" name="code" placeholder="e.g. es" required maxlength="5"
                        class="w-full bg-black/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-purple-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Flag Emoji</label>
                    <input type="text" name="flag" placeholder="e.g. 🇪🇸"
                        class="w-full bg-black/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-purple-500 outline-none">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_rtl" value="1" id="is_rtl"
                        class="rounded bg-gray-700 border-gray-600 text-purple-600">
                    <label for="is_rtl" class="text-gray-300">Right-to-Left (RTL)</label>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('addLanguageModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 bg-gray-800 hover:bg-gray-700 rounded-lg text-white transition">Cancel</button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 rounded-lg text-white font-bold transition">Create</button>
                </div>
            </form>
        </div>
    </div>
@endsection