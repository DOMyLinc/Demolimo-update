@extends('layouts.admin')

@section('title', 'Report Details')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Report #{{ $report->id }}</h1>
            <a href="{{ route('admin.moderation.reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Report Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold">Reporter:</div>
                            <div class="col-md-8">{{ $report->reporter->name ?? 'Unknown' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold">Reported User:</div>
                            <div class="col-md-8">{{ $report->reported->name ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold">Content Type:</div>
                            <div class="col-md-8">{{ class_basename($report->reportable_type) }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold">Reason:</div>
                            <div class="col-md-8">{{ $report->reason }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold">Description:</div>
                            <div class="col-md-8">{{ $report->description ?? 'No description provided.' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 font-weight-bold">Status:</div>
                            <div class="col-md-8">
                                <span
                                    class="badge badge-{{ $report->status === 'resolved' ? 'success' : ($report->status === 'pending' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($report->status) }}
                                </span>
                            </div>
                        </div>
                        @if($report->resolved_at)
                            <div class="row mb-3">
                                <div class="col-md-4 font-weight-bold">Resolved By:</div>
                                <div class="col-md-8">{{ $report->resolver->name ?? 'Unknown' }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 font-weight-bold">Resolved At:</div>
                                <div class="col-md-8">{{ $report->resolved_at->format('Y-m-d H:i') }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 font-weight-bold">Admin Notes:</div>
                                <div class="col-md-8">{{ $report->admin_notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                    </div>
                    <div class="card-body">
                        @if($report->status !== 'resolved')
                            <form action="{{ route('admin.moderation.reports.resolve', $report) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="action">Action</label>
                                    <select name="action" id="action" class="form-control" required>
                                        <option value="">Select Action...</option>
                                        <option value="dismiss">Dismiss Report</option>
                                        <option value="warning">Send Warning</option>
                                        <option value="ban">Ban User</option>
                                        <option value="delete_content">Delete Content</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Resolve Report</button>
                            </form>
                        @else
                            <div class="alert alert-success">
                                This report has been resolved.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection