<?php

// ════════════════════════════════════════════════════
// app/Models/ChatMessage.php
// ════════════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'chat_room_id','sender_id','sender_type','body','message_type',
        'is_read','read_at','is_auto_reply','auto_reply_trigger',
    ];

    protected $casts = [
        'is_read'       => 'boolean',
        'is_auto_reply' => 'boolean',
        'read_at'       => 'datetime',
    ];

    public function room()   { return $this->belongsTo(ChatRoom::class, 'chat_room_id'); }
    public function sender() { return $this->belongsTo(User::class, 'sender_id'); }
    public function attachments() { return $this->hasMany(ChatAttachment::class); }

    public function isFromVendor(): bool   { return $this->sender_type === 'vendor'; }
    public function isFromCustomer(): bool { return $this->sender_type === 'customer'; }
    public function isSystem(): bool       { return $this->sender_type === 'system'; }

    public function markRead(): void
    {
        if (! $this->is_read) {
            $this->update(['is_read' => true, 'read_at' => now()]);
        }
    }

    public function scopeUnread($q) { return $q->where('is_read', false); }
    public function scopeForRoom($q, int $roomId) { return $q->where('chat_room_id', $roomId); }
}
