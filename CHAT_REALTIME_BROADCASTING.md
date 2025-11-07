# Chat Real-Time Broadcasting with Laravel Reverb

## 🚀 Overview

The chat feature uses **Laravel Reverb** for real-time WebSocket communication, providing instant updates for:
- ✅ New messages
- ✅ Message read receipts
- ✅ Typing indicators
- ✅ Message reactions
- ✅ Message deletions

---

## 📡 WebSocket Events

### 1. Message Sent
**Event:** `message.sent`  
**Channel:** `private-conversation.{conversationId}`

**Payload:**
```json
{
  "message": {
    "id": "uuid",
    "conversationId": "uuid",
    "message": "Hello!",
    "messageType": {
      "value": "text",
      "label": "Text Message",
      "icon": "💬"
    },
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
    "isRead": false,
    "isDelivered": false,
    "createdAt": "2025-11-07T11:00:00Z"
  },
  "conversationId": "uuid"
}
```

---

### 2. Message Read
**Event:** `message.read`  
**Channel:** `private-conversation.{conversationId}`

**Payload:**
```json
{
  "messageId": "uuid",
  "conversationId": "uuid",
  "readAt": "2025-11-07T11:05:00Z"
}
```

---

### 3. User Typing
**Event:** `user.typing`  
**Channel:** `private-conversation.{conversationId}`

**Payload:**
```json
{
  "conversationId": "uuid",
  "shopId": "uuid",
  "shopName": "Electronics Plus",
  "userId": "uuid",
  "userName": "Jane Doe",
  "isTyping": true
}
```

**Note:** `isTyping: false` when user stops typing.

---

### 4. Message Deleted
**Event:** `message.deleted`  
**Channel:** `private-conversation.{conversationId}`

**Payload:**
```json
{
  "messageId": "uuid",
  "conversationId": "uuid",
  "deletedBy": "shop-uuid"
}
```

---

### 5. Reaction Added
**Event:** `message.reaction.added`  
**Channel:** `private-conversation.{conversationId}`

**Payload:**
```json
{
  "messageId": "uuid",
  "conversationId": "uuid",
  "userId": "uuid",
  "userName": "John Doe",
  "reaction": "👍"
}
```

---

### 6. Reaction Removed
**Event:** `message.reaction.removed`  
**Channel:** `private-conversation.{conversationId}`

**Payload:**
```json
{
  "messageId": "uuid",
  "conversationId": "uuid",
  "userId": "uuid"
}
```

---

## 🔐 Channel Authorization

Conversations use **private channels** that require authorization.

**Channel Format:** `private-conversation.{conversationId}`

**Authorization Logic:**
- User must belong to a shop that is part of the conversation
- Either `shop_one_id` or `shop_two_id` must match user's shop

**Authorization Endpoint:**
```
POST /broadcasting/auth
```

**Headers:**
```
Authorization: Bearer {token}
```

**Body:**
```json
{
  "channel_name": "private-conversation.uuid",
  "socket_id": "socket-id-from-client"
}
```

---

## 📱 Mobile Client Implementation (Kotlin/Android)

### 1. Install Laravel Echo for Android

**build.gradle:**
```gradle
dependencies {
    implementation 'io.socket:socket.io-client:2.1.0'
    implementation 'com.pusher:pusher-java-client:2.4.2'
}
```

### 2. Initialize Echo Client

