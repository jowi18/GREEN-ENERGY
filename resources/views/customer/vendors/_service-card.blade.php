{{--
    Reusable service card for customer-facing vendor profile.
    $svc = VendorService instance
--}}
<div class="svc-card">

    {{-- Type + featured badge ── --}}
    <div class="svc-card__type">
        <i class="bi bi-tools"></i>
        {{ $svc->service_type_label }}
        @if ($svc->is_featured)
            <span class="svc-featured-badge">
                <i class="bi bi-star-fill"></i> Featured
            </span>
        @endif
    </div>

    {{-- Name ── --}}
    <div class="svc-card__name">{{ $svc->name }}</div>

    {{-- Description ── --}}
    @if ($svc->short_description)
        <div class="svc-card__desc">{{ $svc->short_description }}</div>
    @elseif($svc->description)
        <div class="svc-card__desc">{{ $svc->description }}</div>
    @endif

    {{-- Meta: price + duration + coverage ── --}}
    <div class="svc-card__meta">
        <span class="svc-card__price">{{ $svc->price_display }}</span>

        @if ($svc->duration_display)
            <span>
                <i class="bi bi-clock" style="margin-right:0.2rem;"></i>
                {{ $svc->duration_display }}
            </span>
        @endif

        @if ($svc->coverage_notes)
            <span>
                <i class="bi bi-geo-alt" style="margin-right:0.2rem;"></i>
                {{ $svc->coverage_notes }}
            </span>
        @endif
    </div>

    {{-- Inclusions preview ── --}}
    @if (count($svc->inclusions_list))
        <ul class="svc-card__inclusions">
            @foreach (array_slice($svc->inclusions_list, 0, 4) as $inc)
                <li>{{ $inc }}</li>
            @endforeach
            @if (count($svc->inclusions_list) > 4)
                <li style="color:var(--tx-muted);list-style:none;">
                    + {{ count($svc->inclusions_list) - 4 }} more included
                </li>
            @endif
        </ul>
    @endif

    {{-- Requirements hint ── --}}
    @if (count($svc->requirements_list))
        <div
            style="font-size:0.72rem;color:var(--tx-muted);
                margin-bottom:0.75rem;display:flex;align-items:flex-start;gap:0.3rem;">
            <i class="bi bi-info-circle" style="flex-shrink:0;margin-top:1px;"></i>
            <span>Requires: {{ implode(', ', array_slice($svc->requirements_list, 0, 2)) }}
                @if (count($svc->requirements_list) > 2)
                    and more
                @endif
            </span>
        </div>
    @endif

    {{-- Book button — always at bottom ── --}}
    <a href="{{ route('customer.services.create', [
        'vendor' => $svc->vendor_id,
        'service_type' => $svc->service_type,
    ]) }}"
        class="cu-btn cu-btn--primary cu-btn--full mt-auto" style="margin-top:auto;">
        <i class="bi bi-calendar-check"></i> Book This Service
    </a>

</div>
