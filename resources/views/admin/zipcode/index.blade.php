@extends('layouts.admin')

@section('page-title', 'Zipcode Management')

@section('content')
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">All Zipcodes</h3>
            <div class="actions">
                <!-- Add filter or search here if needed -->
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Zipcode</th>
                        <th>Location</th>
                        <th>Owner</th>
                        <th>Members</th>
                        <th>Status</th>
                        <th>Verified</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($zipcodes as $zipcode)
                        <tr>
                            <td>{{ $zipcode->zipcode }}</td>
                            <td>{{ $zipcode->city }}, {{ $zipcode->state }} ({{ $zipcode->country_code }})</td>
                            <td>
                                @if($zipcode->owner)
                                    <a href="{{ route('admin.users.show', $zipcode->owner) }}" style="color: #fff;">
                                        {{ $zipcode->owner->name }}
                                    </a>
                                @else
                                    <span class="badge badge-warning">Unclaimed</span>
                                @endif
                            </td>
                            <td>{{ $zipcode->members_count ?? $zipcode->members->count() }}</td>
                            <td>
                                @if($zipcode->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                @if($zipcode->is_verified)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-warning">No</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="{{ route('admin.zipcodes.show', $zipcode) }}"
                                        class="btn btn-sm btn-primary">View</a>

                                    <form action="{{ route('admin.zipcodes.toggleStatus', $zipcode) }}" method="POST"
                                        style="display: inline;">
                                        @csrf
                                        <button type="submit"
                                            class="btn btn-sm {{ $zipcode->is_active ? 'btn-danger' : 'btn-success' }}">
                                            {{ $zipcode->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">No zipcodes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1rem;">
            {{ $zipcodes->links() }}
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Zipcodes</div>
            <div class="stat-value">{{ $stats['total_zipcodes'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Active Zipcodes</div>
            <div class="stat-value">{{ $stats['active_zipcodes'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Verified Zipcodes</div>
            <div class="stat-value">{{ $stats['verified_zipcodes'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Members</div>
            <div class="stat-value">{{ $stats['total_members'] }}</div>
        </div>
    </div>
@endsection