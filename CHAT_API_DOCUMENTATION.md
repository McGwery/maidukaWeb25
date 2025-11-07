# Chat & Messaging API - Complete Documentation

## 📱 Overview

The Chat & Messaging feature enables **shop-to-shop** real-time communication with support for:
- ✅ Text messages
- ✅ Image/Video/Audio attachments
- ✅ Product sharing
- ✅ Location sharing
- ✅ Message reactions (emoji)
- ✅ Reply to messages
- ✅ Typing indicators
- ✅ Read receipts
- ✅ Message deletion
- ✅ Conversation archiving
- ✅ Shop blocking

---

## 🔐 Authentication

All endpoints require authentication:
```
Authorization: Bearer {token}
```

---

## 📋 Table of Contents

1. [Conversations](#conversations)
2. [Messages](#messages)
3. [Typing Indicators](#typing-indicators)
4. [Reactions](#reactions)
5. [Blocking](#blocking)
6. [Utilities](#utilities)

---

## 1️⃣ Conversations

### Get All Conversations

**Endpoint:** `GET /api/shops/{shopId}/chat/conversations`

**Query Parameters:**
- `archived` - boolean (filter archived conversations)
- `search` - string (search by shop name)
- `perPage` - integer (default: 15, max: 100)

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "conversations": [
      {
        "id": "uuid",
        "otherShop": {
          "id": "uuid",
          "name": "Electronics Plus",
          "shopType": "retail",
          "logoUrl": "https://...",
          "location": "Dar es Salaam"
        },
        "lastMessage": "Hello, do you have iPhone 15?",
        "lastMessageAt": "2025-11-07T10:30:00Z",
        "lastMessageBy": {
          "id": "uuid",
          "name": "John Doe"
        },
        "unreadCount": 3,
        "isArchived": false,
        "isActive": true,
        "createdAt": "2025-11-01T08:00:00Z",
        "updatedAt": "2025-11-07T10:30:00Z"
      }
    ],
    "pagination": {
      "total": 25,
      "currentPage": 1,
      "lastPage": 2,
      "perPage": 15
    }
  }
}
```

---

### Get Specific Conversation

**Endpoint:** `GET /api/shops/{shopId}/chat/conversations/{conversationId}`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "id": "uuid",
    "otherShop": {
      "id": "uuid",
      "name": "Electronics Plus",
      "shopType": "retail",
      "logoUrl": "https://...",
      "location": "Dar es Salaam"
    },
    "lastMessage": "Hello, do you have iPhone 15?",
    "lastMessageAt": "2025-11-07T10:30:00Z",
    "unreadCount": 3,
    "isArchived": false,
    "isActive": true
  }
}
```

---

### Archive/Unarchive Conversation

**Endpoint:** `POST /api/shops/{shopId}/chat/conversations/{conversationId}/archive`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Conversation archived.",
  "data": {
    "isArchived": true
  }
}
```

---

## 2️⃣ Messages

### Get Messages in Conversation

**Endpoint:** `GET /api/shops/{shopId}/chat/conversations/{conversationId}/messages`

**Query Parameters:**
- `perPage` - integer (default: 50, max: 100)
- `before` - uuid (message ID for pagination)

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "messages": [
      {
        "id": "uuid",
        "conversationId": "uuid",
        "message": "Do you have iPhone 15 in stock?",
        "messageType": {
          "value": "text",
          "label": "Text Message",
          "icon": "💬"
        },
        "attachments": null,
        "sender": {
          "shopId": "uuid",
          "shopName": "Tech Store",
          "userId": "uuid",
          "userName": "John Doe"
        },
        "receiver": {
          "shopId": "uuid",
          "shopName": "Electronics Plus"
        },
        "isSender": false,
        "isRead": true,
        "readAt": "2025-11-07T10:31:00Z",
        "isDelivered": true,
        "deliveredAt": "2025-11-07T10:30:30Z",
        "reactions": [
          {
            "reaction": "👍",
            "count": 2,
            "users": [
              {"id": "uuid", "name": "Jane"},
              {"id": "uuid", "name": "Bob"}
            ]
          }
        ],
        "createdAt": "2025-11-07T10:30:00Z",
        "updatedAt": "2025-11-07T10:30:00Z"
      }
    ],
    "pagination": {
      "total": 45,
      "currentPage": 1,
      "lastPage": 1,
      "perPage": 50,
      "hasMore": false
    }
  }
}
```

---

### Send Message

**Endpoint:** `POST /api/shops/{shopId}/chat/messages`

**Request Body:**

#### Text Message
```json
{
  "receiverShopId": "uuid",
  "message": "Hello! Do you have this product?",
  "messageType": "text"
}
```

#### Image Message
```json
{
  "receiverShopId": "uuid",
  "message": "Check this out",
  "messageType": "image",
  "attachments": [
    "https://cdn.example.com/image1.jpg",
    "https://cdn.example.com/image2.jpg"
  ]
}
```

#### Product Share
```json
{
  "receiverShopId": "uuid",
  "message": "Are you interested in this?",
  "messageType": "product",
  "productId": "uuid"
}
```

#### Location Share
```json
{
  "receiverShopId": "uuid",
  "message": "We're located here",
  "messageType": "location",
  "locationLat": "-6.7924",
  "locationLng": "39.2083",
  "locationName": "Kariakoo Market"
}
```

#### Reply to Message
```json
{
  "receiverShopId": "uuid",
  "message": "Yes, we have it!",
  "messageType": "text",
  "replyToMessageId": "uuid"
}
```

**Response:**
```json
{
  "success": true,
  "code": 201,
  "message": "Message sent successfully.",
  "data": {
    "id": "uuid",
    "conversationId": "uuid",
    "message": "Hello! Do you have this product?",
    "messageType": {
      "value": "text",
      "label": "Text Message",
      "icon": "💬"
    },
    "sender": {
      "shopId": "uuid",
      "shopName": "My Shop",
      "userId": "uuid",
      "userName": "John Doe"
    },
    "isSender": true,
    "isRead": false,
    "isDelivered": false,
    "createdAt": "2025-11-07T11:00:00Z"
  }
}
```

---

### Delete Message

**Endpoint:** `DELETE /api/shops/{shopId}/chat/conversations/{conversationId}/messages/{messageId}`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Message deleted successfully."
}
```

**Note:** Messages are soft-deleted (only hidden for the deleting shop).

---

### Mark Messages as Read

**Endpoint:** `POST /api/shops/{shopId}/chat/conversations/{conversationId}/mark-read`

**Request Body (Optional):**
```json
{
  "messageIds": ["uuid1", "uuid2", "uuid3"]
}
```

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Messages marked as read.",
  "data": {
    "markedCount": 3
  }
}
```

**Note:** If `messageIds` is omitted, all unread messages in the conversation are marked as read.

---

## 3️⃣ Typing Indicators

### Start Typing

**Endpoint:** `POST /api/shops/{shopId}/chat/conversations/{conversationId}/typing/start`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Typing indicator updated."
}
```

**Note:** Typing indicator expires after 5 seconds automatically.

---

### Stop Typing

**Endpoint:** `POST /api/shops/{shopId}/chat/conversations/{conversationId}/typing/stop`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Typing stopped."
}
```

---

### Get Typing Status

**Endpoint:** `GET /api/shops/{shopId}/chat/conversations/{conversationId}/typing`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "isTyping": true,
    "typing": [
      {
        "shopId": "uuid",
        "shopName": "Electronics Plus",
        "userId": "uuid",
        "userName": "Jane Doe"
      }
    ]
  }
}
```

---

## 4️⃣ Reactions

### React to Message

**Endpoint:** `POST /api/shops/{shopId}/chat/conversations/{conversationId}/messages/{messageId}/react`

**Request Body:**
```json
{
  "reaction": "👍"
}
```

**Supported Reactions:**
- 👍 👎 ❤️ 😂 😮 😢 🎉 🔥

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Reaction added.",
  "data": {
    "id": "uuid",
    "reaction": "👍"
  }
}
```

