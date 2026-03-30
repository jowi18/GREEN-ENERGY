@extends('layouts.vendor')

@section('title', 'My Services')
@section('page-title', 'My Services')

@push('styles')
    <style>
        .sc-card {
            background: var(--card-bg);
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-md);
            padding: 1.1rem 1.25rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            position: relative;
        }

        .sc-card:hover {
            border-color: var(--g-300);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        }

        .sc-card.inactive {
            opacity: 0.6;
        }

        .sc-card.inactive:hover {
            opacity: 1;
        }

        .sc-card__drag {
            position: absolute;
            left: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--tx-muted);
            cursor: grab;
            font-size: 1rem;
            padding: 0.25rem;
        }

        .sc-card__drag:active {
            cursor: grabbing;
        }

        .sc-type-badge {
            font-size: 0.67rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 0.18rem 0.55rem;
            border-radius: var(--r-full);
            background: var(--g-50);
            color: var(--g-700);
            border: 1px solid var(--g-100);
        }

        .sc-price {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--g-700);
        }

        .sc-toggle {
            width: 44px;
            height: 24px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            position: relative;
            transition: background 0.2s;
            flex-shrink: 0;
            background: var(--n-300);
        }

        .sc-toggle.on {
            background: var(--g-500);
        }

        .sc-toggle::after {
            content: '';
            position: absolute;
            left: 3px;
            top: 3px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #fff;
            transition: transform 0.2s;
        }

        .sc-toggle.on::after {
            transform: translateX(20px);
        }

        .section-group-header {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--tx-muted);
            padding: 0.5rem 0 0.4rem;
            border-bottom: 1px solid var(--n-100);
            margin-bottom: 0.75rem;
            margin-top: 1.25rem;
        }

        .section-group-header:first-child {
            margin-top: 0;
        }

        .inclusion-list {
            margin: 0;
            padding: 0 0 0 1rem;
            font-size: 0.8rem;
            color: var(--tx-secondary);
        }

        .inclusion-list li {
            margin-bottom: 0.2rem;
        }

        .empty-catalog {
            text-align: center;
            padding: 3.5rem 1rem;
            color: var(--tx-muted);
        }

        .empty-catalog i {
            font-size: 3rem;
            color: var(--n-300);
            display: block;
            margin-bottom: 1rem;
        }

        /* Modal */
        .char-count {
            font-size: 0.72rem;
            color: var(--tx-muted);
            text-align: right;
            margin-top: 0.2rem;
        }
    </style>
@endpush

