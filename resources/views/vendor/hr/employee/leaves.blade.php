@extends('layouts.vendor')
@section('title', 'My Leaves')
@section('page-title', 'My Leave Requests')
@section('breadcrumb')
    <a href="{{ route('vendor.hr.self.dashboard') }}" class="text-secondary">My HR</a>
    <span class="sep">›</span><span class="current">Leaves</span>
@endsection

@section('content')
    <div class="row g-3">

        {{-- Apply form --}}
        <div class="col-12 col-md-5">
            <div class="vd-card" style="position:sticky;top:calc(var(--topbar-h,60px) + 1rem);">
                <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-plus-circle"></i> Apply for
                        Leave</span></div>
                <div class="vd-card__body">
                    <form method="POST" action="{{ route('vendor.hr.self.leaves.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="vd-label">Leave Type <span class="req">*</span></label>
                            <select name="leave_type_id" class="vd-select @error('leave_type_id') is-invalid @enderror"
                                required>
                                <option value="">Select leave type…</option>
                                @foreach ($leaveTypes as $lt)
                                    <option value="{{ $lt->id }}"
                                        {{ old('leave_type_id') == $lt->id ? 'selected' : '' }}>
                                        {{ $lt->name }} ({{ $lt->is_paid ? 'Paid' : 'Unpaid' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('leave_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="vd-label">From <span class="req">*</span></label>
                                <input type="date" name="date_from" value="{{ old('date_from') }}"
                                    min="{{ now()->addDay()->format('Y-m-d') }}"
                                    class="vd-input @error('date_from') is-invalid @enderror" required>
                                @error('date_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <label class="vd-label">To <span class="req">*</span></label>
                                <input type="date" name="date_to" value="{{ old('date_to') }}"
                                    min="{{ now()->addDay()->format('Y-m-d') }}"
                                    class="vd-input @error('date_to') is-invalid @enderror" required>
                                @error('date_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="vd-label">Reason <span class="req">*</span></label>
                            <textarea name="reason" rows="3" class="vd-input @error('reason') is-invalid @enderror"
                                placeholder="Briefly describe the reason for your leave…" required>{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="vd-btn vd-btn--primary w-100"><i class="bi bi-send-check"></i> Submit
                            Leave Request</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- History --}}
        <div class="col-12 col-md-7">
            <div class="vd-card">
                <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-clock-history"></i> My Leave
                        History</span></div>
                @if ($requests->count())
                    <div class="table-responsive">
                        <table class="vd-table">
                            <thead>
                                <tr>
                                    <th>Leave Type</th>
                                    <th>Dates</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requests as $req)
                                    @php $sc=['pending'=>'vd-badge--pending','approved'=>'vd-badge--active','rejected'=>'vd-badge--danger','cancelled'=>'vd-badge--neutral']; @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-600" style="font-size:.875rem;">
                                                {{ $req->leaveType?->name ?? '—' }}</div>
                                            <div class="text-muted" style="font-size:.72rem;">
                                                {{ $req->leaveType?->is_paid ? 'Paid' : 'Unpaid' }}</div>
                                        </td>
                                        <td style="font-size:.82rem;">{{ $req->date_from->format('M d') }} –
                                            {{ $req->date_to->format('M d, Y') }}</td>
                                        <td class="fw-600">{{ $req->days_requested }}d</td>
                                        <td>
                                            <span class="vd-badge {{ $sc[$req->status] }}"
                                                style="font-size:.68rem;">{{ ucfirst($req->status) }}</span>
                                            @if ($req->approver_notes)
                                                <div class="text-muted" style="font-size:.7rem;"
                                                    title="{{ $req->approver_notes }}"><i
                                                        class="bi bi-chat-left-text me-1"></i>Note from HR</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($req->status === 'pending')
                                                <form method="POST"
                                                    action="{{ route('vendor.hr.self.leaves.cancel', $req) }}">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="vd-btn vd-btn--danger vd-btn--sm"
                                                        onclick="return confirm('Cancel this leave request?')">
                                                        <i class="bi bi-x"></i> Cancel
                                                    </button>
                                                </form>
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
                    <div class="empty-state" style="padding:2.5rem;"><i class="bi bi-calendar-x"></i>
                        <h6>No leave requests yet.</h6>
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection
