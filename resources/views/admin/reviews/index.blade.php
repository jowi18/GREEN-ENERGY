@extends('layouts.admin')
@section('title', 'Platform Reviews')
@section('page-title', 'Reviews & Feedback')

@section('content')
    <div class="row g-3 section-gap">
        @foreach ([['Total Reviews', $stats['total'], 'blue', 'bi-star'], ['Flagged', $stats['flagged'], 'red', 'bi-flag'], ['Avg Rating', $stats['average'] . '★', 'green', 'bi-star-half'], ['This Month', $stats['this_month'], 'yellow', 'bi-calendar-check']] as [$l, $v, $c, $i])
            <div class="col-6 col-md-3">
                <div class="stat-card stat-card--{{ $c }}">
                    <div class="stat-card__icon stat-card__icon--{{ $c }}"><i class="bi {{ $i }}"></i>
                    </div>
                    <div class="stat-card__value">{{ $v }}</div>
                    <div class="stat-card__label">{{ $l }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="panel section-gap" style="padding:.85rem 1.25rem;">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm"
                style="width:180px;" placeholder="Customer or review…">
            <select name="rating" class="form-select form-select-sm" style="width:auto;">
                <option value="">All Ratings</option>
                @foreach ([5, 4, 3, 2, 1] as $s)
                    <option value="{{ $s }}" {{ request('rating') == $s ? 'selected' : '' }}>{{ $s }} Stars
                    </option>
                @endforeach
            </select>
            <select name="vendor" class="form-select form-select-sm" style="width:auto;">
                <option value="">All Vendors</option>
                @foreach ($vendors as $v)
                    <option value="{{ $v->id }}" {{ request('vendor') == $v->id ? 'selected' : '' }}>
                        {{ $v->business_name }}</option>
                @endforeach
            </select>
            <select name="flagged" class="form-select form-select-sm" style="width:auto;">
                <option value="">All</option>
                <option value="1" {{ request('flagged') === '1' ? 'selected' : '' }}>Flagged only</option>
            </select>
            <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-funnel"></i></button>
            @if (request()->hasAny(['search', 'rating', 'vendor', 'flagged']))
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-sm btn-light"><i class="bi bi-x"></i></a>
            @endif
        </form>
    </div>

    <div class="d-flex flex-column gap-2">
        @forelse($reviews as $review)
            <div class="panel p-3 {{ $review->is_flagged ? 'border border-danger' : '' }}"
                style="{{ !$review->is_visible ? 'opacity:.55;' : '' }}">
                <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <span class="fw-700" style="font-size:.875rem;">{{ $review->customer->full_name }}</span>
                            <span class="text-muted" style="font-size:.75rem;">→
                                {{ $review->vendor->business_name }}</span>
                            <span class="badge bg-{{ $review->review_type === 'service' ? 'success' : 'info' }}"
                                style="font-size:.62rem;">{{ $review->review_type }}</span>
                            @if ($review->is_flagged)
                                <span class="badge bg-danger" style="font-size:.62rem;">🚩 Flagged</span>
                            @endif
                            @if (!$review->is_visible)
                                <span class="badge bg-secondary" style="font-size:.62rem;">Hidden</span>
                            @endif
                        </div>
                        <div class="d-flex gap-1 mb-1">
                            @for ($s = 1; $s <= 5; $s++)
                                <i class="bi bi-star{{ $s <= $review->rating ? '-fill' : '' }}"
                                    style="color:#f59e0b;font-size:.8rem;"></i>
                            @endfor
                            <span class="text-muted ms-1"
                                style="font-size:.72rem;">{{ $review->created_at->format('M d, Y') }}</span>
                        </div>
                        @if ($review->predefined_tags)
                            <div class="mb-1">
                                @foreach ($review->predefined_tags as $tag)
                                    <span class="badge bg-light text-dark me-1"
                                        style="font-size:.65rem;">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif
                        @if ($review->title)
                            <div class="fw-700" style="font-size:.875rem;">{{ $review->title }}</div>
                        @endif
                        @if ($review->body)
                            <p class="text-secondary mb-0 mt-1" style="font-size:.82rem;line-height:1.7;">
                                {{ Str::limit($review->body, 200) }}</p>
                        @endif
                    </div>
                    <div class="d-flex gap-1 flex-shrink-0">
                        <form method="POST" action="{{ route('admin.reviews.hide', $review) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-{{ $review->is_visible ? 'secondary' : 'success' }}"
                                title="{{ $review->is_visible ? 'Hide review' : 'Show review' }}">
                                <i class="bi bi-eye{{ $review->is_visible ? '-slash' : '' }}"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.reviews.delete', $review) }}"
                            onsubmit="return confirm('Permanently delete this review?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="panel text-center text-muted py-5">No reviews found.</div>
        @endforelse
    </div>
    <div class="mt-3">{{ $reviews->links('pagination::bootstrap-5') }}</div>
@endsection
