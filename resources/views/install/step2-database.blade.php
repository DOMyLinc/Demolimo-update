@extends('install.layout', ['currentStep' => 2])

@section('title', 'Database Configuration')

@section('content')
    <div class="card">
        <h2 class="card-title">Database Configuration</h2>
        <p class="card-subtitle">Enter your database connection details</p>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('install.step2.post') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">Database Host</label>
                <input type="text" name="db_host" class="form-control" value="{{ old('db_host', 'localhost') }}" required>
                <small class="form-text">Usually "localhost" or "127.0.0.1"</small>
            </div>

            <div class="form-group">
                <label class="form-label">Database Port</label>
                <input type="number" name="db_port" class="form-control" value="{{ old('db_port', '3306') }}" required>
                <small class="form-text">Default MySQL port is 3306</small>
            </div>

            <div class="form-group">
                <label class="form-label">Database Name</label>
                <input type="text" name="db_name" class="form-control" value="{{ old('db_name') }}" required>
                <small class="form-text">The database must already exist</small>
            </div>

            <div class="form-group">
                <label class="form-label">Database Username</label>
                <input type="text" name="db_username" class="form-control" value="{{ old('db_username') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Database Password</label>
                <input type="password" name="db_password" class="form-control" value="{{ old('db_password') }}">
                <small class="form-text">Leave blank if no password</small>
            </div>

            <div class="btn-group">
                <a href="{{ route('install.step1') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button type="submit" class="btn btn-primary">
                    Test & Continue <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
@endsection