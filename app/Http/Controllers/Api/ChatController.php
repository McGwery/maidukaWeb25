<?php

namespace App\Http\Controllers\Api;

use App\Enums\MessageType;
use App\Events\MessageDeleted;
use App\Events\MessageRead;
use App\Events\MessageReactionAdded;
use App\Events\MessageReactionRemoved;
use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\BlockedShop;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\Shop;
use App\Models\TypingIndicator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Get all conversations for a shop
     */
    public function getConversations(Request $request, string $shop): JsonResponse
    {
        $request->validate([
            'archived' => 'nullable|boolean',
            'search' => 'nullable|string|max:255',
            'perPage' => 'nullable|integer|min:1|max:100',
        ]);

        $archived = $request->boolean('archived', false);
        $search = $request->input('search');
        $perPage = $request->input('perPage', 15);

        $query = Conversation::forShop($shop)
            ->with(['shopOne', 'shopTwo', 'lastMessageUser'])
            ->active()
            ->latest('last_message_at');

        // Filter by archived status
        if ($archived) {
            $query->where(function ($q) use ($shop) {
                $q->where(function ($subQ) use ($shop) {
                    $subQ->where('shop_one_id', $shop)
                        ->where('is_archived_by_shop_one', true);
                })->orWhere(function ($subQ) use ($shop) {
                    $subQ->where('shop_two_id', $shop)
                        ->where('is_archived_by_shop_two', true);
                });
            });
        } else {
            $query->notArchived($shop);
        }

        // Search by shop name
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('shopOne', function ($shopQ) use ($search) {
                    $shopQ->where('name', 'like', "%{$search}%");
                })->orWhereHas('shopTwo', function ($shopQ) use ($search) {
                    $shopQ->where('name', 'like', "%{$search}%");
                });
            });
        }

        $conversations = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'code' => 200,
            'data' => [
                'conversations' => ConversationResource::collection($conversations->items()),
                'pagination' => [
                    'total' => $conversations->total(),
                    'currentPage' => $conversations->currentPage(),
                    'lastPage' => $conversations->lastPage(),
                    'perPage' => $conversations->perPage(),
                ],
            ],
        ]);
    }

    /**
     * Get a specific conversation
     */
    public function getConversation(string $shop, string $conversation): JsonResponse
    {
        $conv = Conversation::with(['shopOne', 'shopTwo', 'lastMessageUser'])
            ->forShop($shop)
            ->findOrFail($conversation);

        return response()->json([
            'success' => true,
            'code' => 200,
            'data' => new ConversationResource($conv),
        ]);
    }

    /**
     * Get messages in a conversation
     */
    public function getMessages(Request $request, string $shop, string $conversation): JsonResponse
    {
        $request->validate([
            'perPage' => 'nullable|integer|min:1|max:100',
            'before' => 'nullable|uuid|exists:messages,id',
        ]);

        $conv = Conversation::forShop($shop)->findOrFail($conversation);
        $perPage = $request->input('perPage', 50);
        $beforeMessageId = $request->input('before');

        $query = Message::where('conversation_id', $conv->id)
            ->with(['senderShop', 'senderUser', 'receiverShop', 'product', 'replyTo.senderUser', 'reactions.user'])
            ->notDeleted($shop)
            ->latest();

        if ($beforeMessageId) {
            $beforeMessage = Message::findOrFail($beforeMessageId);
            $query->where('created_at', '<', $beforeMessage->created_at);
        }

        $messages = $query->paginate($perPage);

        // Mark unread messages as read
        Message::where('conversation_id', $conv->id)
            ->where('receiver_shop_id', $shop)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'code' => 200,
            'data' => [
                'messages' => MessageResource::collection($messages->items()),
                'pagination' => [
                    'total' => $messages->total(),
                    'currentPage' => $messages->currentPage(),
                    'lastPage' => $messages->lastPage(),
                    'perPage' => $messages->perPage(),
                    'hasMore' => $messages->hasMorePages(),
                ],
            ],
        ]);
    }

    /**
     * Send a message
     */
    public function sendMessage(SendMessageRequest $request, string $shop): JsonResponse
    {
        $receiverShopId = $request->input('receiverShopId');

        // Check if blocked
        if (BlockedShop::isBlocked($shop, $receiverShopId) || BlockedShop::isBlocked($receiverShopId, $shop)) {
            return response()->json([
                'success' => false,
                'code' => 403,
                'message' => 'Cannot send message. One of the shops has blocked the other.',
            ], 403);
        }

        // Validate receiver shop exists
        Shop::findOrFail($receiverShopId);

        DB::beginTransaction();
        try {
            // Find or create conversation
            $conversation = Conversation::findOrCreateConversation($shop, $receiverShopId);

            // Create message
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_shop_id' => $shop,
                'sender_user_id' => auth()->id(),
                'receiver_shop_id' => $receiverShopId,
                'message' => $request->input('message'),
                'message_type' => MessageType::from($request->input('messageType')),
                'attachments' => $request->input('attachments'),
                'product_id' => $request->input('productId'),
                'location_lat' => $request->input('locationLat'),
                'location_lng' => $request->input('locationLng'),
                'location_name' => $request->input('locationName'),
                'reply_to_message_id' => $request->input('replyToMessageId'),
            ]);

            // Update conversation's last message
            $conversation->updateLastMessage($message);

            // Broadcast message sent event
            broadcast(new MessageSent($message, $conversation->id))->toOthers();

            DB::commit();

            $message->load(['senderShop', 'senderUser', 'receiverShop', 'product', 'replyTo.senderUser', 'reactions']);

            return response()->json([
                'success' => true,
                'code' => 201,
                'message' => 'Message sent successfully.',
                'data' => new MessageResource($message),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'code' => 500,
                'message' => 'Failed to send message.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a message for current shop
     */
    public function deleteMessage(string $shop, string $conversation, string $message): JsonResponse
    {
        $msg = Message::where('conversation_id', $conversation)
            ->forShop($shop)
            ->findOrFail($message);

        $msg->deleteFor($shop);

        // Broadcast message deleted event
        broadcast(new MessageDeleted($msg->id, $conversation, $shop))->toOthers();

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Message deleted successfully.',
        ]);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Request $request, string $shop, string $conversation): JsonResponse
    {
        $request->validate([
            'messageIds' => 'nullable|array',
            'messageIds.*' => 'uuid|exists:messages,id',
        ]);

        $messageIds = $request->input('messageIds');

        $query = Message::where('conversation_id', $conversation)
            ->where('receiver_shop_id', $shop)
            ->where('is_read', false);

        if ($messageIds) {
            $query->whereIn('id', $messageIds);
        }

        $readTime = now();
        $updated = $query->update([
            'is_read' => true,
            'read_at' => $readTime,
        ]);

        // Broadcast message read event for each message
        if ($messageIds) {
            foreach ($messageIds as $msgId) {
                broadcast(new MessageRead($msgId, $conversation, $readTime->toIso8601String()))->toOthers();
            }
        }

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Messages marked as read.',
            'data' => [
                'markedCount' => $updated,
            ],
        ]);
    }

    /**
     * Get unread messages count
     */
    public function getUnreadCount(string $shop): JsonResponse
    {
        $count = Message::where('receiver_shop_id', $shop)
            ->where('is_read', false)
            ->count();

        $conversationCounts = Message::where('receiver_shop_id', $shop)
            ->where('is_read', false)
            ->select('conversation_id', DB::raw('count(*) as count'))
            ->groupBy('conversation_id')
            ->get()
            ->mapWithKeys(fn($item) => [$item->conversation_id => $item->count]);

        return response()->json([
            'success' => true,
            'code' => 200,
            'data' => [
                'totalUnread' => $count,
                'byConversation' => $conversationCounts,
            ],
        ]);
    }

    /**
     * Archive/Unarchive conversation
     */
    public function toggleArchive(string $shop, string $conversation): JsonResponse
    {
        $conv = Conversation::forShop($shop)->findOrFail($conversation);

        $isArchived = $conv->isArchived($shop);
        $conv->markAsArchived($shop, !$isArchived);

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => $isArchived ? 'Conversation unarchived.' : 'Conversation archived.',
            'data' => [
                'isArchived' => !$isArchived,
            ],
        ]);
    }

    /**
     * Start/update typing indicator
     */
    public function startTyping(string $shop, string $conversation): JsonResponse
    {
        $conv = Conversation::forShop($shop)->findOrFail($conversation);

        TypingIndicator::updateOrCreate(
            [
                'conversation_id' => $conv->id,
                'shop_id' => $shop,
                'user_id' => auth()->id(),
            ],
            [
                'started_at' => now(),
                'expires_at' => now()->addSeconds(5),
            ]
        );

        // Broadcast typing event
        $user = auth()->user();
        $shopModel = Shop::find($shop);
        broadcast(new UserTyping(
            $conv->id,
            $shop,
            $shopModel->name,
            $user->id,
            $user->name,
            true
        ))->toOthers();

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Typing indicator updated.',
        ]);
    }

    /**
     * Stop typing indicator
     */
    public function stopTyping(string $shop, string $conversation): JsonResponse
    {
        TypingIndicator::where('conversation_id', $conversation)
            ->where('shop_id', $shop)
            ->where('user_id', auth()->id())
            ->delete();

        // Broadcast typing stopped event
        $user = auth()->user();
        $shopModel = Shop::find($shop);
        broadcast(new UserTyping(
            $conversation,
            $shop,
            $shopModel->name,
            $user->id,
            $user->name,
            false
        ))->toOthers();

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Typing stopped.',
        ]);
    }

    /**
     * Get who's typing in conversation
     */
    public function getTypingStatus(string $shop, string $conversation): JsonResponse
    {
        $conv = Conversation::forShop($shop)->findOrFail($conversation);

        $otherShopId = $conv->getOtherShop($shop)?->id;

        $typing = TypingIndicator::where('conversation_id', $conv->id)
            ->where('shop_id', $otherShopId)
            ->active()
            ->with(['user', 'shop'])
            ->get()
            ->map(function ($indicator) {
                return [
                    'shopId' => $indicator->shop_id,
                    'shopName' => $indicator->shop->name,
                    'userId' => $indicator->user_id,
                    'userName' => $indicator->user->name,
                ];
            });

        return response()->json([
            'success' => true,
            'code' => 200,
            'data' => [
                'isTyping' => $typing->isNotEmpty(),
                'typing' => $typing,
            ],
        ]);
    }

    /**
     * React to a message
     */
    public function reactToMessage(Request $request, string $shop, string $conversation, string $message): JsonResponse
    {
        $request->validate([
            'reaction' => 'required|string|max:10',
        ]);

        $msg = Message::where('conversation_id', $conversation)
            ->forShop($shop)
            ->findOrFail($message);

        $reaction = MessageReaction::updateOrCreate(
            [
                'message_id' => $msg->id,
                'user_id' => auth()->id(),
            ],
            [
                'reaction' => $request->input('reaction'),
            ]
        );

        // Broadcast reaction added event
        $user = auth()->user();
        broadcast(new MessageReactionAdded(
            $msg->id,
            $conversation,
            $user->id,
            $user->name,
            $reaction->reaction
        ))->toOthers();

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Reaction added.',
            'data' => [
                'id' => $reaction->id,
                'reaction' => $reaction->reaction,
            ],
        ]);
    }

    /**
     * Remove reaction from message
     */
    public function removeReaction(string $shop, string $conversation, string $message): JsonResponse
    {
        $msg = Message::where('conversation_id', $conversation)
            ->forShop($shop)
            ->findOrFail($message);

        MessageReaction::where('message_id', $msg->id)
            ->where('user_id', auth()->id())
            ->delete();

        // Broadcast reaction removed event
        broadcast(new MessageReactionRemoved(
            $msg->id,
            $conversation,
            auth()->id()
        ))->toOthers();

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Reaction removed.',
        ]);
    }

    /**
     * Block a shop
     */
    public function blockShop(Request $request, string $shop): JsonResponse
    {
        $request->validate([
            'blockedShopId' => 'required|uuid|exists:shops,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $blockedShopId = $request->input('blockedShopId');

        if ($shop === $blockedShopId) {
            return response()->json([
                'success' => false,
                'code' => 400,
                'message' => 'Cannot block your own shop.',
            ], 400);
        }

        $blocked = BlockedShop::firstOrCreate(
            [
                'shop_id' => $shop,
                'blocked_shop_id' => $blockedShopId,
            ],
            [
                'blocked_by' => auth()->id(),
                'reason' => $request->input('reason'),
            ]
        );

        return response()->json([
            'success' => true,
            'code' => 201,
            'message' => 'Shop blocked successfully.',
            'data' => [
                'id' => $blocked->id,
            ],
        ]);
    }

    /**
     * Unblock a shop
     */
    public function unblockShop(Request $request, string $shop): JsonResponse
    {
        $request->validate([
            'blockedShopId' => 'required|uuid|exists:shops,id',
        ]);

        BlockedShop::where('shop_id', $shop)
            ->where('blocked_shop_id', $request->input('blockedShopId'))
            ->delete();

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Shop unblocked successfully.',
        ]);
    }

    /**
     * Get blocked shops
     */
    public function getBlockedShops(string $shop): JsonResponse
    {
        $blocked = BlockedShop::where('shop_id', $shop)
            ->with(['blockedShop', 'blockedByUser'])
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'shop' => [
                        'id' => $item->blockedShop->id,
                        'name' => $item->blockedShop->name,
                        'shopType' => $item->blockedShop->shop_type,
                        'logoUrl' => $item->blockedShop->logo_url,
                    ],
                    'blockedBy' => [
                        'id' => $item->blockedByUser->id,
                        'name' => $item->blockedByUser->name,
                    ],
                    'reason' => $item->reason,
                    'blockedAt' => $item->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'code' => 200,
            'data' => [
                'blockedShops' => $blocked,
                'total' => $blocked->count(),
            ],
        ]);
    }

    /**
     * Search shops to start conversation
     */
    public function searchShops(Request $request, string $shop): JsonResponse
    {
        $request->validate([
            'search' => 'required|string|min:2|max:255',
            'perPage' => 'nullable|integer|min:1|max:50',
        ]);

        $search = $request->input('search');
        $perPage = $request->input('perPage', 20);

        // Get blocked shop IDs
        $blockedShopIds = BlockedShop::where('shop_id', $shop)
            ->pluck('blocked_shop_id')
            ->toArray();

        $shops = Shop::where('id', '!=', $shop)
            ->whereNotIn('id', $blockedShopIds)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            })
            ->select('id', 'name', 'shop_type', 'logo_url', 'location')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'code' => 200,
            'data' => [
                'shops' => $shops->items(),
                'pagination' => [
                    'total' => $shops->total(),
                    'currentPage' => $shops->currentPage(),
                    'lastPage' => $shops->lastPage(),
                    'perPage' => $shops->perPage(),
                ],
            ],
        ]);
    }

    /**
     * Get chat statistics
     */
    public function getStatistics(string $shop): JsonResponse
    {
        $totalConversations = Conversation::forShop($shop)->active()->count();
        $archivedConversations = Conversation::forShop($shop)
            ->where(function ($q) use ($shop) {
                $q->where(function ($subQ) use ($shop) {
                    $subQ->where('shop_one_id', $shop)
                        ->where('is_archived_by_shop_one', true);
                })->orWhere(function ($subQ) use ($shop) {
                    $subQ->where('shop_two_id', $shop)
                        ->where('is_archived_by_shop_two', true);
                });
            })
            ->count();

        $unreadMessages = Message::where('receiver_shop_id', $shop)
            ->where('is_read', false)
            ->count();

        $totalMessagesSent = Message::where('sender_shop_id', $shop)->count();
        $totalMessagesReceived = Message::where('receiver_shop_id', $shop)->count();

        return response()->json([
            'success' => true,
            'code' => 200,
            'data' => [
                'totalConversations' => $totalConversations,
                'archivedConversations' => $archivedConversations,
                'activeConversations' => $totalConversations - $archivedConversations,
                'unreadMessages' => $unreadMessages,
                'totalMessagesSent' => $totalMessagesSent,
                'totalMessagesReceived' => $totalMessagesReceived,
                'totalMessages' => $totalMessagesSent + $totalMessagesReceived,
            ],
        ]);
    }
}

