@extends('layouts.app')

@section('content')
    <div class="gifts-received">
        <h1>Gifts Received</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🎁</div>
                <div>
                    <h3>{{ $stats['total_received'] }}</h3>
                    <p>Total Gifts</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div>
                    <h3>${{ number_format($stats['total_earnings'], 2) }}</h3>
                    <p>Total Earnings</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div>
                    <h3>${{ number_format($stats['this_month'], 2) }}</h3>
                    <p>This Month</p>
                </div>
            </div>
        </div>

        <div class="transactions-list">
            @forelse($transactions as $transaction)
                <div class="transaction-card">
                    <div class="gift-icon">{{ $transaction->gift->icon }}</div>
                    <div class="transaction-info">
                        <h4>{{ $transaction->gift->name }} x{{ $transaction->quantity }}</h4>
                        <p>From: <strong>{{ $transaction->sender_name }}</strong></p>
                        @if($transaction->track)
                            <p>On: <a href="{{ route('tracks.show', $transaction->track) }}">{{ $transaction->track->title }}</a>
                            </p>
                        @endif
                        @if($transaction->message)
                            <p class="message">"{{ $transaction->message }}"</p>
                        @endif
                        <small>{{ $transaction->created_at->diffForHumans() }}</small>
                    </div>
                    <div class="transaction-amount">
                        <span class="total">${{ number_format($transaction->total_amount, 2) }}</span>
                        <span class="earning">You earned: ${{ number_format($transaction->artist_earning, 2) }}</span>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <p>No gifts received yet. Keep creating amazing music! 🎵</p>
                </div>
            @endforelse
        </div>

        {{ $transactions->links() }}
    </div>

    <style>
        .gifts-received {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1)
        }

        .stat-icon {
            font-size: 2.5rem
        }

        .stat-card h3 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 700
        }

        .stat-card p {
            margin: 5px 0 0;
            color: #6b7280
        }

        .transactions-list {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1)
        }

        .transaction-card {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            border-bottom: 1px solid #e5e7eb
        }

        .transaction-card:last-child {
            border-bottom: none
        }

        .gift-icon {
            font-size: 3rem
        }

        .transaction-info {
            flex: 1
        }

        .transaction-info h4 {
            margin: 0 0 10px;
            font-size: 1.125rem
        }

        .transaction-info p {
            margin: 5px 0;
            color: #6b7280
        }

        .transaction-info .message {
            font-style: italic;
            color: #4f46e5
        }

        .transaction-amount {
            text-align: right
        }

        .transaction-amount .total {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: #10b981
        }

        .transaction-amount .earning {
            display: block;
            font-size: .875rem;
            color: #6b7280;
            margin-top: 5px
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280
        }
    </style>
@endsection