@extends('layouts.admin')

@section('page-title', 'System Settings')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="card">
            <h2 class="text-xl font-bold text-white mb-6">System Configuration</h2>

            <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-8">
                @csrf

                <!-- Maintenance Mode -->
                <div class="p-6 bg-white/5 rounded-xl border border-white/10">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center">
                        <i class="fas fa-tools text-yellow-500 mr-3"></i>
                        Maintenance Mode
                    </h3>

                    <div class="space-y-4">
                        <label
                            class="flex items-start gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                            <input type="checkbox" name="maintenance_mode" value="1" {{ $settings['maintenance_mode'] ? 'checked' : '' }}
                                class="w-5 h-5 rounded mt-1 text-yellow-500 focus:ring-yellow-500 bg-gray-700 border-gray-600">
                            <div>
                                <div class="font-medium text-white">Enable Maintenance Mode</div>
                                <div class="text-xs text-gray-400 mt-1">
                                    Put the site offline for maintenance. Admins can still access the site.
                                </div>
                            </div>
                        </label>

                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Maintenance
                                Message (Optional)</label>
                            <textarea name="maintenance_message" rows="3"
                                placeholder="We are currently performing maintenance..."
                                class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-yellow-500 outline-none transition"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Registration Control -->
                <div class="p-6 bg-white/5 rounded-xl border border-white/10">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center">
                        <i class="fas fa-user-plus text-blue-500 mr-3"></i>
                        Registration Control
                    </h3>

                    <label
                        class="flex items-start gap-3 p-4 bg-white/5 rounded-lg cursor-pointer hover:bg-white/10 transition">
                        <input type="checkbox" name="registration_enabled" value="1" {{ $settings['registration_enabled'] ? 'checked' : '' }}
                            class="w-5 h-5 rounded mt-1 text-blue-500 focus:ring-blue-500 bg-gray-700 border-gray-600">
                </div>
            </form>
        </div>
    </div>
@endsection