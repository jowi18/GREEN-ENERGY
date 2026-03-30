{{-- resources/views/vendor/solar/index.blade.php --}}
@extends('layouts.vendor')
@section('title', 'Solar Projects')
@section('page-title', 'Solar Installation Projects')

@section('content')

    {{-- Stats --}}
    <div class="row g-2 gap-section">
        @foreach ([['New Inquiries', $stats['new'], 'yellow', 'bi-inbox-fill'], ['In Progress', $stats['in_progress'], 'blue', 'bi-gear-fill'], ['Pending Approval', $stats['pending_qt'], 'orange', 'bi-hourglass-split'], ['Completed', $stats['completed'], 'green', 'bi-patch-check-fill']] as [$label, $value, $color, $icon])
            <div class="col-6 col-md-3">
                <div class="vd-stat vd-stat--{{ $color }}">
                    <div class="vd-stat__icon vd-stat__icon--{{ $color }}">
                        <i class="bi {{ $icon }}"></i>
                    </div>
                    <div class="vd-stat__value">{{ $value }}</div>
                    <div class="vd-stat__label">{{ $label }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="vd-card gap-section">
        <div class="vd-card__body" style="padding:.8rem 1.25rem;">
            <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
                <div class="vd-search">
                    <i class="bi bi-search vd-search__icon"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="vd-search__input"
                        placeholder="Project # or customer name…">
                </div>
                <select name="status" class="vd-select" style="width:auto;">
                    <option value="">All Statuses</option>
                    @foreach (\App\Models\SolarProject::STATUS_LABELS as $val => $label)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="vd-btn vd-btn--primary">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('vendor.solar.index') }}" class="vd-btn vd-btn--ghost">
                        <i class="bi bi-x"></i> Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="vd-card">
        <div class="vd-card__header">
            <span class="vd-card__title"><i class="bi bi-sun"></i> Solar Projects</span>
            <span class="vd-card__subtitle">{{ $projects->total() }} total</span>
        </div>

        @if ($projects->count())
            <div class="table-responsive">
                <table class="vd-table">
                    <thead>
                        <tr>
                            <th>Project #</th>
                            <th>Customer</th>
                            <th>Property</th>
                            <th>Engineer</th>
                            <th>Status</th>
                            <th>Quotation</th>
                            <th>Filed</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projects as $project)
                            <tr>
                                <td>
                                    <a href="{{ route('vendor.solar.show', $project) }}" class="mono fw-700 text-green"
                                        style="font-size:.82rem;">
                                        {{ $project->project_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-600" style="font-size:.875rem;">
                                        {{ $project->customer->full_name }}
                                    </div>
                                    <div class="text-muted" style="font-size:.72rem;">
                                        {{ $project->installation_city ?? '—' }}
                                    </div>
                                </td>
                                <td style="font-size:.82rem;">
                                    {{ ucfirst($project->property_type) }}
                                    @if ($project->system_type)
                                        <div class="text-muted" style="font-size:.72rem;">
                                            {{ ucwords(str_replace('_', '-', $project->system_type)) }}
                                        </div>
                                    @endif
                                </td>
                                <td style="font-size:.82rem;">
                                    {{ $project->engineer?->user->name ?? '—' }}
                                </td>
                                <td>
                                    <span class="vd-badge {{ $project->status_badge_class }}" style="font-size:.65rem;">
                                        {{ $project->status_label }}
                                    </span>
                                </td>
                                <td>
                                    @if ($project->activeQuotation)
                                        <div class="mono fw-600" style="font-size:.82rem;color:var(--g-700);">
                                            ₱{{ number_format($project->activeQuotation->total_amount, 2) }}
                                        </div>
                                        <div class="text-muted" style="font-size:.68rem;">
                                            {{ $project->activeQuotation->status_label }}
                                        </div>
                                    @else
                                        <span class="text-muted" style="font-size:.78rem;">—</span>
                                    @endif
                                </td>
                                <td class="text-muted" style="font-size:.78rem;">
                                    {{ $project->created_at->format('M d, Y') }}
                                </td>
                                <td>
                                    <a href="{{ route('vendor.solar.show', $project) }}"
                                        class="vd-btn vd-btn--ghost vd-btn--icon" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="vd-card__footer">
                {{ $projects->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-sun"></i>
                <h6>No solar projects yet</h6>
                <p>Customer installation inquiries will appear here.</p>
            </div>
        @endif
    </div>

@endsection
