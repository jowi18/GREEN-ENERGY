@extends('layouts.vendor')
@section('title', 'Reviews & Feedback')
@section('page-title', 'Reviews & Feedback')

@push('styles')
    <style>
        .rating-bar {
            display: flex;
            align-items: center;
            gap: .65rem;
            margin-bottom: .35rem;
        }

        .rating-bar__label {
            font-size: .75rem;
            font-weight: 700;
            width: 12px;
            color: var(--tx-secondary);
            flex-shrink: 0;
        }

        .rating-bar__track {
            flex: 1;
            height: 8px;
            border-radius: 4px;
            background: var(--n-100);
            overflow: hidden;
        }

        .rating-bar__fill {
            height: 100%;
            border-radius: 4px;
            background: #f59e0b;
            transition: width .6s;
        }

        .rating-bar__count {
            font-size: .72rem;
            color: var(--tx-muted);
            width: 28px;
            text-align: right;
            flex-shrink: 0;
        }

        .review-card {
            background: var(--card-bg);
            border: 1.5px solid var(--card-border);
            border-radius: var(--r-lg);
            padding: 1.25rem;
            margin-bottom: .85rem;
            transition: border-color .2s;
        }

        .review-card:hover {
            border-color: var(--g-200);
        }

        .review-card.flagged {
            border-color: #fecaca;
            background: #fff5f5;
        }

        .star-row {
            display: flex;
            gap: 2px;
        }

        .star-row i {
            font-size: .9rem;
        }

        .reply-box {
            background: var(--g-50);
            border: 1.5px solid var(--g-200);
            border-radius: var(--r-md);
            padding: 1rem;
            margin-top: .85rem;
        }

        .reply-box__header {
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--g-700);
            margin-bottom: .4rem;
        }

        .tag-pill {
            display: inline-flex;
            font-size: .68rem;
            padding: .12rem .5rem;
            border-radius: var(--r-full);
            background: var(--g-50);
            color: var(--g-700);
            border: 1px solid var(--g-100);
            margin: .1rem;
            font-weight: 600;
        }

        .avg-score {
            font-size: 3rem;
            font-weight: 800;
            font-family: 'JetBrains Mono', monospace;
            color: var(--g-700);
            line-height: 1;
        }

        .avg-stars {
            display: flex;
            gap: 3px;
            margin: .3rem 0;
        }

        .avg-stars i {
            font-size: 1.1rem;
        }
    </style>
@endpush

