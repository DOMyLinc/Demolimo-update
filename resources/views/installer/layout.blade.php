<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DemoLimo Installer')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #0f0f13;
            font-family: 'Inter', sans-serif;
        }

        .lava-gradient {
            background: linear-gradient(135deg, #DC2626 0%, #EA580C 100%);
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .step-active {
            background: #DC2626;
            color: white;
            border-color: #DC2626;
            box-shadow: 0 0 15px rgba(220, 38, 38, 0.5);
        }

        .step-completed {
            background: #10B981;
            color: white;
            border-color: #10B981;
        }

        .step-inactive {
            background: transparent;
            color: #6B7280;
            border-color: #374151;
        }
    </style>
</head>

<body
    class="min-h-screen flex items-center justify-center p-4 bg-[url('https://images.unsplash.com/photo-1470225620780-dba8ba36b745?q=80&w=2070&auto=format&fit=crop')] bg-cover bg-center bg-no-repeat relative">

    <!-- Dark Overlay -->
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>

    <div class="relative z-10 w-full max-w-4xl">

        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-black text-white mb-2 tracking-tight">
                <span class="text-transparent bg-clip-text lava-gradient">DemoLimo</span> Installer
            </h1>
            <p class="text-gray-400">Set up your music platform in minutes</p>
        </div>

        <div class="glass-panel rounded-2xl shadow-2xl overflow-hidden flex flex-col md:flex-row min-h-[500px]">

            <!-- Sidebar / Steps -->
            <div class="w-full md:w-1/3 bg-black/40 p-8 border-r border-white/5">
                <div class="space-y-8">
                    <!-- Step 1: Requirements -->
                    <div class="flex items-center gap-4">
                        <div
                            class="w-10 h-10 rounded-full border-2 flex items-center justify-center font-bold {{ $step > 1 ? 'step-completed' : ($step == 1 ? 'step-active' : 'step-inactive') }}">
                            @if($step > 1) <i class="fas fa-check"></i> @else 1 @endif
                        </div>
                        <div>
                            <h3 class="{{ $step >= 1 ? 'text-white' : 'text-gray-500' }} font-bold">
                                {{ __('installer.requirements') }}
                            </h3>
                            <p
                                class="text-xs {{ $step > 1 ? 'text-green-400' : ($step == 1 ? 'text-red-400' : 'text-gray-600') }}">
                                {{ $step > 1 ? __('installer.passed') : ($step == 1 ? __('installer.in_progress') : __('installer.pending')) }}
                            </p>
                        </div>
                    </div>

                    <!-- Step 2: Database -->
                    <div class="flex items-center gap-4">
                        <div
                            class="w-10 h-10 rounded-full border-2 flex items-center justify-center font-bold {{ $step > 2 ? 'step-completed' : ($step == 2 ? 'step-active' : 'step-inactive') }}">
                            @if($step > 2) <i class="fas fa-check"></i> @else 2 @endif
                        </div>
                        <div>
                            <h3 class="{{ $step >= 2 ? 'text-white' : 'text-gray-500' }} font-bold">
                                {{ __('installer.database_setup') }}
                            </h3>
                            <p
                                class="text-xs {{ $step > 2 ? 'text-green-400' : ($step == 2 ? 'text-red-400' : 'text-gray-600') }}">
                                {{ $step > 2 ? __('installer.completed') : ($step == 2 ? __('installer.in_progress') : __('installer.pending')) }}
                            </p>
                        </div>
                    </div>

                    <!-- Step 3: Admin Setup -->
                    <div class="flex items-center gap-4">
                        <div
                            class="w-10 h-10 rounded-full border-2 flex items-center justify-center font-bold {{ $step > 3 ? 'step-completed' : ($step == 3 ? 'step-active' : 'step-inactive') }}">
                            @if($step > 3) <i class="fas fa-check"></i> @else 3 @endif
                        </div>
                        <div>
                            <h3 class="{{ $step >= 3 ? 'text-white' : 'text-gray-500' }} font-bold">
                                {{ __('installer.admin_account') }}
                            </h3>
                            <p
                                class="text-xs {{ $step > 3 ? 'text-green-400' : ($step == 3 ? 'text-red-400' : 'text-gray-600') }}">
                                {{ $step > 3 ? __('installer.completed') : ($step == 3 ? __('installer.in_progress') : __('installer.pending')) }}
                            </p>
                        </div>
                    </div>

                    <!-- Step 4: Features -->
                    <div class="flex items-center gap-4">
                        <div
                            class="w-10 h-10 rounded-full border-2 flex items-center justify-center font-bold {{ $step > 4 ? 'step-completed' : ($step == 4 ? 'step-active' : 'step-inactive') }}">
                            @if($step > 4) <i class="fas fa-check"></i> @else 4 @endif
                        </div>
                        <div>
                            <h3 class="{{ $step >= 4 ? 'text-white' : 'text-gray-500' }} font-bold">
                                {{ __('installer.features_setup') }}
                            </h3>
                            <p
                                class="text-xs {{ $step > 4 ? 'text-green-400' : ($step == 4 ? 'text-red-400' : 'text-gray-600') }}">
                                {{ $step > 4 ? __('installer.completed') : ($step == 4 ? __('installer.in_progress') : __('installer.pending')) }}
                            </p>
                        </div>
                    </div>

                    <!-- Step 5: Settings -->
                    <div class="flex items-center gap-4">
                        <div
                            class="w-10 h-10 rounded-full border-2 flex items-center justify-center font-bold {{ $step > 5 ? 'step-completed' : ($step == 5 ? 'step-active' : 'step-inactive') }}">
                            @if($step > 5) <i class="fas fa-check"></i> @else 5 @endif
                        </div>
                        <div>
                            <h3 class="{{ $step >= 5 ? 'text-white' : 'text-gray-500' }} font-bold">
                                System Settings</h3>
                            <p
                                class="text-xs {{ $step > 5 ? 'text-green-400' : ($step == 5 ? 'text-red-400' : 'text-gray-600') }}">
                                {{ $step > 5 ? __('installer.completed') : ($step == 5 ? __('installer.in_progress') : __('installer.pending')) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="w-full md:w-2/3 p-8 flex flex-col">
                @if(session('error'))
                    <div
                        class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg flex items-center gap-3">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </div>

        <div class="text-center mt-6 text-gray-500 text-sm">
            {{ __('installer.copyright', ['year' => date('Y')]) }}
        </div>
    </div>

</body>

</html>