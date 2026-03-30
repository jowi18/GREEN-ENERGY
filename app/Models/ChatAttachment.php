<?php

// ════════════════════════════════════════════════════
// app/Models/ChatAttachment.php
// ════════════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatAttachment extends Model
{
    protected $fillable = [
        'chat_message_id','chat_room_id','file_path','file_name',
        'file_mime','file_size','attachment_type',
    ];

    public function message() { return $this->belongsTo(ChatMessage::class, 'chat_message_id'); }

    public function getUrlAttribute(): string { return asset('storage/' . $this->file_path); }
    public function isImage(): bool { return $this->attachment_type === 'image'; }

    public function getFileSizeHumanAttribute(): string
    {
        $b = $this->file_size ?? 0;
        if ($b < 1024) return $b . ' B';
        if ($b < 1048576) return round($b/1024, 1) . ' KB';
        return round($b/1048576, 1) . ' MB';
    }
}
