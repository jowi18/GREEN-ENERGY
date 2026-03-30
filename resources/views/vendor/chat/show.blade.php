@extends('layouts.vendor')

@section('title', 'Chat — ' . $chatRoom->customer->full_name)
@section('page-title', 'Customer Chat')

@push('styles')
    <link href="{{ asset('layout_css/chat.css') }}" rel="stylesheet">
    <style>
        .vd-content {
            padding: 0 !important;
        }
    </style>
@endpush

@section('content')
    <div class="chat-shell">

        {{-- ── Sidebar: conversation list ── --}}
        <div class="chat-sidebar">
            <div class="chat-sidebar__header">
                <div class="chat-sidebar__title">
                    <i class="bi bi-chat-dots-fill text-green"></i>
                    Conversations
                    @php $total = $rooms->sum('vendor_unread'); @endphp
                    @if ($total > 0)
                        <span class="chat-unread-badge">{{ $total }}</span>
                    @endif
                </div>
                <form method="GET" class="chat-search">
                    <i class="bi bi-search chat-search__icon"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="chat-search__input"
                        placeholder="Search customers…">
                </form>
            </div>

            <div class="chat-rooms">
                @forelse($rooms as $room)
                    @php $isActive = $room->id === $chatRoom->id; @endphp
                    <a href="{{ route('vendor.chat.show', $room->id) }}"
                        class="chat-room-item {{ $isActive ? 'active' : '' }}">
                        <div class="chat-room-item__avatar">
                            {{ strtoupper(substr($room->customer->first_name, 0, 1) . substr($room->customer->last_name, 0, 1)) }}
                            @if ($room->isCustomerOnline())
                                <span class="chat-room-item__online-dot"></span>
                            @endif
                        </div>
                        <div class="chat-room-item__body">
                            <div class="chat-room-item__name">{{ $room->customer->full_name }}</div>
                            <div class="chat-room-item__preview {{ $room->vendor_unread > 0 ? 'unread' : '' }}">
                                {{ $room->last_message_preview ?? 'No messages yet' }}
                            </div>
                        </div>
                        <div class="chat-room-item__meta">
                            <div class="chat-room-item__time">
                                {{ $room->last_message_at?->diffForHumans(short: true) ?? '' }}
                            </div>
                            @if ($room->vendor_unread > 0)
                                <span class="chat-unread-badge">{{ $room->vendor_unread }}</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div style="padding:2rem;text-align:center;color:var(--tx-muted);font-size:0.82rem;">
                        No conversations yet
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ── Main chat ── --}}
        <div class="chat-main">

            {{-- Header --}}
            <div class="chat-header">
                <div class="chat-header__avatar">
                    {{ strtoupper(substr($chatRoom->customer->first_name, 0, 1) . substr($chatRoom->customer->last_name, 0, 1)) }}
                    @if ($chatRoom->isCustomerOnline())
                        <span class="chat-room-item__online-dot"></span>
                    @endif
                </div>
                <div style="flex:1;">
                    <div class="chat-header__name">{{ $chatRoom->customer->full_name }}</div>
                    <div class="chat-header__status" id="statusText">
                        @if ($chatRoom->isCustomerTyping())
                            <span class="typing">Customer is typing…</span>
                        @elseif($chatRoom->isCustomerOnline())
                            <span class="online">● Online</span>
                        @else
                            {{ $chatRoom->customer_last_seen?->diffForHumans() ?? 'Offline' }}
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('vendor.chat.predefined') }}" class="vd-btn vd-btn--ghost vd-btn--sm"
                        title="Quick reply templates">
                        <i class="bi bi-lightning"></i> Quick Replies
                    </a>
                </div>
            </div>

            {{-- Messages ─────────────────────── --}}
            <div class="chat-messages" id="chatMessages">
                @php $lastDate = null; @endphp
                @foreach ($chatRoom->messages as $msg)
                    @php $dateLabel = $msg->created_at->isToday() ? 'Today' : ($msg->created_at->isYesterday() ? 'Yesterday' : $msg->created_at->format('M d, Y')); @endphp
                    @if ($dateLabel !== $lastDate)
                        <div class="chat-date-divider">{{ $dateLabel }}</div>
                        @php $lastDate = $dateLabel; @endphp
                    @endif

                    @include('vendor.chat._message', ['msg' => $msg, 'myType' => 'vendor'])
                @endforeach

                <div class="typing-indicator" id="typingIndicator" style="display:none;">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>

            {{-- Quick reply pills ─────────────── --}}
            @if ($quickReplies->count())
                <div class="chat-quick-panel">
                    <div class="chat-quick-label"><i class="bi bi-lightning me-1"></i>Quick Replies</div>
                    <div class="chat-quick-pills">
                        @foreach ($quickReplies as $qr)
                            <button class="chat-quick-pill" onclick="sendQuickReply({{ json_encode($qr->question) }})">
                                {{ $qr->question }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Input area ────────────────────── --}}
            <div class="chat-input-area">
                <div class="chat-attach-previews" id="attachPreviews"></div>
                <div class="chat-input-row">
                    <button class="chat-action-btn" onclick="document.getElementById('fileInput').click()"
                        title="Attach file">
                        <i class="bi bi-paperclip"></i>
                    </button>
                    <input type="file" id="fileInput" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt"
                        style="display:none;" onchange="previewAttachments(this)">

                    <textarea id="chatInput" class="chat-input" rows="1" placeholder="Type a message…" onkeydown="handleKey(event)"
                        oninput="autoGrow(this); onTyping()"></textarea>

                    <button class="chat-send-btn" id="sendBtn" onclick="sendMessage()" title="Send">
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
        const ROOM_ID = {{ $chatRoom->id }};
        const SEND_URL = '{{ route('vendor.chat.send', $chatRoom->id) }}';
        const POLL_URL = '{{ route('vendor.chat.poll', $chatRoom->id) }}';
        const TYPING_URL = '{{ route('vendor.chat.typing', $chatRoom->id) }}';
        const MY_TYPE = 'vendor';
        let lastTimestamp = null;
        let typingTimer = null;
        let pendingFiles = [];

        /* ── Init ─────────────────────────────── */
        scrollBottom();
        startPolling();

        /* ── Scroll ───────────────────────────── */
        function scrollBottom(smooth = false) {
            const el = document.getElementById('chatMessages');
            el.scrollTo({
                top: el.scrollHeight,
                behavior: smooth ? 'smooth' : 'auto'
            });
        }

        /* ── Send text ────────────────────────── */
        function handleKey(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        }

        function sendMessage() {
            const input = document.getElementById('chatInput');
            const body = input.value.trim();
            if (!body && pendingFiles.length === 0) return;

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
                }
            });
        }

        function sendQuickReply(text) {
            document.getElementById('chatInput').value = text;
            sendMessage();
        }

        /* ── Attachments ──────────────────────── */
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
                        div.innerHTML =
                            `<img src="${e.target.result}"><button class="chat-attach-preview__rm" onclick="removeFile(${i})"><i class="bi bi-x"></i></button>`;
                    };
                    r.readAsDataURL(file);
                } else {
                    div.innerHTML =
                        `<i class="bi bi-file-earmark" style="font-size:1.2rem;color:var(--tx-muted)"></i><button class="chat-attach-preview__rm" onclick="removeFile(${i})"><i class="bi bi-x"></i></button>`;
                }
                container.appendChild(div);
            });
        }

        function removeFile(index) {
            pendingFiles.splice(index, 1);
            const dummy = {
                files: pendingFiles
            };
            previewAttachments(dummy);
        }

        /* ── Render message ───────────────────── */
        function appendMessage(msg) {
            const el = document.getElementById('chatMessages');
            const div = document.createElement('div');
            div.id = `msg-${msg.id}`;
            div.innerHTML = buildBubble(msg);
            // Insert before typing indicator
            const typing = document.getElementById('typingIndicator');
            el.insertBefore(div, typing);
            if (msg.created_at) lastTimestamp = msg.created_at;
            scrollBottom(true);
        }

        function buildBubble(msg) {
            const isOut = msg.sender_type === MY_TYPE;
            const isSystem = msg.sender_type === 'system' || msg.message_type === 'auto_reply';
            const cls = isSystem ? 'chat-msg--system' : (isOut ? 'chat-msg--out' : 'chat-msg--in');
            const bubbleCls = msg.message_type === 'auto_reply' ? 'chat-bubble auto-reply' : 'chat-bubble';

            let attachHtml = '';
            (msg.attachments || []).forEach(a => {
                if (a.type === 'image') {
                    attachHtml +=
                        `<div class="chat-attachment" onclick="openLightbox('${a.url}')"><img src="${a.url}" alt="${a.name}"></div>`;
                } else {
                    attachHtml +=
                        `<a href="${a.url}" target="_blank" class="chat-file"><i class="bi bi-file-earmark-text"></i><span>${a.name}<br><small>${a.size}</small></span></a>`;
                }
            });

            const tick = isOut ?
                `<span class="read-tick ${msg.is_read ? 'read' : ''}"><i class="bi bi-check-all"></i></span>` : '';
            const autoTag = msg.is_auto_reply ?
                '<span style="font-size:0.65rem;opacity:0.7;margin-right:0.4rem;">🤖 Auto</span>' : '';

            return `<div class="chat-msg ${cls}">
        <div>
            <div class="${bubbleCls}">${escHtml(msg.body || '')}${attachHtml}</div>
            <div class="chat-time">${autoTag}${msg.time_label}${tick}</div>
        </div>
    </div>`;
        }

        function escHtml(t) {
            return t.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/\n/g, '<br>');
        }

        /* ── Polling ──────────────────────────── */
        function startPolling() {
            // Set initial timestamp from last message
            const msgs = document.querySelectorAll('[id^="msg-"]');
            if (msgs.length) {
                lastTimestamp = msgs[msgs.length - 1].dataset?.ts || null;
            }
            setInterval(poll, 3000);
        }

        function poll() {
            $.get(POLL_URL, {
                since: lastTimestamp
            }, function(data) {
                (data.messages || []).forEach(msg => {
                    if (!document.getElementById(`msg-${msg.id}`)) {
                        appendMessage(msg);
                    } else if (msg.is_read) {
                        // Update tick
                        const el = document.getElementById(`msg-${msg.id}`);
                        el?.querySelectorAll('.read-tick').forEach(t => t.classList.add('read'));
                    }
                });
                if (data.messages?.length) {
                    lastTimestamp = data.messages[data.messages.length - 1].created_at;
                }

                // Typing indicator
                const typing = document.getElementById('typingIndicator');
                typing.style.display = data.customer_typing ? 'flex' : 'none';
                if (data.customer_typing) scrollBottom(true);

                // Status
                const status = document.getElementById('statusText');
                if (status) {
                    if (data.customer_typing) status.innerHTML = '<span class="typing">Customer is typing…</span>';
                    else if (data.customer_online) status.innerHTML = '<span class="online">● Online</span>';
                    else status.innerHTML = 'Offline';
                }
            });
        }

        /* ── Typing signal ────────────────────── */
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

        /* ── Lightbox ─────────────────────────── */
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
