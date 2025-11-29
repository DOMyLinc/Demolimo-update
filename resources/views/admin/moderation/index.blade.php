@extends('layouts.admin')

@section('title', 'Content Moderation')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Content Moderation</h1>
        </div>

        <div class="row">
            <!-- Stats Cards -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Tracks</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_tracks'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-music fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Events</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_events'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved Today</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['approved_today'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejected Today</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['rejected_today'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('admin.moderation.queue', ['type' => 'tracks']) }}"
                            class="btn btn-primary btn-block mb-3">
                            <i class="fas fa-music mr-2"></i> Review Pending Tracks
                        </a>
                        <a href="{{ route('admin.moderation.queue', ['type' => 'events']) }}"
                            class="btn btn-info btn-block mb-3">
                            <i class="fas fa-calendar mr-2"></i> Review Pending Events
                        </a>
                        <a href="{{ route('admin.moderation.reports.index') }}" class="btn btn-warning btn-block">
                            <i class="fas fa-flag mr-2"></i> View User Reports
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Settings</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.moderation.updateSettings') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="autoApproveTracks"
                                        name="auto_approve_tracks" value="1" {{ $settings['auto_approve_tracks'] ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="autoApproveTracks">Auto-approve Tracks</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="autoApproveEvents"
                                        name="auto_approve_events" value="1" {{ $settings['auto_approve_events'] ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="autoApproveEvents">Auto-approve Events</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="autoApproveVerified"
                                        name="auto_approve_verified_users" value="1" {{ $settings['auto_approve_verified_users'] ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="autoApproveVerified">Auto-approve Verified
                                        Users</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection