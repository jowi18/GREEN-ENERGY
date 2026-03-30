<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $transaction->transaction_number }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            padding: 2rem 1rem;
            color: #111;
        }

        .receipt {
            background: #fff;
            width: 320px;
            padding: 1.5rem 1.25rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
        }

        /* Header */
        .receipt-header {
            text-align: center;
            margin-bottom: 1rem;
        }

        .receipt-header .store-name {
            font-size: 1.05rem;
            font-weight: 700;
            font-family: Arial, sans-serif;
            letter-spacing: -0.01em;
        }

        .receipt-header .store-address,
        .receipt-header .store-phone {
            font-size: 0.78rem;
            color: #555;
            margin-top: 2px;
        }

        .receipt-header .receipt-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            border-top: 1px dashed #ccc;
            border-bottom: 1px dashed #ccc;
            padding: 0.35rem 0;
            margin-top: 0.75rem;
        }

        /* Transaction meta */
        .receipt-meta {
            margin-bottom: 0.85rem;
        }

        .receipt-meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.78rem;
            line-height: 1.8;
        }

        .receipt-meta-row .label {
            color: #666;
        }

        /* Items */
        .receipt-items {
            margin-bottom: 0.85rem;
        }

        .receipt-items-header {
            display: flex;
            justify-content: space-between;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border-top: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
            padding: 0.3rem 0;
            margin-bottom: 0.5rem;
        }

        .item-row {
            margin-bottom: 0.55rem;
        }

        .item-row-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 0.82rem;
        }

        .item-name {
            flex: 1;
            padding-right: 0.5rem;
            font-weight: 600;
        }

        .item-total {
            font-weight: 700;
            white-space: nowrap;
        }

        .item-detail {
            font-size: 0.72rem;
            color: #555;
            margin-top: 1px;
            padding-left: 0.5rem;
        }

        /* Totals */
        .receipt-totals {
            border-top: 1px dashed #ccc;
            padding-top: 0.6rem;
            margin-bottom: 0.85rem;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.82rem;
            line-height: 1.9;
        }

        .totals-row.grand-total {
            border-top: 1px solid #111;
            margin-top: 0.35rem;
            padding-top: 0.35rem;
            font-size: 1rem;
            font-weight: 700;
        }

        /* Payment */
        .receipt-payment {
            background: #f9f9f9;
            border: 1px dashed #ccc;
            padding: 0.6rem 0.75rem;
            margin-bottom: 0.85rem;
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            line-height: 1.9;
            font-weight: 600;
        }

        .payment-row.change {
            font-size: 1rem;
            font-weight: 700;
        }

        /* Footer */
        .receipt-footer {
            text-align: center;
            font-size: 0.75rem;
            color: #666;
            border-top: 1px dashed #ccc;
            padding-top: 0.75rem;
        }

        .receipt-footer .thank-you {
            font-size: 0.9rem;
            font-weight: 700;
            font-family: Arial, sans-serif;
            color: #111;
            margin-bottom: 0.3rem;
        }

        /* Print actions (hidden on print) */
        .print-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            margin-top: 1.5rem;
        }

        .btn-print {
            padding: 0.6rem 1.5rem;
            background: #157a30;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            font-family: Arial, sans-serif;
        }

        .btn-back {
            padding: 0.6rem 1.5rem;
            background: transparent;
            color: #555;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 0.875rem;
            cursor: pointer;
            font-family: Arial, sans-serif;
            text-decoration: none;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }

            .receipt {
                box-shadow: none;
                width: 100%;
            }

            .print-actions {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div>
        <div class="receipt" id="receipt">

            {{-- Header --}}
            <div class="receipt-header">
                <div class="store-name">{{ $transaction->vendor->business_name }}</div>
                @if ($transaction->vendor->full_address)
                    <div class="store-address">{{ $transaction->vendor->full_address }}</div>
                @endif
                @if ($transaction->vendor->business_phone)
                    <div class="store-phone">{{ $transaction->vendor->business_phone }}</div>
                @endif
                <div class="receipt-title">Official Receipt</div>
            </div>

            {{-- Transaction meta --}}
            <div class="receipt-meta">
                <div class="receipt-meta-row">
                    <span class="label">Receipt #</span>
                    <span>{{ $transaction->transaction_number }}</span>
                </div>
                <div class="receipt-meta-row">
                    <span class="label">Date</span>
                    <span>{{ date('M d, Y', strtotime($transaction->completed_at)) }}</span>
                </div>
                <div class="receipt-meta-row">
                    <span class="label">Time</span>
                    <span>{{ date('h:i A', strtotime($transaction->completed_at)) }}</span>
                </div>
                <div class="receipt-meta-row">
                    <span class="label">Cashier</span>
                    <span>{{ $transaction->cashier?->name ?? 'POS Terminal' }}</span>
                </div>
                @if ($transaction->customer_name)
                    <div class="receipt-meta-row">
                        <span class="label">Customer</span>
                        <span>{{ $transaction->customer_name }}</span>
                    </div>
                @endif
            </div>

            {{-- Items --}}
            <div class="receipt-items">
                <div class="receipt-items-header">
                    <span>Item</span>
                    <span>Amount</span>
                </div>

                @foreach ($transaction->items as $item)
                    <div class="item-row">
                        <div class="item-row-top">
                            <span class="item-name">{{ $item->product_name }}</span>
                            <span class="item-total">₱{{ number_format($item->total_price, 2) }}</span>
                        </div>
                        <div class="item-detail">
                            {{ $item->quantity }} x ₱{{ number_format($item->unit_price, 2) }}
                            @if ($item->discount_amount > 0)
                                — Disc: ₱{{ number_format($item->discount_amount, 2) }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Totals --}}
            <div class="receipt-totals">
                <div class="totals-row">
                    <span>Subtotal</span>
                    <span>₱{{ number_format($transaction->subtotal, 2) }}</span>
                </div>
                @if ($transaction->discount_amount > 0)
                    <div class="totals-row">
                        <span>Discount</span>
                        <span>— ₱{{ number_format($transaction->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="totals-row grand-total">
                    <span>TOTAL</span>
                    <span>₱{{ number_format($transaction->total_amount, 2) }}</span>
                </div>
            </div>

            {{-- Payment --}}
            <div class="receipt-payment">
                <div class="payment-row">
                    <span>Payment</span>
                    <span>CASH</span>
                </div>
                <div class="payment-row">
                    <span>Cash Tendered</span>
                    <span>₱{{ number_format($transaction->cash_tendered, 2) }}</span>
                </div>
                <div class="payment-row change">
                    <span>Change Due</span>
                    <span>₱{{ number_format($transaction->change_due, 2) }}</span>
                </div>
            </div>

            @if ($transaction->notes)
                <p style="font-size:0.75rem;color:#666;margin-bottom:0.85rem;font-style:italic;">
                    Note: {{ $transaction->notes }}
                </p>
            @endif

            {{-- Footer --}}
            <div class="receipt-footer">
                <div class="thank-you">Thank you!</div>
                <p>{{ $transaction->vendor->business_name }}</p>
                <p style="margin-top:0.35rem;font-size:0.7rem;">
                    Items sold are not returnable unless defective.
                </p>
                @if ($transaction->vendor->business_website)
                    <p style="margin-top:0.3rem;">{{ $transaction->vendor->business_website }}</p>
                @endif
            </div>

        </div>

        <div class="print-actions">
            <button class="btn-print" onclick="window.print()">🖨 Print Receipt</button>
            <a href="{{ route('vendor.pos.index') }}" class="btn-back">← New Sale</a>
        </div>
    </div>

</body>

</html>
