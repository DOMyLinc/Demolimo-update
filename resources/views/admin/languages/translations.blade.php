@extends('layouts.admin')

@section('title', 'Translate ' . $language->name)

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('admin.languages.index') }}" class="text-gray-400 hover:text-white transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Translate {{ $language->name }} {{ $language->flag }}</h1>
                <p class="text-gray-400">Edit translations for the frontend and backend</p>
            </div>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-black/20 text-gray-400 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-4">Group / Key</th>
                            <th class="px-6 py-4">Translation</th>
                            <th class="px-6 py-4 w-20">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($translations as $translation)
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-6 py-4">
                                    <span
                                        class="text-purple-400 text-xs font-bold uppercase tracking-wider block mb-1">{{ $translation->group }}</span>
                                    <span class="text-white font-mono text-sm">{{ $translation->key }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <textarea id="trans-{{ $translation->id }}"
                                        class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-purple-500 outline-none transition resize-y"
                                        rows="2">{{ $translation->value }}</textarea>
                                </td>
                                <td class="px-6 py-4">
                                    <button onclick="saveTranslation({{ $translation->id }})"
                                        class="text-purple-500 hover:text-purple-400 transition p-2 rounded-lg hover:bg-purple-500/10"
                                        title="Save">
                                        <i class="fas fa-save text-xl"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-white/10">
                {{ $translations->links() }}
            </div>
        </div>
    </div>

    <script>
        function saveTranslation(id) {
            const value = document.getElementById(`trans-${id}`).value;
            const btn = event.currentTarget;
            const icon = btn.querySelector('i');

            // Loading state
            icon.className = 'fas fa-spinner fa-spin';

            fetch(`/admin/languages/translations/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ value: value })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        icon.className = 'fas fa-check text-green-500';
                        setTimeout(() => {
                            icon.className = 'fas fa-save text-xl';
                        }, 2000);
                    } else {
                        icon.className = 'fas fa-times text-red-500';
                    }
                })
                .catch(() => {
                    icon.className = 'fas fa-times text-red-500';
                });
        }
    </script>
@endsection