<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Chat conversation channel
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    // Check if user belongs to any shop that is part of this conversation
    $conversation = Conversation::find($conversationId);

    if (!$conversation) {
        return false;
    }

    // Get user's shops
    $userShopIds = $user->shops()->pluck('shops.id')->toArray();

    // Check if user's shop is part of the conversation
    return in_array($conversation->shop_one_id, $userShopIds)
        || in_array($conversation->shop_two_id, $userShopIds);
});

