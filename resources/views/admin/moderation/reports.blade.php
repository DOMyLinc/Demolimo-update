@extends('layouts.admin')

@section('title', 'User Reports')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">User Reports</h1>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="reportsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Reporter</th>
                                <th>Reported User</th>
                                <th>Content Type</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                                <tr>
                                    <td>{{ $report->id }}</td>
                                    <td>{{ $report->reporter->name ?? 'Unknown' }}</td>
                                    <td>{{ $report->reported->name ?? 'N/A' }}</td>
                                    <td>{{ class_basename($report->reportable_type) }}</td>
                                    <td>{{ $report->reason }}</td>
                                    <td>
                                        <span
                                            class="badge badge-{{ $report->status === 'resolved' ? 'success' : ($report->status === 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($report->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $report->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.moderation.reports.show', $report) }}"
                                            class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $reports->links() }}
            </div>
        </div>
    </div>
@endsection