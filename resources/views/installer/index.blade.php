@extends('installer.layout', ['step' => 1])

@section('title', __('installer.server_requirements') . ' - DemoLimo Installer')

@section('content')
    <div class="flex-1">
        <h2 class="text-2xl font-bold text-white mb-6">{{ __('installer.server_requirements') }}</h2>

        <div class="space-y-3 mb-8">
            @foreach($requirements as $label => $met)
                <div
                    class="flex items-center justify-between p-4 rounded-lg border {{ $met ? 'bg-green-500/10 border-green-500/20' : 'bg-red-500/10 border-red-500/20' }}">
                    <span class="text-gray-300 font-medium">{{ $label }}</span>
                    @if($met)
                        <div class="flex items-center gap-2 text-green-400">
                            <span class="text-xs font-bold uppercase">{{ __('installer.passed') }}</span>
                            <i class="fas fa-check-circle"></i>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-red-400">
                            <span class="text-xs font-bold uppercase">{{ __('installer.failed') }}</span>
                            <i class="fas fa-times-circle"></i>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Footer Actions -->
        <div class="mt-8 pt-6 border-t border-white/10 flex justify-end items-center">
            @if($allMet)
                <a href="{{ route('installer.database') }}"
                    class="px-8 py-3 lava-gradient hover:brightness-110 text-white font-bold rounded-lg shadow-lg shadow-red-900/30 transition transform hover:-translate-y-0.5 flex items-center">
                    {{ __('installer.continue_database') }} <i class="fas fa-arrow-right ml-2"></i>
                </a>
            @else
                <button disabled
                    class="px-8 py-3 bg-gray-700 text-gray-400 font-bold rounded-lg cursor-not-allowed flex items-center">
                    {{ __('installer.fix_requirements') }} <i class="fas fa-lock ml-2"></i>
                </button>
            @endif
        </div>
    </div>
@endsection