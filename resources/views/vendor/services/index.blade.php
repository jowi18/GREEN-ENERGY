@extends('layouts.vendor')

@section('title', 'Service Requests')
@section('page-title', 'Service Requests')

@section('content')

    {{-- ── Stats ── --}}
    <div class="row g-2 gap-section">
        @php
            $statItems = [
                ['New', $stats['submitted'], 'yellow', 'bi-inbox'],
                ['Scheduled', $stats['scheduled'], 'blue', 'bi-calendar-check'],
                ['In Progress', $stats['in_progress'], 'orange', 'bi-gear-wide-connected'],
                ['Completed', $stats['completed'], 'green', 'bi-patch-check'],
            ];
        @endphp
        @foreach ($statItems as [$label, $count, $color, $icon])
            <div class="col-6 col-md-3">
                <div class="vd-stat vd-stat--{{ $color }}">
                    <div class="vd-stat__icon vd-stat__icon--{{ $color }}"><i class="bi {{ $icon }}"></i></div>
                    <div class="vd-stat__value">{{ number_format($count) }}</div>
                    <div class="vd-stat__label">{{ $label }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── Filters ── --}}
    <div class="vd-card gap-section">
        <div class="vd-card__body" style="padding:0.85rem 1.25rem;">
            <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
                <div class="vd-search">
                    <i class="bi bi-search vd-search__icon"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="vd-search__input"
                        placeholder="Request # or customer name…">
                </div>

                <select name="status" class="vd-select" style="width:auto;">
                    <option value="">All Statuses</option>
                    @foreach (['submitted', 'reviewing', 'quoted', 'quote_accepted', 'scheduled', 'in_progress', 'completed', 'rejected', 'cancelled'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                            {{ ucwords(str_replace('_', ' ', $s)) }}
                        </option>
                    @endforeach
                </select>

                @if ($serviceTypes->count())
                    <select name="type" class="vd-select" style="width:auto;">
                        <option value="">All Types</option>
                        @foreach ($serviceTypes as $t)
                            <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $t)) }}
                            </option>
                        @endforeach
                    </select>
                @endif

                <button type="submit" class="vd-btn vd-btn--primary"><i class="bi bi-funnel"></i> Filter</button>

                @if (request()->hasAny(['search', 'status', 'type']))
                    <a href="{{ route('vendor.services.index') }}" class="vd-btn vd-btn--ghost">
                        <i class="bi bi-x"></i> Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- ── Request list ── --}}
    <div class="vd-card">
        <div class="vd-card__header">
            <span class="vd-card__title"><i class="bi bi-tools"></i> Service Requests</span>
            <span class="vd-card__subtitle">{{ $requests->total() }} total</span>
        </div>

        @if ($requests->count())
            <div class="table-responsive">
                <table class="vd-table">
                    <thead>
                        <tr>
                            <th>Request</th>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Location</th>
                            <th>Preferred</th>
                            <th>Scheduled</th>
                            <th>Status</th>
                            <th>Proof</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $sr)
                            @php
                                $statusColors = [
                                    'submitted' => 'vd-badge--pending',
                                    'reviewing' => 'vd-badge--info',
                                    'quoted' => 'vd-badge--warning',
                                    'quote_accepted' => 'vd-badge--active',
                                    'quote_rejected' => 'vd-badge--danger',
                                    'scheduled' => 'vd-badge--info',
                                    'in_progress' => 'vd-badge--warning',
                                    'completed' => 'vd-badge--active',
                                    'rejected' => 'vd-badge--danger',
                                    'cancelled' => 'vd-badge--neutral',
                                ];
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('vendor.services.show', $sr) }}" class="mono fw-600 text-green"
                                        style="font-size:0.82rem;">
                                        {{ $sr->request_number }}
                                    </a>
                                    <div class="text-muted" style="font-size:0.72rem;">
                                        {{ $sr->created_at->diffForHumans() }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-600" style="font-size:0.875rem;">{{ $sr->customer->full_name }}</div>
                                    <div class="text-muted" style="font-size:0.75rem;">{{ $sr->customer->phone ?? '—' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-600" style="font-size:0.845rem;">
                                        {{ Str::limit($sr->service_title, 28) }}</div>
                                    <div class="text-muted" style="font-size:0.72rem;">
                                        {{ ucwords(str_replace('_', ' ', $sr->service_type)) }}
                                    </div>
                                </td>
                                <td class="text-secondary" style="font-size:0.82rem;">
                                    {{ $sr->service_city }}
                                </td>
                                <td class="text-muted" style="font-size:0.8rem;">
                                    @if ($sr->preferred_date)
                                        {{ $sr->preferred_date->format('M d, Y') }}<br>
                                        <span style="font-size:0.72rem;">{{ ucfirst($sr->preferred_time ?? '') }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-muted" style="font-size:0.8rem;">
                                    @if ($sr->scheduled_date)
                                        <span class="text-green fw-600">{{ $sr->scheduled_date->format('M d, Y') }}</span>
                                        @if ($sr->assigned_to)
                                            <div style="font-size:0.72rem;">👷 {{ $sr->assigned_to }}</div>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <span class="vd-badge {{ $statusColors[$sr->status] ?? 'vd-badge--neutral' }}"
                                        style="font-size:0.68rem;">
                                        {{ $sr->status_label }}
                                    </span>
                                </td>
                                <td>
                                    @if ($sr->proofs->count())
                                        <span class="vd-badge vd-badge--active" style="font-size:0.68rem;">
                                            <i class="bi bi-images me-1"></i>{{ $sr->proofs->count() }}
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size:0.8rem;">—</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('vendor.services.show', $sr) }}"
                                        class="vd-btn vd-btn--ghost vd-btn--icon" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($requests->hasPages())
                <div class="vd-card__footer">
                    <span class="text-muted" style="font-size:0.8rem;">
                        Showing {{ $requests->firstItem() }}–{{ $requests->lastItem() }} of {{ $requests->total() }}
                    </span>
                    {{ $requests->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <i class="bi bi-tools"></i>
                <h6>No service requests yet</h6>
                <p>Customer bookings will appear here once they submit a service request.</p>
            </div>
        @endif
    </div>

@endsection
