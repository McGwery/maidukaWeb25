# Chat API - Quick Reference

## 🚀 Base URL
```
/api/shops/{shopId}/chat
```

---

## 📋 Quick Links

### Conversations
```http
GET    /conversations                    # List all
GET    /conversations/{id}               # Get one
POST   /conversations/{id}/archive       # Archive/Unarchive
```

### Messages
```http
GET    /conversations/{id}/messages      # Get messages
POST   /messages                         # Send message
DELETE /conversations/{id}/messages/{id} # Delete
POST   /conversations/{id}/mark-read     # Mark read
```

### Typing
```http
POST   /conversations/{id}/typing/start  # Start typing
POST   /conversations/{id}/typing/stop   # Stop typing
GET    /conversations/{id}/typing        # Get status
```

### Reactions
```http
POST   /conversations/{id}/messages/{id}/react   # Add
DELETE /conversations/{id}/messages/{id}/react   # Remove
```

### Blocking
```http
POST   /block         # Block shop
POST   /unblock       # Unblock shop
GET    /blocked       # List blocked
```

### Utilities
```http
GET    /unread-count     # Unread count
GET    /statistics       # Chat stats
GET    /search-shops     # Find shops
```

---

## 💬 Send Message Examples

### Text
```json
{
  "receiverShopId": "uuid",
  "message": "Hello!",
  "messageType": "text"
}
```

### Image
```json
{
  "receiverShopId": "uuid",
  "messageType": "image",
  "attachments": ["https://..."]
}
```

### Product
```json
{
  "receiverShopId": "uuid",
  "messageType": "product",
  "productId": "uuid",
  "message": "Check this out"
}
```

### Location
```json
{
  "receiverShopId": "uuid",
  "messageType": "location",
  "locationLat": "-6.7924",
  "locationLng": "39.2083",
  "locationName": "Kariakoo"
}
```

### Reply
```json
{
  "receiverShopId": "uuid",
  "message": "Yes!",
  "messageType": "text",
  "replyToMessageId": "uuid"
}
```

---

## 📊 Response Format

### Success
```json
{
  "success": true,
  "code": 200,
  "message": "Optional",
  "data": {...}
}
```

### Error
```json
{
  "success": false,
  "code": 400,
  "message": "Error message",
  "errors": {...}
}
```

---

## 🎯 Message Types

- `text` - Text message
- `image` - Image(s)
- `video` - Video
- `audio` - Voice/audio
- `document` - Files
- `product` - Product share
- `location` - Location pin

---

## 👍 Reactions

```
👍 👎 ❤️ 😂 😮 😢 🎉 🔥
```

---

## ⚡ Quick Tips

1. **Pagination:** Use `before` for infinite scroll
2. **Typing:** Auto-expires in 5 seconds
3. **Read:** Mark as read when opening chat
4. **Block:** Prevents all communication
5. **Archive:** Hides from main list

---

## 📱 Implementation Flow

### Send Message
```
1. POST /messages
2. Update UI immediately
3. Poll or WebSocket for delivery status
```

### Receive Message
```
1. Poll /conversations (every 5-10s)
2. Check unread count
3. GET /messages when opening chat
4. POST /mark-read
```

### Typing Indicator
```
1. User types → POST /typing/start
2. Poll /typing every 2-3s
3. User stops → POST /typing/stop
```

---

## 🔧 Query Parameters

### Conversations
- `archived` - boolean
- `search` - string
- `perPage` - int (max: 100)

### Messages
- `perPage` - int (max: 100)
- `before` - uuid (for pagination)

### Search Shops
- `search` - string (required)
- `perPage` - int (max: 50)

---

## 📈 Statistics

```http
GET /statistics
```

Returns:
- Total conversations
- Active conversations
- Archived conversations
- Unread messages
- Messages sent/received
- Total messages

---

## 🚨 Common Errors

| Code | Message |
|------|---------|
| 403 | Shop blocked |
| 404 | Not found |
| 422 | Validation failed |
| 500 | Server error |

---

## 🎨 UI States

### Message Status
- **Sending** - Gray checkmark
- **Delivered** - Single gray checkmark
- **Read** - Double blue checkmarks

### Typing
- "John is typing..."
- "Multiple people are typing..."

### Unread Badge
- Show count on conversation
- Clear when marked as read

---

**Quick Start:**
1. Search shops to find conversation partner
2. Send first message (auto-creates conversation)
3. Start chatting!

---

**Version:** 1.0.0  
**Last Updated:** November 7, 2025

