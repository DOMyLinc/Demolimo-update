@extends('layouts.admin')

@section('page-title', 'Zipcode Analytics')

@section('content')
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Top Country</div>
            <div class="stat-value">
                {{ $zipcodesByCountry->sortByDesc('count')->first()->country_code ?? 'N/A' }}
            </div>
        </div>
        <!-- Add more summary stats here -->
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Top Zipcodes by Members</h3>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Zipcode</th>
                        <th>Location</th>
                        <th>Members</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topZipcodes as $zipcode)
                        <tr>
                            <td>
                                <a href="{{ route('admin.zipcodes.show', $zipcode) }}" style="color: #fff;">
                                    {{ $zipcode->zipcode }}
                                </a>
                            </td>
                            <td>{{ $zipcode->city }}, {{ $zipcode->country_code }}</td>
                            <td>{{ $zipcode->members_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Claims</h3>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Zipcode</th>
                        <th>Owner</th>
                        <th>Claimed At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentClaims as $zipcode)
                        <tr>
                            <td>{{ $zipcode->zipcode }}</td>
                            <td>{{ $zipcode->owner->name ?? 'Unknown' }}</td>
                            <td>{{ $zipcode->claimed_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection