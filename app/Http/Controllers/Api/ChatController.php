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
use App\Traits\HasStandardResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ChatController extends Controller
{
    use HasStandardResponse;

    /**
     * Get all conversations for a shop
     */
    public function getConversations(Request $request, string $shop): JsonResponse
    {
        $this->initRequestTime();

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

        $transformedConversations = $conversations->setCollection(collect(ConversationResource::collection($conversations->getCollection())));

        return $this->paginatedResponse(
            'Conversations retrieved successfully.',
            $transformedConversations
        );
    }

    /**
     * Get a specific conversation
     */
    public function getConversation(string $shop, string $conversation): JsonResponse
    {
        $this->initRequestTime();

        $conv = Conversation::with(['shopOne', 'shopTwo', 'lastMessageUser'])
            ->forShop($shop)
            ->findOrFail($conversation);

        return $this->successResponse(
            'Conversation retrieved successfully.',
            new ConversationResource($conv)
        );
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

        $transformedMessages = $messages->setCollection(collect(MessageResource::collection($messages->getCollection())));

        return $this->paginatedResponse(
            'Messages retrieved successfully.',
            $transformedMessages,
            ['hasMore' => $messages->hasMorePages()]
        );
    }

    /**
     * Send a message
     */
    public function sendMessage(SendMessageRequest $request, string $shop): JsonResponse
    {
        $this->initRequestTime();

        $receiverShopId = $request->input('receiverShopId');

        // Check if blocked
        if (BlockedShop::isBlocked($shop, $receiverShopId) || BlockedShop::isBlocked($receiverShopId, $shop)) {
            return $this->errorResponse(
                'Cannot send message. One of the shops has blocked the other.',
                null,
                Response::HTTP_FORBIDDEN
            );
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

            return $this->successResponse(
                'Message sent successfully.',
                new MessageResource($message),
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(
                'Failed to send message.',
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Delete a message for current shop
     */
    public function deleteMessage(string $shop, string $conversation, string $message): JsonResponse
    {
        $this->initRequestTime();

        $msg = Message::where('conversation_id', $conversation)
            ->forShop($shop)
            ->findOrFail($message);

        $msg->deleteFor($shop);

        // Broadcast message deleted event
        broadcast(new MessageDeleted($msg->id, $conversation, $shop))->toOthers();

        return $this->successResponse('Message deleted successfully.');
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Request $request, string $shop, string $conversation): JsonResponse
    {
        $this->initRequestTime();

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

        return $this->successResponse(
            'Messages marked as read.',
            ['markedCount' => $updated]
        );
    }

    /**
     * Get unread messages count
     */
    public function getUnreadCount(string $shop): JsonResponse
    {
        $this->initRequestTime();

        $count = Message::where('receiver_shop_id', $shop)
            ->where('is_read', false)
            ->count();

        $conversationCounts = Message::where('receiver_shop_id', $shop)
            ->where('is_read', false)
            ->select('conversation_id', DB::raw('count(*) as count'))
            ->groupBy('conversation_id')
            ->get()
            ->mapWithKeys(fn($item) => [$item->conversation_id => $item->count]);

        return $this->successResponse(
            'Unread messages count retrieved successfully.',
            [
                'totalUnread' => $count,
                'byConversation' => $conversationCounts,
            ]
        );
    }

    /**
     * Archive/Unarchive conversation
     */
    public function toggleArchive(string $shop, string $conversation): JsonResponse
    {
        $this->initRequestTime();

        $conv = Conversation::forShop($shop)->findOrFail($conversation);

        $isArchived = $conv->isArchived($shop);
        $conv->markAsArchived($shop, !$isArchived);

        return $this->successResponse(
            $isArchived ? 'Conversation unarchived.' : 'Conversation archived.',
            ['isArchived' => !$isArchived]
        );
    }

    /**
     * Start/update typing indicator
     */
    public function startTyping(string $shop, string $conversation): JsonResponse
    {
        $this->initRequestTime();

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

        return $this->successResponse('Typing indicator updated.');
    }

    /**
     * Stop typing indicator
     */
    public function stopTyping(string $shop, string $conversation): JsonResponse
    {
        $this->initRequestTime();

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

        return $this->successResponse('Typing stopped.');
    }

    /**
     * Get who's typing in conversation
     */
    public function getTypingStatus(string $shop, string $conversation): JsonResponse
    {
        $this->initRequestTime();

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

        return $this->successResponse(
            'Typing status retrieved successfully.',
            [
                'isTyping' => $typing->isNotEmpty(),
                'typing' => $typing,
            ]
        );
    }

    /**
     * React to a message
     */
    public function reactToMessage(Request $request, string $shop, string $conversation, string $message): JsonResponse
    {
        $this->initRequestTime();

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

        return $this->successResponse(
            'Reaction added.',
            [
                'id' => $reaction->id,
                'reaction' => $reaction->reaction,
            ]
        );
    }

    /**
     * Remove reaction from message
     */
    public function removeReaction(string $shop, string $conversation, string $message): JsonResponse
    {
        $this->initRequestTime();

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

        return $this->successResponse('Reaction removed.');
    }

    /**
     * Block a shop
     */
    public function blockShop(Request $request, string $shop): JsonResponse
    {
        $this->initRequestTime();

        $request->validate([
            'blockedShopId' => 'required|uuid|exists:shops,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $blockedShopId = $request->input('blockedShopId');

        if ($shop === $blockedShopId) {
            return $this->errorResponse(
                'Cannot block your own shop.',
                null,
                Response::HTTP_BAD_REQUEST
            );
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

        return $this->successResponse(
            'Shop blocked successfully.',
            ['id' => $blocked->id],
            Response::HTTP_CREATED
        );
    }

    /**
     * Unblock a shop
     */
    public function unblockShop(Request $request, string $shop): JsonResponse
    {
        $this->initRequestTime();

        $request->validate([
            'blockedShopId' => 'required|uuid|exists:shops,id',
        ]);

        BlockedShop::where('shop_id', $shop)
            ->where('blocked_shop_id', $request->input('blockedShopId'))
            ->delete();

        return $this->successResponse('Shop unblocked successfully.');
    }

    /**
     * Get blocked shops
     */
    public function getBlockedShops(string $shop): JsonResponse
    {
        $this->initRequestTime();

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

        return $this->successResponse(
            'Blocked shops retrieved successfully.',
            [
                'blockedShops' => $blocked,
                'total' => $blocked->count(),
            ]
        );
    }

    /**
     * Search shops to start conversation
     */
    public function searchShops(Request $request, string $shop): JsonResponse
    {
        $this->initRequestTime();

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

        return $this->paginatedResponse(
            'Shops retrieved successfully.',
            $shops
        );
    }

    /**
     * Get chat statistics
     */
    public function getStatistics(string $shop): JsonResponse
    {
        $this->initRequestTime();

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

        return $this->successResponse(
            'Chat statistics retrieved successfully.',
            [
                'totalConversations' => $totalConversations,
                'archivedConversations' => $archivedConversations,
                'activeConversations' => $totalConversations - $archivedConversations,
                'unreadMessages' => $unreadMessages,
                'totalMessagesSent' => $totalMessagesSent,
                'totalMessagesReceived' => $totalMessagesReceived,
                'totalMessages' => $totalMessagesSent + $totalMessagesReceived,
            ]
        );
    }
}

