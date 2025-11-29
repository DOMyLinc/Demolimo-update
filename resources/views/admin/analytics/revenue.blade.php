@extends('layouts.admin')

@section('title', 'Revenue Analytics')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Revenue Analytics</h1>
            <a href="{{ route('admin.analytics.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Analytics
            </a>
        </div>

        <!-- Revenue Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-white-50">Total Active Subscriptions</h6>
                        <h2 class="mb-0">{{ $revenueByMonth->sum('subscriptions') }}</h2>
                        <small class="text-white-50">All time</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-white-50">This Month</h6>
                        <h2 class="mb-0">{{ $revenueByMonth->last()->subscriptions ?? 0 }}</h2>
                        <small class="text-white-50">New subscriptions</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-white-50">Average per Month</h6>
                        <h2 class="mb-0">
                            {{ $revenueByMonth->isNotEmpty() ? round($revenueByMonth->avg('subscriptions')) : 0 }}</h2>
                        <small class="text-white-50">Last 12 months</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Chart -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Subscription Revenue Trend (Last 12 Months)</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>

        <!-- Monthly Breakdown Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Monthly Breakdown</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th class="text-end">New Subscriptions</th>
                                <th class="text-end">Growth</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $previous = 0; @endphp
                            @foreach($revenueByMonth as $revenue)
                                @php
                                    $growth = $previous > 0 ? (($revenue->subscriptions - $previous) / $previous) * 100 : 0;
                                    $previous = $revenue->subscriptions;
                                @endphp
                                <tr>
                                    <td>{{ date('F Y', mktime(0, 0, 0, $revenue->month, 1, $revenue->year)) }}</td>
                                    <td class="text-end">{{ $revenue->subscriptions }}</td>
                                    <td class="text-end">
                                        @if($growth > 0)
                                            <span class="text-success">
                                                <i class="fas fa-arrow-up"></i> {{ number_format($growth, 1) }}%
                                            </span>
                                        @elseif($growth < 0)
                                            <span class="text-danger">
                                                <i class="fas fa-arrow-down"></i> {{ number_format(abs($growth), 1) }}%
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($revenueByMonth->map(function ($r) {
            return date('M Y', mktime(0, 0, 0, $r->month, 1, $r->year)); })) !!},
                    datasets: [{
                        label: 'Subscriptions',
                        data: {!! json_encode($revenueByMonth->pluck('subscriptions')) !!},
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        </script>
    @endpush
@endsection