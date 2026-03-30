<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\ChatPredefinedReply;
use App\Models\Vendor;
use App\Services\ChatService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(private readonly ChatService $chat) {}

    private function customer() { return auth()->user()->customer; }

    // ── Inbox ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $customer = $this->customer();
        $rooms    = $this->chat->customerRooms($customer->id, $request->input('search'));
        $totalUnread = $this->chat->totalUnread($customer->id, 'customer');

        return view('customer.chat.index', compact('rooms', 'totalUnread'));
    }

    // ── Open / start a conversation ───────────────────────────────────────

    public function show(Request $request, Vendor $vendor)
    {
        abort_unless($vendor->isActive(), 404);

        $customer = $this->customer();
        $room     = $this->chat->getOrCreateRoom($vendor->id, $customer->id);

        $room->load(['messages.attachments', 'messages.sender', 'vendor']);

        // Mark as read
        $this->chat->markRoomRead($room, 'customer');

        // Sidebar rooms
        $rooms = $this->chat->customerRooms($customer->id);

        // Predefined quick questions for this vendor
        $predefined = ChatPredefinedReply::forVendor($vendor->id)->active()
            ->orderBy('sort_order')
            ->get(['id', 'trigger_key', 'question']);

        // Default questions if vendor hasn't customised
        if ($predefined->isEmpty()) {
            $predefined = collect([
                ['id' => null, 'trigger_key' => 'services',  'question' => 'What are your available services?'],
                ['id' => null, 'trigger_key' => 'pricing',   'question' => 'How much do your services cost?'],
                ['id' => null, 'trigger_key' => 'schedule',  'question' => 'When are you available?'],
                ['id' => null, 'trigger_key' => 'warranty',  'question' => 'Do you offer warranty?'],
                ['id' => null, 'trigger_key' => 'products',  'question' => 'What products do you carry?'],
                ['id' => null, 'trigger_key' => 'location',  'question' => 'Where are you located?'],
            ]);
        }

        return view('customer.chat.show', compact('room', 'vendor', 'rooms', 'predefined'));
    }

    // ── Send message ──────────────────────────────────────────────────────

    public function send(Request $request, Vendor $vendor)
    {
        $customer = $this->customer();
        $room     = $this->chat->getOrCreateRoom($vendor->id, $customer->id);

        $request->validate([
            'body'          => ['nullable', 'string', 'max:5000'],
            'attachments'   => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240',
                'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,txt,zip'],
        ]);

        if (! $request->filled('body') && ! $request->hasFile('attachments')) {
            return response()->json(['success' => false, 'message' => 'Message is empty.'], 422);
        }

        $message = $this->chat->sendMessage(
            room:       $room,
            senderId:   auth()->id(),
            senderType: 'customer',
            body:       $request->input('body'),
            files:      $request->file('attachments', []),
        );

        return response()->json([
            'success' => true,
            'message' => $this->chat->messagePayload($message),
        ]);
    }

    // ── Auto-reply trigger (predefined question) ──────────────────────────

    public function autoReply(Request $request, Vendor $vendor)
    {
        $request->validate(['trigger_key' => ['required', 'string']]);

        $customer = $this->customer();
        $room     = $this->chat->getOrCreateRoom($vendor->id, $customer->id);

        $reply = $this->chat->handleAutoReply($room, $request->trigger_key, auth()->id());

        if (! $reply) {
            return response()->json(['success' => false, 'message' => 'Unknown trigger.'], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $this->chat->messagePayload($reply),
        ]);
    }

    // ── Poll ──────────────────────────────────────────────────────────────

    public function poll(Request $request, Vendor $vendor)
    {
        $customer = $this->customer();
        $room     = $this->chat->getOrCreateRoom($vendor->id, $customer->id);

        $this->chat->updatePresence($room, 'customer');

        $data = $this->chat->pollMessages($room, $request->input('since'), 'customer');

        return response()->json($data);
    }

    // ── Typing ────────────────────────────────────────────────────────────

    public function typing(Request $request, Vendor $vendor)
    {
        $customer = $this->customer();
        $room     = $this->chat->getOrCreateRoom($vendor->id, $customer->id);
        $this->chat->updateTyping($room, 'customer', (bool) $request->input('typing', false));
        return response()->json(['ok' => true]);
    }
}
