@extends('layouts.admin')

@section('title', 'General Settings')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">⚙️ General Settings</h1>
            <p class="text-gray-400">Manage site identity, footer, and SEO</p>
        </div>

        @if(session('success'))
            <div class="bg-green-500/20 border border-green-500 text-green-500 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.settings.general.update') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Site Identity -->
            <div class="bg-white/5 border border-white/10 rounded-xl p-6">
                <h2 class="text-xl font-bold text-white mb-4">Site Identity</h2>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Site Title</label>
                    <input type="text" name="site_title" value="{{ $settings['site_title'] }}"
                        class="w-full bg-black/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-purple-500 outline-none">
                </div>
            </div>

            <!-- Footer & Copyright -->
            <div class="bg-white/5 border border-white/10 rounded-xl p-6">
                <h2 class="text-xl font-bold text-white mb-4">Footer & Copyright</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Footer Text</label>
                        <input type="text" name="footer_text" value="{{ $settings['footer_text'] }}"
                            class="w-full bg-black/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-purple-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Copyright Text</label>
                        <input type="text" name="copyright_text" value="{{ $settings['copyright_text'] }}"
                            class="w-full bg-black/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-purple-500 outline-none">
                    </div>
                </div>
            </div>

            <!-- Social Media -->
            <div class="bg-white/5 border border-white/10 rounded-xl p-6">
                <h2 class="text-xl font-bold text-white mb-4">Social Media Links</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1"><i
                                class="fab fa-facebook text-blue-500 mr-1"></i> Facebook URL</label>
                        <input type="url" name="facebook_url" value="{{ $settings['facebook_url'] }}"
                            class="w-full bg-black/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-purple-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1"><i
                                class="fab fa-twitter text-blue-400 mr-1"></i> Twitter URL</label>
                        <input type="url" name="twitter_url" value="{{ $settings['twitter_url'] }}"
                            class="w-full bg-black/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-purple-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1"><i
                                class="fab fa-instagram text-pink-500 mr-1"></i> Instagram URL</label>
                        <input type="url" name="instagram_url" value="{{ $settings['instagram_url'] }}"
                            class="w-full bg-black/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-purple-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1"><i
                                class="fab fa-youtube text-red-500 mr-1"></i> YouTube URL</label>
                        <input type="url" name="youtube_url" value="{{ $settings['youtube_url'] }}"
                            class="w-full bg-black/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-purple-500 outline-none">
                    </div>
                </div>
            </div>

            <!-- SEO Settings -->
            <div class="bg-white/5 border border-white/10 rounded-xl p-6">
                <h2 class="text-xl font-bold text-white mb-4">SEO Settings</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Meta Description</label>
                        <textarea name="meta_description" rows="3"
                            class="w-full bg-black/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-purple-500 outline-none">{{ $settings['meta_description'] }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Meta Keywords (comma separated)</label>
                        <input type="text" name="meta_keywords" value="{{ $settings['meta_keywords'] }}"
                            class="w-full bg-black/50 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-purple-500 outline-none">
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="px-8 py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 rounded-lg text-white font-bold shadow-lg transition transform hover:scale-105">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
@endsection