@section('content')

    {{-- ── Stats row ── --}}
    <div class="row g-2 gap-section">
        @php
            $statItems = [
                ['Total Services', $stats['total'], 'green', 'bi-tools'],
                ['Active', $stats['active'], 'green', 'bi-check-circle'],
                ['Inactive', $stats['inactive'], 'yellow', 'bi-dash-circle'],
                ['Featured', $stats['featured'], 'blue', 'bi-star'],
            ];
        @endphp
        @foreach ($statItems as [$label, $count, $color, $icon])
            <div class="col-6 col-md-3">
                <div class="vd-stat vd-stat--{{ $color }}">
                    <div class="vd-stat__icon vd-stat__icon--{{ $color }}"><i class="bi {{ $icon }}"></i></div>
                    <div class="vd-stat__value">{{ $count }}</div>
                    <div class="vd-stat__label">{{ $label }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── Toolbar ── --}}
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
        <p class="text-muted mb-0" style="font-size:0.82rem;">
            <i class="bi bi-info-circle me-1"></i>
            Active services are visible to customers on your vendor profile.
            Drag to reorder.
        </p>
        <button class="vd-btn vd-btn--primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
            <i class="bi bi-plus-lg"></i> Add Service
        </button>
    </div>

    {{-- ── Service cards grouped by type ── --}}
    @if ($services->count())

        <div id="serviceList">
            @foreach ($grouped as $type => $typeServices)
                <div class="section-group-header">
                    {{ $serviceTypes[$type] ?? ucwords(str_replace('_', ' ', $type)) }}
                    <span style="font-weight:400;color:var(--n-300);margin-left:0.4rem;">
                        {{ $typeServices->count() }} service{{ $typeServices->count() !== 1 ? 's' : '' }}
                    </span>
                </div>

                @foreach ($typeServices as $svc)
                    <div class="sc-card {{ !$svc->is_active ? 'inactive' : '' }} mb-2" id="sc-{{ $svc->id }}"
                        data-id="{{ $svc->id }}">
                        <i class="bi bi-grip-vertical sc-card__drag"></i>

                        <div style="display:flex;align-items:flex-start;gap:0.85rem;padding-left:1.25rem;">

                            {{-- Info block --}}
                            <div style="flex:1;min-width:0;">
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <span class="fw-700 outfit" style="font-size:0.9rem;">{{ $svc->name }}</span>
                                    <span class="sc-type-badge">{{ $svc->service_type_label }}</span>
                                    @if ($svc->is_featured)
                                        <span class="vd-badge vd-badge--info" style="font-size:0.65rem;">
                                            <i class="bi bi-star-fill me-1"></i>Featured
                                        </span>
                                    @endif
                                    @if (!$svc->is_active)
                                        <span class="vd-badge vd-badge--neutral" style="font-size:0.65rem;">Disabled</span>
                                    @endif
                                </div>

                                @if ($svc->short_description)
                                    <p class="text-secondary mb-1" style="font-size:0.82rem;line-height:1.5;">
                                        {{ $svc->short_description }}
                                    </p>
                                @endif

                                <div class="d-flex align-items-center gap-3 flex-wrap mt-1">
                                    <span class="sc-price">{{ $svc->price_display }}</span>

                                    @if ($svc->duration_display)
                                        <span class="text-muted" style="font-size:0.78rem;">
                                            <i class="bi bi-clock me-1"></i>{{ $svc->duration_display }}
                                        </span>
                                    @endif

                                    @if ($svc->coverage_notes)
                                        <span class="text-muted" style="font-size:0.78rem;">
                                            <i class="bi bi-geo-alt me-1"></i>{{ $svc->coverage_notes }}
                                        </span>
                                    @endif
                                </div>

                                @if (count($svc->inclusions_list))
                                    <ul class="inclusion-list mt-2">
                                        @foreach (array_slice($svc->inclusions_list, 0, 3) as $inc)
                                            <li>{{ $inc }}</li>
                                        @endforeach
                                        @if (count($svc->inclusions_list) > 3)
                                            <li style="color:var(--tx-muted);">+{{ count($svc->inclusions_list) - 3 }}
                                                more…</li>
                                        @endif
                                    </ul>
                                @endif
                            </div>

                            {{-- Controls --}}
                            <div class="d-flex flex-column align-items-end gap-2" style="flex-shrink:0;">
                                {{-- Active toggle --}}
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted" style="font-size:0.72rem;">
                                        {{ $svc->is_active ? 'Active' : 'Disabled' }}
                                    </span>
                                    <button class="sc-toggle {{ $svc->is_active ? 'on' : '' }}"
                                        id="toggle-{{ $svc->id }}" onclick="toggleService({{ $svc->id }}, this)"
                                        title="{{ $svc->is_active ? 'Disable' : 'Enable' }} service">
                                    </button>
                                </div>

                                {{-- Action buttons --}}
                                <div class="d-flex gap-1">
                                    <button class="vd-btn vd-btn--ghost vd-btn--icon"
                                        title="{{ $svc->is_featured ? 'Remove from featured' : 'Mark as featured' }}"
                                        onclick="toggleFeatured({{ $svc->id }}, this)">
                                        <i class="bi bi-star{{ $svc->is_featured ? '-fill' : '' }}"
                                            style="{{ $svc->is_featured ? 'color:var(--y-400)' : '' }}"></i>
                                    </button>
                                    <button class="vd-btn vd-btn--ghost vd-btn--icon" title="Edit"
                                        onclick="openEdit({{ $svc->id }})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="vd-btn vd-btn--danger vd-btn--icon" title="Remove"
                                        onclick="confirmDelete({{ $svc->id }}, '{{ addslashes($svc->name) }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    @else
        <div class="vd-card">
            <div class="empty-catalog">
                <i class="bi bi-tools"></i>
                <h6 class="outfit fw-700" style="color:var(--tx-secondary);margin-bottom:0.5rem;">
                    No services in your catalog yet
                </h6>
                <p style="font-size:0.875rem;max-width:340px;margin:0 auto 1.25rem;">
                    Add the services you offer so customers can browse them and book directly from your profile.
                </p>
                <button class="vd-btn vd-btn--primary d-inline-flex" data-bs-toggle="modal"
                    data-bs-target="#addServiceModal">
                    <i class="bi bi-plus-lg"></i> Add Your First Service
                </button>
            </div>
        </div>
    @endif

    {{-- ════ Add Service Modal ════ --}}
    <div class="modal fade" id="addServiceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('vendor.service-catalog.store') }}" id="addServiceForm">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-700">
                            <i class="bi bi-plus-circle me-2 text-green"></i>Add Service
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @include('vendor.service-catalog._form', ['service' => null])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="vd-btn vd-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="vd-btn vd-btn--primary">
                            <i class="bi bi-check-lg"></i> Add Service
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ════ Edit Service Modal ════ --}}
    <div class="modal fade" id="editServiceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form method="POST" id="editServiceForm">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h6 class="modal-title fw-700">
                            <i class="bi bi-pencil me-2 text-green"></i>Edit Service
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="editModalBody">
                        @include('vendor.service-catalog._form', ['service' => null])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="vd-btn vd-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="vd-btn vd-btn--primary">
                            <i class="bi bi-check-lg"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete form --}}
    <form method="POST" id="deleteForm" style="display:none;">@csrf @method('DELETE')</form>