```kotlin
import com.pusher.client.Pusher
import com.pusher.client.PusherOptions
import com.pusher.client.channel.PrivateChannel
import com.pusher.client.connection.ConnectionEventListener
import com.pusher.client.connection.ConnectionState
import com.pusher.client.connection.ConnectionStateChange

class ChatWebSocketManager(
    private val authToken: String,
    private val reverbHost: String = "your-reverb-host.com",
    private val reverbPort: Int = 443,
    private val appKey: String = "your-app-key"
) {
    
    private var pusher: Pusher? = null
    private val subscribedChannels = mutableMapOf<String, PrivateChannel>()
    
    fun connect() {
        val options = PusherOptions().apply {
            setCluster("mt1") // Not used with Reverb, but required
            setHost(reverbHost)
            setWsPort(reverbPort)
            setWssPort(reverbPort)
            isEncrypted = true
            setUseTLS(true)
            
            // Authorization
            authorizer = { channelName, socketId ->
                val authUrl = "https://your-api.com/broadcasting/auth"
                val params = mapOf(
                    "channel_name" to channelName,
                    "socket_id" to socketId
                )
                
                // Make HTTP request to your Laravel backend
                val response = makeAuthRequest(authUrl, params, authToken)
                response
            }
        }
        
        pusher = Pusher(appKey, options).apply {
            connect(object : ConnectionEventListener {
                override fun onConnectionStateChange(change: ConnectionStateChange) {
                    Log.d("WebSocket", "State changed from ${change.previousState} to ${change.currentState}")
                }
                
                override fun onError(message: String, code: String?, e: Exception?) {
                    Log.e("WebSocket", "Error: $message", e)
                }
            }, ConnectionState.ALL)
        }
    }
    
    fun subscribeToConversation(
        conversationId: String,
        callbacks: ConversationCallbacks
    ) {
        val channelName = "private-conversation.$conversationId"
        
        val channel = pusher?.subscribePrivate(channelName) as? PrivateChannel
        
        channel?.apply {
            // Message sent
            bind("message.sent") { event ->
                val data = parseMessageSentEvent(event.data)
                callbacks.onMessageReceived(data)
            }
            
            // Message read
            bind("message.read") { event ->
                val data = parseMessageReadEvent(event.data)
                callbacks.onMessageRead(data)
            }
            
            // User typing
            bind("user.typing") { event ->
                val data = parseTypingEvent(event.data)
                callbacks.onTypingStatusChanged(data)
            }
            
            // Message deleted
            bind("message.deleted") { event ->
                val data = parseMessageDeletedEvent(event.data)
                callbacks.onMessageDeleted(data)
            }
            
            // Reaction added
            bind("message.reaction.added") { event ->
                val data = parseReactionAddedEvent(event.data)
                callbacks.onReactionAdded(data)
            }
            
            // Reaction removed
            bind("message.reaction.removed") { event ->
                val data = parseReactionRemovedEvent(event.data)
                callbacks.onReactionRemoved(data)
            }
        }
        
        subscribedChannels[conversationId] = channel!!
    }
    
    fun unsubscribeFromConversation(conversationId: String) {
        val channelName = "private-conversation.$conversationId"
        pusher?.unsubscribe(channelName)
        subscribedChannels.remove(conversationId)
    }
    
    fun disconnect() {
        subscribedChannels.clear()
        pusher?.disconnect()
    }
    
    private fun makeAuthRequest(url: String, params: Map<String, String>, token: String): String {
        // Implement HTTP POST request with authorization header
        // Return JSON response: {"auth": "..."}
    }
}

interface ConversationCallbacks {
    fun onMessageReceived(message: MessageData)
    fun onMessageRead(data: MessageReadData)
    fun onTypingStatusChanged(data: TypingData)
    fun onMessageDeleted(data: MessageDeletedData)
    fun onReactionAdded(data: ReactionData)
    fun onReactionRemoved(data: ReactionData)
}
```

### 3. Usage in Chat Screen

```kotlin
class ChatActivity : AppCompatActivity() {
    
    private lateinit var wsManager: ChatWebSocketManager
    private lateinit var conversationId: String
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        
        conversationId = intent.getStringExtra("conversationId") ?: return
        
        // Initialize WebSocket
        wsManager = ChatWebSocketManager(
            authToken = getAuthToken(),
            reverbHost = BuildConfig.REVERB_HOST,
            appKey = BuildConfig.REVERB_APP_KEY
        )
        
        wsManager.connect()
        wsManager.subscribeToConversation(conversationId, object : ConversationCallbacks {
            override fun onMessageReceived(message: MessageData) {
                runOnUiThread {
                    addMessageToUI(message)
                    playNotificationSound()
                }
            }
            
            override fun onMessageRead(data: MessageReadData) {
                runOnUiThread {
                    updateMessageReadStatus(data.messageId, data.readAt)
                }
            }
            
            override fun onTypingStatusChanged(data: TypingData) {
                runOnUiThread {
                    if (data.isTyping) {
                        showTypingIndicator(data.userName)
                    } else {
                        hideTypingIndicator()
                    }
                }
            }
            
            override fun onMessageDeleted(data: MessageDeletedData) {
                runOnUiThread {
                    removeMessageFromUI(data.messageId)
                }
            }
            
            override fun onReactionAdded(data: ReactionData) {
                runOnUiThread {
                    addReactionToMessage(data.messageId, data.reaction, data.userName)
                }
            }
            
            override fun onReactionRemoved(data: ReactionData) {
                runOnUiThread {
                    removeReactionFromMessage(data.messageId, data.userId)
                }
            }
        })
    }
    
    override fun onDestroy() {
        wsManager.unsubscribeFromConversation(conversationId)
        wsManager.disconnect()
        super.onDestroy()
    }
}
```

---

## 🌐 Web Client Implementation (JavaScript)

### 1. Install Laravel Echo

```bash
npm install --save laravel-echo pusher-js
```

### 2. Initialize Echo

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            Authorization: `Bearer ${authToken}`
        }
    }
});
```

### 3. Subscribe to Conversation

```javascript
const conversationId = 'uuid';

Echo.private(`conversation.${conversationId}`)
    .listen('.message.sent', (e) => {
        console.log('New message:', e.message);
        addMessageToChat(e.message);
        playNotificationSound();
    })
    .listen('.message.read', (e) => {
        console.log('Message read:', e);
        updateMessageReadStatus(e.messageId, e.readAt);
    })
    .listen('.user.typing', (e) => {
        console.log('User typing:', e);
        if (e.isTyping) {
            showTypingIndicator(e.userName);
        } else {
            hideTypingIndicator();
        }
    })
    .listen('.message.deleted', (e) => {
        console.log('Message deleted:', e);
        removeMessageFromChat(e.messageId);
    })
    .listen('.message.reaction.added', (e) => {
        console.log('Reaction added:', e);
        addReactionToMessage(e.messageId, e.reaction, e.userName);
    })
    .listen('.message.reaction.removed', (e) => {
        console.log('Reaction removed:', e);
        removeReactionFromMessage(e.messageId, e.userId);
    });
```

### 4. Unsubscribe

```javascript
Echo.leave(`conversation.${conversationId}`);
```

---

## ⚙️ Server Configuration

### 1. Start Reverb Server

```bash
php artisan reverb:start
```

**Development:**
```bash
php artisan reverb:start --debug
```

**Production (with Supervisor):**

Create `/etc/supervisor/conf.d/reverb.conf`:
```ini
[program:reverb]
command=php /path/to/artisan reverb:start
directory=/path/to/your-app
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/your-app/storage/logs/reverb.log
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb
```

### 2. Environment Variables

**.env:**
```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

**Production:**
```env
REVERB_HOST=your-domain.com
REVERB_PORT=443
REVERB_SCHEME=https
```

### 3. Queue Configuration

For better performance, run the queue worker:

```bash
php artisan queue:work
```

Events will be broadcast asynchronously.

---

## 🔧 Nginx Configuration (Production)

```nginx
# WebSocket proxy
location /app {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_cache_bypass $http_upgrade;
    proxy_read_timeout 86400;
}

# Broadcasting auth endpoint
location /broadcasting/auth {
    proxy_pass http://127.0.0.1:80;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
}
```

---

## 📊 Testing Real-Time Events

### Using Laravel Tinker

```php
php artisan tinker

use App\Events\MessageSent;
use App\Models\Message;

$message = Message::first();
broadcast(new MessageSent($message, $message->conversation_id));
```

### Using Browser Console

```javascript
// Check connection
Echo.connector.pusher.connection.state

// Listen to all events
Echo.private('conversation.uuid')
    .listenToAll((event, data) => {
        console.log('Event:', event, 'Data:', data);
    });
```

---

## 🚨 Troubleshooting

### 1. Connection Refused
- Check if Reverb server is running: `php artisan reverb:start`
- Verify `REVERB_HOST` and `REVERB_PORT` in `.env`
- Check firewall rules

### 2. Authentication Failed
- Verify auth token is valid
- Check `/broadcasting/auth` endpoint returns proper response
- Ensure user has access to conversation

### 3. Events Not Broadcasting
- Check `BROADCAST_CONNECTION=reverb` in `.env`
- Verify queue worker is running
- Check Laravel logs: `storage/logs/laravel.log`

### 4. Multiple Connections
- Ensure you unsubscribe when leaving chat
- Use single Echo instance across app
- Properly disconnect on app close

---

## ⚡ Performance Tips

1. **Lazy Load Channels**: Only subscribe to active conversations
2. **Batch Events**: Group multiple read receipts into single event
3. **Debounce Typing**: Send typing events max once per 500ms
4. **Presence Optimization**: Use presence channels for online status
5. **Message Queuing**: Queue broadcast events for better performance

---

## 📈 Monitoring

### Check Connected Clients

```bash
php artisan reverb:stats
```

### Monitor Logs

```bash
tail -f storage/logs/reverb.log
```

---

## 🔒 Security Best Practices

1. **Always use Private Channels** for conversations
2. **Validate Authorization** in `routes/channels.php`
3. **Use HTTPS/WSS** in production
4. **Rate Limit** broadcasting auth endpoint
5. **Sanitize** message content before broadcasting

---

**Status:** ✅ Production Ready  
**Version:** 1.0.0  
**Last Updated:** November 7, 2025

