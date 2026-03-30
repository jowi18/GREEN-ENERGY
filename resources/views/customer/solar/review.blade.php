{{-- resources/views/customer/solar/review.blade.php --}}
@extends('layouts.customer')
@section('title', 'Rate Your Installation · ' . $solarProject->project_number)

@push('styles')
    <style>
        .star-rating {
            display: flex;
            gap: .4rem;
            flex-direction: row-reverse;
            justify-content: flex-end;
            margin-bottom: .5rem;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            font-size: 2rem;
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
            transform: scale(1.15);
        }

        .sub-rating {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: .65rem;
        }

        .sub-rating__label {
            font-size: .82rem;
            font-weight: 600;
            color: var(--tx-secondary);
            min-width: 130px;
        }

        .sub-stars {
            display: flex;
            gap: .2rem;
        }

        .sub-star {
            font-size: 1.2rem;
            cursor: pointer;
            color: var(--cn-300);
            transition: color .1s;
        }

        .sub-star.active {
            color: #f59e0b;
        }

        .tag-chip {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .3rem .75rem;
            border-radius: var(--r-full);
            font-size: .78rem;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid var(--card-border);
            background: var(--card-bg);
            color: var(--tx-secondary);
            transition: all .15s;
            user-select: none;
        }

        .tag-chip.selected {
            background: var(--cg-600);
            color: #fff;
            border-color: var(--cg-600);
        }

        .img-previews {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-top: .5rem;
        }

        .img-prev-thumb {
            width: 68px;
            height: 68px;
            border-radius: var(--r-md);
            object-fit: cover;
            border: 1.5px solid var(--card-border);
        }
    </style>
@endpush

