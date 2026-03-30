@extends('layouts.vendor')

@section('title', 'Stock Movements')
@section('page-title', 'Stock Movements')
@section('breadcrumb')
    <a href="{{ route('vendor.inventory.index') }}" class="text-secondary">Inventory</a>
    <span class="sep">›</span><span class="current">Movements</span>
@endsection

@section('content')

    {{-- ── Filters ── --}}
    <div class="vd-card gap-section">
        <div class="vd-card__body" style="padding:0.85rem 1.25rem;">
            <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">

                <select name="product_id" class="vd-select" style="width:auto;min-width:200px;">
                    <option value="">All Products</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>

                <select name="type" class="vd-select" style="width:auto;">
                    <option value="">All Types</option>
                    <option value="purchase" {{ request('type') === 'purchase' ? 'selected' : '' }}>Purchase</option>
                    <option value="sale" {{ request('type') === 'sale' ? 'selected' : '' }}>Online Sale
                    </option>
                    <option value="pos_sale" {{ request('type') === 'pos_sale' ? 'selected' : '' }}>POS Sale</option>
                    <option value="adjustment_add" {{ request('type') === 'adjustment_add' ? 'selected' : '' }}>Adjustment
                        (+)</option>
                    <option value="adjustment_sub" {{ request('type') === 'adjustment_sub' ? 'selected' : '' }}>Adjustment
                        (-)</option>
                    <option value="return" {{ request('type') === 'return' ? 'selected' : '' }}>Return</option>
                    <option value="damage" {{ request('type') === 'damage' ? 'selected' : '' }}>Damage</option>
                    <option value="opening_stock" {{ request('type') === 'opening_stock' ? 'selected' : '' }}>Opening
                        Stock</option>
                </select>

                <select name="direction" class="vd-select" style="width:auto;">
                    <option value="">In & Out</option>
                    <option value="in" {{ request('direction') === 'in' ? 'selected' : '' }}>Inbound (+)</option>
                    <option value="out" {{ request('direction') === 'out' ? 'selected' : '' }}>Outbound (-)</option>
                </select>

                <input type="date" name="date_from" value="{{ request('date_from') }}" class="vd-input"
                    style="width:auto;" title="From date">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="vd-input" style="width:auto;"
                    title="To date">

                <button type="submit" class="vd-btn vd-btn--primary"><i class="bi bi-funnel"></i> Filter</button>

                @if (request()->hasAny(['product_id', 'type', 'direction', 'date_from', 'date_to']))
                    <a href="{{ route('vendor.inventory.movements') }}" class="vd-btn vd-btn--ghost">
                        <i class="bi bi-x"></i> Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- ── Movements table ── --}}
    <div class="vd-card">
        <div class="vd-card__header">
            <span class="vd-card__title"><i class="bi bi-clock-history"></i> Movement Log</span>
            <span class="vd-card__subtitle">{{ $movements->total() }} records</span>
        </div>

        @if ($movements->count())
            <div class="table-responsive">
                <table class="vd-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Change</th>
                            <th>Before</th>
                            <th>After</th>
                            <th>Performed By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movements as $mov)
                            <tr>
                                <td class="text-muted" style="font-size:0.8rem;white-space:nowrap;">
                                    {{ $mov->created_at->format('M d, Y') }}<br>
                                    <span style="font-size:0.72rem;">{{ $mov->created_at->format('H:i') }}</span>
                                </td>
                                <td>
                                    <div class="fw-600" style="font-size:0.875rem;">{{ $mov->product->name ?? '-' }}</div>
                                    <div class="mono text-muted" style="font-size:0.72rem;">{{ $mov->product->sku ?? '—' }}
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="vd-badge {{ $mov->isInbound() ? 'vd-badge--active' : 'vd-badge--neutral' }}"
                                        style="font-size:0.7rem;">
                                        {{ $mov->movement_type_label }}
                                    </span>
                                </td>
                                <td>
                                    <span class="mono fw-600 {{ $mov->isInbound() ? 'text-green' : 'text-red' }}"
                                        style="font-size:0.9rem;">
                                        {{ $mov->isInbound() ? '+' : '' }}{{ $mov->quantity_change }}
                                    </span>
                                </td>
                                <td class="mono text-secondary">{{ $mov->quantity_before }}</td>
                                <td class="mono fw-600">{{ $mov->quantity_after }}</td>
                                <td class="text-secondary" style="font-size:0.82rem;">
                                    {{ $mov->performer?->name ?? 'System' }}
                                </td>
                                <td class="text-muted" style="font-size:0.8rem;max-width:180px;">
                                    {{ $mov->notes ? Str::limit($mov->notes, 60) : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($movements->hasPages())
                <div class="vd-card__footer">
                    <span class="text-muted" style="font-size:0.8rem;">
                        Showing {{ $movements->firstItem() }}–{{ $movements->lastItem() }} of {{ $movements->total() }}
                    </span>
                    {{ $movements->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <i class="bi bi-clock-history"></i>
                <h6>No movements found</h6>
                <p>Stock movements will appear here as products are bought, sold, or adjusted.</p>
            </div>
        @endif
    </div>

@endsection
