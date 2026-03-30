@extends('layouts.customer')
@section('title', 'Chat — ' . $vendor->business_name)

@push('styles')
    <link href="{{ asset('layout_css/chat.css') }}" rel="stylesheet">
    <style>
        .cu-page {
            padding: 0 !important;
            max-width: 100% !important;
        }

        body {
            overflow: hidden;
        }
    </style>
@endpush

@section('content')
    <div class="chat-shell" style="height:calc(100vh - var(--navbar-h, 60px));">

        {{-- ── Sidebar: vendor conversations ── --}}
        <div class="chat-sidebar" id="chatSidebar">
            <div class="chat-sidebar__header">
                <div class="chat-sidebar__title">
                    <i class="bi bi-chat-dots-fill" style="color:var(--cg-500);"></i>
                    Messages
                    @php $totalUnread = $rooms->sum('customer_unread'); @endphp
                    @if ($totalUnread > 0)
                        <span class="chat-unread-badge">{{ $totalUnread }}</span>
                    @endif
                </div>
                <form method="GET" class="chat-search">
                    <i class="bi bi-search chat-search__icon"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="chat-search__input"
                        placeholder="Search vendors…">
                </form>
            </div>

            <div class="chat-rooms">
                @forelse($rooms as $room)
                    <a href="{{ route('customer.chat.show', $room->vendor_id) }}"
                        class="chat-room-item {{ $room->vendor_id === $vendor->id ? 'active' : '' }}">
                        <div class="chat-room-item__avatar">
                            {{ strtoupper(substr($room->vendor->business_name, 0, 2)) }}
                            @if ($room->isVendorOnline())
                                <span class="chat-room-item__online-dot"></span>
                            @endif
                        </div>
                        <div class="chat-room-item__body">
                            <div class="chat-room-item__name">{{ $room->vendor->business_name }}</div>
                            <div class="chat-room-item__preview {{ $room->customer_unread > 0 ? 'unread' : '' }}">
                                {{ $room->last_message_preview ?? 'Start a conversation' }}
                            </div>
                        </div>
                        <div class="chat-room-item__meta">
                            <div class="chat-room-item__time">
                                {{ $room->last_message_at?->diffForHumans(short: true) ?? '' }}
                            </div>
                            @if ($room->customer_unread > 0)
                                <span class="chat-unread-badge">{{ $room->customer_unread }}</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div style="padding:2rem;text-align:center;color:var(--tx-muted);font-size:0.82rem;">
                        No other conversations
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ── Main chat area ── --}}
        <div class="chat-main">

            {{-- Header ────────────────────────── --}}
            <div class="chat-header">
                <button class="chat-action-btn d-md-none me-1"
                    onclick="document.getElementById('chatSidebar').classList.toggle('mobile-open')" style="flex-shrink:0;">
                    <i class="bi bi-list"></i>
                </button>

                <div class="chat-header__avatar">
                    {{ strtoupper(substr($vendor->business_name, 0, 2)) }}
                    @if ($room->isVendorOnline())
                        <span class="chat-room-item__online-dot"></span>
                    @endif
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="chat-header__name">{{ $vendor->business_name }}</div>
                    <div class="chat-header__status" id="statusText">
                        @if ($room->isVendorTyping())
                            <span class="typing">Vendor is typing…</span>
                        @elseif($room->isVendorOnline())
                            <span class="online">● Online</span>
                        @else
                            @if ($vendor->city)
                                {{ $vendor->city }}, {{ $vendor->province_state }}
                            @else
                                Offline
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Vendor quick info ─────────── --}}
                <div class="d-none d-md-flex align-items-center gap-2">
                    <a href="{{ route('customer.vendors.show', $vendor) }}" class="cu-btn cu-btn--ghost cu-btn--sm">
                        <i class="bi bi-shop"></i> View Shop
                    </a>
                    <a href="{{ route('customer.services.create', ['vendor' => $vendor->id]) }}"
                        class="cu-btn cu-btn--primary cu-btn--sm">
                        <i class="bi bi-calendar-plus"></i> Book
                    </a>
                </div>
            </div>

            {{-- Messages ──────────────────────── --}}
            <div class="chat-messages" id="chatMessages">

                {{-- Welcome message if no messages yet --}}
                @if ($room->messages->isEmpty())
                    <div class="chat-msg chat-msg--system">
                        <div>
                            <div class="chat-bubble">
                                👋 Start a conversation with <strong>{{ $vendor->business_name }}</strong>.
                                Use the quick questions below or type your own message.
                            </div>
                        </div>
                    </div>
                @endif

                @php $lastDate = null; @endphp
                @foreach ($room->messages as $msg)
                    @php
                        $dateLabel = $msg->created_at->isToday()
                            ? 'Today'
                            : ($msg->created_at->isYesterday()
                                ? 'Yesterday'
                                : $msg->created_at->format('M d, Y'));
                    @endphp
                    @if ($dateLabel !== $lastDate)
                        <div class="chat-date-divider">{{ $dateLabel }}</div>
                        @php $lastDate = $dateLabel; @endphp
                    @endif

                    @include('customer.chat._message', ['msg' => $msg, 'myType' => 'customer'])
                @endforeach

                {{-- Typing indicator --}}
                <div class="typing-indicator" id="typingIndicator" style="display:none;">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>

            {{-- Predefined quick questions ────── --}}
            @if ($predefined->count())
                <div class="chat-quick-panel" id="quickPanel">
                    <div class="chat-quick-label">
                        <i class="bi bi-lightning-charge me-1"></i>
                        Quick Questions
                        <button onclick="document.getElementById('quickPanel').style.display='none'"
                            style="background:none;border:none;cursor:pointer;color:var(--tx-muted);
                               float:right;font-size:0.75rem;padding:0;">
                            ✕ hide
                        </button>
                    </div>
                    <div class="chat-quick-pills">
                        @foreach ($predefined as $q)
                            <button class="chat-quick-pill" onclick="triggerAutoReply('{{ $q['trigger_key'] }}', this)">
                                {{ $q['question'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Input area ─────────────────────── --}}
            <div class="chat-input-area">
                <div class="chat-attach-previews" id="attachPreviews"></div>
                <div class="chat-input-row">
                    <button class="chat-action-btn" onclick="document.getElementById('fileInput').click()"
                        title="Attach file">
                        <i class="bi bi-paperclip"></i>
                    </button>
                    <input type="file" id="fileInput" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt"
                        style="display:none;" onchange="previewAttachments(this)">

                    <textarea id="chatInput" class="chat-input" rows="1" placeholder="Message {{ $vendor->business_name }}…"
                        onkeydown="handleKey(event)" oninput="autoGrow(this); onTyping()"></textarea>

                    <button class="chat-send-btn" id="sendBtn" onclick="sendMessage()">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- Lightbox --}}
    <div class="chat-lightbox" id="chatLightbox" onclick="closeLightbox()">
        <img id="lightboxImg" src="" alt="Preview">
    </div>