---

### Remove Reaction

**Endpoint:** `DELETE /api/shops/{shopId}/chat/conversations/{conversationId}/messages/{messageId}/react`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Reaction removed."
}
```

---

## 5️⃣ Blocking

### Block Shop

**Endpoint:** `POST /api/shops/{shopId}/chat/block`

**Request Body:**
```json
{
  "blockedShopId": "uuid",
  "reason": "Spam messages"
}
```

**Response:**
```json
{
  "success": true,
  "code": 201,
  "message": "Shop blocked successfully.",
  "data": {
    "id": "uuid"
  }
}
```

---

### Unblock Shop

**Endpoint:** `POST /api/shops/{shopId}/chat/unblock`

**Request Body:**
```json
{
  "blockedShopId": "uuid"
}
```

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Shop unblocked successfully."
}
```

---

### Get Blocked Shops

**Endpoint:** `GET /api/shops/{shopId}/chat/blocked`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "blockedShops": [
      {
        "id": "uuid",
        "shop": {
          "id": "uuid",
          "name": "Spam Shop",
          "shopType": "retail",
          "logoUrl": "https://..."
        },
        "blockedBy": {
          "id": "uuid",
          "name": "John Doe"
        },
        "reason": "Spam messages",
        "blockedAt": "2025-11-05T14:00:00Z"
      }
    ],
    "total": 2
  }
}
```

---

## 6️⃣ Utilities

### Get Unread Count

**Endpoint:** `GET /api/shops/{shopId}/chat/unread-count`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "totalUnread": 15,
    "byConversation": {
      "conversation-uuid-1": 8,
      "conversation-uuid-2": 5,
      "conversation-uuid-3": 2
    }
  }
}
```

