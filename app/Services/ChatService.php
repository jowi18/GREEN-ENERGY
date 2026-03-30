<?php

namespace App\Services;

use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\ChatAttachment;
use App\Models\ChatPredefinedReply;
use App\Models\Vendor;
use App\Models\Customer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChatService
{
    // ──────────────────────────────────────────────────────────────────────
    // ROOM MANAGEMENT
    // ──────────────────────────────────────────────────────────────────────

    public function getOrCreateRoom(int $vendorId, int $customerId): ChatRoom
    {
        return ChatRoom::findOrCreateForPair($vendorId, $customerId);
    }

    /** All rooms for a vendor, ordered by last activity. */
    public function vendorRooms(int $vendorId, ?string $search = null): \Illuminate\Support\Collection
    {
        $query = ChatRoom::with(['customer.user', 'latestMessage'])
            ->forVendor($vendorId)
            ->where('is_archived', false)
            ->orderByDesc('last_message_at');

        if ($search) {
            $query->whereHas('customer', fn ($q) =>
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name',  'like', "%{$search}%")
            );
        }

        return $query->get();
    }

    /** All rooms for a customer, ordered by last activity. */
    public function customerRooms(int $customerId, ?string $search = null): \Illuminate\Support\Collection
    {
        $query = ChatRoom::with(['vendor', 'latestMessage'])
            ->forCustomer($customerId)
            ->where('is_archived', false)
            ->orderByDesc('last_message_at');

        if ($search) {
            $query->whereHas('vendor', fn ($q) =>
                $q->where('business_name', 'like', "%{$search}%")
            );
        }

        return $query->get();
    }

    // ──────────────────────────────────────────────────────────────────────
    // SEND MESSAGES
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Send a text message and optionally attach files.
     *
     * @param  UploadedFile[]  $files
     */
    public function sendMessage(
        ChatRoom $room,
        int      $senderId,
        string   $senderType,       // 'vendor' | 'customer'
        ?string  $body,
        array    $files = [],
        string   $messageType = 'text',
        bool     $isAutoReply = false,
        ?string  $autoTrigger = null
    ): ChatMessage {
        return DB::transaction(function () use (
            $room, $senderId, $senderType, $body, $files,
            $messageType, $isAutoReply, $autoTrigger
        ) {
            $message = ChatMessage::create([
                'chat_room_id'       => $room->id,
                'sender_id'          => $senderId,
                'sender_type'        => $senderType,
                'body'               => $body,
                'message_type'       => $messageType,
                'is_read'            => false,
                'is_auto_reply'      => $isAutoReply,
                'auto_reply_trigger' => $autoTrigger,
            ]);

            // Handle file attachments
            foreach ($files as $file) {
                $path = $file->store("chat/{$room->id}", 'public');
                $mime = $file->getMimeType();
                $type = str_starts_with($mime, 'image/') ? 'image' : 'document';

                ChatAttachment::create([
                    'chat_message_id' => $message->id,
                    'chat_room_id'    => $room->id,
                    'file_path'       => $path,
                    'file_name'       => $file->getClientOriginalName(),
                    'file_mime'       => $mime,
                    'file_size'       => $file->getSize(),
                    'attachment_type' => $type,
                ]);

                if ($messageType === 'text') {
                    $message->update(['message_type' => $type]);
                }
            }

            // Update room denormalized fields
            $preview = $body ? \Illuminate\Support\Str::limit($body, 60) : '📎 Attachment';
            $room->update([
                'last_message_at'      => now(),
                'last_message_preview' => $preview,
                // Increment the OTHER party's unread count
                $senderType === 'vendor' ? 'customer_unread' : 'vendor_unread'
                    => DB::raw(($senderType === 'vendor' ? 'customer_unread' : 'vendor_unread') . ' + 1'),
            ]);

            return $message->load('attachments');
        });
    }

    // ──────────────────────────────────────────────────────────────────────
    // AUTO-REPLY ENGINE
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Handle a predefined question selection from the customer.
     * Returns the auto-reply message or null if trigger is unknown.
     */
    public function handleAutoReply(
        ChatRoom $room,
        string   $triggerKey,
        int      $customerUserId
    ): ?ChatMessage {
        // First send the customer's question as a message
        $questionMsg = $this->sendPredefinedQuestion($room, $triggerKey, $customerUserId);

        if (! $questionMsg) return null;

        // Generate the auto-reply text
        $replyBody = $this->generateAutoReply($room->vendor, $triggerKey);

        // Post the system auto-reply
        $systemUser = \App\Models\User::where('user_type', 'admin')->first();
        $systemId   = $systemUser?->id ?? $customerUserId;

        return $this->sendMessage(
            room:        $room,
            senderId:    $systemId,
            senderType:  'vendor',
            body:        $replyBody,
            messageType: 'auto_reply',
            isAutoReply: true,
            autoTrigger: $triggerKey,
        );
    }

    private function sendPredefinedQuestion(ChatRoom $room, string $triggerKey, int $senderUserId): ?ChatMessage
    {
        // Get the question text from predefined replies
        $predefined = ChatPredefinedReply::forVendor($room->vendor_id)
            ->active()
            ->where('trigger_key', $triggerKey)
            ->first();

        $questionText = $predefined?->question ?? $this->defaultQuestion($triggerKey);

        if (! $questionText) return null;

        return $this->sendMessage(
            room:       $room,
            senderId:   $senderUserId,
            senderType: 'customer',
            body:       $questionText,
        );
    }

    private function generateAutoReply(Vendor $vendor, string $triggerKey): string
    {
        // Check if vendor has a custom reply template
        $predefined = ChatPredefinedReply::forVendor($vendor->id)
            ->active()
            ->where('trigger_key', $triggerKey)
            ->first();

        if ($predefined?->reply_template) {
            return $predefined->reply_template;
        }

        // Dynamic auto-generation based on vendor data
        return match($triggerKey) {
            'services' => $this->buildServicesReply($vendor),
            'pricing'  => $this->buildPricingReply($vendor),
            'schedule' => $this->buildScheduleReply($vendor),
            'warranty' => $this->buildWarrantyReply($vendor),
            'location' => $this->buildLocationReply($vendor),
            'contact'  => $this->buildContactReply($vendor),
            'products' => $this->buildProductsReply($vendor),
            default    => "Thank you for your inquiry! Our team will respond shortly.",
        };
    }

    private function buildServicesReply(Vendor $vendor): string
    {
        $services = \App\Models\VendorService::forVendor($vendor->id)->active()
            ->orderBy('sort_order')->get();

        if ($services->isEmpty()) {
            return "We offer solar installation, maintenance, and repair services. Please contact us for more details!";
        }

        $list = $services->map(fn ($s) =>
            "• **{$s->name}** — {$s->price_display}"
            . ($s->short_description ? "\n  {$s->short_description}" : "")
        )->join("\n");

        return "Here are our available services:\n\n{$list}\n\nWould you like to book any of these? Just let us know! 😊";
    }

    private function buildPricingReply(Vendor $vendor): string
    {
        $services = \App\Models\VendorService::forVendor($vendor->id)->active()
            ->whereNotNull('price')->orderBy('price')->get();

        if ($services->isEmpty()) {
            return "Our pricing varies depending on the scope of work. Please send us your details and we'll provide a custom quote!";
        }

        $list = $services->map(fn ($s) => "• {$s->name}: {$s->price_display}")->join("\n");
        return "Here's a summary of our service pricing:\n\n{$list}\n\nPrices may vary based on location and system size. Contact us for a free on-site assessment!";
    }

    private function buildScheduleReply(Vendor $vendor): string
    {
        return "We're available for service scheduling Monday to Saturday, 8 AM to 5 PM.\n\n"
             . "To book an appointment, please let us know:\n"
             . "• Your preferred date\n"
             . "• Your service address\n"
             . "• Type of service needed\n\n"
             . "We'll confirm your schedule within 24 hours! 📅";
    }

    private function buildWarrantyReply(Vendor $vendor): string
    {
        return "Yes, we offer warranty on all our services and products! 🛡️\n\n"
             . "• **Solar Panels**: 25-year performance warranty\n"
             . "• **Inverters**: Up to 10-year manufacturer warranty\n"
             . "• **Installation Labor**: 1-year workmanship warranty\n\n"
             . "For warranty claims, please provide your order number or service request number and we'll process it right away.";
    }

    private function buildLocationReply(Vendor $vendor): string
    {
        $loc = trim(($vendor->city ?? '') . ', ' . ($vendor->province_state ?? ''), ', ');
        return "We're based in **{$loc}**, Philippines.\n\n"
             . ($vendor->full_address ? "📍 Address: {$vendor->full_address}\n\n" : '')
             . "We serve customers within the area and nearby regions. Send us your address and we'll check if you're within our service coverage!";
    }

    private function buildContactReply(Vendor $vendor): string
    {
        $lines = ["Here's how you can reach us:"];
        if ($vendor->business_phone) $lines[] = "📞 Phone: {$vendor->business_phone}";
        if ($vendor->business_email) $lines[] = "✉️ Email: {$vendor->business_email}";
        if ($vendor->business_website) $lines[] = "🌐 Website: {$vendor->business_website}";
        $lines[] = "\nOr simply continue chatting here — we'll respond as soon as possible!";
        return implode("\n", $lines);
    }

    private function buildProductsReply(Vendor $vendor): string
    {
        $products = \App\Models\Product::forVendor($vendor->id)
            ->where('status', 'active')->where('product_type', 'physical')
            ->orderBy('price')->limit(8)->get();

        if ($products->isEmpty()) {
            return "We carry a range of solar panels, batteries, inverters, and accessories. Contact us for our latest product catalog!";
        }

        $list = $products->map(fn ($p) =>
            "• {$p->name} — ₱" . number_format($p->price, 0)
        )->join("\n");

        return "Here are some of our available products:\n\n{$list}\n\nAll products come with manufacturer warranty. Ask us about availability!";
    }

    private function defaultQuestion(string $triggerKey): ?string
    {
        return match($triggerKey) {
            'services' => 'What are your available services?',
            'pricing'  => 'How much do your services cost?',
            'schedule' => 'When are you available for installation?',
            'warranty' => 'Do you offer warranty on products and services?',
            'location' => 'Where are you located / what areas do you cover?',
            'contact'  => 'How can I contact you?',
            'products' => 'What solar products do you carry?',
            default    => null,
        };
    }

    // ──────────────────────────────────────────────────────────────────────
    // MARK READ
    // ──────────────────────────────────────────────────────────────────────

    public function markRoomRead(ChatRoom $room, string $readerType): void
    {
        // Mark all messages sent by the OTHER party as read
        $senderType = $readerType === 'vendor' ? 'customer' : 'vendor';

        ChatMessage::where('chat_room_id', $room->id)
            ->where('sender_type', $senderType)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        // Reset unread counter
        $room->update(["{$readerType}_unread" => 0]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // PRESENCE (polling-based)
    // ──────────────────────────────────────────────────────────────────────

    public function updatePresence(ChatRoom $room, string $partyType): void
    {
        $room->update(["{$partyType}_last_seen" => now()]);
    }

    public function updateTyping(ChatRoom $room, string $partyType, bool $isTyping): void
    {
        $room->update([
            "{$partyType}_typing_at" => $isTyping ? now() : null,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // POLL — fetch new messages since a timestamp
    // ──────────────────────────────────────────────────────────────────────

    public function pollMessages(ChatRoom $room, ?string $since, string $readerType): array
    {
        $query = ChatMessage::with('attachments')
            ->where('chat_room_id', $room->id);

        if ($since) {
            $query->where('created_at', '>', $since);
        }

        $messages = $query->orderBy('created_at')->get();

        // Mark new messages from the other party as read
        $messages->where('sender_type', '!=', $readerType)
                 ->where('is_read', false)
                 ->each(fn ($m) => $m->markRead());

        if ($messages->where('sender_type', '!=', $readerType)->where('is_read', true)->count()) {
            $room->update(["{$readerType}_unread" => 0]);
        }

        return [
            'messages'         => $messages->map(fn ($m) => $this->messagePayload($m)),
            'vendor_typing'    => $room->fresh()->isVendorTyping(),
            'customer_typing'  => $room->fresh()->isCustomerTyping(),
            'vendor_online'    => $room->fresh()->isVendorOnline(),
            'customer_online'  => $room->fresh()->isCustomerOnline(),
        ];
    }

    public function messagePayload(ChatMessage $m): array
    {
        return [
            'id'           => $m->id,
            'body'         => $m->body,
            'sender_type'  => $m->sender_type,
            'sender_name'  => $m->sender?->name ?? ($m->sender_type === 'system' ? 'Assistant' : 'Unknown'),
            'message_type' => $m->message_type,
            'is_auto_reply'=> $m->is_auto_reply,
            'is_read'      => $m->is_read,
            'created_at'   => $m->created_at->toISOString(),
            'time_label'   => $m->created_at->format('g:i A'),
            'date_label'   => $m->created_at->isToday()
                ? 'Today' : ($m->created_at->isYesterday() ? 'Yesterday' : $m->created_at->format('M d')),
            'attachments'  => $m->attachments->map(fn ($a) => [
                'id'       => $a->id,
                'url'      => $a->url,
                'name'     => $a->file_name,
                'type'     => $a->attachment_type,
                'size'     => $a->file_size_human,
            ])->toArray(),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // TOTAL UNREAD COUNT
    // ──────────────────────────────────────────────────────────────────────

    public function totalUnread(int $id, string $party): int
    {
        $col   = "{$party}_unread";
        $field = $party === 'vendor' ? 'vendor_id' : 'customer_id';
        return (int) ChatRoom::where($field, $id)->sum($col);
    }
}
