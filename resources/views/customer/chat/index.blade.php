@extends('layouts.customer')
@section('title', 'My Conversations')

@push('styles')
    <link href="{{ asset('layout_css/chat.css') }}" rel="stylesheet">
    <style>
        /* Override customer layout padding for full-height chat shell */
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

        {{-- ── Sidebar ── --}}
        <div class="chat-sidebar">
            <div class="chat-sidebar__header">
                <div class="chat-sidebar__title">
                    <i class="bi bi-chat-dots-fill" style="color:var(--cg-500);"></i>
                    Messages
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
                    <a href="{{ route('customer.chat.show', $room->vendor_id) }}" class="chat-room-item">
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
                    <div style="padding:2.5rem 1rem;text-align:center;color:var(--tx-muted);">
                        <i class="bi bi-chat-dots" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                        <div style="font-size:0.82rem;">No conversations yet.</div>
                        <a href="{{ route('customer.vendors.index') }}"
                            style="font-size:0.78rem;color:var(--cg-600);font-weight:700;margin-top:0.35rem;display:inline-block;">
                            Browse vendors →
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ── Empty right panel ── --}}
        <div class="chat-main">
            <div class="chat-empty">
                <i class="bi bi-chat-dots"></i>
                <h6 style="font-weight:700;margin-bottom:0.35rem;">Select a conversation</h6>
                <p style="font-size:0.875rem;max-width:260px;">
                    Choose a vendor from the list to continue your conversation.
                </p>
                <a href="{{ route('customer.vendors.index') }}" class="cu-btn cu-btn--primary mt-3 d-inline-flex">
                    <i class="bi bi-shop me-1"></i> Find Vendors
                </a>
            </div>
        </div>

    </div>
@endsection
