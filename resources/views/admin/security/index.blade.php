@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">🔒 Security Settings</h3>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <form action="{{ route('admin.security.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Tabs -->
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#login-security">Login Security</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#password-policies">Password Policies</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#recaptcha">reCAPTCHA</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#ip-security">IP Security</a>
                                </li>
                            </ul>

                            <div class="tab-content mt-3">
                                <!-- Login Security Tab -->
                                <div id="login-security" class="tab-pane fade show active">
                                    <h4>Login Lockout Settings</h4>

                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="enable_login_lockout" value="1" {{ $settings->enable_login_lockout ? 'checked' : '' }}>
                                            Enable Login Lockout
                                        </label>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Max Login Attempts</label>
                                                <input type="number" name="max_login_attempts" class="form-control"
                                                    value="{{ $settings->max_login_attempts }}" min="1" max="20">
                                                <small class="text-muted">Number of failed attempts before lockout</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Lockout Duration (minutes)</label>
                                                <input type="number" name="lockout_duration" class="form-control"
                                                    value="{{ $settings->lockout_duration }}" min="1" max="1440">
                                                <small class="text-muted">How long to lock out the user</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-info">
                                        <strong>Current Status:</strong> Users are locked out for
                                        {{ $settings->lockout_duration }} minutes after {{ $settings->max_login_attempts }}
                                        failed attempts.
                                    </div>

                                    <form action="{{ route('admin.security.clear-lockouts') }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-warning">Clear All Lockouts</button>
                                    </form>
                                </div>

                                <!-- Password Policies Tab -->
                                <div id="password-policies" class="tab-pane fade">
                                    <h4>Password Requirements</h4>

                                    <div class="form-group">
                                        <label>Minimum Password Length</label>
                                        <input type="number" name="min_password_length" class="form-control"
                                            value="{{ $settings->min_password_length }}" min="6" max="32">
                                    </div>

                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="require_uppercase" value="1" {{ $settings->require_uppercase ? 'checked' : '' }}>
                                            Require Uppercase Letters
                                        </label>
                                    </div>

                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="require_lowercase" value="1" {{ $settings->require_lowercase ? 'checked' : '' }}>
                                            Require Lowercase Letters
                                        </label>
                                    </div>

                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="require_numbers" value="1" {{ $settings->require_numbers ? 'checked' : '' }}>
                                            Require Numbers
                                        </label>
                                    </div>

                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="require_special_chars" value="1" {{ $settings->require_special_chars ? 'checked' : '' }}>
                                            Require Special Characters
                                        </label>
                                    </div>

                                    <div class="form-group">
                                        <label>Password Expiry (days, 0 = never)</label>
                                        <input type="number" name="password_expiry_days" class="form-control"
                                            value="{{ $settings->password_expiry_days }}" min="0">
                                    </div>
                                </div>

                                <!-- reCAPTCHA Tab -->
                                <div id="recaptcha" class="tab-pane fade">
                                    <h4>Google reCAPTCHA Configuration</h4>

                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="recaptcha_enabled" value="1" {{ $settings->recaptcha_enabled ? 'checked' : '' }}>
                                            Enable reCAPTCHA
                                        </label>
                                    </div>

                                    <div class="form-group">
                                        <label>reCAPTCHA Version</label>
                                        <select name="recaptcha_version" class="form-control">
                                            <option value="v2" {{ $settings->recaptcha_version == 'v2' ? 'selected' : '' }}>v2
                                                (Checkbox)</option>
                                            <option value="v3" {{ $settings->recaptcha_version == 'v3' ? 'selected' : '' }}>v3
                                                (Invisible)</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Site Key</label>
                                        <input type="text" name="recaptcha_site_key" class="form-control"
                                            value="{{ $settings->recaptcha_site_key }}">
                                    </div>

                                    <div class="form-group">
                                        <label>Secret Key</label>
                                        <input type="text" name="recaptcha_secret_key" class="form-control"
                                            value="{{ $settings->recaptcha_secret_key }}">
                                    </div>

                                    <div class="form-group" id="v3-threshold"
                                        style="display: {{ $settings->recaptcha_version == 'v3' ? 'block' : 'none' }}">
                                        <label>Score Threshold (v3 only)</label>
                                        <input type="number" name="recaptcha_score_threshold" class="form-control"
                                            value="{{ $settings->recaptcha_score_threshold }}" step="0.1" min="0" max="1">
                                        <small class="text-muted">0.0 - 1.0 (higher = stricter)</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Enable reCAPTCHA on:</label>
                                        <div>
                                            <label>
                                                <input type="checkbox" name="recaptcha_on_login" value="1" {{ $settings->recaptcha_on_login ? 'checked' : '' }}>
                                                Login
                                            </label>
                                        </div>
                                        <div>
                                            <label>
                                                <input type="checkbox" name="recaptcha_on_register" value="1" {{ $settings->recaptcha_on_register ? 'checked' : '' }}>
                                                Register
                                            </label>
                                        </div>
                                        <div>
                                            <label>
                                                <input type="checkbox" name="recaptcha_on_forgot_password" value="1" {{ $settings->recaptcha_on_forgot_password ? 'checked' : '' }}>
                                                Forgot Password
                                            </label>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-info" onclick="testRecaptcha()">Test
                                        reCAPTCHA</button>
                                    <div id="recaptcha-test-result" class="mt-2"></div>
                                </div>

                                <!-- IP Security Tab -->
                                <div id="ip-security" class="tab-pane fade">
                                    <h4>IP Whitelist/Blacklist</h4>

                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="enable_ip_whitelist" value="1" {{ $settings->enable_ip_whitelist ? 'checked' : '' }}>
                                            Enable IP Whitelist
                                        </label>
                                        <small class="text-muted">Only allow access from these IPs</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Whitelisted IPs (one per line)</label>
                                        <textarea name="ip_whitelist[]" class="form-control"
                                            rows="5">{{ implode("\n", $settings->ip_whitelist ?? []) }}</textarea>
                                    </div>

                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" name="enable_ip_blacklist" value="1" {{ $settings->enable_ip_blacklist ? 'checked' : '' }}>
                                            Enable IP Blacklist
                                        </label>
                                        <small class="text-muted">Block access from these IPs</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Blacklisted IPs (one per line)</label>
                                        <textarea name="ip_blacklist[]" class="form-control"
                                            rows="5">{{ implode("\n", $settings->ip_blacklist ?? []) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">Save Security Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function testRecaptcha() {
            const siteKey = document.querySelector('[name="recaptcha_site_key"]').value;
            const secretKey = document.querySelector('[name="recaptcha_secret_key"]').value;
            const version = document.querySelector('[name="recaptcha_version"]').value;

            fetch('{{ route("admin.security.test-recaptcha") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ site_key: siteKey, secret_key: secretKey, version: version })
            })
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('recaptcha-test-result');
                    if (data.success) {
                        resultDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                    } else {
                        resultDiv.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                    }
                });
        }

        // Show/hide v3 threshold based on version
        document.querySelector('[name="recaptcha_version"]').addEventListener('change', function () {
            document.getElementById('v3-threshold').style.display = this.value === 'v3' ? 'block' : 'none';
        });
    </script>
@endsection