@section('content')
    <div class="cu-page" style="max-width:640px;">

        {{-- Header --}}
        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;">
            <a href="{{ route('customer.solar.show', $solarProject) }}" class="cu-btn cu-btn--ghost cu-btn--sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="fw-800 mb-0" style="font-family:'Nunito',sans-serif;">Rate Your Installation</h5>
        </div>

        {{-- Vendor card --}}
        <div
            style="display:flex;align-items:center;gap:.85rem;padding:.85rem 1rem;
        background:var(--cg-50);border:1.5px solid var(--cg-200);
        border-radius:var(--r-md);margin-bottom:1.5rem;">
            <div
                style="width:44px;height:44px;border-radius:var(--r-md);background:var(--cg-200);
            color:var(--cg-700);font-weight:800;font-size:.9rem;
            display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                {{ strtoupper(substr($vendor->business_name, 0, 2)) }}
            </div>
            <div>
                <div class="fw-700" style="font-size:.9rem;">{{ $vendor->business_name }}</div>
                <div class="text-muted" style="font-size:.75rem;">
                    Solar Installation · Project {{ $solarProject->project_number }}
                </div>
            </div>
            <i class="bi bi-patch-check-fill ms-auto" style="color:var(--cg-500);font-size:1.1rem;"></i>
        </div>

        <form method="POST" action="{{ route('customer.solar.review.store', $solarProject) }}"
            enctype="multipart/form-data" id="reviewForm">
            @csrf

            {{-- Overall rating --}}
            <div class="cu-card mb-3">
                <div class="cu-card__body">
                    <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                        <i class="bi bi-star-fill" style="color:#f59e0b;"></i> Overall Rating
                    </div>
                    <div class="star-rating" id="starRating">
                        @for ($i = 5; $i >= 1; $i--)
                            <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}"
                                {{ old('rating') == $i ? 'checked' : '' }} onchange="onRatingChange({{ $i }})">
                            <label for="star{{ $i }}">★</label>
                        @endfor
                    </div>
                    <div id="ratingLabel"
                        style="font-size:.875rem;font-weight:700;
                    color:var(--tx-secondary);min-height:1.4rem;margin-bottom:.75rem;">
                        Select a rating
                    </div>
                    @error('rating')
                        <div style="color:#ef4444;font-size:.78rem;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Sub-ratings --}}
            <div class="cu-card mb-3">
                <div class="cu-card__body">
                    <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                        <i class="bi bi-bar-chart-fill text-green me-1"></i> Rate Specific Areas
                        <span class="text-muted fw-400" style="font-size:.75rem;">(optional)</span>
                    </div>
                    @foreach ([['quality_rating', '🔧', 'Installation Quality'], ['timeliness_rating', '⏱️', 'Timeliness'], ['professionalism_rating', '👷', 'Professionalism'], ['value_rating', '💰', 'Value for Money']] as [$name, $emoji, $label])
                        <div class="sub-rating">
                            <div class="sub-rating__label">{{ $emoji }} {{ $label }}</div>
                            <div class="sub-stars" data-name="{{ $name }}">
                                @for ($i = 1; $i <= 5; $i++)
                                    <span class="sub-star" data-val="{{ $i }}"
                                        onclick="setSubRating('{{ $name }}', {{ $i }})">★</span>
                                @endfor
                            </div>
                            <input type="hidden" name="{{ $name }}" id="{{ $name }}" value="">
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Tags --}}
            <div class="cu-card mb-3" id="tagsCard">
                <div class="cu-card__body">
                    <div class="fw-800 mb-1" style="font-family:'Nunito',sans-serif;">
                        <i class="bi bi-tags text-green me-1"></i> Quick Feedback
                        <span class="text-muted fw-400" style="font-size:.75rem;">(select all that apply)</span>
                    </div>
                    <div class="text-muted mb-3" style="font-size:.78rem;">
                        Select a star rating above to see relevant tags.
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:.4rem;" id="tagChips"></div>
                    <div id="tagInputs"></div>
                </div>
            </div>

            {{-- Written review --}}
            <div class="cu-card mb-3">
                <div class="cu-card__body">
                    <div class="fw-800 mb-3" style="font-family:'Nunito',sans-serif;">
                        <i class="bi bi-pencil-square text-green me-1"></i> Your Feedback
                        <span class="text-muted fw-400" style="font-size:.75rem;">(optional)</span>
                    </div>
                    <div class="mb-3">
                        <label class="cu-label">Review title</label>
                        <input type="text" name="title" value="{{ old('title') }}" class="cu-input"
                            placeholder="e.g. Great installation team!" maxlength="150">
                    </div>
                    <div>
                        <label class="cu-label">Detailed feedback</label>
                        <textarea name="body" rows="4" class="cu-input" maxlength="2000"
                            placeholder="Share your experience with the installation process, the team, and the final result…">{{ old('body') }}</textarea>
                        <div style="font-size:.72rem;color:var(--tx-muted);text-align:right;margin-top:.2rem;">
                            <span id="bodyCount">0</span>/2000
                        </div>
                    </div>
                </div>
            </div>

            {{-- Photos --}}
            <div class="cu-card mb-4">
                <div class="cu-card__body">
                    <div class="fw-800 mb-1" style="font-family:'Nunito',sans-serif;">
                        <i class="bi bi-camera text-green me-1"></i> Add Photos
                        <span class="text-muted fw-400" style="font-size:.75rem;">(optional, max 4)</span>
                    </div>
                    <div class="text-muted mb-3" style="font-size:.78rem;">
                        Show the finished installation — panels on the roof, inverter setup, etc.
                    </div>
                    <div class="img-previews" id="imgPreviews"></div>
                    <label
                        style="display:inline-flex;align-items:center;gap:.5rem;margin-top:.5rem;
                    padding:.55rem 1rem;border:1.5px dashed var(--card-border);
                    border-radius:var(--r-md);cursor:pointer;font-size:.82rem;color:var(--tx-secondary);">
                        <i class="bi bi-plus-circle text-green"></i> Add photos
                        <input type="file" name="images[]" id="imgInput" accept="image/*" multiple
                            style="display:none;" onchange="previewImages(this)">
                    </label>
                </div>
            </div>

            <button type="submit" class="cu-btn cu-btn--primary cu-btn--full cu-btn--lg" id="submitBtn">
                <i class="bi bi-send-check"></i> Submit Review
            </button>
            <p class="text-muted text-center mt-2" style="font-size:.75rem;">
                <i class="bi bi-shield-check me-1"></i>
                Your review helps improve service quality and helps other customers.
            </p>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        const TAGS = {
            5: ['Excellent workmanship', 'Professional team', 'Completed on time', 'Clean installation', 'Great value',
                'System works perfectly', 'Friendly and respectful', 'Would recommend'
            ],
            4: ['Good installation', 'Professional team', 'Mostly on schedule', 'Quality materials',
                'Good communication', 'Satisfied customer'
            ],
            3: ['Average experience', 'Room for improvement', 'Acceptable quality', 'Okay service', 'Could be faster'],
            2: ['Below expectations', 'Delayed installation', 'Quality issues', 'Poor communication',
                'Needs improvement'
            ],
            1: ['Very poor service', 'Unprofessional', 'Did not follow plans', 'Total disappointment',
                'Not recommended'],
        };
        const RATING_LABELS = {
            5: '⭐⭐⭐⭐⭐ Excellent — Highly Recommend!',
            4: '⭐⭐⭐⭐ Good — Satisfied',
            3: '⭐⭐⭐ Average — Okay Experience',
            2: '⭐⭐ Below Average — Needs Improvement',
            1: '⭐ Poor — Not Recommended',
        };
        let selectedTags = [];

        function onRatingChange(r) {
            document.getElementById('ratingLabel').textContent = RATING_LABELS[r] || '';
            selectedTags = [];
            const chips = TAGS[r] || [];
            document.getElementById('tagChips').innerHTML = chips.map(t =>
                `<span class="tag-chip" onclick="toggleTag(this,'${t.replace(/'/g,"\\'")}')">
                ${t}
             </span>`
            ).join('');
            syncTagInputs();
        }

        function toggleTag(el, tag) {
            el.classList.toggle('selected');
            if (el.classList.contains('selected')) selectedTags.push(tag);
            else selectedTags = selectedTags.filter(t => t !== tag);
            syncTagInputs();
        }

        function syncTagInputs() {
            document.getElementById('tagInputs').innerHTML = selectedTags.map(t =>
                `<input type="hidden" name="tags[]" value="${t}">`
            ).join('');
        }

        function setSubRating(name, val) {
            document.getElementById(name).value = val;
            const stars = document.querySelectorAll(`.sub-stars[data-name="${name}"] .sub-star`);
            stars.forEach((s, i) => s.classList.toggle('active', i < val));
        }

        // Char counter
        document.querySelector('textarea[name="body"]').addEventListener('input', function() {
            document.getElementById('bodyCount').textContent = this.value.length;
        });

        // Image preview
        let pendingFiles = [];

        function previewImages(input) {
            if (pendingFiles.length + input.files.length > 4) {
                alert('Maximum 4 photos allowed.');
                input.value = '';
                return;
            }
            [...input.files].forEach(file => {
                pendingFiles.push(file);
                const r = new FileReader();
                r.onload = e => {
                    const idx = pendingFiles.length - 1;
                    const div = document.createElement('div');
                    div.style.cssText = 'position:relative;display:inline-block;';
                    div.innerHTML = `
                    <img src="${e.target.result}" class="img-prev-thumb" id="prev-${idx}">
                    <button type="button" onclick="removeImg(${idx})"
                        style="position:absolute;top:2px;right:2px;background:rgba(239,68,68,.85);
                               color:#fff;border:none;border-radius:50%;width:18px;height:18px;
                               font-size:.6rem;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-x"></i>
                    </button>`;
                    document.getElementById('imgPreviews').appendChild(div);
                };
                r.readAsDataURL(file);
            });
        }

        function removeImg(idx) {
            document.getElementById(`prev-${idx}`)?.parentElement?.remove();
            pendingFiles[idx] = null;
        }

        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            const dt = new DataTransfer();
            pendingFiles.filter(Boolean).forEach(f => dt.items.add(f));
            document.getElementById('imgInput').files = dt.files;
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting…';
        });
    </script>
@endpush
