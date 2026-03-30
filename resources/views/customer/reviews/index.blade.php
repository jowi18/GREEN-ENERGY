@extends('layouts.customer')
@section('title', 'My Reviews')

@section('content')
    <div class="cu-page">

        <div class="cu-section-head mb-4">
            <div class="cu-section-title">⭐ My Reviews</div>
        </div>

        @if ($reviews->count())
            <div style="display:flex;flex-direction:column;gap:1rem;">
                @foreach ($reviews as $review)
                    <div class="cu-card">
                        <div class="cu-card__body">
                            {{-- Header --}}
                            <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <div style="font-family:'Nunito',sans-serif;font-weight:800;font-size:.9rem;">
                                            {{ $review->vendor->business_name }}
                                        </div>
                                        <span
                                            style="font-size:.68rem;background:var(--cg-50);color:var(--cg-600);
                                         border:1px solid var(--cg-200);border-radius:var(--r-full);
                                         padding:.1rem .5rem;font-weight:700;">
                                            {{ $review->review_type === 'service' ? '🔧 Service' : '🛍️ Order' }}
                                        </span>
                                        @if ($review->is_verified_purchase)
                                            <span
                                                style="font-size:.68rem;background:#eff6ff;color:#1d4ed8;
                                         border:1px solid #bfdbfe;border-radius:var(--r-full);
                                         padding:.1rem .5rem;font-weight:700;">✓
                                                Verified</span>
                                        @endif
                                    </div>
                                    {{-- Stars --}}
                                    <div class="d-flex gap-1 mb-1">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <i class="bi bi-star{{ $s <= $review->rating ? '-fill' : '' }}"
                                                style="color:{{ $s <= $review->rating ? '#f59e0b' : 'var(--cn-300)' }};font-size:.9rem;"></i>
                                        @endfor
                                        <span class="text-muted ms-1" style="font-size:.75rem;">
                                            {{ $review->created_at->format('M d, Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Tags --}}
                            @if ($review->predefined_tags)
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    @foreach ($review->predefined_tags as $tag)
                                        <span
                                            style="font-size:.72rem;padding:.15rem .55rem;border-radius:var(--r-full);
                                 background:var(--cg-50);color:var(--cg-700);
                                 border:1px solid var(--cg-200);">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Title + body --}}
                            @if ($review->title)
                                <div class="fw-700" style="font-size:.9rem;margin-bottom:.25rem;">{{ $review->title }}</div>
                            @endif
                            @if ($review->body)
                                <p
                                    style="font-size:.875rem;color:var(--tx-secondary);line-height:1.7;margin-bottom:.75rem;">
                                    {{ $review->body }}
                                </p>
                            @endif

                            {{-- Images --}}
                            @if ($review->images)
                                <div class="d-flex gap-2 flex-wrap mb-2">
                                    @foreach ($review->images as $img)
                                        <img src="{{ asset('storage/' . $img) }}"
                                            style="width:70px;height:70px;object-fit:cover;border-radius:var(--r-sm);
                                border:1.5px solid var(--card-border);cursor:pointer;"
                                            onclick="document.getElementById('lb').style.display='flex';
                                  document.getElementById('lbImg').src=this.src;">
                                    @endforeach
                                </div>
                            @endif

                            {{-- Vendor reply --}}
                            @if ($review->vendor_reply)
                                <div
                                    style="background:var(--cg-50);border-left:3px solid var(--cg-400);
                            border-radius:0 var(--r-sm) var(--r-sm) 0;
                            padding:.75rem 1rem;margin-top:.75rem;">
                                    <div
                                        style="font-size:.72rem;font-weight:800;color:var(--cg-700);
                                text-transform:uppercase;letter-spacing:.06em;margin-bottom:.3rem;">
                                        <i class="bi bi-shop me-1"></i>
                                        {{ $review->vendor->business_name }} replied
                                        <span class="text-muted fw-400">·
                                            {{ $review->vendor_replied_at->format('M d, Y') }}</span>
                                    </div>
                                    <p style="font-size:.875rem;color:var(--tx-secondary);margin:0;line-height:1.7;">
                                        {{ $review->vendor_reply }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 d-flex justify-content-center">
                {{ $reviews->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="cu-empty">
                <i class="bi bi-star"></i>
                <h6>No reviews yet</h6>
                <p>Reviews you write for completed orders and services will appear here.</p>
            </div>
        @endif

    </div>

    {{-- Lightbox --}}
    <div id="lb"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.9);
     z-index:9999;align-items:center;justify-content:center;cursor:pointer;"
        onclick="this.style.display='none'">
        <img id="lbImg" style="max-width:90vw;max-height:90vh;border-radius:8px;">
    </div>
@endsection
