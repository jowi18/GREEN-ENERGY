@extends('layouts.admin')
@section('title', $customer->full_name)
@section('page-title', 'Customer Profile')
@section('breadcrumb')
    <a href="{{ route('admin.customers.index') }}" class="text-secondary">Customers</a>
    <span class="sep">›</span><span class="current">{{ $customer->full_name }}</span>
@endsection

@section('content')
    <div class="row g-3">

        {{-- Profile card --}}
        <div class="col-12 col-lg-4">
            <div class="panel mb-3">
                <div class="text-center" style="padding:2rem 1.5rem 1rem;">
                    <div
                        style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#1d4ed8);
                            color:#fff;font-size:1.5rem;font-weight:800;display:flex;align-items:center;
                            justify-content:center;margin:0 auto 1rem;">
                        {{ strtoupper(substr($customer->first_name, 0, 1) . substr($customer->last_name, 0, 1)) }}
                    </div>
                    <h5 class="fw-800 mb-0" style="font-size:1.05rem;">{{ $customer->full_name }}</h5>
                    <div class="text-muted mt-1" style="font-size:.82rem;">{{ $customer->user->email }}</div>
                    <div class="mt-2">
                        @php $sc=['verified'=>'bg-success','unverified'=>'bg-warning text-dark','suspended'=>'bg-danger']; @endphp
                        <span class="badge {{ $sc[$customer->verification_status] ?? 'bg-secondary' }}">
                            {{ ucfirst($customer->verification_status) }}
                        </span>
                    </div>
                </div>
                <div style="border-top:1px solid #f1f5f1;">
                    @foreach ([['bi-telephone', 'Phone', $customer->phone ?? '—'], ['bi-geo-alt', 'City', trim(($customer->city ?? '—') . ', ' . ($customer->province ?? ''), ', ')], ['bi-calendar', 'Joined', $customer->created_at->format('M d, Y')], ['bi-clock', 'Active', $customer->user->updated_at->diffForHumans()]] as [$ic, $lb, $vl])
                        <div
                            style="display:flex;align-items:center;gap:.65rem;padding:.55rem 1.25rem;border-bottom:1px solid #f8faf8;font-size:.82rem;">
                            <i class="bi {{ $ic }}"
                                style="color:#60a580;width:16px;text-align:center;flex-shrink:0;"></i>
                            <span style="color:#6b7a70;width:70px;flex-shrink:0;">{{ $lb }}</span>
                            <span style="font-weight:600;color:#1a2e1c;">{{ $vl }}</span>
                        </div>
                    @endforeach
                </div>
                <div style="padding:1rem 1.25rem;display:flex;gap:.5rem;flex-wrap:wrap;border-top:1px solid #f1f5f1;">
                    @if ($customer->verification_status !== 'verified')
                        <form method="POST" action="{{ route('admin.customers.verify', $customer) }}">@csrf
                            <button class="btn btn-sm btn-success"><i class="bi bi-shield-check me-1"></i>Verify</button>
                        </form>
                    @endif
                    @if ($customer->verification_status !== 'suspended')
                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#suspendModal">
                            <i class="bi bi-slash-circle me-1"></i>Suspend
                        </button>
                    @else
                        <form method="POST" action="{{ route('admin.customers.restore', $customer) }}">@csrf
                            <button class="btn btn-sm btn-outline-success"><i
                                    class="bi bi-arrow-counterclockwise me-1"></i>Restore</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="panel">
                <div
                    style="padding:.7rem 1.25rem;border-bottom:1px solid #f1f5f1;font-weight:800;font-size:.75rem;color:#6b7a70;text-transform:uppercase;letter-spacing:.06em;">
                    Activity</div>
                @php
                    $spent = $customer->orders()->where('payment_status', 'paid')->sum('total_amount');
                @endphp
                @foreach ([['bi-bag-check', '#3b82f6', 'Total Orders', $orders->total()], ['bi-currency-exchange', '#10b981', 'Total Spent', '₱' . number_format($spent, 2)], ['bi-tools', '#f59e0b', 'Services', $serviceRequests->count()], ['bi-star', '#f59e0b', 'Reviews', $reviews->count()]] as [$ic, $cl, $lb, $vl])
                    <div
                        style="display:flex;align-items:center;justify-content:space-between;padding:.6rem 1.25rem;border-bottom:1px solid #f8faf8;font-size:.82rem;">
                        <span style="display:flex;align-items:center;gap:.5rem;color:#4b5563;"><i
                                class="bi {{ $ic }}"
                                style="color:{{ $cl }};"></i>{{ $lb }}</span>
                        <span style="font-weight:800;color:#1a2e1c;">{{ $vl }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Tabs --}}
        <div class="col-12 col-lg-8">
            <div style="display:flex;gap:.25rem;border-bottom:2px solid #e5ede5;margin-bottom:1rem;">
                @foreach ([['orders', '🛍️ Orders'], ['services', '🔧 Services'], ['reviews', '⭐ Reviews']] as [$t, $l])
                    <button class="tab-btn {{ $t === 'orders' ? 'active' : '' }}"
                        onclick="showTab('{{ $t }}',this)">{{ $l }}</button>
                @endforeach
            </div>

            <div id="tab-orders">
                @if ($orders->count())
                    <div class="panel">
                        <table class="table table-hover mb-0" style="font-size:.82rem;">
                            <thead style="background:#f8faf8;">
                                <tr>
                                    <th>Order #</th>
                                    <th>Vendor</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $o)
                                    <tr>
                                        <td><a href="{{ route('admin.orders.show', $o) }}"
                                                class="font-monospace fw-600 text-success"
                                                style="font-size:.78rem;">{{ $o->order_number }}</a></td>
                                        <td style="font-size:.78rem;">{{ $o->vendor->business_name }}</td>
                                        <td class="font-monospace fw-600">₱{{ number_format($o->total_amount, 2) }}</td>
                                        <td><span
                                                class="badge bg-{{ $o->status === 'completed' ? 'success' : ($o->status === 'cancelled' ? 'danger' : 'warning text-dark') }}"
                                                style="font-size:.65rem;">{{ ucfirst($o->status) }}</span></td>
                                        <td style="font-size:.78rem;">{{ $o->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if ($orders->hasPages())
                            <div class="p-3 border-top">{{ $orders->links('pagination::bootstrap-5') }}</div>
                        @endif
                    </div>
                @else<div class="panel text-center text-muted py-5"><i class="bi bi-bag-x"
                            style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>No orders yet.</div>
                @endif
            </div>

            <div id="tab-services" style="display:none;">
                @if ($serviceRequests->count())
                    <div class="panel">
                        <table class="table table-hover mb-0" style="font-size:.82rem;">
                            <thead style="background:#f8faf8;">
                                <tr>
                                    <th>Ref #</th>
                                    <th>Service</th>
                                    <th>Vendor</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($serviceRequests as $sr)
                                    <tr>
                                        <td class="font-monospace" style="font-size:.75rem;">{{ $sr->request_number }}</td>
                                        <td style="font-size:.78rem;">{{ Str::limit($sr->service_title ?? '—', 28) }}</td>
                                        <td style="font-size:.78rem;">{{ $sr->vendor->business_name }}</td>
                                        <td><span class="badge bg-secondary"
                                                style="font-size:.65rem;">{{ ucfirst(str_replace('_', ' ', $sr->status)) }}</span>
                                        </td>
                                        <td style="font-size:.78rem;">{{ $sr->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else<div class="panel text-center text-muted py-5"><i class="bi bi-tools"
                            style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>No service requests.</div>
                @endif
            </div>

            <div id="tab-reviews" style="display:none;">
                @if ($reviews->count())
                    <div class="d-flex flex-column gap-2">
                        @foreach ($reviews as $r)
                            <div class="panel p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-700 mb-1" style="font-size:.875rem;">{{ $r->vendor->business_name }}
                                        </div>
                                        <div>
                                            @for ($s = 1; $s <= 5; $s++)
                                                <i class="bi bi-star{{ $s <= $r->rating ? '-fill' : '' }}"
                                                    style="color:#f59e0b;font-size:.8rem;"></i>
                                            @endfor
                                            <span class="text-muted ms-1"
                                                style="font-size:.72rem;">{{ $r->created_at->format('M d, Y') }}</span>
                                        </div>
                                        @if ($r->body)
                                            <p class="text-secondary mt-1 mb-0" style="font-size:.82rem;">
                                                {{ Str::limit($r->body, 180) }}</p>
                                        @endif
                                    </div>
                                    @if ($r->is_flagged)
                                        <span class="badge bg-danger flex-shrink-0" style="font-size:.62rem;">🚩
                                            Flagged</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else<div class="panel text-center text-muted py-5"><i class="bi bi-star"
                            style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>No reviews.</div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="suspendModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.customers.suspend', $customer) }}">@csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-700">Suspend {{ $customer->first_name }}?</h6><button type="button"
                            class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body"><label class="form-label fw-600">Reason <span
                                class="text-danger">*</span></label>
                        <textarea name="reason" rows="3" class="form-control" required placeholder="Reason for suspension…"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm">Suspend</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <style>
        .tab-btn {
            padding: .5rem 1.1rem;
            background: none;
            border: none;
            font-size: .875rem;
            font-weight: 700;
            color: #6b7a70;
            cursor: pointer;
            border-bottom: 2.5px solid transparent;
            margin-bottom: -2px;
            white-space: nowrap;
            transition: color .12s, border-color .12s;
        }

        .tab-btn:hover,
        .tab-btn.active {
            color: #157a30;
        }

        .tab-btn.active {
            border-bottom-color: #157a30;
        }
    </style>
    @push('scripts')
        <script>
            function showTab(id, btn) {
                document.querySelectorAll('[id^="tab-"]').forEach(el => el.style.display = 'none');
                document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
                document.getElementById('tab-' + id).style.display = '';
                btn.classList.add('active');
            }
        </script>
    @endpush
@endsection
