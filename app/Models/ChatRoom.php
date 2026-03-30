<?php
// ════════════════════════════════════════════════════
// app/Models/ChatRoom.php
// ════════════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    protected $fillable = [
        'room_key','vendor_id','customer_id','last_message_at','last_message_preview',
        'vendor_unread','customer_unread','vendor_last_seen','customer_last_seen',
        'vendor_typing_at','customer_typing_at','is_archived',
    ];

    protected $casts = [
        'last_message_at'    => 'datetime',
        'vendor_last_seen'   => 'datetime',
        'customer_last_seen' => 'datetime',
        'vendor_typing_at'   => 'datetime',
        'customer_typing_at' => 'datetime',
        'is_archived'        => 'boolean',
    ];

    public function vendor()   { return $this->belongsTo(Vendor::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function messages() { return $this->hasMany(ChatMessage::class)->orderBy('created_at'); }
    public function latestMessage() { return $this->hasOne(ChatMessage::class)->latestOfMany(); }
    public function attachments() { return $this->hasMany(ChatAttachment::class); }

    public static function findOrCreateForPair(int $vendorId, int $customerId): self
    {
        $key = "vendor_{$vendorId}_customer_{$customerId}";
        return static::firstOrCreate(
            ['room_key' => $key],
            ['vendor_id' => $vendorId, 'customer_id' => $customerId]
        );
    }

    public function isVendorOnline(): bool
    {
        return $this->vendor_last_seen && $this->vendor_last_seen->diffInMinutes(now()) < 5;
    }

    public function isCustomerOnline(): bool
    {
        return $this->customer_last_seen && $this->customer_last_seen->diffInMinutes(now()) < 5;
    }

    public function isVendorTyping(): bool
    {
        return $this->vendor_typing_at && $this->vendor_typing_at->diffInSeconds(now()) < 5;
    }

    public function isCustomerTyping(): bool
    {
        return $this->customer_typing_at && $this->customer_typing_at->diffInSeconds(now()) < 5;
    }

    public function scopeForVendor($q, int $vendorId) { return $q->where('vendor_id', $vendorId); }
    public function scopeForCustomer($q, int $customerId) { return $q->where('customer_id', $customerId); }
}








