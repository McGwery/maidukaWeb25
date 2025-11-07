<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Shop;
use App\Models\User;
use App\Traits\HasShopPolicy;

class ChatPolicy
{
    use HasShopPolicy;

    /**
     * Determine if the user can view any conversations.
     */
    public function viewAny(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'view_conversations') ||
               $this->hasPermission($user, $shop, 'use_chat');
    }

    /**
     * Determine if the user can view the conversation.
     */
    public function view(User $user, Conversation $conversation, Shop $shop): bool
    {
        // Must be part of the conversation
        $isParticipant = $conversation->shop_one_id === $shop->id ||
                         $conversation->shop_two_id === $shop->id;

        if (!$isParticipant) {
            return false;
        }

        return $this->hasPermission($user, $shop, 'view_conversations') ||
               $this->hasPermission($user, $shop, 'use_chat');
    }

    /**
     * Determine if the user can send messages.
     */
    public function sendMessage(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'send_messages') ||
               $this->hasPermission($user, $shop, 'use_chat');
    }

    /**
     * Determine if the user can delete messages.
     */
    public function deleteMessage(User $user, Message $message, Shop $shop): bool
    {
        // Can delete own messages
        if ($message->sender_shop_id === $shop->id) {
            return $this->hasPermission($user, $shop, 'use_chat');
        }

        return false;
    }

    /**
     * Determine if the user can archive conversations.
     */
    public function archiveConversation(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'use_chat');
    }

    /**
     * Determine if the user can block shops.
     */
    public function blockShop(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'manage_employees') ||
               $this->isOwner($user, $shop);
    }

    /**
     * Determine if the user can search shops.
     */
    public function searchShops(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'use_chat');
    }
}

