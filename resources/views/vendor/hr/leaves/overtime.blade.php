@extends('layouts.vendor')
@section('title', 'Overtime Requests')
@section('page-title', 'Leave & Overtime Management')

@section('content')

    {{-- Tabs --}}
    <div style="display:flex;gap:.4rem;border-bottom:2px solid var(--card-border);margin-bottom:1.25rem;">
        <a href="{{ route('vendor.hr.leaves.index') }}" class="vd-btn vd-btn--ghost"
            style="border-bottom:2px solid transparent;border-radius:0;margin-bottom:-2px;">
            <i class="bi bi-calendar-x me-1"></i>Leave Requests
        </a>
        <a href="{{ route('vendor.hr.leaves.overtime') }}" class="vd-btn vd-btn--ghost"
            style="border-bottom:2px solid var(--g-500);border-radius:0;margin-bottom:-2px;">
            <i class="bi bi-clock-history me-1"></i>Overtime Requests
            @if ($pendingCnt > 0)
                <span class="vd-nav__badge ms-1">{{ $pendingCnt }}</span>
            @endif
        </a>
    </div>

    <div class="vd-card">
        <div class="vd-card__header">
            <span class="vd-card__title"><i class="bi bi-clock-history"></i> Overtime Requests</span>
            <span class="vd-card__subtitle">{{ $requests->total() }} total</span>
        </div>
        @if ($requests->count())
            <div class="table-responsive">
                <table class="vd-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Hours</th>
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
                                <td style="font-size:.82rem;">{{ $req->overtime_date->format('M d, Y (D)') }}</td>
                                <td class="mono" style="font-size:.82rem;">{{ $req->start_time }} – {{ $req->end_time }}
                                </td>
                                <td class="fw-600 mono">{{ number_format($req->hours_requested, 2) }}h</td>
                                <td class="text-secondary" style="font-size:.82rem;max-width:180px;">
                                    {{ \Illuminate\Support\Str::limit($req->reason ?? '—', 60) }}</td>
                                <td><span class="vd-badge {{ $sc[$req->status] }}"
                                        style="font-size:.68rem;">{{ ucfirst($req->status) }}</span></td>
                                <td>
                                    @if ($req->status === 'pending')
                                        <div class="d-flex gap-1">
                                            <form method="POST"
                                                action="{{ route('vendor.hr.leaves.overtime.approve', $req) }}">
                                                @csrf
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="vd-btn vd-btn--primary vd-btn--sm"><i
                                                        class="bi bi-check"></i> Approve</button>
                                            </form>
                                            <form method="POST"
                                                action="{{ route('vendor.hr.leaves.overtime.approve', $req) }}">
                                                @csrf
                                                <input type="hidden" name="action" value="reject">
                                                <input type="hidden" name="notes" value="Rejected by HR">
                                                <button type="submit" class="vd-btn vd-btn--danger vd-btn--sm"><i
                                                        class="bi bi-x"></i> Reject</button>
                                            </form>
                                        </div>
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
            <div class="empty-state"><i class="bi bi-clock-history"></i>
                <h6>No overtime requests</h6>
            </div>
        @endif
    </div>
@endsection