---

### Get Chat Statistics

**Endpoint:** `GET /api/shops/{shopId}/chat/statistics`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "totalConversations": 25,
    "archivedConversations": 5,
    "activeConversations": 20,
    "unreadMessages": 15,
    "totalMessagesSent": 450,
    "totalMessagesReceived": 380,
    "totalMessages": 830
  }
}
```

---

### Search Shops

**Endpoint:** `GET /api/shops/{shopId}/chat/search-shops`

**Query Parameters:**
- `search` - string (required, min: 2)
- `perPage` - integer (default: 20, max: 50)

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "shops": [
      {
        "id": "uuid",
        "name": "Electronics Plus",
        "shopType": "retail",
        "logoUrl": "https://...",
        "location": "Dar es Salaam"
      }
    ],
    "pagination": {
      "total": 10,
      "currentPage": 1,
      "lastPage": 1,
      "perPage": 20
    }
  }
}
```

---

## 📊 Message Types

| Type | Description | Required Fields |
|------|-------------|----------------|
| `text` | Plain text message | `message` |
| `image` | Image attachment(s) | `attachments` |
| `video` | Video attachment | `attachments` |
| `audio` | Audio/voice message | `attachments` |
| `document` | Document/file | `attachments` |
| `product` | Product share | `productId` |
| `location` | Location/map pin | `locationLat`, `locationLng` |

---

## 🔄 Real-Time Updates (Recommended)

For real-time chat experience, implement:

1. **Polling:** Poll typing status every 2-3 seconds
2. **WebSocket:** Use Laravel Echo + Pusher for real-time events
3. **Firebase Cloud Messaging:** For push notifications

---

## ✅ Best Practices

### 1. Message Delivery Flow
```
Send → Delivered (receiver's device) → Read (user opened chat)
```

### 2. Typing Indicator
- Call `/typing/start` when user starts typing
- Call `/typing/stop` when user stops or sends message
- Auto-expires after 5 seconds

### 3. Pagination
- Load 50 messages initially
- Use `before` parameter for infinite scroll
- Messages are returned newest first

### 4. Read Receipts
- Mark messages as read when conversation is opened
- Batch mark using conversation endpoint

### 5. Blocking
- Blocked shops cannot send or receive messages
- Existing conversations remain but are inaccessible

---

## 🚨 Error Responses

### Shop Blocked
```json
{
  "success": false,
  "code": 403,
  "message": "Cannot send message. One of the shops has blocked the other."
}
```

### Invalid Shop
```json
{
  "success": false,
  "code": 404,
  "message": "Shop not found."
}
```

### Validation Error
```json
{
  "success": false,
  "code": 422,
  "message": "Validation failed.",
  "errors": {
    "receiverShopId": ["Receiver shop is required."],
    "message": ["Message text is required for text messages."]
  }
}
```

---

## 📱 Mobile Implementation Tips

### UI Components
1. **Conversation List** - Show last message, timestamp, unread badge
2. **Chat Screen** - Messages grouped by date, typing indicator at bottom
3. **Message Bubble** - Different colors for sent/received
4. **Attachments** - Preview images/videos inline
5. **Reactions** - Long-press to react, show reaction count

### Performance
- Cache conversations locally
- Lazy load images/videos
- Debounce typing indicator (500ms)
- Batch read receipts

---

## 🎯 Use Cases

### 1. Product Inquiry
```
Shop A: "Do you have iPhone 15 in stock?"
Shop B: [shares product] "Yes! 5 units available"
Shop A: "Can you deliver to Mwanza?"
Shop B: [shares location] "Yes, we ship nationwide"
```

### 2. Business Partnership
```
Shop A: "Interested in wholesale partnership?"
Shop B: "Yes! What quantities?"
Shop A: "500 units per month"
Shop B: "Great! Let's discuss pricing"
```

### 3. Order Coordination
```
Shop A: "Order #1234 is ready for pickup"
Shop B: [reacts with 👍]
Shop B: "Picking up in 30 minutes"
Shop A: "Perfect! See you soon"
```

---

## 📞 Support & Troubleshooting

### Common Issues

1. **Messages not delivering**
   - Check if shop is blocked
   - Verify receiver shop exists
   - Check network connectivity

2. **Typing indicator not showing**
   - Ensure polling interval < 5 seconds
   - Check if indicator expired

3. **Read receipts not updating**
   - Call mark-read endpoint when opening chat
   - Check authorization

---

**Last Updated:** November 7, 2025  
**Version:** 1.0.0  
**Status:** ✅ Production Ready

