@extends('layouts.admin')

@section('title', 'Advertising Management')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Advertising Management</h1>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Ad Campaigns</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="adsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Advertiser</th>
                                <th>Budget</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ads as $ad)
                                <tr>
                                    <td>{{ $ad->title }}</td>
                                    <td>{{ $ad->user->name }}</td>
                                    <td>${{ number_format($ad->budget, 2) }}</td>
                                    <td>
                                        <span
                                            class="badge badge-{{ $ad->status == 'active' ? 'success' : ($ad->status == 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($ad->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($ad->status == 'pending')
                                            <form action="{{ route('admin.advertising.approve', $ad->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                            </form>
                                            <form action="{{ route('admin.advertising.reject', $ad->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.advertising.show', $ad->id) }}"
                                            class="btn btn-info btn-sm">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $ads->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection