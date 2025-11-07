# Subscription Feature - Quick Reference

## 📋 Quick Overview

The Subscription feature enables shops to:
- Subscribe to different plans (Free, Basic, Premium, Enterprise)
- Choose operation mode (Offline, Online, Both)
- Manage subscription lifecycle (Create, Renew, Cancel, Suspend)
- Track subscription usage and statistics

---

## 🚀 Quick Start

### 1. Get Available Plans
```http
GET /api/subscription-plans
```

### 2. Create Subscription
```http
POST /api/shops/{shopId}/subscriptions
Content-Type: application/json

{
  "plan": "premium",
  "type": "both",
  "autoRenew": true,
  "paymentMethod": "card",
  "transactionReference": "TXN123456"
}
```

### 3. Get Current Subscription
```http
GET /api/shops/{shopId}/subscriptions/current
```

---

## 📊 Subscription Plans

| Plan | Price | Duration | Users | Products | Mode |
|------|-------|----------|-------|----------|------|
| **Free** | $0 | 365 days | 1 | 50 | Offline |
| **Basic** | $9.99 | 30 days | 3 | 500 | Online or Offline |
| **Premium** | $29.99 | 30 days | 10 | Unlimited | Both |
| **Enterprise** | $99.99 | 30 days | Unlimited | Unlimited | Both |

---

## 🔑 Key Endpoints

### Management
- `GET /api/shops/{shopId}/subscriptions` - List all subscriptions
- `GET /api/shops/{shopId}/subscriptions/current` - Get active subscription
- `GET /api/shops/{shopId}/subscriptions/{id}` - Get specific subscription
- `POST /api/shops/{shopId}/subscriptions` - Create subscription
- `PUT /api/shops/{shopId}/subscriptions/{id}` - Update subscription

### Actions
- `POST /api/shops/{shopId}/subscriptions/{id}/renew` - Renew subscription
- `POST /api/shops/{shopId}/subscriptions/{id}/cancel` - Cancel subscription
- `POST /api/shops/{shopId}/subscriptions/{id}/suspend` - Suspend subscription
- `POST /api/shops/{shopId}/subscriptions/{id}/activate` - Activate subscription

### Statistics
- `GET /api/shops/{shopId}/subscriptions/statistics` - Get statistics
- `GET /api/subscription-plans` - Get available plans

---

## 🎯 Subscription Types

| Type | Value | Description |
|------|-------|-------------|
| **Offline** | `offline` | Shop operates offline only |
| **Online** | `online` | Shop operates online only |
| **Both** | `both` | Shop operates both online and offline |

---

## 📈 Subscription Status

| Status | Description |
|--------|-------------|
| **Active** | Currently active and valid |
| **Expired** | Subscription has expired |
| **Cancelled** | Cancelled by user |
| **Suspended** | Temporarily suspended |
| **Pending** | Pending activation |

---

## 💡 Common Use Cases

### Check if shop can operate online
```javascript
// Get current subscription
const response = await fetch('/api/shops/{shopId}/subscriptions/current');
const { data } = await response.json();

// Check type
if (data.type.value === 'online' || data.type.value === 'both') {
  // Shop can operate online
}
```

### Check subscription limits
```javascript
const { data } = await response.json();

if (data.maxUsers && currentUsers >= data.maxUsers) {
  // User limit reached
}

if (data.maxProducts && currentProducts >= data.maxProducts) {
  // Product limit reached
}
```

### Check if subscription is expiring soon
```javascript
const { data } = await response.json();

if (data.isExpiringSoon) {
  // Show renewal reminder
  alert(`Your subscription expires in ${data.daysRemaining} days`);
}
```

---

## 🔄 Subscription Lifecycle

```
┌─────────────┐
│   Create    │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   Active    │◄────────┐
└──────┬──────┘         │
       │                │
       ├────────────────┘
       │    Renew
       │
       ├──────────┐
       │          │
       ▼          ▼
┌──────────┐  ┌──────────┐
│ Suspend  │  │  Cancel  │
└────┬─────┘  └──────────┘
     │
     │ Activate
     │
     └─────────────────┐
                       │
                       ▼
                 ┌──────────┐
                 │ Expired  │
                 └──────────┘
```

---

## 📝 Response Format (camelCase)

