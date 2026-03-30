@extends('layouts.vendor')
@section('title', 'My Overtime')
@section('page-title', 'My Overtime Requests')
@section('breadcrumb')
    <a href="{{ route('vendor.hr.self.dashboard') }}" class="text-secondary">My HR</a>
    <span class="sep">›</span><span class="current">Overtime</span>
@endsection

@section('content')
    <div class="row g-3">

        {{-- File OT form --}}
        <div class="col-12 col-md-4">
            <div class="vd-card" style="position:sticky;top:calc(var(--topbar-h,60px) + 1rem);">
                <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-plus-circle"></i> File
                        Overtime</span></div>
                <div class="vd-card__body">
                    <form method="POST" action="{{ route('vendor.hr.self.overtime.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="vd-label">OT Date <span class="req">*</span></label>
                            <input type="date" name="overtime_date" value="{{ old('overtime_date') }}"
                                class="vd-input @error('overtime_date') is-invalid @enderror" required>
                            @error('overtime_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="vd-label">Start Time <span class="req">*</span></label>
                                <input type="time" name="start_time" value="{{ old('start_time', '17:00') }}"
                                    class="vd-input @error('start_time') is-invalid @enderror" required>
                            </div>
                            <div class="col-6">
                                <label class="vd-label">End Time <span class="req">*</span></label>
                                <input type="time" name="end_time" value="{{ old('end_time', '19:00') }}"
                                    class="vd-input @error('end_time') is-invalid @enderror" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="vd-label">Reason <span class="req">*</span></label>
                            <textarea name="reason" rows="3" class="vd-input @error('reason') is-invalid @enderror"
                                placeholder="Reason for overtime work…" required>{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="vd-btn vd-btn--primary w-100"><i class="bi bi-send-check"></i> Submit
                            Request</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- History --}}
        <div class="col-12 col-md-8">
            <div class="vd-card">
                <div class="vd-card__header"><span class="vd-card__title"><i class="bi bi-clock-history"></i> My Overtime
                        History</span></div>
                @if ($requests->count())
                    <div class="table-responsive">
                        <table class="vd-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Hours</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requests as $req)
                                    @php $sc=['pending'=>'vd-badge--pending','approved'=>'vd-badge--active','rejected'=>'vd-badge--danger','cancelled'=>'vd-badge--neutral']; @endphp
                                    <tr>
                                        <td class="fw-600" style="font-size:.82rem;">
                                            {{ $req->overtime_date->format('M d, Y (D)') }}</td>
                                        <td class="mono" style="font-size:.82rem;">{{ $req->start_time }} –
                                            {{ $req->end_time }}</td>
                                        <td class="mono fw-600">{{ number_format($req->hours_requested, 2) }}h</td>
                                        <td class="text-secondary" style="font-size:.82rem;">
                                            {{ \Illuminate\Support\Str::limit($req->reason ?? '—', 50) }}</td>
                                        <td><span class="vd-badge {{ $sc[$req->status] }}"
                                                style="font-size:.68rem;">{{ ucfirst($req->status) }}</span></td>
                                        <td class="text-muted" style="font-size:.75rem;">{{ $req->approver_notes ?? '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="vd-card__footer">{{ $requests->links('pagination::bootstrap-5') }}</div>
                @else
                    <div class="empty-state" style="padding:2.5rem;"><i class="bi bi-clock-history"></i>
                        <h6>No overtime requests yet.</h6>
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection
