@extends('layouts.app')

@section('content')
    <div class="available-gifts">
        <h1>Send a Gift</h1>
        <p>Support your favorite artists by sending them gifts!</p>

        <div class="gifts-grid">
            @foreach($gifts as $gift)
                <div class="gift-card" data-gift-id="{{ $gift->id }}" data-gift-price="{{ $gift->price }}">
                    <div class="gift-icon">{{ $gift->icon }}</div>
                    <h3>{{ $gift->name }}</h3>
                    <p>{{ $gift->description }}</p>
                    <div class="gift-price">${{ number_format($gift->price, 2) }}</div>
                    <button class="btn btn-primary"
                        onclick="selectGift({{ $gift->id }}, '{{ $gift->name }}', {{ $gift->price }})">
                        Select
                    </button>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Gift Modal (to be triggered from track pages) -->
    <div id="giftModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="closeGiftModal()">&times;</span>
            <h2>Send Gift</h2>
            <form id="giftForm" method="POST">
                @csrf
                <input type="hidden" name="gift_id" id="selected_gift_id">
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" name="quantity" id="gift_quantity" value="1" min="1" max="100"
                        class="form-control">
                </div>
                <div class="form-group">
                    <label>Message (optional)</label>
                    <textarea name="message" rows="3" class="form-control" placeholder="Say something nice..."></textarea>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_anonymous" value="1">
                        <span>Send anonymously</span>
                    </label>
                </div>
                <div class="total-display">
                    Total: $<span id="total_amount">0.00</span>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Send Gift</button>
            </form>
        </div>
    </div>

    <style>
        .available-gifts {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px
        }

        .gifts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px
        }

        .gift-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
            transition: transform .2s
        }

        .gift-card:hover {
            transform: translateY(-4px)
        }

        .gift-icon {
            font-size: 4rem;
            margin-bottom: 15px
        }

        .gift-card h3 {
            margin: 0 0 10px;
            font-size: 1.25rem
        }

        .gift-card p {
            color: #6b7280;
            font-size: .875rem;
            margin-bottom: 15px
        }

        .gift-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #10b981;
            margin-bottom: 15px
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            width: 100%
        }

        .btn-primary {
            background: #4f46e5;
            color: #fff
        }

        .btn-primary:hover {
            background: #4338ca
        }

        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, .5)
        }

        .modal-content {
            background: #fff;
            margin: 5% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            position: relative
        }

        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 28px;
            font-weight: 700;
            cursor: pointer
        }

        .form-group {
            margin-bottom: 20px
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px
        }

        .total-display {
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            margin: 20px 0;
            color: #10b981
        }

        .btn-block {
            width: 100%
        }
    </style>

    <script>
        function selectGift(id, name, price) {
            document.getElementById('selected_gift_id').value = id;
            document.getElementById('gift_quantity').value = 1;
            updateTotal(price);
            document.getElementById('giftModal').style.display = 'block';
        }

        function closeGiftModal() {
            document.getElementById('giftModal').style.display = 'none';
        }

        document.getElementById('gift_quantity')?.addEventListener('input', function () {
            const price = parseFloat(document.querySelector('.gift-card[data-gift-id="' + document.getElementById('selected_gift_id').value + '"]')?.dataset.giftPrice || 0);
            updateTotal(price);
        });

        function updateTotal(price) {
            const qty = parseInt(document.getElementById('gift_quantity').value) || 1;
            document.getElementById('total_amount').textContent = (price * qty).toFixed(2);
        }
    </script>
@endsection