@extends('installer.layout', ['step' => 5])

@section('title', 'System Settings - DemoLimo Installer')

@section('content')
    <div class="flex-1">
        <h2 class="text-2xl font-bold text-white mb-2">System Settings</h2>
        <p class="text-gray-400 mb-6">Configure your application environment</p>

        <form action="{{ route('installer.settings.post') }}" method="POST" class="space-y-8">
            @csrf

            <!-- General Settings -->
            <div class="p-6 bg-white/5 rounded-xl border border-white/10">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center">
                    <i class="fas fa-globe text-blue-500 mr-3"></i>
                    General Settings
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">App
                            Name</label>
                        <input type="text" name="app_name" value="{{ old('app_name', 'DemoLimo') }}" required
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-blue-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">App URL</label>
                        <input type="url" name="app_url" value="{{ old('app_url', url('/')) }}" required
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-blue-500 outline-none transition">
                    </div>
                </div>
            </div>

            <!-- Theme Selection -->
            <div class="p-6 bg-white/5 rounded-xl border border-white/10">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center">
                    <i class="fas fa-palette text-pink-500 mr-3"></i>
                    Theme Selection
                </h3>
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-400 mb-3 uppercase tracking-wider">Choose Your Theme</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @forelse($themes as $theme)
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="frontend_theme" value="{{ $theme->id }}" 
                                    {{ $theme->slug === 'lava' ? 'checked' : '' }}
                                    class="peer sr-only">
                                <div class="p-4 bg-white/5 border-2 border-white/10 rounded-lg transition-all peer-checked:border-pink-500 peer-checked:bg-pink-500/10 hover:border-white/30">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-bold text-white">{{ $theme->name }}</h4>
                                        @if($theme->slug === 'lava')
                                            <span class="text-xs bg-pink-500 px-2 py-1 rounded-full">Default</span>
                                        @endif
                                    </div>
                                    @if($theme->description)
                                        <p class="text-xs text-gray-400">{{ $theme->description }}</p>
                                    @endif
                                    @if($theme->primary_color || $theme->secondary_color)
                                        <div class="mt-3 flex gap-1">
                                            @if($theme->primary_color)
                                                <div class="w-6 h-6 rounded-full border border-white/20" style="background: {{ $theme->primary_color }}"></div>
                                            @endif
                                            @if($theme->secondary_color)
                                                <div class="w-6 h-6 rounded-full border border-white/20" style="background: {{ $theme->secondary_color }}"></div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </label>
                        @empty
                            <div class="col-span-full text-center text-gray-400 py-4">
                                No themes available. Default theme will be used.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Mail Settings -->
            <div class="p-6 bg-white/5 rounded-xl border border-white/10">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center">
                    <i class="fas fa-envelope text-yellow-500 mr-3"></i>
                    Mail Configuration (SMTP)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Mail
                            Host</label>
                        <input type="text" name="mail_host" value="{{ old('mail_host', 'smtp.mailtrap.io') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-yellow-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Mail
                            Port</label>
                        <input type="text" name="mail_port" value="{{ old('mail_port', '2525') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-yellow-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Mail
                            Username</label>
                        <input type="text" name="mail_username" value="{{ old('mail_username') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-yellow-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Mail
                            Password</label>
                        <input type="password" name="mail_password" value="{{ old('mail_password') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-yellow-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Mail
                            Encryption</label>
                        <select name="mail_encryption"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-yellow-500 outline-none transition">
                            <option value="tls">TLS</option>
                            <option value="ssl">SSL</option>
                            <option value="">None</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">From
                            Address</label>
                        <input type="email" name="mail_from_address"
                            value="{{ old('mail_from_address', 'hello@example.com') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-yellow-500 outline-none transition">
                    </div>
                </div>
            </div>

            <!-- Storage Settings -->
            <div class="p-6 bg-white/5 rounded-xl border border-white/10">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center">
                    <i class="fas fa-hdd text-green-500 mr-3"></i>
                    Storage Configuration
                </h3>
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Filesystem
                        Driver</label>
                    <select name="filesystem_disk" id="filesystem_disk" onchange="toggleS3()"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-green-500 outline-none transition">
                        <option value="local">Local Storage (Public)</option>
                        <option value="s3">AWS S3 / Compatible</option>
                    </select>
                </div>

                <div id="s3_settings" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">AWS Access Key
                            ID</label>
                        <input type="text" name="aws_access_key_id" value="{{ old('aws_access_key_id') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-green-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">AWS Secret
                            Access Key</label>
                        <input type="password" name="aws_secret_access_key" value="{{ old('aws_secret_access_key') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-green-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">AWS Default
                            Region</label>
                        <input type="text" name="aws_default_region" value="{{ old('aws_default_region', 'us-east-1') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-green-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">AWS
                            Bucket</label>
                        <input type="text" name="aws_bucket" value="{{ old('aws_bucket') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-green-500 outline-none transition">
                    </div>
                </div>
            </div>

            <!-- Pusher Settings (Optional) -->
            <div class="p-6 bg-white/5 rounded-xl border border-white/10">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center">
                    <i class="fas fa-broadcast-tower text-purple-500 mr-3"></i>
                    Real-time Features (Optional)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">App
                            ID</label>
                        <input type="text" name="pusher_app_id" value="{{ old('pusher_app_id') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-purple-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">App
                            Key</label>
                        <input type="text" name="pusher_app_key" value="{{ old('pusher_app_key') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-purple-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">App
                            Secret</label>
                        <input type="password" name="pusher_app_secret" value="{{ old('pusher_app_secret') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-purple-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">App
                            Cluster</label>
                        <input type="text" name="pusher_app_cluster" value="{{ old('pusher_app_cluster', 'mt1') }}"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-purple-500 outline-none transition">
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="mt-8 pt-6 border-t border-white/10 flex justify-between items-center">
                <a href="{{ route('installer.features') }}"
                    class="px-6 py-3 bg-white/5 hover:bg-white/10 text-white font-bold rounded-lg transition flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
                <button type="submit"
                    class="px-8 py-3 lava-gradient hover:brightness-110 text-white font-bold rounded-lg shadow-lg shadow-red-900/30 transition transform hover:-translate-y-0.5 flex items-center">
                    Finish Installation <i class="fas fa-check-circle ml-2"></i>
                </button>
            </div>
        </form>
    </div>

    <script>
        function toggleS3() {
            const driver = document.getElementById('filesystem_disk').value;
            const s3Settings = document.getElementById('s3_settings');
            if (driver === 's3') {
                s3Settings.classList.remove('hidden');
            } else {
                s3Settings.classList.add('hidden');
            }
        }
    </script>
@endsection