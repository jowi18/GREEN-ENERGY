<?php

// ════════════════════════════════════════════════════
// app/Models/ChatPredefinedReply.php
// ════════════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatPredefinedReply extends Model
{
    protected $fillable = [
        'vendor_id','trigger_key','question','reply_template','is_active','sort_order',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function vendor() { return $this->belongsTo(Vendor::class); }

    public function scopeForVendor($q, int $vendorId) { return $q->where('vendor_id', $vendorId); }
    public function scopeActive($q)                    { return $q->where('is_active', true); }
}
