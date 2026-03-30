@extends('layouts.customer')
@section('title', 'Write a Review')

@push('styles')
    <style>
        /* ── Star rating interactive ──────────────── */
        .star-rating {
            display: flex;
            gap: .35rem;
            flex-direction: row-reverse;
            justify-content: flex-end;
            margin-bottom: .5rem;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            font-size: 2.2rem;
            cursor: pointer;
            color: var(--cn-300);
            transition: color .12s, transform .1s;
        }

        .star-rating label:hover,
        .star-rating label:hover~label,
        .star-rating input:checked~label {
            color: #f59e0b;
        }

        .star-rating label:hover {
            transform: scale(1.12);
        }

        /* ── Tag chips ────────────────────────────── */
        .tag-chips {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
            min-height: 2rem;
            transition: opacity .2s;
        }

        .tag-chip {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .35rem .8rem;
            border-radius: var(--r-full);
            font-size: .8rem;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid var(--card-border);
            background: var(--card-bg);
            color: var(--tx-secondary);
            transition: all .15s;
            user-select: none;
        }

        .tag-chip:hover {
            border-color: var(--cg-300);
            color: var(--cg-700);
        }

        .tag-chip.selected {
            background: var(--cg-600);
            color: #fff;
            border-color: var(--cg-600);
        }

        .tag-chip.selected .tag-x {
            display: inline;
        }

        .tag-x {
            display: none;
            font-size: .7rem;
        }

        /* ── Rating label ─────────────────────────── */
        .rating-label {
            font-size: .875rem;
            font-weight: 700;
            color: var(--tx-secondary);
            min-height: 1.4rem;
            margin-bottom: .75rem;
        }

        /* ── Image previews ───────────────────────── */
        .img-preview-grid {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
            margin-top: .5rem;
        }

        .img-preview-item {
            width: 72px;
            height: 72px;
            border-radius: var(--r-md);
            overflow: hidden;
            position: relative;
            border: 1.5px solid var(--card-border);
        }

        .img-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .img-preview-item__rm {
            position: absolute;
            top: 2px;
            right: 2px;
            background: rgba(239, 68, 68, .85);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: .6rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .vendor-card-mini {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .85rem 1rem;
            background: var(--cg-50);
            border: 1.5px solid var(--cg-200);
            border-radius: var(--r-md);
            margin-bottom: 1.25rem;
        }

        .vendor-card-mini__avatar {
            width: 44px;
            height: 44px;
            border-radius: var(--r-md);
            background: var(--cg-200);
            color: var(--cg-700);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: .9rem;
            flex-shrink: 0;
        }
    </style>
@endpush

@section('content')
    <div class="cu-page" style="max-width:640px;">

        <div class="d-flex align-items-center gap-2 mb-4">
            <a href="{{ $backRoute }}" class="cu-btn cu-btn--ghost cu-btn--sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="fw-800 mb-0" style="font-family:'Nunito',sans-serif;">Write a Review</h5>
        </div>

        {{-- What you're reviewing --}}
        <div class="vendor-card-mini">
            <div class="vendor-card-mini__avatar">
                {{ strtoupper(substr($vendor->business_name, 0, 2)) }}
            </div>
            <div style="flex:1;min-width:0;">
                <div class="fw-700" style="font-size:.9rem;">{{ $vendor->business_name }}</div>
                <div class="text-muted" style="font-size:.78rem;">
                    {{ $subject_type === 'order' ? '🛍️ Order Review' : '🔧 Service Review' }}
                    &nbsp;·&nbsp; {{ $subject_label }}
                </div>
            </div>
            <i class="bi bi-patch-check-fill" style="color:var(--cg-500);font-size:1.1rem;"></i>
        </div>

        <form method="POST"
            action="{{ $subject_type === 'order'
                ? route('customer.reviews.order.store', $subject->id)
                : route('customer.reviews.service.store', $subject->id) }}"
            enctype="multipart/form-data" id="reviewForm">
            @csrf

            {{-- ── Star Rating ── --}}
            <div class="cu-card mb-3">
                <div class="cu-card__body">
                    <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                        <i class="bi bi-star-fill" style="color:#f59e0b;"></i> Your Rating
                    </div>

                    <div class="star-rating" id="starRating">
                        @for ($i = 5; $i >= 1; $i--)
                            <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}"
                                {{ old('rating') == $i ? 'checked' : '' }} onchange="onRatingChange({{ $i }})">
                            <label for="star{{ $i }}">★</label>
                        @endfor
                    </div>
                    <div class="rating-label" id="ratingLabel">Select a rating</div>

                    @error('rating')
                        <div style="color:#ef4444;font-size:.78rem;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- ── Predefined Tags ── --}}
            <div class="cu-card mb-3" id="tagsCard">
                <div class="cu-card__body">
                    <div class="fw-800 mb-1" style="font-family:'Nunito',sans-serif;">
                        <i class="bi bi-tags me-1 text-green"></i> Quick Feedback
                        <span class="text-muted fw-400" style="font-size:.78rem;">(select all that apply)</span>
                    </div>
                    <div class="text-muted mb-3" style="font-size:.78rem;">
                        Select a star rating above to see relevant feedback options.
                    </div>
                    <div class="tag-chips" id="tagChips">
                        {{-- Populated by JS based on rating --}}
                    </div>
                    {{-- Hidden inputs for selected tags --}}
                    <div id="tagInputs"></div>
                </div>
            </div>

            {{-- ── Written Review ── --}}
            <div class="cu-card mb-3">
                <div class="cu-card__body">
                    <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                        <i class="bi bi-pencil-square me-1 text-green"></i> Your Feedback
                        <span class="text-muted fw-400" style="font-size:.78rem;">(optional)</span>
                    </div>

                    <div class="mb-3">
                        <label class="cu-label">Review title</label>
                        <input type="text" name="title" value="{{ old('title') }}" class="cu-input"
                            placeholder="Summarize your experience…" maxlength="150">
                    </div>

                    <div>
                        <label class="cu-label">Detailed feedback</label>
                        <textarea name="body" rows="4" class="cu-textarea"
                            placeholder="Tell others about the quality, service, and your overall experience…" maxlength="2000">{{ old('body') }}</textarea>
                        <div style="font-size:.72rem;color:var(--tx-muted);text-align:right;margin-top:.2rem;">
                            <span id="bodyCount">0</span>/2000
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Photo Upload ── --}}
            <div class="cu-card mb-4">
                <div class="cu-card__body">
                    <div class="fw-800 mb-1" style="font-family:'Nunito',sans-serif;">
                        <i class="bi bi-camera me-1 text-green"></i> Add Photos
                        <span class="text-muted fw-400" style="font-size:.78rem;">(optional, max 4)</span>
                    </div>
                    <div class="text-muted mb-3" style="font-size:.78rem;">
                        Show the product quality, installation, or the service result.
                    </div>

                    <div class="img-preview-grid" id="imgPreviews"></div>

                    <label
                        style="display:inline-flex;align-items:center;gap:.5rem;
                              padding:.55rem 1rem;border:1.5px dashed var(--card-border);
                              border-radius:var(--r-md);cursor:pointer;font-size:.82rem;
                              color:var(--tx-secondary);margin-top:.5rem;transition:border-color .15s;"
                        onmouseover="this.style.borderColor='var(--cg-300)'"
                        onmouseout="this.style.borderColor='var(--card-border)'">
                        <i class="bi bi-plus-circle text-green"></i>
                        Add photos
                        <input type="file" name="images[]" id="imgInput" accept="image/*" multiple style="display:none;"
                            onchange="previewImages(this)">
                    </label>
                </div>
            </div>

            <button type="submit" class="cu-btn cu-btn--primary cu-btn--full cu-btn--lg" id="submitBtn">
                <i class="bi bi-send-check"></i> Submit Review
            </button>

            <p class="text-muted text-center mt-2" style="font-size:.75rem;">
                <i class="bi bi-shield-check me-1"></i>
                Your review helps other customers make informed decisions.
            </p>

        </form>
    </div>
