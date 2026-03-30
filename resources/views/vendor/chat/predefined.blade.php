@extends('layouts.vendor')
@section('title', 'Quick Reply Templates')
@section('page-title', 'Quick Reply Templates')
@section('breadcrumb')
    <a href="{{ route('vendor.chat.index') }}" class="text-secondary">Chat</a>
    <span class="sep">›</span><span class="current">Quick Replies</span>
@endsection

@section('content')
    <div style="max-width:760px;">

        <p class="text-muted gap-section" style="font-size:0.875rem;">
            Configure the predefined questions customers can tap in the chat, and optionally set custom reply text.
            If no custom reply is set, the system auto-generates one based on your services and vendor data.
        </p>

        {{-- Existing custom replies --}}
        @if ($replies->count())
            <div class="vd-card gap-section">
                <div class="vd-card__header">
                    <span class="vd-card__title"><i class="bi bi-lightning"></i> Your Quick Reply Templates</span>
                </div>
                @foreach ($replies as $reply)
                    <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--n-100);">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div style="flex:1;min-width:0;">
                                <div class="fw-600" style="font-size:0.875rem;">{{ $reply->question }}</div>
                                <div class="mono text-muted" style="font-size:0.72rem;margin-top:2px;">
                                    {{ $reply->trigger_key }}</div>
                                @if ($reply->reply_template)
                                    <p class="text-secondary mt-1 mb-0"
                                        style="font-size:0.8rem;line-height:1.6;white-space:pre-line;">
                                        {{ Str::limit($reply->reply_template, 120) }}
                                    </p>
                                @else
                                    <span class="vd-badge vd-badge--info mt-1" style="font-size:0.65rem;">Auto-generated
                                        reply</span>
                                @endif
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0 align-items-center">
                                <span class="vd-badge {{ $reply->is_active ? 'vd-badge--active' : 'vd-badge--neutral' }}"
                                    style="font-size:0.65rem;">
                                    {{ $reply->is_active ? 'Active' : 'Disabled' }}
                                </span>
                                <form method="POST" action="{{ route('vendor.chat.predefined.destroy', $reply) }}"
                                    style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="vd-btn vd-btn--danger vd-btn--icon"
                                        onclick="return confirm('Remove this template?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Add / configure templates --}}
        <div class="vd-card">
            <div class="vd-card__header">
                <span class="vd-card__title"><i class="bi bi-plus-circle"></i> Configure Template</span>
            </div>
            <div class="vd-card__body">
                <form method="POST" action="{{ route('vendor.chat.predefined.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="vd-label">Trigger key <span class="req">*</span></label>
                            <select name="trigger_key" class="vd-select" required>
                                @foreach ($defaultTriggers as $key => $label)
                                    <option value="{{ $key }}">{{ $key }} — "{{ $label }}"
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-hint">The question button the customer taps.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="vd-label">Button label shown to customer <span class="req">*</span></label>
                            <input type="text" name="question" class="vd-input" required
                                placeholder="e.g. What are your available services?">
                        </div>
                        <div class="col-12">
                            <label class="vd-label">Custom reply text</label>
                            <textarea name="reply_template" rows="4" class="vd-input"
                                placeholder="Leave blank to use the auto-generated reply based on your services data.&#10;&#10;You can use plain text, line breaks, and bullet points."></textarea>
                            <div class="form-hint">
                                Leave empty → system auto-generates based on your services, products, and vendor info.
                            </div>
                        </div>
                        <div class="col-12">
                            <label style="display:flex;align-items:center;gap:0.6rem;cursor:pointer;font-size:0.875rem;">
                                <input type="checkbox" name="is_active" value="1" checked
                                    style="accent-color:var(--g-500);width:15px;height:15px;">
                                <span>Active — show this question in customer chat</span>
                            </label>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="vd-btn vd-btn--primary">
                                <i class="bi bi-check-lg"></i> Save Template
                            </button>
                            <a href="{{ route('vendor.chat.index') }}" class="vd-btn vd-btn--ghost ms-2">
                                ← Back to Chat
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Auto-generated reply preview --}}
        <div class="vd-card mt-3">
            <div class="vd-card__header">
                <span class="vd-card__title"><i class="bi bi-robot"></i> Auto-Generated Reply Preview</span>
                <span class="vd-card__subtitle">What customers see when no custom reply is set</span>
            </div>
            <div class="vd-card__body">
                @foreach ($defaultTriggers as $key => $label)
                    <div style="margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid var(--n-100);">
                        <div class="fw-600" style="font-size:0.82rem;margin-bottom:0.25rem;">
                            <span class="mono text-green">{{ $key }}</span> — "{{ $label }}"
                        </div>
                        <div style="font-size:0.78rem;color:var(--tx-muted);">
                            Auto-generated dynamically from your services list and vendor data.
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
@endsection
