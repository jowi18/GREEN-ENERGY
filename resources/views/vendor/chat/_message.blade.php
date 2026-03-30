{{-- resources/views/vendor/chat/_message.blade.php --}}
@php
    $isOut = $msg->sender_type === $myType;
    $isSystem = $msg->sender_type === 'system' || $msg->message_type === 'auto_reply';
    $cls = $isSystem ? 'chat-msg--system' : ($isOut ? 'chat-msg--out' : 'chat-msg--in');
    $bubbleCls = $msg->message_type === 'auto_reply' ? 'chat-bubble auto-reply' : 'chat-bubble';
@endphp

<div class="chat-msg {{ $cls }}" id="msg-{{ $msg->id }}" data-ts="{{ $msg->created_at->toISOString() }}">
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
                        <span>{{ $att->file_name }}<br><small>{{ $att->file_size_human }}</small></span>
                    </a>
                @endif
            @endforeach
        </div>
        <div class="chat-time">
            @if ($msg->is_auto_reply)
                <span style="font-size:0.65rem;opacity:0.7;">🤖 Auto</span>
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
