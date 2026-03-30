<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\ChatPredefinedReply;
use App\Services\ChatService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(private readonly ChatService $chat) {}

    private function vendor()
    {
        $u = auth()->user();
        return $u->isEmployee() ? $u->employee->vendor : $u->vendor;
    }

    // ── Inbox (room list) ─────────────────────────────────────────────────

    public function index(Request $request)
    {
        $vendor = $this->vendor();
        $rooms  = $this->chat->vendorRooms($vendor->id, $request->input('search'));
        $totalUnread = $this->chat->totalUnread($vendor->id, 'vendor');

        return view('vendor.chat.index', compact('rooms', 'totalUnread'));
    }

    // ── Open a conversation ────────────────────────────────────────────────

    public function show(ChatRoom $chatRoom)
    {
        $this->authorize($chatRoom);
        $chatRoom->load(['customer.user', 'messages.attachments', 'messages.sender']);

        // Mark as read
        $this->chat->markRoomRead($chatRoom, 'vendor');

        $vendor = $this->vendor();
        $rooms  = $this->chat->vendorRooms($vendor->id);

        // Quick-reply templates
        $quickReplies = ChatPredefinedReply::forVendor($vendor->id)->active()
            ->orderBy('sort_order')->get(['id','question']);

        return view('vendor.chat.show', compact('chatRoom', 'rooms', 'quickReplies'));
    }

    // ── Send message ──────────────────────────────────────────────────────

    public function send(Request $request, ChatRoom $chatRoom)
    {
        $this->authorize($chatRoom);

        $request->validate([
            'body'        => ['nullable', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240',
                'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,txt,zip'],
        ]);

        if (! $request->filled('body') && ! $request->hasFile('attachments')) {
            return response()->json(['success' => false, 'message' => 'Message is empty.'], 422);
        }

        $message = $this->chat->sendMessage(
            room:       $chatRoom,
            senderId:   auth()->id(),
            senderType: 'vendor',
            body:       $request->input('body'),
            files:      $request->file('attachments', []),
        );

        return response()->json([
            'success' => true,
            'message' => $this->chat->messagePayload($message),
        ]);
    }

    // ── AJAX Poll (every 3s) ──────────────────────────────────────────────

    public function poll(Request $request, ChatRoom $chatRoom)
    {
        $this->authorize($chatRoom);
        $this->chat->updatePresence($chatRoom, 'vendor');

        $data = $this->chat->pollMessages(
            $chatRoom,
            $request->input('since'),
            'vendor'
        );

        return response()->json($data);
    }

    // ── Typing indicator ──────────────────────────────────────────────────

    public function typing(Request $request, ChatRoom $chatRoom)
    {
        $this->authorize($chatRoom);
        $this->chat->updateTyping($chatRoom, 'vendor', (bool) $request->input('typing', false));
        return response()->json(['ok' => true]);
    }

    // ── Predefined replies management ─────────────────────────────────────

    public function predefined()
    {
        $vendor  = $this->vendor();
        $replies = ChatPredefinedReply::forVendor($vendor->id)->orderBy('sort_order')->get();

        $defaultTriggers = [
            'services' => 'What are your available services?',
            'pricing'  => 'How much do your services cost?',
            'schedule' => 'When are you available?',
            'warranty' => 'Do you offer warranty?',
            'location' => 'Where are you located?',
            'contact'  => 'How can I contact you?',
            'products' => 'What products do you carry?',
        ];

        return view('vendor.chat.predefined', compact('replies', 'defaultTriggers'));
    }

    public function storePredefined(Request $request)
    {
        $data = $request->validate([
            'trigger_key'    => ['required', 'string', 'max:100'],
            'question'       => ['required', 'string', 'max:300'],
            'reply_template' => ['nullable', 'string', 'max:3000'],
            'is_active'      => ['boolean'],
        ]);

        $vendor = $this->vendor();

        ChatPredefinedReply::updateOrCreate(
            ['vendor_id' => $vendor->id, 'trigger_key' => $data['trigger_key']],
            [
                'question'       => $data['question'],
                'reply_template' => $data['reply_template'] ?? null,
                'is_active'      => $request->boolean('is_active', true),
            ]
        );

        return back()->with('success', 'Quick reply saved.');
    }

    public function destroyPredefined(ChatPredefinedReply $reply)
    {
        if ($reply->vendor_id !== $this->vendor()->id) abort(403);
        $reply->delete();
        return back()->with('success', 'Quick reply removed.');
    }

    // ── Helper ────────────────────────────────────────────────────────────

    private function authorize(ChatRoom $room): void
    {
        if ($room->vendor_id !== $this->vendor()->id) abort(403);
    }
}
