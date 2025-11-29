@extends('layouts.app')

@section('content')
    <div class="gift-earnings">
        <h1>Gift Earnings</h1>

        <div class="total-earnings-card">
            <div class="icon">💎</div>
            <div>
                <h2>Total Earnings</h2>
                <div class="amount">${{ number_format($totalEarnings, 2) }}</div>
                <p>From all gifts received</p>
            </div>
        </div>

        <h3>Earnings by Gift Type</h3>
        <div class="earnings-grid">
            @foreach($earningsByGift as $earning)
                <div class="earning-card">
                    <div class="gift-icon">{{ $earning->gift->icon }}</div>
                    <div class="earning-info">
                        <h4>{{ $earning->gift->name }}</h4>
                        <p>{{ $earning->count }} gifts received</p>
                        <div class="earning-amount">${{ number_format($earning->total, 2) }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <h3>Monthly Earnings</h3>
        <div class="monthly-chart">
            @foreach($monthlyEarnings as $month)
                <div class="month-bar">
                    <div class="bar" style="height: {{ ($month->total / $monthlyEarnings->max('total')) * 100 }}%"></div>
                    <div class="month-label">{{ date('M', strtotime($month->month . '-01')) }}</div>
                    <div class="month-amount">${{ number_format($month->total, 2) }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <style>
        .gift-earnings {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px
        }

        .total-earnings-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border-radius: 16px;
            padding: 40px;
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 40px
        }

        .total-earnings-card .icon {
            font-size: 4rem
        }

        .total-earnings-card h2 {
            margin: 0 0 10px;
            font-size: 1.25rem;
            opacity: .9
        }

        .total-earnings-card .amount {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 10px
        }

        .total-earnings-card p {
            margin: 0;
            opacity: .8
        }

        .earnings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px
        }

        .earning-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
            display: flex;
            align-items: center;
            gap: 15px
        }

        .gift-icon {
            font-size: 2.5rem
        }

        .earning-info h4 {
            margin: 0 0 5px;
            font-size: 1.125rem
        }

        .earning-info p {
            margin: 0 0 10px;
            color: #6b7280;
            font-size: .875rem
        }

        .earning-amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: #10b981
        }

        .monthly-chart {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
            display: flex;
            gap: 15px;
            align-items: flex-end;
            height: 300px
        }

        .month-bar {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px
        }

        .bar {
            width: 100%;
            background: linear-gradient(to top, #4f46e5, #8b5cf6);
            border-radius: 8px 8px 0 0;
            min-height: 20px
        }

        .month-label {
            font-size: .875rem;
            color: #6b7280
        }

        .month-amount {
            font-size: .75rem;
            font-weight: 600;
            color: #374151
        }
    </style>
@endsection