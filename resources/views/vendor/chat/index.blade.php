@extends('layouts.vendor')
@section('title', 'Customer Chat')
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

        <div class="chat-sidebar">
            <div class="chat-sidebar__header">
                <div class="chat-sidebar__title">
                    <i class="bi bi-chat-dots-fill text-green"></i>
                    Conversations
                    @if ($totalUnread > 0)
                        <span class="chat-unread-badge">{{ $totalUnread }}</span>
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
                    <a href="{{ route('vendor.chat.show', $room->id) }}" class="chat-room-item">
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
                                {{ $room->last_message_at?->diffForHumans(short: true) ?? '' }}</div>
                            @if ($room->vendor_unread > 0)
                                <span class="chat-unread-badge">{{ $room->vendor_unread }}</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div style="padding:2rem;text-align:center;color:var(--tx-muted);font-size:0.82rem;">
                        No conversations yet.<br>
                        Customers will appear here when they message you.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="chat-main">
            <div class="chat-empty">
                <i class="bi bi-chat-dots"></i>
                <h6 style="font-weight:700;margin-bottom:0.35rem;">No conversation selected</h6>
                <p style="font-size:0.875rem;">Select a conversation from the left to start chatting.</p>
            </div>
        </div>
    </div>
@endsection