@endsection

@push('scripts')
    <script>
        const VENDOR_ID = {{ $vendor->id }};
        const SEND_URL = '{{ route('customer.chat.send', $vendor->id) }}';
        const POLL_URL = '{{ route('customer.chat.poll', $vendor->id) }}';
        const TYPING_URL = '{{ route('customer.chat.typing', $vendor->id) }}';
        const AUTOREPLY_URL = '{{ route('customer.chat.auto-reply', $vendor->id) }}';
        const MY_TYPE = 'customer';

        let lastTimestamp = null;
        let typingTimer = null;
        let pendingFiles = [];
        let isWaiting = false; // prevent double-send

        /* ── Boot ──────────────────────────────── */
        scrollBottom();
        setLastTimestamp();
        startPolling();

        function setLastTimestamp() {
            const msgs = document.querySelectorAll('[data-ts]');
            if (msgs.length) lastTimestamp = msgs[msgs.length - 1].dataset.ts;
        }

        /* ── Scroll ────────────────────────────── */
        function scrollBottom(smooth = false) {
            const el = document.getElementById('chatMessages');
            el.scrollTo({
                top: el.scrollHeight,
                behavior: smooth ? 'smooth' : 'auto'
            });
        }

        /* ── Send ──────────────────────────────── */
        function handleKey(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        }

        function sendMessage() {
            if (isWaiting) return;
            const input = document.getElementById('chatInput');
            const body = input.value.trim();
            if (!body && pendingFiles.length === 0) return;

            isWaiting = true;
            const btn = document.getElementById('sendBtn');
            btn.disabled = true;

            const fd = new FormData();
            if (body) fd.append('body', body);
            pendingFiles.forEach(f => fd.append('attachments[]', f));

            input.value = '';
            input.style.height = '';
            pendingFiles = [];
            document.getElementById('attachPreviews').innerHTML = '';

            $.ajax({
                url: SEND_URL,
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success(res) {
                    if (res.success) appendMessage(res.message);
                },
                complete() {
                    isWaiting = false;
                    btn.disabled = false;
                }
            });
        }

        /* ── Auto-reply (predefined question) ─── */
        function triggerAutoReply(triggerKey, btn) {
            btn.disabled = true;
            btn.style.opacity = '0.5';

            $.post(AUTOREPLY_URL, {
                trigger_key: triggerKey
            }, function(res) {
                // The question was already sent as a customer message server-side;
                // poll will pick it up. The auto-reply is returned directly.
                if (res.success) {
                    // Force immediate poll to show the customer's own question message
                    pollNow();
                }
            }).fail(function() {
                btn.disabled = false;
                btn.style.opacity = '';
            });
        }

        /* ── Attachments ───────────────────────── */
        function previewAttachments(input) {
            pendingFiles = [...input.files];
            const container = document.getElementById('attachPreviews');
            container.innerHTML = '';
            pendingFiles.forEach((file, i) => {
                const div = document.createElement('div');
                div.className = 'chat-attach-preview';
                if (file.type.startsWith('image/')) {
                    const r = new FileReader();
                    r.onload = e => {
                        div.innerHTML = `<img src="${e.target.result}" alt="">
                    <button class="chat-attach-preview__rm" onclick="removeFile(${i})">
                        <i class="bi bi-x"></i>
                    </button>`;
                    };
                    r.readAsDataURL(file);
                } else {
                    div.innerHTML = `<i class="bi bi-file-earmark" style="font-size:1.2rem;color:var(--tx-muted)"></i>
                <button class="chat-attach-preview__rm" onclick="removeFile(${i})">
                    <i class="bi bi-x"></i>
                </button>`;
                }
                container.appendChild(div);
            });
        }

        function removeFile(i) {
            pendingFiles.splice(i, 1);
            document.getElementById('fileInput').value = '';
            // Re-render
            const dummy = {
                files: pendingFiles
            };
            previewAttachments({
                files: pendingFiles
            });
        }

        /* ── Render ────────────────────────────── */
        function appendMessage(msg) {
            if (document.getElementById(`msg-${msg.id}`)) return; // dedupe
            const el = document.getElementById('chatMessages');
            const div = document.createElement('div');
            div.setAttribute('data-ts', msg.created_at);
            div.innerHTML = buildBubble(msg);
            const typing = document.getElementById('typingIndicator');
            el.insertBefore(div, typing);
            lastTimestamp = msg.created_at;
            scrollBottom(true);
        }

        function buildBubble(msg) {
            const isOut = msg.sender_type === MY_TYPE;
            const isAuto = msg.is_auto_reply || msg.message_type === 'auto_reply';
            const isSystem = msg.sender_type === 'system';
            const cls = isSystem ? 'chat-msg chat-msg--system' :
                `chat-msg ${isOut ? 'chat-msg--out' : 'chat-msg--in'}`;
            const bubbleCls = isAuto ? 'chat-bubble auto-reply' : 'chat-bubble';

            let body = escHtml(msg.body || '').replace(/\n/g, '<br>')
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>'); // bold **text**

            let attachHtml = '';
            (msg.attachments || []).forEach(a => {
                if (a.type === 'image') {
                    attachHtml += `<div class="chat-attachment" onclick="openLightbox('${a.url}')">
                <img src="${a.url}" alt="${escHtml(a.name)}"></div>`;
                } else {
                    attachHtml += `<a href="${a.url}" target="_blank" class="chat-file">
                <i class="bi bi-file-earmark-text" style="font-size:1.2rem;flex-shrink:0;"></i>
                <span>${escHtml(a.name)}<br><small>${a.size}</small></span></a>`;
                }
            });

            const autoTag = isAuto ? '<span style="font-size:0.65rem;opacity:0.7;margin-right:4px;">🤖</span>' : '';
            const tick = isOut ?
                `<span class="read-tick ${msg.is_read ? 'read' : ''}"><i class="bi bi-check-all"></i></span>` :
                '';

            return `<div class="${cls}" id="msg-${msg.id}" data-ts="${msg.created_at}">
        <div>
            <div class="${bubbleCls}">${body}${attachHtml}</div>
            <div class="chat-time">${autoTag}${msg.time_label}${tick}</div>
        </div>
    </div>`;
        }

        function escHtml(t) {
            return String(t)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        /* ── Polling ───────────────────────────── */
        function startPolling() {
            setInterval(pollNow, 3000);
        }

        function pollNow() {
            $.get(POLL_URL, {
                since: lastTimestamp
            }, function(data) {
                (data.messages || []).forEach(msg => {
                    appendMessage(msg);
                });

                // Typing indicator
                const tip = document.getElementById('typingIndicator');
                const show = data.vendor_typing;
                tip.style.display = show ? 'flex' : 'none';
                if (show) scrollBottom(true);

                // Status text
                const st = document.getElementById('statusText');
                if (st) {
                    if (data.vendor_typing) st.innerHTML = '<span class="typing">Vendor is typing…</span>';
                    else if (data.vendor_online) st.innerHTML = '<span class="online">● Online</span>';
                    else st.innerHTML = 'Offline';
                }

                // Update read ticks on outgoing messages
                (data.messages || []).forEach(msg => {
                    if (msg.sender_type !== MY_TYPE && msg.is_read) {
                        document.querySelectorAll('.read-tick').forEach(t => t.classList.add('read'));
                    }
                });
            });
        }

        /* ── Typing signal ─────────────────────── */
        function onTyping() {
            clearTimeout(typingTimer);
            $.post(TYPING_URL, {
                typing: 1
            });
            typingTimer = setTimeout(() => $.post(TYPING_URL, {
                typing: 0
            }), 3000);
        }

        function autoGrow(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 120) + 'px';
        }

        /* ── Lightbox ──────────────────────────── */
        function openLightbox(src) {
            document.getElementById('lightboxImg').src = src;
            document.getElementById('chatLightbox').classList.add('open');
        }

        function closeLightbox() {
            document.getElementById('chatLightbox').classList.remove('open');
        }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeLightbox();
        });
    </script>
@endpush
