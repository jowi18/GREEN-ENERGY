@extends('layouts.vendor')
@section('title', 'Leave Requests')
@section('page-title', 'Leave & Overtime Management')

@section('content')

    {{-- Tabs --}}
    <div style="display:flex;gap:.4rem;border-bottom:2px solid var(--card-border);margin-bottom:1.25rem;">
        <a href="{{ route('vendor.hr.leaves.index') }}" class="vd-btn vd-btn--ghost"
            style="border-bottom:{{ !request()->routeIs('vendor.hr.leaves.overtime') ? '2px solid var(--g-500)' : '2px solid transparent' }};border-radius:0;margin-bottom:-2px;">
            <i class="bi bi-calendar-x me-1"></i>Leave Requests
            @if ($pendingCnt > 0)
                <span class="vd-nav__badge ms-1">{{ $pendingCnt }}</span>
            @endif
        </a>
        <a href="{{ route('vendor.hr.leaves.overtime') }}" class="vd-btn vd-btn--ghost"
            style="border-bottom:{{ request()->routeIs('vendor.hr.leaves.overtime') ? '2px solid var(--g-500)' : '2px solid transparent' }};border-radius:0;margin-bottom:-2px;">
            <i class="bi bi-clock-history me-1"></i>Overtime Requests
        </a>
    </div>

    {{-- Status filter --}}
    <div
        style="display:flex;gap:.35rem;background:var(--n-100);padding:.3rem;border-radius:var(--r-md);width:fit-content;margin-bottom:1.25rem;flex-wrap:wrap;">
        @foreach ([
            'all' => ['All', 'bi-list-ul'],
            'pending' => ['Pending', 'bi-hourglass-split'],
            'approved' => ['Approved', 'bi-check-circle'],
            'rejected' => ['Rejected', 'bi-x-circle'],
        ] as $val => [$label, $icon])
            @php $active = request('status', 'pending') === $val; @endphp
            <a href="{{ route('vendor.hr.leaves.index', ['status' => $val]) }}"
                style="display:inline-flex;align-items:center;gap:.35rem;
                  padding:.4rem .9rem;border-radius:var(--r-sm);
                  font-size:.8rem;font-weight:600;text-decoration:none;
                  transition:all .15s;white-space:nowrap;
                  {{ $active
                      ? 'background:var(--card-bg);color:var(--tx-primary);box-shadow:0 1px 4px rgba(0,0,0,.1);'
                      : 'background:transparent;color:var(--tx-muted);' }}">
                <i class="bi {{ $icon }}"
                    style="font-size:.75rem;
                      {{ $val === 'pending' ? 'color:var(--y-500);' : '' }}
                      {{ $val === 'approved' ? 'color:var(--g-500);' : '' }}
                      {{ $val === 'rejected' ? 'color:var(--red-500);' : '' }}
                      {{ $val === 'all' ? 'color:var(--tx-muted);' : '' }}"></i>
                {{ $label }}
                @if ($val === 'pending' && isset($pendingCnt) && $pendingCnt > 0)
                    <span
                        style="background:var(--y-500);color:#fff;font-size:.65rem;
                             font-weight:700;padding:.05rem .4rem;border-radius:var(--r-full);
                             line-height:1.6;min-width:18px;text-align:center;">
                        {{ $pendingCnt }}
                    </span>
                @endif
            </a>
        @endforeach
    </div>

    <div class="vd-card">
        <div class="vd-card__header">
            <span class="vd-card__title"><i class="bi bi-calendar-x"></i> Leave Requests</span>
            <span class="vd-card__subtitle">{{ $requests->total() }} total</span>
        </div>
        @if ($requests->count())
            <div class="table-responsive">
                <table class="vd-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>Dates</th>
                            <th>Days</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $req)
                            @php $sc=['pending'=>'vd-badge--pending','approved'=>'vd-badge--active','rejected'=>'vd-badge--danger','cancelled'=>'vd-badge--neutral']; @endphp
                            <tr>
                                <td>
                                    <div class="fw-600" style="font-size:.875rem;">{{ $req->profile->full_name }}</div>
                                    <div class="text-muted" style="font-size:.72rem;">
                                        {{ $req->profile->department?->name ?? '—' }}</div>
                                </td>
                                <td>
                                    <span class="vd-badge vd-badge--info"
                                        style="font-size:.68rem;">{{ $req->leaveType?->name ?? 'Leave' }}</span>
                                    <div class="text-muted" style="font-size:.72rem;">
                                        {{ $req->leaveType?->is_paid ? 'Paid' : 'Unpaid' }}</div>
                                </td>
                                <td style="font-size:.82rem;">
                                    {{ $req->date_from->format('M d') }} – {{ $req->date_to->format('M d, Y') }}
                                </td>
                                <td class="fw-600">{{ $req->days_requested }}
                                    day{{ $req->days_requested !== 1 ? 's' : '' }}</td>
                                <td class="text-secondary" style="font-size:.82rem;max-width:200px;">
                                    {{ \Illuminate\Support\Str::limit($req->reason ?? '—', 60) }}</td>
                                <td>
                                    <span class="vd-badge {{ $sc[$req->status] }}"
                                        style="font-size:.68rem;">{{ ucfirst($req->status) }}</span>
                                    @if ($req->approved_at)
                                        <div class="text-muted" style="font-size:.68rem;">
                                            {{ $req->approved_at->format('M d') }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($req->status === 'pending')
                                        <div class="d-flex gap-1">
                                            <form method="POST" action="{{ route('vendor.hr.leaves.approve', $req) }}">
                                                @csrf
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="vd-btn vd-btn--primary vd-btn--sm"><i
                                                        class="bi bi-check"></i> Approve</button>
                                            </form>
                                            <button class="vd-btn vd-btn--danger vd-btn--sm"
                                                onclick="rejectLeave({{ $req->id }})">
                                                <i class="bi bi-x"></i> Reject
                                            </button>
                                        </div>
                                    @elseif($req->approver_notes)
                                        <span class="text-muted" style="font-size:.72rem;"
                                            title="{{ $req->approver_notes }}"><i class="bi bi-chat-left-text me-1"></i>Has
                                            note</span>
                                    @else
                                        <span class="text-muted" style="font-size:.75rem;">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="vd-card__footer">{{ $requests->links('pagination::bootstrap-5') }}</div>
        @else
            <div class="empty-state"><i class="bi bi-calendar-x"></i>
                <h6>No leave requests</h6>
            </div>
        @endif
    </div>

    {{-- Reject modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="rejectForm">
                    @csrf
                    <input type="hidden" name="action" value="reject">
                    <div class="modal-header">
                        <h6 class="modal-title fw-700">Reject Leave Request</h6><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="vd-label">Reason for rejection <span class="req">*</span></label>
                        <textarea name="notes" rows="3" class="vd-input" required
                            placeholder="Explain why the leave is being rejected…"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="vd-btn vd-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="vd-btn vd-btn--danger"><i class="bi bi-x-circle"></i> Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function rejectLeave(id) {
            document.getElementById('rejectForm').action = `/vendor/hr/leaves/${id}/approve`;
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        }
    </script>
@endpush
