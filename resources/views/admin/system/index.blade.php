@extends('layouts.admin')

@section('title', 'System Configuration')
@section('page-title', 'System Configuration')

@section('content')
<div class="space-y-6">
    <!-- FFMPEG Configuration Card -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">🎵 FFMPEG Audio Processing</h2>
            <p class="text-gray-400 text-sm">Configure FFMPEG for waveform generation and audio processing</p>
        </div>

        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">FFMPEG Path</label>
                    <input type="text" id="ffmpeg_path" 
                        value="{{ $configurations['audio_processing']['ffmpeg_path'] ?? '' }}"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white"
                        placeholder="C:\ffmpeg\bin\ffmpeg.exe">
                </div>
                <div class="flex items-end gap-2">
                    <button onclick="testFFMPEG()" class="btn btn-primary">
                        <i class="fas fa-vial mr-2"></i> Test FFMPEG
                    </button>
                    <button onclick="downloadFFMPEG()" class="btn btn-success">
                        <i class="fas fa-download mr-2"></i> Auto Download
                    </button>
                </div>
            </div>

            <div id="ffmpeg-status" class="hidden p-4 rounded-lg"></div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <label class="flex items-center gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                    <input type="checkbox" name="ffmpeg_enabled" 
                        {{ ($configurations['audio_processing']['ffmpeg_enabled'] ?? false) ? 'checked' : '' }}
                        class="w-5 h-5 rounded">
                    <div>
                        <div class="font-medium">Enable FFMPEG</div>
                        <div class="text-xs text-gray-400">Activate audio processing</div>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                    <input type="checkbox" name="waveform_generation" 
                        {{ ($configurations['audio_processing']['waveform_generation'] ?? false) ? 'checked' : '' }}
                        class="w-5 h-5 rounded">
                    <div>
                        <div class="font-medium">Waveform Generation</div>
                        <div class="text-xs text-gray-400">Auto-generate waveforms</div>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                    <input type="checkbox" name="audio_normalization" 
                        {{ ($configurations['audio_processing']['audio_normalization'] ?? false) ? 'checked' : '' }}
                        class="w-5 h-5 rounded">
                    <div>
                        <div class="font-medium">Audio Normalization</div>
                        <div class="text-xs text-gray-400">Normalize audio levels</div>
                    </div>
                </label>
            </div>
        </div>
    </div>

    <!-- Feature Toggles Card -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">⚙️ Feature Management</h2>
            <p class="text-gray-400 text-sm">Enable or disable platform features</p>
        </div>

        <form action="{{ route('admin.system.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Chat Feature -->
                <label class="flex items-center gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                    <input type="checkbox" name="configurations[chat_enabled]" value="1"
                        {{ ($configurations['features']['chat_enabled'] ?? false) ? 'checked' : '' }}
                        class="w-5 h-5 rounded">
                    <div>
                        <div class="font-medium">💬 Direct Messaging</div>
                        <div class="text-xs text-gray-400">User-to-user chat</div>
                    </div>
                </label>

                <!-- Blog Feature -->
                <label class="flex items-center gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                    <input type="checkbox" name="configurations[blog_enabled]" value="1"
                        {{ ($configurations['features']['blog_enabled'] ?? false) ? 'checked' : '' }}
                        class="w-5 h-5 rounded">
                    <div>
                        <div class="font-medium">📝 Blog/Articles</div>
                        <div class="text-xs text-gray-400">Content management</div>
                    </div>
                </label>

                <!-- Radio Feature -->
                <label class="flex items-center gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                    <input type="checkbox" name="configurations[radio_enabled]" value="1"
                        {{ ($configurations['features']['radio_enabled'] ?? false) ? 'checked' : '' }}
                        class="w-5 h-5 rounded">
                    <div>
                        <div class="font-medium">📻 Radio Stations</div>
                        <div class="text-xs text-gray-400">Auto-generated stations</div>
                    </div>
                </label>

                <!-- Podcasts Feature -->
                <label class="flex items-center gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                    <input type="checkbox" name="configurations[podcasts_enabled]" value="1"
                        {{ ($configurations['features']['podcasts_enabled'] ?? false) ? 'checked' : '' }}
                        class="w-5 h-5 rounded">
                    <div>
                        <div class="font-medium">🎙️ Podcasts</div>
                        <div class="text-xs text-gray-400">Podcast hosting</div>
                    </div>
                </label>

                <!-- Affiliate Feature -->
                <label class="flex items-center gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                    <input type="checkbox" name="configurations[affiliate_enabled]" value="1"
                        {{ ($configurations['features']['affiliate_enabled'] ?? false) ? 'checked' : '' }}
                        class="w-5 h-5 rounded">
                    <div>
                        <div class="font-medium">🤝 Affiliate System</div>
                        <div class="text-xs text-gray-400">Referral program</div>
                    </div>
                </label>

                <!-- Points Feature -->
                <label class="flex items-center gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                    <input type="checkbox" name="configurations[points_enabled]" value="1"
                        {{ ($configurations['features']['points_enabled'] ?? false) ? 'checked' : '' }}
                        class="w-5 h-5 rounded">
                    <div>
                        <div class="font-medium">⭐ Points/Rewards</div>
                        <div class="text-xs text-gray-400">Gamification system</div>
                    </div>
                </label>

                <!-- Import Feature -->
                <label class="flex items-center gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                    <input type="checkbox" name="configurations[import_enabled]" value="1"
                        {{ ($configurations['features']['import_enabled'] ?? false) ? 'checked' : '' }}
                        class="w-5 h-5 rounded">
                    <div>
                        <div class="font-medium">📥 Audio Import</div>
                        <div class="text-xs text-gray-400">YouTube/SoundCloud</div>
                    </div>
                </label>
            </div>

            <!-- Hidden inputs for unchecked boxes -->
            <input type="hidden" name="configurations[ffmpeg_path]" id="ffmpeg_path_hidden">
            <input type="hidden" name="configurations[ffmpeg_enabled]" value="0">
            <input type="hidden" name="configurations[waveform_generation]" value="0">
            <input type="hidden" name="configurations[audio_normalization]" value="0">

            <div class="mt-6 flex justify-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> Save Configuration
                </button>
            </div>
        </form>
    </div>

    <!-- Real-time Configuration Card -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">⚡ Real-time Features</h2>
            <p class="text-gray-400 text-sm">Configure Laravel Reverb for real-time notifications and chat</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Reverb App ID</label>
                <input type="text" name="configurations[reverb_app_id]" 
                    value="{{ $configurations['realtime']['reverb_app_id'] ?? '' }}"
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white"
                    placeholder="your-app-id">
            </div>

            <div class="flex items-end">
                <label class="flex items-center gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition w-full">
                    <input type="checkbox" name="configurations[reverb_enabled]" value="1"
                        {{ ($configurations['realtime']['reverb_enabled'] ?? false) ? 'checked' : '' }}
                        class="w-5 h-5 rounded">
                    <div>
                        <div class="font-medium">Enable Real-time</div>
                        <div class="text-xs text-gray-400">Live notifications & chat</div>
                    </div>
                </label>
            </div>
        </div>
    </div>

    <!-- Import Configuration Card -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📥 Import Settings</h2>
            <p class="text-gray-400 text-sm">Configure API keys for audio import features</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">YouTube API Key</label>
                <input type="text" name="configurations[youtube_api_key]" 
                    value="{{ $configurations['import']['youtube_api_key'] ?? '' }}"
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white"
                    placeholder="AIza...">
                <p class="text-xs text-gray-400 mt-1">Get your API key from <a href="https://console.cloud.google.com/" target="_blank" class="text-blue-400 hover:underline">Google Cloud Console</a></p>
            </div>
        </div>
    </div>