@endsection

@push('scripts')
    <script>
        const PREDEFINED_TAGS = @json($predefinedTags);

        const RATING_LABELS = {
            5: '⭐⭐⭐⭐⭐ Excellent — Highly Recommend!',
            4: '⭐⭐⭐⭐ Good — Satisfied',
            3: '⭐⭐⭐ Average — Okay Experience',
            2: '⭐⭐ Below Average — Needs Improvement',
            1: '⭐ Poor — Not Recommended',
        };

        let selectedTags = [];

        function onRatingChange(rating) {
            document.getElementById('ratingLabel').textContent = RATING_LABELS[rating] || '';

            // Render tag chips for this rating
            const chips = PREDEFINED_TAGS[rating] || [];
            const wrapper = document.getElementById('tagChips');
            const hint = wrapper.previousElementSibling;
            if (hint) hint.style.display = 'none';

            selectedTags = [];
            syncTagInputs();

            wrapper.innerHTML = chips.map(tag =>
                `<button type="button"
                 class="tag-chip"
                 onclick="toggleTag(this, '${tag.replace(/'/g, "\\'")}')">
            ${tag}<span class="tag-x ms-1">✕</span>
         </button>`
            ).join('');
        }

        function toggleTag(el, tag) {
            if (el.classList.contains('selected')) {
                el.classList.remove('selected');
                selectedTags = selectedTags.filter(t => t !== tag);
            } else {
                el.classList.add('selected');
                selectedTags.push(tag);
            }
            syncTagInputs();
        }

        function syncTagInputs() {
            const container = document.getElementById('tagInputs');
            container.innerHTML = selectedTags.map(t =>
                `<input type="hidden" name="tags[]" value="${t}">`
            ).join('');
        }

        // Body char counter
        document.querySelector('textarea[name="body"]').addEventListener('input', function() {
            document.getElementById('bodyCount').textContent = this.value.length;
        });

        // Image preview
        let pendingFiles = [];

        function previewImages(input) {
            const total = pendingFiles.length + input.files.length;
            if (total > 4) {
                Swal.fire({
                    icon: 'warning',
                    text: 'Maximum 4 photos allowed.',
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false
                });
                input.value = '';
                return;
            }
            [...input.files].forEach(file => {
                pendingFiles.push(file);
                const r = new FileReader();
                r.onload = e => {
                    const idx = pendingFiles.length - 1;
                    const div = document.createElement('div');
                    div.className = 'img-preview-item';
                    div.id = `prev-${idx}`;
                    div.innerHTML = `<img src="${e.target.result}">
                <button type="button" class="img-preview-item__rm"
                        onclick="removeImg(${idx})"><i class="bi bi-x"></i></button>`;
                    document.getElementById('imgPreviews').appendChild(div);
                };
                r.readAsDataURL(file);
            });
        }

        function removeImg(idx) {
            document.getElementById(`prev-${idx}`)?.remove();
            pendingFiles[idx] = null;
        }

        // Form submit
        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            // Rebuild file input with only remaining files
            const dt = new DataTransfer();
            pendingFiles.filter(Boolean).forEach(f => dt.items.add(f));
            document.getElementById('imgInput').files = dt.files;

            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting…';
        });

        // Pre-select rating if old() value set
        const oldRating = {{ old('rating', 0) }};
        if (oldRating > 0) onRatingChange(oldRating);
    </script>
@endpush
