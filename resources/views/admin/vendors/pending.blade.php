@extends('layouts.admin')

@section('title', 'Approval Queue')
@section('page-title', 'Approval Queue')

@section('content')

    <div class="panel">
        <div class="panel__header">
            <span class="panel__title">
                <i class="bi bi-hourglass-split"></i> Pending Applications
                <span class="nav-badge ms-1">{{ $vendors->total() }}</span>
            </span>
            <span class="panel__subtitle">Applications awaiting review</span>
        </div>

        @if ($vendors->count())
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Business</th>
                            <th>Owner</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Documents</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vendors as $vendor)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar--vendor">
                                            {{ strtoupper(substr($vendor->business_name, 0, 2)) }}</div>
                                        <div>
                                            <div class="fw-600" style="font-size:0.875rem;">{{ $vendor->business_name }}
                                            </div>
                                            <div class="mono text-muted" style="font-size:0.72rem;">
                                                {{ $vendor->business_registration_number }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-600" style="font-size:0.875rem;">{{ $vendor->owner_full_name }}</div>
                                    <div class="text-muted" style="font-size:0.75rem;">{{ $vendor->user->email }}</div>
                                </td>
                                <td class="text-secondary" style="font-size:0.82rem;">
                                    {{ ucwords(str_replace('_', ' ', $vendor->business_type)) }}
                                </td>
                                <td class="text-secondary" style="font-size:0.82rem;">
                                    {{ $vendor->city }}, {{ $vendor->province_state }}
                                </td>
                                <td>
                                    <span class="text-secondary" style="font-size:0.8rem;">
                                        <i class="bi bi-file-earmark-text me-1"></i>
                                        {{ $vendor->documents->count() }}
                                        file{{ $vendor->documents->count() !== 1 ? 's' : '' }}
                                    </span>
                                </td>
                                <td class="text-secondary" style="font-size:0.8rem;">
                                    {{ $vendor->created_at->format('M d, Y') }}<br>
                                    <span class="text-muted"
                                        style="font-size:0.72rem;">{{ $vendor->created_at->diffForHumans() }}</span>
                                </td>
                                <td>
                                    <span
                                        class="badge-status {{ $vendor->status === 'pending' ? 'badge--pending' : 'badge--review' }}">
                                        {{ $vendor->status === 'under_review' ? 'Under Review' : 'Pending' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.vendors.show', $vendor) }}" class="btn btn--primary">
                                        <i class="bi bi-eye"></i> Review
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($vendors->hasPages())
                <div class="panel__footer">
                    <span class="text-muted" style="font-size:0.8rem;">
                        Showing {{ $vendors->firstItem() }}–{{ $vendors->lastItem() }} of {{ $vendors->total() }}
                    </span>
                    {{ $vendors->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @else
            <div class="empty-state" style="padding:4rem 0;">
                <i class="bi bi-check2-circle"></i>
                <h6 style="font-weight:700;margin-bottom:0.35rem;">No pending applications</h6>
                <p>All vendor applications have been reviewed.</p>
            </div>
        @endif
    </div>

@endsection