All API responses follow this structure:
```json
{
  "success": true,
  "code": 200,
  "message": "Optional message",
  "data": {
    "id": "uuid",
    "shopId": "uuid",
    "plan": {
      "value": "premium",
      "label": "Premium Plan",
      "price": 29.99,
      "durationDays": 30,
      "features": [...]
    },
    "type": {
      "value": "both",
      "label": "Both Online and Offline",
      "description": "Shop operates both online and offline"
    },
    "status": {
      "value": "active",
      "label": "Active"
    },
    "startsAt": "2025-11-07T00:00:00Z",
    "expiresAt": "2025-12-07T00:00:00Z",
    "isActive": true,
    "isExpired": false,
    "isExpiringSoon": false,
    "daysRemaining": 30,
    "autoRenew": true,
    "maxUsers": 10,
    "maxProducts": null
  }
}
```

---

## ⚠️ Important Notes

1. **One Active Subscription:** A shop can only have one active subscription at a time
2. **Auto-Renewal:** Set `autoRenew: true` to automatically renew subscriptions
3. **Grace Period:** Consider implementing a grace period after expiration
4. **Notifications:** Send alerts when subscription is expiring soon (7 days)
5. **Feature Enforcement:** Check subscription limits before allowing actions

---

## 🛠️ Database Schema

```sql
-- Subscriptions table
CREATE TABLE subscriptions (
    id UUID PRIMARY KEY,
    shop_id UUID REFERENCES shops(id),
    plan VARCHAR(20),           -- free, basic, premium, enterprise
    type VARCHAR(20),            -- offline, online, both
    status VARCHAR(20),          -- active, expired, cancelled, suspended, pending
    price DECIMAL(10,2),
    currency VARCHAR(3),
    starts_at TIMESTAMP,
    expires_at TIMESTAMP,
    auto_renew BOOLEAN,
    payment_method VARCHAR(50),
    transaction_reference VARCHAR(100),
    features JSON,
    max_users INTEGER,
    max_products INTEGER,
    notes TEXT,
    cancelled_at TIMESTAMP,
    cancelled_reason TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

---

## 📚 Related Files

- **Model:** `app/Models/Subscription.php`
- **Controller:** `app/Http/Controllers/Api/SubscriptionController.php`
- **Resource:** `app/Http/Resources/SubscriptionResource.php`
- **Requests:** 
  - `app/Http/Requests/StoreSubscriptionRequest.php`
  - `app/Http/Requests/UpdateSubscriptionRequest.php`
  - `app/Http/Requests/CancelSubscriptionRequest.php`
  - `app/Http/Requests/RenewSubscriptionRequest.php`
- **Enums:**
  - `app/Enums/SubscriptionPlan.php`
  - `app/Enums/SubscriptionType.php`
  - `app/Enums/SubscriptionStatus.php`
- **Migration:** `database/migrations/*_create_subscriptions_table.php`
- **Routes:** `routes/api.php`

---

## 🧪 Testing Examples

### Kotlin/Android Example
```kotlin
// Get current subscription
suspend fun getCurrentSubscription(shopId: String): SubscriptionResponse {
    return api.get("/api/shops/$shopId/subscriptions/current")
}

// Create subscription
suspend fun createSubscription(
    shopId: String,
    plan: String,
    type: String,
    autoRenew: Boolean
): SubscriptionResponse {
    val request = CreateSubscriptionRequest(
        plan = plan,
        type = type,
        autoRenew = autoRenew,
        paymentMethod = "card",
        transactionReference = "TXN123"
    )
    return api.post("/api/shops/$shopId/subscriptions", request)
}

// Check if can add more users
fun canAddUser(subscription: Subscription): Boolean {
    return subscription.maxUsers == null || 
           currentUserCount < subscription.maxUsers
}
```

---

## 🎨 UI Implementation Tips

1. **Display Plan Badge:** Show current plan prominently
2. **Progress Bar:** Show days remaining until expiration
3. **Feature Lock:** Disable features not included in current plan
4. **Upgrade Prompt:** Suggest upgrade when limits are reached
5. **Expiry Alert:** Show countdown when subscription is expiring soon

---

## 📞 Support

For issues or questions about the Subscription feature, please contact the development team.

