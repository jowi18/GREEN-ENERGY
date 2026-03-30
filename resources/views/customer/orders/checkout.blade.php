@extends('layouts.customer')
@section('title', 'Checkout')

@push('styles')
    <style>
        .payment-option {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 1rem 1.1rem;
            border: 2px solid var(--card-border);
            border-radius: var(--r-md);
            cursor: pointer;
            transition: border-color var(--t-base), background var(--t-base);
            margin-bottom: 0.65rem;
            user-select: none;
        }

        .payment-option:last-child {
            margin-bottom: 0;
        }

        .payment-option:hover {
            border-color: var(--cg-300);
            background: var(--cg-50);
        }

        .payment-option.selected {
            border-color: var(--cg-500);
            background: var(--cg-50);
        }

        .payment-option input[type="radio"] {
            display: none;
        }

        .payment-option__radio {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid var(--cn-300);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: border-color var(--t-fast), background var(--t-fast);
        }

        .payment-option.selected .payment-option__radio {
            border-color: var(--cg-500);
            background: var(--cg-500);
        }

        .payment-option.selected .payment-option__radio::after {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #fff;
        }

        .payment-option__icon {
            width: 42px;
            height: 42px;
            border-radius: var(--r-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .payment-option__icon--cod {
            background: var(--cg-50);
            color: var(--cg-600);
        }

        .payment-option__icon--paypal {
            background: #e8f0fe;
            color: #0070ba;
        }

        .payment-option__label {
            font-weight: 700;
            font-size: 0.9rem;
        }

        .payment-option__sub {
            font-size: 0.78rem;
            color: var(--tx-muted);
            margin-top: 1px;
        }

        /* PayPal notice --*/
        .paypal-notice {
            display: none;
            background: #e8f0fe;
            border: 1px solid #c5d5f5;
            border-radius: var(--r-sm);
            padding: 0.65rem 0.85rem;
            font-size: 0.8rem;
            color: #1a4a8a;
            margin-top: 0.65rem;
            line-height: 1.6;
        }

        .paypal-notice.show {
            display: flex;
            gap: 0.5rem;
            align-items: flex-start;
        }
    </style>
@endpush

@section('content')
    <div class="cu-page" style="max-width:720px;">

        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.5rem;">
            <a href="{{ route('customer.products.index') }}" class="cu-btn cu-btn--ghost cu-btn--sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="fw-800 mb-0" style="font-family:'Nunito',sans-serif;">Checkout</h5>
        </div>

        <form method="POST" action="{{ route('customer.orders.store') }}" id="checkoutForm">
            @csrf

            <div class="row g-3">

                {{-- ── Left: delivery + payment ── --}}
                <div class="col-12 col-md-7">

                    {{-- Delivery address --}}
                    <div class="cu-card mb-3">
                        <div class="cu-card__body">
                            <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                                <i class="bi bi-geo-alt-fill text-green me-1"></i>Delivery Address
                            </div>

                            <div class="mb-3">
                                <label class="cu-label">Street address <span class="req">*</span></label>
                                <input type="text" name="shipping_address_line1"
                                    value="{{ old('shipping_address_line1', $customer->address_line1) }}"
                                    class="cu-input @error('shipping_address_line1') is-invalid @enderror"
                                    placeholder="123 Rizal Street, Barangay…" required>
                                @error('shipping_address_line1')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="cu-label">Address line 2</label>
                                <input type="text" name="shipping_address_line2"
                                    value="{{ old('shipping_address_line2') }}" class="cu-input"
                                    placeholder="Unit / Floor (optional)">
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="cu-label">City <span class="req">*</span></label>
                                    <input type="text" name="shipping_city"
                                        value="{{ old('shipping_city', $customer->city) }}"
                                        class="cu-input @error('shipping_city') is-invalid @enderror" required>
                                    @error('shipping_city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-6">
                                    <label class="cu-label">Province <span class="req">*</span></label>
                                    <input type="text" name="shipping_province"
                                        value="{{ old('shipping_province', $customer->province_state) }}"
                                        class="cu-input @error('shipping_province') is-invalid @enderror" required>
                                    @error('shipping_province')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="cu-label">Postal code <span class="req">*</span></label>
                                <input type="text" name="shipping_postal_code"
                                    value="{{ old('shipping_postal_code', $customer->postal_code) }}"
                                    class="cu-input @error('shipping_postal_code') is-invalid @enderror"
                                    style="max-width:160px;" required>
                                @error('shipping_postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="cu-label">Order notes</label>
                                <textarea name="notes" rows="2" class="cu-textarea"
                                    placeholder="Special instructions, preferred delivery time…">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Payment method --}}
                    <div class="cu-card mb-3">
                        <div class="cu-card__body">
                            <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                                <i class="bi bi-credit-card-fill text-green me-1"></i>Payment Method
                            </div>

                            {{-- Cash on Delivery --}}
                            <label class="payment-option selected" id="opt-cod"
                                onclick="selectPayment('cash_on_delivery')">
                                <input type="radio" name="payment_method" value="cash_on_delivery" checked>
                                <div class="payment-option__radio"></div>
                                <div class="payment-option__icon payment-option__icon--cod">
                                    <i class="bi bi-cash-coin"></i>
                                </div>
                                <div>
                                    <div class="payment-option__label">Cash on Delivery</div>
                                    <div class="payment-option__sub">Pay when your order arrives at your door</div>
                                </div>
                            </label>

                            {{-- PayPal --}}
                            <label class="payment-option" id="opt-paypal" onclick="selectPayment('paypal')">
                                <input type="radio" name="payment_method" value="paypal">
                                <div class="payment-option__radio"></div>
                                <div class="payment-option__icon payment-option__icon--paypal">
                                    <img src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/PP_logo_h_100x26.png"
                                        alt="PayPal" style="height:14px;filter:none;">
                                </div>
                                <div>
                                    <div class="payment-option__label">PayPal</div>
                                    <div class="payment-option__sub">Pay securely via PayPal — instant confirmation</div>
                                </div>
                            </label>

                            {{-- PayPal note --}}
                            <div class="paypal-notice" id="paypalNotice">
                                <i class="bi bi-info-circle-fill" style="flex-shrink:0;margin-top:1px;"></i>
                                <span>
                                    You'll be redirected to PayPal to complete payment.
                                    Your order is reserved and will be confirmed automatically once payment is successful.
                                    <br><strong>Note:</strong> PayPal processes payment in USD. Your bank may apply a
                                    conversion rate.
                                </span>
                            </div>

                            {{-- Hidden field that JS updates --}}
                            <input type="hidden" name="payment_method" id="paymentMethodField" value="cash_on_delivery">
                        </div>
                    </div>

                </div>

                {{-- Warranty terms notice --}}
                <div class="cu-card mb-3">
                    <div class="cu-card__body" style="padding:.85rem 1.1rem;">

                        {{-- Header toggle --}}
                        <button type="button"
                            style="width:100%;background:none;border:none;padding:0;
                                display:flex;align-items:center;gap:.65rem;cursor:pointer;text-align:left;"
                            onclick="toggleWarrantyTerms(this)">
                            <div
                                style="width:34px;height:34px;border-radius:var(--r-sm);background:var(--cg-50);
                                    color:var(--cg-600);display:flex;align-items:center;justify-content:center;
                                    font-size:.9rem;flex-shrink:0;">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div style="flex:1;">
                                <div class="fw-700" style="font-size:.875rem;color:var(--tx-primary);">
                                    Warranty Policy
                                </div>
                                <div style="font-size:.75rem;color:var(--tx-muted);margin-top:1px;">
                                    Tap to read what's covered with your purchase
                                </div>
                            </div>
                            <i class="bi bi-chevron-down"
                                style="color:var(--tx-muted);font-size:.8rem;transition:transform .2s;flex-shrink:0;"></i>
                        </button>

                        {{-- Collapsible body --}}
                        <div id="warrantyTermsBody"
                            style="display:none;margin-top:.85rem;
                                padding-top:.85rem;border-top:1px solid var(--card-border);">

                                {{-- Covered --}}
                            <div
                                style="font-size:.72rem;font-weight:700;text-transform:uppercase;
                                    letter-spacing:.06em;color:var(--cg-600);margin-bottom:.5rem;">
                                ✅ Covered by Warranty
                            </div>
                            @foreach (['Factory and manufacturing defects', 'Hardware malfunction under normal use', 'Faulty components (board, wiring, sensors)', 'Early failure within the warranty period', 'Free repair parts during the warranty period'] as $item)
                                <div
                                    style="display:flex;align-items:flex-start;gap:.45rem;
                                            font-size:.8rem;color:var(--tx-secondary);margin-bottom:.35rem;">
                                    <i class="bi bi-check-circle-fill"
                                        style="color:var(--cg-500);font-size:.72rem;margin-top:.2rem;flex-shrink:0;"></i>
                                    {{ $item }}
                                </div>
                            @endforeach

                            <div style="height:.65rem;"></div>

                            {{-- Not covered --}}
                            <div
                                style="font-size:.72rem;font-weight:700;text-transform:uppercase;
                                    letter-spacing:.06em;color:#dc2626;margin-bottom:.5rem;">
                                ❌ Not Covered
                            </div>
                            @foreach (['Physical damage (drops, cracks, dents)', 'Water or fire damage', 'Improper installation or unauthorized repair', 'Power surge or lightning damage', 'Normal wear and tear'] as $item)
                                <div
                                    style="display:flex;align-items:flex-start;gap:.45rem;
                                        font-size:.8rem;color:var(--tx-secondary);margin-bottom:.35rem;">
                                    <i class="bi bi-x-circle-fill"
                                        style="color:#ef4444;font-size:.72rem;margin-top:.2rem;flex-shrink:0;"></i>
                                    {{ $item }}
                                </div>
                            @endforeach

                            {{-- Link to full terms --}}
                            <a href="{{ route('customer.warranty.customer.warranty.terms', $vendor) }}" target="_blank"
                                style="display:inline-flex;align-items:center;gap:.35rem;margin-top:.75rem;
                                    font-size:.78rem;font-weight:600;color:var(--cg-600);text-decoration:none;">
                                <i class="bi bi-box-arrow-up-right" style="font-size:.7rem;"></i>
                                Read full warranty terms & conditions
                            </a>
                        </div>

                    </div>
                </div>

                {{-- ── Right: order summary ── --}}
                <div class="col-12 col-md-5">
                    <div class="cu-card" style="position:sticky;top:calc(var(--navbar-h) + 1rem);">
                        <div class="cu-card__body">
                            <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                                Order from {{ $vendor->business_name }}
                            </div>

                            @foreach ($items as $item)
                                <div
                                    style="display:flex;gap:0.65rem;margin-bottom:0.75rem;
                                    padding-bottom:0.75rem;border-bottom:1px solid var(--cn-100);">
                                    @if ($item['product']->primaryImage)
                                        <img src="{{ asset('storage/' . $item['product']->primaryImage->file_path) }}"
                                            style="width:46px;height:46px;border-radius:var(--r-sm);
                                        object-fit:cover;border:1px solid var(--card-border);flex-shrink:0;">
                                    @else
                                        <div
                                            style="width:46px;height:46px;border-radius:var(--r-sm);
                                        background:var(--cn-50);display:flex;align-items:center;
                                        justify-content:center;flex-shrink:0;">
                                            <i class="bi bi-box-seam text-muted"></i>
                                        </div>
                                    @endif
                                    <div style="flex:1;min-width:0;">
                                        <div style="font-size:0.845rem;font-weight:700;line-height:1.3;">
                                            {{ $item['product']->name }}
                                        </div>
                                        <div class="text-muted" style="font-size:0.75rem;">
                                            ₱{{ number_format($item['product']->price, 2) }} × {{ $item['qty'] }}
                                        </div>
                                    </div>
                                    <div class="mono fw-700" style="font-size:0.845rem;flex-shrink:0;">
                                        ₱{{ number_format($item['lineTotal'], 2) }}
                                    </div>
                                </div>
                            @endforeach

                            {{-- Totals --}}
                            <div style="font-size:0.875rem;">
                                <div style="display:flex;justify-content:space-between;margin-bottom:0.35rem;">
                                    <span class="text-muted">Subtotal</span>
                                    <span class="mono">₱{{ number_format($total, 2) }}</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;margin-bottom:0.35rem;">
                                    <span class="text-muted">Shipping</span>
                                    <span class="text-muted" style="font-size:0.8rem;">Arranged by vendor</span>
                                </div>
                                <div
                                    style="display:flex;justify-content:space-between;
                                        border-top:1.5px solid var(--card-border);
                                        padding-top:0.6rem;margin-top:0.35rem;
                                        font-weight:800;font-size:1rem;">
                                    <span>Total</span>
                                    <span class="mono"
                                        style="color:var(--cg-700);">₱{{ number_format($total, 2) }}</span>
                                </div>
                            </div>

                            <hr style="border-color:var(--cn-100);margin:1rem 0;">

                            {{-- Submit button — label changes based on payment method --}}
                            <button type="submit" class="cu-btn cu-btn--primary cu-btn--full cu-btn--lg"
                                id="placeOrderBtn">
                                <i class="bi bi-bag-check" id="btnIcon"></i>
                                <span id="btnText">Place Order — ₱{{ number_format($total, 2) }}</span>
                            </button>

                            <p class="text-muted text-center mt-2" style="font-size:0.75rem;">
                                <i class="bi bi-lock-fill me-1"></i>
                                Your information is safe and secure
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        let currentMethod = 'cash_on_delivery';
        const totalFormatted = '₱{{ number_format($total, 2) }}';

        function selectPayment(method) {
            currentMethod = method;

            // Update hidden field (the radio inputs are visually-only)
            document.getElementById('paymentMethodField').value = method;

            // Update visual state
            document.getElementById('opt-cod').classList.toggle('selected', method === 'cash_on_delivery');
            document.getElementById('opt-paypal').classList.toggle('selected', method === 'paypal');

            // Show/hide PayPal notice
            document.getElementById('paypalNotice').classList.toggle('show', method === 'paypal');

            // Update button text
            const btnText = document.getElementById('btnText');
            const btnIcon = document.getElementById('btnIcon');

            if (method === 'paypal') {
                btnText.textContent = `Pay with PayPal — ${totalFormatted}`;
                btnIcon.className = 'bi bi-paypal';
            } else {
                btnText.textContent = `Place Order — ${totalFormatted}`;
                btnIcon.className = 'bi bi-bag-check';
            }
        }

        document.getElementById('checkoutForm').addEventListener('submit', function() {
            const btn = document.getElementById('placeOrderBtn');
            btn.disabled = true;

            if (currentMethod === 'paypal') {
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Redirecting to PayPal…';
            } else {
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Placing order…';
            }
        });

        function toggleWarrantyTerms(btn) {
            const body    = document.getElementById('warrantyTermsBody');
            const chevron = btn.querySelector('.bi-chevron-down, .bi-chevron-up');
            const open    = body.style.display !== 'none';

            body.style.display     = open ? 'none' : 'block';
            chevron.style.transform = open ? '' : 'rotate(180deg)';
        }
    </script>
@endpush
