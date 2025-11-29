@extends('installer.layout', ['step' => 2])

@section('title', 'Database Configuration - DemoLimo Installer')

@section('content')
    <div class="flex-1">
        <h2 class="text-2xl font-bold text-white mb-6">Database Configuration</h2>

        <form action="{{ route('installer.database.post') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Database Type Selection -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Database Type</label>
                <select name="connection" id="db_connection" onchange="updatePort()" required
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none transition">
                    @foreach($databases as $key => $name)
                        <option value="{{ $key }}" {{ old('connection', 'mysql') == $key ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('connection') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Database
                        Host</label>
                    <input type="text" name="host" value="{{ old('host', '127.0.0.1') }}" required
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none transition">
                    @error('host') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Database
                        Port</label>
                    <input type="text" name="port" value="{{ old('port', '3306') }}" required
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none transition">
                    @error('port') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Database Name</label>
                <input type="text" name="database" value="{{ old('database', 'demolimo') }}" required
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none transition">
                @error('database') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Username</label>
                <input type="text" name="username" value="{{ old('username', 'root') }}" required
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none transition">
                @error('username') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1 uppercase tracking-wider">Password</label>
                <input type="password" name="password" placeholder="••••••••"
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none transition">
                @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Footer Actions -->
            <div class="mt-8 pt-6 border-t border-white/10 flex justify-between items-center">
                <a href="{{ route('installer.index') }}" class="text-gray-400 hover:text-white transition font-medium">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
                <button type="submit"
                    class="px-8 py-3 lava-gradient hover:brightness-110 text-white font-bold rounded-lg shadow-lg shadow-red-900/30 transition transform hover:-translate-y-0.5">
                    Test Connection & Continue <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </form>
    </div>

    <script>
        function updatePort() {
            const connection = document.getElementById('db_connection').value;
            const portInput = document.querySelector('input[name="port"]');

            if (connection === 'pgsql') {
                portInput.value = '5432';
            } else {
                portInput.value = '3306';
            }
        }
    </script>
@endsection