<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #111;
            background: #f5f5f5;
            padding: 2rem 1rem;
        }

        .invoice {
            max-width: 720px;
            margin: 0 auto;
            background: #fff;
            padding: 2.5rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        /* ── Header ── */
        .inv-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #157a30;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .inv-brand-name {
            font-size: 1.3rem;
            font-weight: 800;
            color: #157a30;
            letter-spacing: -0.02em;
        }

        .inv-brand-meta {
            font-size: 0.78rem;
            color: #555;
            line-height: 1.7;
            margin-top: 0.35rem;
        }

        .inv-label {
            text-align: right;
        }

        .inv-label .title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #111;
            letter-spacing: -0.02em;
        }

        .inv-label .number {
            font-family: 'Courier New', monospace;
            font-size: 0.88rem;
            color: #555;
            margin-top: 0.25rem;
        }

        /* ── Meta rows ── */
        .inv-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .inv-meta-block h6 {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #888;
            margin-bottom: 0.45rem;
        }

        .inv-meta-block p {
            font-size: 0.85rem;
            color: #333;
            line-height: 1.7;
        }

        .inv-meta-block strong {
            font-weight: 700;
            color: #111;
        }

        /* ── Items table ── */
        .inv-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }

        .inv-table thead th {
            background: #f0f5f0;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #555;
            padding: 0.55rem 0.85rem;
            border-bottom: 1.5px solid #d5ddd7;
            text-align: left;
        }

        .inv-table thead th:last-child {
            text-align: right;
        }

        .inv-table tbody td {
            padding: 0.7rem 0.85rem;
            border-bottom: 1px solid #eaf0eb;
            font-size: 0.85rem;
            vertical-align: top;
        }

        .inv-table tbody td:last-child {
            text-align: right;
            font-family: 'Courier New', monospace;
        }

        .inv-table tbody td.qty {
            text-align: center;
            font-family: 'Courier New', monospace;
        }

        .inv-table tbody td.price {
            font-family: 'Courier New', monospace;
        }

        .inv-table tfoot td {
            padding: 0.5rem 0.85rem;
            font-size: 0.85rem;
        }

        .inv-table tfoot .total-row td {
            font-size: 1rem;
            font-weight: 800;
            border-top: 2px solid #157a30;
            padding-top: 0.65rem;
        }

        .inv-table tfoot td:last-child {
            text-align: right;
            font-family: 'Courier New', monospace;
        }

        /* ── Totals sidebar ── */
        .inv-totals {
            display: flex;
            justify-content: flex-end;
        }

        .inv-totals-inner {
            width: 260px;
        }

        .inv-total-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            padding: 0.3rem 0;
        }

        .inv-total-row.grand {
            font-size: 1rem;
            font-weight: 800;
            border-top: 2px solid #157a30;
            margin-top: 0.35rem;
            padding-top: 0.5rem;
            color: #157a30;
        }

        .inv-total-row .amount {
            font-family: 'Courier New', monospace;
        }

        /* ── Payment & status ── */
        .inv-payment {
            display: flex;
            gap: 1.5rem;
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px dashed #ccc;
        }

        .inv-payment-block h6 {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #888;
            margin-bottom: 0.35rem;
        }

        .inv-payment-block p {
            font-size: 0.85rem;
            font-weight: 700;
            color: #111;
        }

        .status-paid {
            color: #157a30;
        }

        .status-unpaid {
            color: #e67e22;
        }

        .status-cancelled {
            color: #e74c3c;
        }

        /* ── Footer ── */
        .inv-footer {
            margin-top: 2rem;
            padding-top: 1.25rem;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 0.78rem;
            color: #888;
        }

        .inv-footer strong {
            color: #157a30;
        }

        /* ── Print actions (screen only) ── */
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
            font-weight: 700;
            cursor: pointer;
        }

        .btn-close-tab {
            padding: 0.6rem 1.5rem;
            background: transparent;
            color: #555;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 0.875rem;
            cursor: pointer;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }

            .invoice {
                box-shadow: none;
                padding: 0;
            }

            .print-actions {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="invoice" id="invoice">

        {{-- Header --}}
        <div class="inv-header">
            <div>
                <div class="inv-brand-name">{{ $order->vendor->business_name }}</div>
                <div class="inv-brand-meta">
                    {{ $order->vendor->full_address }}<br>
                    @if ($order->vendor->business_phone)
                        {{ $order->vendor->business_phone }}<br>
                    @endif
                    @if ($order->vendor->business_email)
                        {{ $order->vendor->business_email }}
                    @endif
                </div>
            </div>
            <div class="inv-label">
                <div class="title">INVOICE</div>
                <div class="number">{{ $order->order_number }}</div>
            </div>
        </div>

        {{-- Meta --}}
        <div class="inv-meta">
            <div class="inv-meta-block">
                <h6>Bill To</h6>
                <p>
                    <strong>{{ $order->customer->full_name }}</strong><br>
                    {{ $order->shipping_address_line1 }}
                    @if ($order->shipping_address_line2)
                        , {{ $order->shipping_address_line2 }}
                    @endif
                    <br>
                    {{ $order->shipping_city }}, {{ $order->shipping_province }}
                    {{ $order->shipping_postal_code }}<br>
                    {{ $order->customer->phone }}
                </p>
            </div>
            <div class="inv-meta-block">
                <h6>Invoice Details</h6>
                <p>
                    <strong>Date:</strong> {{ $order->created_at->format('F d, Y') }}<br>
                    @if ($order->paid_at)
                        <strong>Paid:</strong> {{ $order->paid_at->format('F d, Y') }}<br>
                    @endif
                    <strong>Payment:</strong>
                    {{ $order->payment_method === 'paypal' ? 'PayPal' : 'Cash on Delivery' }}<br>
                    <strong>Status:</strong>
                    <span
                        class="status-{{ $order->payment_status === 'paid' ? 'paid' : ($order->status === 'cancelled' ? 'cancelled' : 'unpaid') }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </p>
            </div>
        </div>

        {{-- Items --}}
        <table class="inv-table">
            <thead>
                <tr>
                    <th style="width:50%;">Description</th>
                    <th class="qty" style="width:10%;text-align:center;">Qty</th>
                    <th style="width:20%;">Unit Price</th>
                    <th style="width:20%;text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->product_name }}</strong>
                            @if ($item->warranty_months)
                                <br><span style="font-size:0.75rem;color:#888;">
                                    {{ $item->warranty_months }}-month warranty included
                                </span>
                            @endif
                        </td>
                        <td class="qty">{{ $item->quantity }}</td>
                        <td class="price">₱{{ number_format($item->unit_price, 2) }}</td>
                        <td>₱{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="inv-totals">
            <div class="inv-totals-inner">
                <div class="inv-total-row">
                    <span>Subtotal</span>
                    <span class="amount">₱{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if ($order->discount_amount > 0)
                    <div class="inv-total-row">
                        <span>Discount</span>
                        <span class="amount">— ₱{{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="inv-total-row">
                    <span>Shipping</span>
                    <span
                        class="amount">{{ $order->shipping_fee > 0 ? '₱' . number_format($order->shipping_fee, 2) : 'TBD' }}</span>
                </div>
                <div class="inv-total-row grand">
                    <span>TOTAL</span>
                    <span class="amount">₱{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Payment & Delivery --}}
        <div class="inv-payment">
            <div class="inv-payment-block">
                <h6>Payment Method</h6>
                <p>{{ $order->payment_method === 'paypal' ? 'PayPal' : 'Cash on Delivery' }}</p>
            </div>
            @if ($order->delivery)
                <div class="inv-payment-block">
                    <h6>Courier</h6>
                    <p>{{ $order->delivery->courier_name }}</p>
                </div>
                @if ($order->delivery->tracking_number)
                    <div class="inv-payment-block">
                        <h6>Tracking #</h6>
                        <p style="font-family:'Courier New',monospace;">{{ $order->delivery->tracking_number }}</p>
                    </div>
                @endif
            @endif
        </div>

        @if ($order->notes)
            <div
                style="margin-top:1.25rem;padding:0.85rem;background:#f9fafb;border-radius:6px;font-size:0.82rem;color:#555;">
                <strong style="color:#333;">Customer Note:</strong> {{ $order->notes }}
            </div>
        @endif

        {{-- Footer --}}
        <div class="inv-footer">
            <p>Thank you for your business! — <strong>{{ $order->vendor->business_name }}</strong></p>
            @if ($order->vendor->business_website)
                <p style="margin-top:0.3rem;">{{ $order->vendor->business_website }}</p>
            @endif
            <p style="margin-top:0.65rem;">
                This is a computer-generated invoice. No signature required.
            </p>
        </div>

    </div>

    <div class="print-actions">
        <button class="btn-print" onclick="window.print()">🖨 Print Invoice</button>
        <button class="btn-close-tab" onclick="window.close()">Close</button>
    </div>

</body>

</html>