@endsection

@push('scripts')
    <script>
        /* ── Toggle active ─────────────────────────────────── */
        function toggleService(id, btn) {
            $.post(`/vendor/service-catalog/${id}/toggle`, {}, function(res) {
                if (!res.success) return;
                btn.classList.toggle('on', res.is_active);
                btn.title = res.is_active ? 'Disable service' : 'Enable service';
                const card = document.getElementById(`sc-${id}`);
                card?.classList.toggle('inactive', !res.is_active);
                const label = btn.previousElementSibling;
                if (label) label.textContent = res.is_active ? 'Active' : 'Disabled';
                Swal.fire({
                    icon: 'success',
                    text: res.message,
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        }

        /* ── Toggle featured ───────────────────────────────── */
        function toggleFeatured(id, btn) {
            $.post(`/vendor/service-catalog/${id}/featured`, {}, function(res) {
                if (!res.success) return;
                const icon = btn.querySelector('i');
                if (res.is_featured) {
                    icon.className = 'bi bi-star-fill';
                    icon.style.color = 'var(--y-400)';
                } else {
                    icon.className = 'bi bi-star';
                    icon.style.color = '';
                }
                Swal.fire({
                    icon: 'success',
                    text: res.is_featured ? 'Marked as featured.' : 'Removed from featured.',
                    toast: true,
                    position: 'top-end',
                    timer: 1800,
                    showConfirmButton: false
                });
            });
        }

        /* ── Open edit modal ───────────────────────────────── */
        function openEdit(id) {
            $.get(`/vendor/service-catalog/${id}/edit`, function(svc) {
                const form = document.getElementById('editServiceForm');
                form.action = `/vendor/service-catalog/${id}`;

                // Populate all fields
                const fields = ['name', 'service_type', 'short_description', 'description',
                    'pricing_type', 'price', 'price_max', 'duration_hours_min', 'duration_hours_max',
                    'coverage_notes', 'inclusions', 'requirements'
                ];

                fields.forEach(f => {
                    const el = form.querySelector(`[name="${f}"]`);
                    if (el) el.value = svc[f] ?? '';
                });

                // Checkboxes
                form.querySelector('[name="is_active"]').checked = !!svc.is_active;
                form.querySelector('[name="is_featured"]').checked = !!svc.is_featured;

                // Show price field based on pricing type
                updatePriceVisibility(form.querySelector('[name="pricing_type"]'));

                new bootstrap.Modal(document.getElementById('editServiceModal')).show();
            });
        }

        /* ── Delete ────────────────────────────────────────── */
        function confirmDelete(id, name) {
            Swal.fire({
                title: `Remove "${name}"?`,
                text: 'It will no longer appear on your profile.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Remove',
                confirmButtonColor: '#ef4444',
            }).then(r => {
                if (!r.isConfirmed) return;
                const f = document.getElementById('deleteForm');
                f.action = `/vendor/service-catalog/${id}`;
                f.submit();
            });
        }

        /* ── Price visibility based on pricing type ────────── */
        function updatePriceVisibility(select) {
            const form = select.closest('form');
            const priceWrap = form.querySelector('#priceWrap');
            const priceMaxWrap = form.querySelector('#priceMaxWrap');
            const isCustom = select.value === 'custom_quote';

            if (priceWrap) priceWrap.style.display = isCustom ? 'none' : '';
            if (priceMaxWrap) priceMaxWrap.style.display = select.value === 'starting_from' ? '' : 'none';
        }

        // Init price visibility on page load
        document.querySelectorAll('[name="pricing_type"]').forEach(el => {
            updatePriceVisibility(el);
            el.addEventListener('change', () => updatePriceVisibility(el));
        });

        /* ── Drag to reorder ───────────────────────────────── */
        (function initSortable() {
            const list = document.getElementById('serviceList');
            if (!list) return;

            let dragging = null;

            list.querySelectorAll('.sc-card').forEach(card => {
                card.setAttribute('draggable', true);

                card.addEventListener('dragstart', e => {
                    dragging = card;
                    setTimeout(() => card.style.opacity = '0.4', 0);
                });

                card.addEventListener('dragend', () => {
                    card.style.opacity = '';
                    dragging = null;
                    saveOrder();
                });

                card.addEventListener('dragover', e => {
                    e.preventDefault();
                    if (dragging && dragging !== card) {
                        const rect = card.getBoundingClientRect();
                        const mid = rect.top + rect.height / 2;
                        card.parentNode.insertBefore(dragging, e.clientY < mid ? card : card
                            .nextSibling);
                    }
                });
            });

            function saveOrder() {
                const ids = [...list.querySelectorAll('.sc-card')].map(c => c.dataset.id);
                $.post('/vendor/service-catalog/reorder', {
                    order: ids
                });
            }
        })();
    </script>
@endpush
