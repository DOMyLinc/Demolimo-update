@extends('install.layout', ['currentStep' => 3])

@section('title', 'Site Configuration')

@section('content')
    <div class="card">
        <h2 class="card-title">Site Configuration</h2>
        <p class="card-subtitle">Configure your site settings and create admin account</p>

        <form action="{{ route('install.step3.post') }}" method="POST">
            @csrf

            <h3 style="margin-top: 20px; margin-bottom: 15px;">Site Information</h3>

            <div class="form-group">
                <label class="form-label">Site Name</label>
                <input type="text" name="site_name" class="form-control" value="{{ old('site_name', 'DemoLimo') }}"
                    required>
            </div>

            <div class="form-group">
                <label class="form-label">Site URL</label>
                <input type="url" name="site_url" class="form-control" value="{{ old('site_url', url('/')) }}" required>
                <small class="form-text">Your full site URL (e.g., https://yourdomain.com)</small>
            </div>

            <div class="form-group">
                <label class="form-label">Timezone</label>
                <select name="timezone" class="form-control" required>
                    @foreach($timezones as $tz)
                        <option value="{{ $tz }}" {{ old('timezone', 'UTC') == $tz ? 'selected' : '' }}>
                            {{ $tz }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Default Language</label>
                <select name="language" class="form-control" required>
                    @foreach($languages as $code => $name)
                        <option value="{{ $code }}" {{ old('language', 'en') == $code ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <h3 style="margin-top: 30px; margin-bottom: 15px;">Admin Account</h3>

            <div class="form-group">
                <label class="form-label">Admin Email</label>
                <input type="email" name="admin_email" class="form-control" value="{{ old('admin_email') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Admin Username</label>
                <input type="text" name="admin_username" class="form-control" value="{{ old('admin_username', 'admin') }}"
                    required>
                <small class="form-text">Minimum 3 characters</small>
            </div>

            <div class="form-group">
                <label class="form-label">Admin Password</label>
                <input type="password" name="admin_password" class="form-control" required>
                <small class="form-text">Minimum 8 characters</small>
            </div>

            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="admin_password_confirmation" class="form-control" required>
            </div>

            <div class="btn-group">
                <a href="{{ route('install.step2') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button type="submit" class="btn btn-primary">
                    Continue <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
@endsection