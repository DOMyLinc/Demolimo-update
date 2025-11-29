@extends('install.layout', ['currentStep' => 4])

@section('title', 'Installation')

@section('content')
    <div class="card">
        <h2 class="card-title">Installing DemoLimo</h2>
        <p class="card-subtitle">Please wait while we set up your platform...</p>

        <div id="installation-log"
            style="background: var(--bg-card); padding: 20px; border-radius: 8px; min-height: 300px; max-height: 400px; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 13px; margin: 20px 0;">
            <div class="log-entry">Initializing installation...</div>
        </div>

        <div style="background: var(--bg-card); border-radius: 8px; padding: 4px; margin-bottom: 20px;">
            <div id="progress-bar"
                style="width: 0%; height: 30px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 6px; transition: width 0.3s ease; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 14px;">
                0%
            </div>
        </div>

        <div class="btn-group">
            <button id="install-btn" class="btn btn-primary" onclick="startInstallation()">
                <i class="fas fa-rocket"></i> Start Installation
            </button>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let installStarted = false;

        function startInstallation() {
            if (installStarted) return;
            installStarted = true;

            const btn = document.getElementById('install-btn');
            const log = document.getElementById('installation-log');
            const progressBar = document.getElementById('progress-bar');

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Installing...';

            // Clear initial message
            log.innerHTML = '';

            // Add log entry function
            function addLog(message, type = 'info') {
                const entry = document.createElement('div');
                entry.className = 'log-entry';
                entry.style.marginBottom = '5px';
                entry.style.color = type === 'success' ? 'var(--success)' : type === 'error' ? 'var(--danger)' : 'var(--text-main)';
                entry.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'circle'}"></i> ${message}`;
                log.appendChild(entry);
                log.scrollTop = log.scrollHeight;
            }

            // Update progress
            function updateProgress(percent) {
                progressBar.style.width = percent + '%';
                progressBar.textContent = percent + '%';
            }

            addLog('Starting installation process...', 'info');
            updateProgress(10);

            // Make AJAX request to process installation
            fetch('{{ route('install.step4.process') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Display log entries
                        if (data.log && Array.isArray(data.log)) {
                            let progress = 20;
                            const increment = 70 / data.log.length;

                            data.log.forEach((entry, index) => {
                                setTimeout(() => {
                                    const type = entry.includes('✓') ? 'success' : entry.includes('✗') ? 'error' : 'info';
                                    addLog(entry, type);
                                    progress += increment;
                                    updateProgress(Math.min(90, Math.round(progress)));
                                }, index * 200);
                            });

                            // Complete
                            setTimeout(() => {
                                updateProgress(100);
                                addLog('Installation completed successfully!', 'success');

                                setTimeout(() => {
                                    window.location.href = '{{ route('install.step5') }}';
                                }, 1000);
                            }, data.log.length * 200 + 500);
                        } else {
                            updateProgress(100);
                            addLog('Installation completed successfully!', 'success');
                            setTimeout(() => {
                                window.location.href = '{{ route('install.step5') }}';
                            }, 1500);
                        }
                    } else {
                        addLog('Installation failed: ' + (data.error || 'Unknown error'), 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-redo"></i> Retry Installation';
                        installStarted = false;
                    }
                })
                .catch(error => {
                    addLog('Installation failed: ' + error.message, 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-redo"></i> Retry Installation';
                    installStarted = false;
                });
        }
    </script>
@endsection