@section('content')

    {{-- ── Stats row ── --}}
    <div class="row g-2 gap-section">
        @foreach ([['Total Reviews', $stats['total'], 'green', 'bi-star'], ['Average Rating', $stats['average'] . '★', 'yellow', 'bi-star-half'], ['Unreplied', $stats['unreplied'], 'orange', 'bi-chat-left'], ['This Month', $stats['this_month'], 'blue', 'bi-calendar-check'], ['Flagged', $stats['flagged'], 'danger', 'bi-flag']] as [$l, $v, $c, $i])
            <div class="col-6 col-md col-lg">
                <div class="vd-stat vd-stat--{{ $c }}">
                    <div class="vd-stat__icon vd-stat__icon--{{ $c }}"><i class="bi {{ $i }}"></i></div>
                    <div class="vd-stat__value">{{ $v }}</div>
                    <div class="vd-stat__label">{{ $l }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">

        {{-- ── Left: Rating distribution ── --}}
        <div class="col-12 col-xl-3">
            <div class="vd-card" style="position:sticky;top:calc(var(--topbar-h,64px) + 1rem);">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-bar-chart"></i> Rating Breakdown</span>
                </div>
                <div class="vd-card__body">
                    <div class="text-center mb-3">
                        <div class="avg-score">{{ $stats['average'] }}</div>
                        <div class="avg-stars justify-content-center">
                            @for ($s = 1; $s <= 5; $s++)
                                <i class="bi bi-star{{ $s <= round($stats['average']) ? '-fill' : ($s - 0.5 <= $stats['average'] ? '-half' : '') }}"
                                    style="color:#f59e0b;"></i>
                            @endfor
                        </div>
                        <div class="text-muted" style="font-size:.78rem;">{{ $stats['total'] }}
                            review{{ $stats['total'] !== 1 ? 's' : '' }}</div>
                    </div>

                    @foreach ($distribution as $star => $data)
                        <div class="rating-bar">
                            <span class="rating-bar__label">{{ $star }}</span>
                            <div class="rating-bar__track">
                                <div class="rating-bar__fill" style="width:{{ $data['percent'] }}%"></div>
                            </div>
                            <span class="rating-bar__count">{{ $data['count'] }}</span>
                        </div>
                    @endforeach

                    <hr style="border-color:var(--n-100);margin:1rem 0;">

                    {{-- Filter shortcuts --}}
                    <div
                        style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:var(--tx-muted);margin-bottom:.5rem;">
                        Quick Filter</div>
                    @foreach ([5, 4, 3, 2, 1] as $star)
                        <a href="{{ route('vendor.reviews.index', ['rating' => $star]) }}"
                            class="d-flex align-items-center gap-2 py-1 text-decoration-none"
                            style="font-size:.82rem;color:var(--tx-secondary);">
                            @for ($s = 1; $s <= 5; $s++)
                                <i class="bi bi-star{{ $s <= $star ? '-fill' : '' }}"
                                    style="color:#f59e0b;font-size:.7rem;"></i>
                            @endfor
                            <span class="text-muted">({{ $distribution[$star]['count'] ?? 0 }})</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Right: Reviews list ── --}}
        <div class="col-12 col-xl-9">

            {{-- Filters --}}
            <div class="vd-card gap-section">
                <div class="vd-card__body" style="padding:.8rem 1.25rem;">
                    <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
                        <div class="vd-search">
                            <i class="bi bi-search vd-search__icon"></i>
                            <input type="text" name="search" value="{{ request('search') }}" class="vd-search__input"
                                placeholder="Search reviews…">
                        </div>
                        <select name="rating" class="vd-select" style="width:auto;">
                            <option value="">All Stars</option>
                            @foreach ([5, 4, 3, 2, 1] as $s)
                                <option value="{{ $s }}" {{ request('rating') == $s ? 'selected' : '' }}>
                                    {{ $s }} Stars</option>
                            @endforeach
                        </select>
                        <select name="type" class="vd-select" style="width:auto;">
                            <option value="">All Types</option>
                            <option value="order" {{ request('type') === 'order' ? 'selected' : '' }}>🛍️ Orders
                            </option>
                            <option value="service" {{ request('type') === 'service' ? 'selected' : '' }}>🔧 Services
                            </option>
                        </select>
                        <select name="replied" class="vd-select" style="width:auto;">
                            <option value="">All Reviews</option>
                            <option value="no" {{ request('replied') === 'no' ? 'selected' : '' }}>Unreplied</option>
                            <option value="yes" {{ request('replied') === 'yes' ? 'selected' : '' }}>Replied</option>
                        </select>
                        <button type="submit" class="vd-btn vd-btn--primary"><i class="bi bi-funnel"></i></button>
                        @if (request()->hasAny(['search', 'rating', 'type', 'replied']))
                            <a href="{{ route('vendor.reviews.index') }}" class="vd-btn vd-btn--ghost"><i
                                    class="bi bi-x"></i></a>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Reviews --}}
            @forelse($reviews as $review)
                <div class="review-card {{ $review->is_flagged ? 'flagged' : '' }}" id="review-{{ $review->id }}">

                    {{-- Header --}}
                    <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                        <div class="d-flex align-items-center gap-2">
                            <div
                                style="width:38px;height:38px;border-radius:50%;background:var(--g-100);color:var(--g-700);display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:800;flex-shrink:0;">
                                {{ strtoupper(substr($review->customer->first_name, 0, 1) . substr($review->customer->last_name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-700" style="font-size:.9rem;">{{ $review->customer->full_name }}</div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <div class="star-row">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <i class="bi bi-star{{ $s <= $review->rating ? '-fill' : '' }}"
                                                style="color:{{ $s <= $review->rating ? '#f59e0b' : 'var(--n-300)' }};"></i>
                                        @endfor
                                    </div>
                                    <span class="text-muted"
                                        style="font-size:.72rem;">{{ $review->created_at->format('M d, Y') }}</span>
                                    <span
                                        style="font-size:.65rem;padding:.1rem .4rem;border-radius:var(--r-full);
                                         background:{{ $review->review_type === 'service' ? 'var(--g-50)' : '#eff6ff' }};
                                         color:{{ $review->review_type === 'service' ? 'var(--g-700)' : '#1d4ed8' }};
                                         border:1px solid {{ $review->review_type === 'service' ? 'var(--g-200)' : '#bfdbfe' }};
                                         font-weight:700;">
                                        {{ $review->review_type === 'service' ? '🔧 Service' : '🛍️ Order' }}
                                    </span>
                                    @if ($review->is_verified_purchase)
                                        <span
                                            style="font-size:.65rem;padding:.1rem .4rem;border-radius:var(--r-full);background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;font-weight:700;">✓
                                            Verified</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex gap-1">
                            @if ($review->is_flagged)
                                <span class="vd-badge vd-badge--danger" style="font-size:.65rem;"><i
                                        class="bi bi-flag me-1"></i>Flagged</span>
                            @endif
                            <form method="POST" action="{{ route('vendor.reviews.flag', $review) }}">
                                @csrf
                                <button type="submit" class="vd-btn vd-btn--ghost vd-btn--icon"
                                    title="{{ $review->is_flagged ? 'Unflag' : 'Flag review' }}">
                                    <i class="bi bi-flag{{ $review->is_flagged ? '-fill' : '' }}"
                                        style="{{ $review->is_flagged ? 'color:#ef4444;' : '' }}"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Predefined tags --}}
                    @if ($review->predefined_tags)
                        <div class="mt-2 mb-1">
                            @foreach ($review->predefined_tags as $tag)
                                <span class="tag-pill">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif

                    {{-- Title + body --}}
                    @if ($review->title)
                        <div class="fw-700 mt-2" style="font-size:.9rem;">{{ $review->title }}</div>
                    @endif
                    @if ($review->body)
                        <p class="text-secondary mt-1 mb-2" style="font-size:.875rem;line-height:1.7;">
                            {{ $review->body }}</p>
                    @endif

                    {{-- Images --}}
                    @if ($review->images)
                        <div class="d-flex gap-2 flex-wrap mb-2">
                            @foreach ($review->images as $img)
                                <img src="{{ asset('storage/' . $img) }}"
                                    style="width:64px;height:64px;object-fit:cover;border-radius:var(--r-sm);
                            border:1.5px solid var(--card-border);cursor:pointer;"
                                    onclick="document.getElementById('lb').style.display='flex';document.getElementById('lbImg').src=this.src;">
                            @endforeach
                        </div>
                    @endif

                    {{-- Source link --}}
                    <div class="text-muted" style="font-size:.72rem;">
                        @if ($review->order)
                            <i class="bi bi-bag me-1"></i>
                            Order: <a href="{{ route('vendor.orders.show', $review->order) }}"
                                class="text-green">{{ $review->order->order_number }}</a>
                        @elseif($review->serviceRequest)
                            <i class="bi bi-tools me-1"></i>
                            Service: <a href="{{ route('vendor.services.show', $review->serviceRequest) }}"
                                class="text-green">{{ $review->serviceRequest->request_number }}</a>
                        @endif
                    </div>

                    {{-- ── Vendor Reply ── --}}
                    @if ($review->vendor_reply)
                        <div class="reply-box">
                            <div class="reply-box__header">
                                <i class="bi bi-shop me-1"></i>Your Reply
                                <span class="text-muted fw-400 ms-1" style="font-size:.7rem;">
                                    · {{ $review->vendor_replied_at->format('M d, Y') }}
                                </span>
                                <form method="POST" action="{{ route('vendor.reviews.reply.delete', $review) }}"
                                    class="d-inline ms-2">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="vd-btn vd-btn--ghost vd-btn--sm"
                                        style="font-size:.65rem;padding:.1rem .45rem;"
                                        onclick="return confirm('Delete this reply?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                            <p id="reply-text-{{ $review->id }}"
                                style="font-size:.875rem;color:var(--tx-secondary);line-height:1.7;margin:0;">
                                {{ $review->vendor_reply }}
                            </p>
                            <button class="vd-btn vd-btn--ghost vd-btn--sm mt-2"
                                onclick="openReplyEdit({{ $review->id }}, '{{ addslashes($review->vendor_reply) }}')">
                                <i class="bi bi-pencil"></i> Edit Reply
                            </button>
                        </div>
                    @else
                        {{-- No reply yet — show reply form inline --}}
                        <div class="mt-2" id="reply-area-{{ $review->id }}">
                            <button class="vd-btn vd-btn--ghost vd-btn--sm" onclick="openReplyForm({{ $review->id }})">
                                <i class="bi bi-chat-left-text me-1"></i>Reply to this review
                            </button>
                            <div id="reply-form-{{ $review->id }}" style="display:none;margin-top:.65rem;">
                                <textarea class="vd-input" rows="3" id="reply-input-{{ $review->id }}"
                                    placeholder="Write a professional, helpful response…" style="min-height:80px;font-size:.875rem;"></textarea>
                                <div class="d-flex gap-2 mt-2">
                                    <button class="vd-btn vd-btn--primary vd-btn--sm"
                                        onclick="submitReply({{ $review->id }})">
                                        <i class="bi bi-send"></i> Post Reply
                                    </button>
                                    <button class="vd-btn vd-btn--ghost vd-btn--sm"
                                        onclick="cancelReply({{ $review->id }})">Cancel</button>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            @empty
                <div class="vd-card">
                    <div class="empty-state">
                        <i class="bi bi-star"></i>
                        <h6>No reviews yet</h6>
                        <p>Customer reviews for your orders and services will appear here.</p>
                    </div>
                </div>
            @endforelse

            @if ($reviews->hasPages())
                <div class="mt-3">{{ $reviews->links('pagination::bootstrap-5') }}</div>
            @endif

        </div>
    </div>

    {{-- Image lightbox --}}
    <div id="lb"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.9);
     z-index:9999;align-items:center;justify-content:center;cursor:pointer;"
        onclick="this.style.display='none'">
        <img id="lbImg" style="max-width:90vw;max-height:90vh;border-radius:8px;">
    </div>

@endsection

@push('scripts')
    <script>
        function openReplyForm(id) {
            document.getElementById(`reply-form-${id}`).style.display = '';
        }

        function cancelReply(id) {
            document.getElementById(`reply-form-${id}`).style.display = 'none';
            document.getElementById(`reply-input-${id}`).value = '';
        }

        function submitReply(id) {
            const text = document.getElementById(`reply-input-${id}`).value.trim();
            if (!text) {
                Swal.fire({
                    icon: 'warning',
                    text: 'Reply cannot be empty.',
                    toast: true,
                    position: 'top-end',
                    timer: 1800,
                    showConfirmButton: false
                });
                return;
            }

            $.ajax({
                url: `/vendor/reviews/${id}/reply`,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    vendor_reply: text
                }),
                success(res) {
                    if (res.success) {
                        // Replace reply area with posted reply
                        const area = document.getElementById(`reply-area-${id}`);
                        area.innerHTML = `<div class="reply-box">
                    <div class="reply-box__header"><i class="bi bi-shop me-1"></i>Your Reply <span class="text-muted fw-400 ms-1" style="font-size:.7rem;">· Just now</span></div>
                    <p style="font-size:.875rem;color:var(--tx-secondary);line-height:1.7;margin:0;">${escHtml(res.vendor_reply)}</p>
                </div>`;
                        Swal.fire({
                            icon: 'success',
                            text: 'Reply posted!',
                            toast: true,
                            position: 'top-end',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                }
            });
        }

        function openReplyEdit(id, currentText) {
            const replyBox = document.getElementById(`review-${id}`).querySelector('.reply-box');
            replyBox.innerHTML = `
        <textarea class="vd-input" rows="3" id="edit-input-${id}"
                  style="min-height:80px;font-size:.875rem;">${currentText}</textarea>
        <div class="d-flex gap-2 mt-2">
            <button class="vd-btn vd-btn--primary vd-btn--sm" onclick="saveEditReply(${id})">
                <i class="bi bi-floppy"></i> Save
            </button>
            <button class="vd-btn vd-btn--ghost vd-btn--sm" onclick="location.reload()">Cancel</button>
        </div>`;
        }

        function saveEditReply(id) {
            const text = document.getElementById(`edit-input-${id}`).value.trim();
            if (!text) return;
            $.ajax({
                url: `/vendor/reviews/${id}/reply`,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    vendor_reply: text
                }),
                success(res) {
                    if (res.success) location.reload();
                }
            });
        }

        function escHtml(t) {
            return String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
        }
    </script>
@endpush
