{{--
    resources/views/customer/chat/_message.blade.php
    $msg    = ChatMessage
    $myType = 'customer' | 'vendor'
--}}
@php
    $isOut = $msg->sender_type === $myType;
    $isAuto = $msg->is_auto_reply || $msg->message_type === 'auto_reply';
    $isSystem = $msg->sender_type === 'system';
    $cls = $isSystem ? 'chat-msg chat-msg--system' : 'chat-msg ' . ($isOut ? 'chat-msg--out' : 'chat-msg--in');
    $bubbleCls = $isAuto ? 'chat-bubble auto-reply' : 'chat-bubble';
@endphp

<div class="{{ $cls }}" id="msg-{{ $msg->id }}" data-ts="{{ $msg->created_at->toISOString() }}">
    <div>
        <div class="{{ $bubbleCls }}">
            @if ($msg->body)
                {!! nl2br(e($msg->body)) !!}
            @endif

            @foreach ($msg->attachments as $att)
                @if ($att->isImage())
                    <div class="chat-attachment" onclick="openLightbox('{{ $att->url }}')">
                        <img src="{{ $att->url }}" alt="{{ $att->file_name }}">
                    </div>
                @else
                    <a href="{{ $att->url }}" target="_blank" class="chat-file">
                        <i class="bi bi-file-earmark-text" style="font-size:1.2rem;flex-shrink:0;"></i>
                        <span>
                            {{ $att->file_name }}
                            <br><small>{{ $att->file_size_human }}</small>
                        </span>
                    </a>
                @endif
            @endforeach
        </div>

        <div class="chat-time">
            @if ($isAuto)
                <span style="font-size:0.65rem;opacity:0.7;margin-right:4px;">🤖 Auto</span>
            @endif
            {{ $msg->created_at->format('g:i A') }}
            @if ($isOut)
                <span class="read-tick {{ $msg->is_read ? 'read' : '' }}">
                    <i class="bi bi-check-all"></i>
                </span>
            @endif
        </div>
    </div>
</div>
