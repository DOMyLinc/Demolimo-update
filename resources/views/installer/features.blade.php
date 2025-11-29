@extends('installer.layout', ['step' => 4])

@section('title', __('installer.features_setup') . ' - DemoLimo Installer')

@section('content')
    <div class="flex-1">
        <h2 class="text-2xl font-bold text-white mb-2">Platform Features</h2>
        <p class="text-gray-400 mb-6">Configure FFMPEG and enable the features you want</p>

        <form action="{{ route('installer.features.post') }}" method="POST">
            @csrf

            <!-- FFMPEG Setup -->
            <div class="mb-8 p-6 bg-white/5 rounded-xl border border-white/10">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center">
                    <i class="fas fa-waveform-lines text-red-500 mr-3"></i>
                    FFMPEG Audio Processing
                </h3>

                <div class="space-y-4">
                    <label
                        class="flex items-start gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                        <input type="checkbox" name="auto_download_ffmpeg" value="1" checked class="w-5 h-5 rounded mt-1">
                        <div class="flex-1">
                            <div class="font-medium text-white">Auto-Download FFMPEG (Recommended)</div>
                            <div class="text-sm text-gray-400 mt-1">
                                Automatically download and install FFMPEG for waveform generation and audio processing.
                                This may take 2-3 minutes.
                            </div>
                        </div>
                    </label>

                    <div class="text-center text-gray-500 text-sm">OR</div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Manual FFMPEG Path</label>
                        <input type="text" name="ffmpeg_path" value="{{ $suggestedPath }}"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white"
                            placeholder="Enter FFMPEG executable path">
                        <p class="text-xs text-gray-400 mt-2">
                            If you already have FFMPEG installed, enter the path here.
                            Download from <a href="https://ffmpeg.org/" target="_blank"
                                class="text-red-400 hover:underline">ffmpeg.org</a>
                        </p>
                    </div>

                    <label
                        class="flex items-center gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                        <input type="checkbox" name="waveform_generation" value="1" checked class="w-5 h-5 rounded">
                        <div>
                            <div class="font-medium text-white">Enable Waveform Generation</div>
                            <div class="text-xs text-gray-400">Automatically generate waveforms for uploaded tracks</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Feature Selection -->
            <div class="mb-8 p-6 bg-white/5 rounded-xl border border-white/10">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center">
                    <i class="fas fa-toggle-on text-red-500 mr-3"></i>
                    Platform Features
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label
                        class="flex items-start gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                        <input type="checkbox" name="chat_enabled" value="1" checked class="w-5 h-5 rounded mt-1">
                        <div>
                            <div class="font-medium text-white">💬 Direct Messaging</div>
                            <div class="text-xs text-gray-400">User-to-user chat system</div>
                        </div>
                    </label>

                    <label
                        class="flex items-start gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                        <input type="checkbox" name="blog_enabled" value="1" checked class="w-5 h-5 rounded mt-1">
                        <div>
                            <div class="font-medium text-white">📝 Blog & Articles</div>
                            <div class="text-xs text-gray-400">Content management system</div>
                        </div>
                    </label>

                    <label
                        class="flex items-start gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                        <input type="checkbox" name="radio_enabled" value="1" checked class="w-5 h-5 rounded mt-1">
                        <div>
                            <div class="font-medium text-white">📻 Radio Stations</div>
                            <div class="text-xs text-gray-400">Auto-generated music stations</div>
                        </div>
                    </label>

                    <label
                        class="flex items-start gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                        <input type="checkbox" name="podcasts_enabled" value="1" checked class="w-5 h-5 rounded mt-1">
                        <div>
                            <div class="font-medium text-white">🎙️ Podcasts</div>
                            <div class="text-xs text-gray-400">Podcast hosting platform</div>
                        </div>
                    </label>

                    <label
                        class="flex items-start gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                        <input type="checkbox" name="affiliate_enabled" value="1" checked class="w-5 h-5 rounded mt-1">
                        <div>
                            <div class="font-medium text-white">🤝 Affiliate System</div>
                            <div class="text-xs text-gray-400">Referral & commission program</div>
                        </div>
                    </label>

                    <label
                        class="flex items-start gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                        <input type="checkbox" name="points_enabled" value="1" checked class="w-5 h-5 rounded mt-1">
                        <div>
                            <div class="font-medium text-white">⭐ Points & Rewards</div>
                            <div class="text-xs text-gray-400">Gamification system</div>
                        </div>
                    </label>

                    <label
                        class="flex items-start gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                        <input type="checkbox" name="import_enabled" value="1" class="w-5 h-5 rounded mt-1">
                        <div>
                            <div class="font-medium text-white">📥 Audio Import</div>
                            <div class="text-xs text-gray-400">Import from YouTube/SoundCloud</div>
                        </div>
                    </label>
                </div>

                <div class="mt-4 p-4 bg-blue-500/10 rounded-lg border border-blue-500/20">
                    <div class="flex gap-3">
                        <i class="fas fa-info-circle text-blue-400 mt-1"></i>
                        <p class="text-xs text-blue-200 leading-relaxed">
                            You can enable or disable these features anytime from the Admin Panel → System Configuration.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="mt-8 pt-6 border-t border-white/10 flex justify-between items-center">
                <a href="{{ route('installer.admin') }}"
                    class="px-6 py-3 bg-white/5 hover:bg-white/10 text-white font-bold rounded-lg transition flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
                <button type="submit"
                    class="px-8 py-3 lava-gradient hover:brightness-110 text-white font-bold rounded-lg shadow-lg shadow-red-900/30 transition transform hover:-translate-y-0.5 flex items-center">
                    Complete Installation <i class="fas fa-check ml-2"></i>
                </button>
            </div>
        </form>
    </div>
@endsection