</div>

<script>
// Update hidden input when path changes
document.getElementById('ffmpeg_path').addEventListener('input', function() {
    document.getElementById('ffmpeg_path_hidden').value = this.value;
});

// Set initial value
document.getElementById('ffmpeg_path_hidden').value = document.getElementById('ffmpeg_path').value;

function testFFMPEG() {
    const statusDiv = document.getElementById('ffmpeg-status');
    statusDiv.className = 'p-4 rounded-lg bg-blue-500/10 border border-blue-500/20';
    statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Testing FFMPEG...';
    statusDiv.classList.remove('hidden');

    fetch('{{ route("admin.system.test-ffmpeg") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusDiv.className = 'p-4 rounded-lg bg-green-500/10 border border-green-500/20';
            statusDiv.innerHTML = `<i class="fas fa-check-circle text-green-400 mr-2"></i> ${data.message} <span class="text-gray-400">(${data.version})</span>`;
        } else {
            statusDiv.className = 'p-4 rounded-lg bg-red-500/10 border border-red-500/20';
            statusDiv.innerHTML = `<i class="fas fa-times-circle text-red-400 mr-2"></i> ${data.message}`;
        }
    });
}

function downloadFFMPEG() {
    if (!confirm('This will download and install FFMPEG automatically. Continue?')) {
        return;
    }

    const statusDiv = document.getElementById('ffmpeg-status');
    statusDiv.className = 'p-4 rounded-lg bg-blue-500/10 border border-blue-500/20';
    statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Downloading FFMPEG... This may take a few minutes.';
    statusDiv.classList.remove('hidden');

    fetch('{{ route("admin.system.download-ffmpeg") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusDiv.className = 'p-4 rounded-lg bg-green-500/10 border border-green-500/20';
            statusDiv.innerHTML = `<i class="fas fa-check-circle text-green-400 mr-2"></i> ${data.message} <div class="text-sm text-gray-400 mt-2">Path: ${data.path}</div>`;
            document.getElementById('ffmpeg_path').value = data.path;
            document.getElementById('ffmpeg_path_hidden').value = data.path;
        } else {
            statusDiv.className = 'p-4 rounded-lg bg-red-500/10 border border-red-500/20';
            statusDiv.innerHTML = `<i class="fas fa-times-circle text-red-400 mr-2"></i> ${data.message}`;
        }
    });
}
</script>
@endsection
