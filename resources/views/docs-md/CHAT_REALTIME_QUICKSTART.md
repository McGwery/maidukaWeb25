# Chat Real-Time Setup - Quick Start Guide

## 🚀 Quick Setup (5 Minutes)

### 1. Environment Configuration ✅
Already configured! Check your `.env`:
```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### 2. Start Reverb Server 🚀
```bash
php artisan reverb:start
```

You should see:
```
Reverb server started on 127.0.0.1:8080
```

### 3. Test Connection 🧪

**Option A: Browser Console (JavaScript)**
```javascript
const echo = new Echo({
    broadcaster: 'reverb',
    key: 'your-app-key',
    wsHost: 'localhost',
    wsPort: 8080,
    forceTLS: false,
    authEndpoint: 'http://your-api/broadcasting/auth',
    auth: {
        headers: {
            Authorization: 'Bearer YOUR_TOKEN'
        }
    }
});

echo.private('conversation.YOUR_CONVERSATION_ID')
    .listen('.message.sent', (e) => {
        console.log('New message:', e);
    });
```

**Option B: Laravel Tinker**
```bash
php artisan tinker
```
```php
use App\Events\MessageSent;
use App\Models\Message;

$message = Message::first();
broadcast(new MessageSent($message, $message->conversation_id));
```

---

## 📱 Mobile Integration (Kotlin)

### Step 1: Add Dependencies
```gradle
dependencies {
    implementation 'com.pusher:pusher-java-client:2.4.2'
}
```

### Step 2: Connect to WebSocket
```kotlin
val options = PusherOptions().apply {
    setHost("your-api.com")
    setWsPort(8080)
    setWssPort(443)
    isEncrypted = false // true for production
}

val pusher = Pusher("your-app-key", options)
pusher.connect()
```

### Step 3: Subscribe to Conversation
```kotlin
val channel = pusher.subscribePrivate("private-conversation.uuid")

channel.bind("message.sent") { event ->
    val message = parseMessage(event.data)
    updateUI(message)
}

channel.bind("user.typing") { event ->
    val typing = parseTyping(event.data)
    showTypingIndicator(typing.userName)
}
```

---

## 🌐 Web Integration (React/Vue)

### Step 1: Install Echo
```bash
npm install laravel-echo pusher-js
```

### Step 2: Initialize
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: 'your-app-key',
    wsHost: 'localhost',
    wsPort: 8080,
    forceTLS: false,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            Authorization: `Bearer ${token}`
        }
    }
});
```

### Step 3: Listen to Events
```javascript
Echo.private(`conversation.${conversationId}`)
    .listen('.message.sent', (e) => {
        addMessage(e.message);
    })
    .listen('.user.typing', (e) => {
        if (e.isTyping) {
            showTyping(e.userName);
        }
    });
```

---

## 🎯 Real-Time Events Reference

| Event | When Triggered | Payload |
|-------|---------------|---------|
| `message.sent` | New message sent | Full message object |
| `message.read` | Message marked as read | messageId, readAt |
| `user.typing` | User starts/stops typing | shopId, userName, isTyping |
| `message.deleted` | Message deleted | messageId, deletedBy |
| `message.reaction.added` | Reaction added | messageId, reaction, userId |
| `message.reaction.removed` | Reaction removed | messageId, userId |

---

## 🔐 Channel Authorization

**Endpoint:** `POST /broadcasting/auth`

**Headers:**
```
Authorization: Bearer {token}
```

**Body:**
```json
{
  "channel_name": "private-conversation.uuid",
  "socket_id": "12345.67890"
}
```

**Success Response:**
```json
{
  "auth": "app-key:signature"
}
```

---

## ✅ Verification Checklist

- [ ] Reverb server running (`php artisan reverb:start`)
- [ ] `.env` configured with Reverb credentials
- [ ] Broadcasting auth endpoint working
- [ ] WebSocket connection successful
- [ ] Can subscribe to channels
- [ ] Can receive events
- [ ] Can send messages with real-time updates

---

## 🚨 Common Issues & Fixes

### Issue: "Connection refused"
**Fix:** Start Reverb server
```bash
php artisan reverb:start
```

### Issue: "Unauthorized"
**Fix:** Check auth token and channel authorization in `routes/channels.php`

### Issue: "Events not received"
**Fix:** 
1. Check `BROADCAST_CONNECTION=reverb` in `.env`
2. Verify event is dispatched: `broadcast(new MessageSent(...))`
3. Check Laravel logs

### Issue: "CORS error"
**Fix:** Add CORS headers for `/broadcasting/auth` in `cors.php`

---

## 📊 Testing Checklist

### Backend
- [ ] Send message → Event broadcasts
- [ ] Mark as read → Read event broadcasts
- [ ] Start typing → Typing event broadcasts
- [ ] Add reaction → Reaction event broadcasts

### Frontend
- [ ] Connect to WebSocket
- [ ] Receive new messages
- [ ] See typing indicators
- [ ] See read receipts
- [ ] See reactions in real-time

---

## 🎓 Next Steps

1. **Production Setup:**
   - Use WSS (secure WebSocket)
   - Setup Supervisor for Reverb
   - Configure Nginx proxy
   - Use Redis for better performance

2. **Mobile Optimization:**
   - Handle reconnection
   - Cache messages locally
   - Background sync
   - Push notifications integration

3. **Features to Add:**
   - Online/offline status (Presence channels)
   - Delivery confirmations
   - Message editing notifications
   - File upload progress

---

## 📚 Documentation Links

- **Full API Docs:** `CHAT_API_DOCUMENTATION.md`
- **Broadcasting Guide:** `CHAT_REALTIME_BROADCASTING.md`
- **Quick Reference:** `CHAT_QUICK_REFERENCE.md`

---

**Ready to go! 🎉**

Start Reverb and begin chatting in real-time!

```bash
php artisan reverb:start
```

