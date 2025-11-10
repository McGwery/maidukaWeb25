# Subscription Feature API Documentation

## Overview
The Subscription feature allows shops to manage their subscription plans, which control access to online/offline modes and various platform features.

## Base URL
All endpoints are prefixed with: `/api/shops/{shopId}/subscriptions`

## Authentication
All endpoints require authentication using Laravel Sanctum:
```
Authorization: Bearer {token}
```

---

## Subscription Plans

### Get Available Plans
Get all available subscription plans with their features and pricing.

**Endpoint:** `GET /api/subscription-plans`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "plans": [
      {
        "value": "free",
        "label": "Free Plan",
        "price": 0,
        "durationDays": 365,
        "features": [
          "Basic inventory management",
          "Up to 50 products",
          "Offline mode only",
          "Single user"
        ]
      },
      {
        "value": "basic",
        "label": "Basic Plan",
        "price": 9.99,
        "durationDays": 30,
        "features": [
          "Advanced inventory management",
          "Up to 500 products",
          "Online or Offline mode",
          "Up to 3 users",
          "Basic reports",
          "Customer management"
        ]
      },
      {
        "value": "premium",
        "label": "Premium Plan",
        "price": 29.99,
        "durationDays": 30,
        "features": [
          "Unlimited products",
          "Both online and offline mode",
          "Up to 10 users",
          "Advanced reports and analytics",
          "Multi-location support",
          "Priority support"
        ]
      },
      {
        "value": "enterprise",
        "label": "Enterprise Plan",
        "price": 99.99,
        "durationDays": 30,
        "features": [
          "Everything in Premium",
          "Unlimited users",
          "Custom integrations",
          "Dedicated support",
          "API access",
          "Custom features"
        ]
      }
    ]
  }
}
```

---

## Subscription Management

### 1. Get All Subscriptions
Retrieve all subscriptions for a shop with optional filters.

**Endpoint:** `GET /api/shops/{shopId}/subscriptions`

**Query Parameters:**
- `status` (optional): Filter by status (active, expired, cancelled, suspended, pending)
- `plan` (optional): Filter by plan (free, basic, premium, enterprise)
- `type` (optional): Filter by type (offline, online, both)
- `isActive` (optional): Filter active subscriptions
- `isExpired` (optional): Filter expired subscriptions
- `isExpiringSoon` (optional): Filter subscriptions expiring within 7 days
- `sortBy` (optional): Sort field
- `sortDirection` (optional): Sort direction (asc, desc)
- `perPage` (optional): Items per page (default: 15)

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "subscriptions": [
      {
        "id": "uuid",
        "shopId": "uuid",
        "plan": {
          "value": "premium",
          "label": "Premium Plan",
          "price": 29.99,
          "durationDays": 30,
          "features": ["..."]
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
        "price": 29.99,
        "currency": {
          "value": "TZS",
          "label": "Tanzanian Shilling",
          "symbol": "TSh"
        },
        "startsAt": "2025-11-07T00:00:00Z",
        "expiresAt": "2025-12-07T00:00:00Z",
        "autoRenew": true,
        "paymentMethod": "card",
        "transactionReference": "TXN123456",
        "features": ["..."],
        "maxUsers": 10,
        "maxProducts": null,
        "notes": "Premium subscription",
        "cancelledAt": null,
        "cancelledReason": null,
        "isActive": true,
        "isExpired": false,
        "isExpiringSoon": false,
        "daysRemaining": 30,
        "createdAt": "2025-11-07T00:00:00Z",
        "updatedAt": "2025-11-07T00:00:00Z"
      }
    ],
    "pagination": {
      "total": 10,
      "currentPage": 1,
      "lastPage": 1,
      "perPage": 15
    }
  }
}
```

---

### 2. Get Current Active Subscription
Get the currently active subscription for a shop.

**Endpoint:** `GET /api/shops/{shopId}/subscriptions/current`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "id": "uuid",
    "shopId": "uuid",
    "plan": {
      "value": "premium",
      "label": "Premium Plan",
      "price": 29.99,
      "durationDays": 30,
      "features": ["..."]
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
    "daysRemaining": 30
  }
}
```

---

### 3. Create New Subscription
Create a new subscription for a shop.

**Endpoint:** `POST /api/shops/{shopId}/subscriptions`

**Request Body:**
```json
{
  "plan": "premium",
  "type": "both",
  "autoRenew": true,
  "paymentMethod": "card",
  "transactionReference": "TXN123456",
  "notes": "Premium subscription for online and offline"
}
```

**Validation Rules:**
- `plan`: required, string, one of (free, basic, premium, enterprise)
- `type`: required, string, one of (offline, online, both)
- `autoRenew`: optional, boolean
- `paymentMethod`: optional, string
- `transactionReference`: optional, string
- `notes`: optional, string

**Response:**
```json
{
  "success": true,
  "code": 201,
  "message": "Subscription created successfully.",
  "data": {
    "id": "uuid",
    "shopId": "uuid",
    "plan": {
      "value": "premium",
      "label": "Premium Plan",
      "price": 29.99,
      "durationDays": 30,
      "features": ["..."]
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
    "isActive": true
  }
}
```

**Error Response (Shop has active subscription):**
```json
{
  "success": false,
  "code": 409,
  "message": "Shop already has an active subscription. Please cancel or let it expire before creating a new one.",
  "data": {
    "id": "existing-subscription-uuid"
  }
}
```

---

### 4. Get Specific Subscription
Get details of a specific subscription.

**Endpoint:** `GET /api/shops/{shopId}/subscriptions/{subscriptionId}`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "id": "uuid",
    "shopId": "uuid",
    "plan": {...},
    "type": {...},
    "status": {...},
    "startsAt": "2025-11-07T00:00:00Z",
    "expiresAt": "2025-12-07T00:00:00Z"
  }
}
```

---

### 5. Update Subscription
Update an existing subscription.

**Endpoint:** `PUT /api/shops/{shopId}/subscriptions/{subscriptionId}`

**Request Body:**
```json
{
  "plan": "enterprise",
  "type": "both",
  "status": "active",
  "autoRenew": true,
  "paymentMethod": "bank_transfer",
  "transactionReference": "TXN789012",
  "notes": "Upgraded to enterprise"
}
```

**Validation Rules:**
- All fields are optional
- `plan`: string, one of (free, basic, premium, enterprise)
- `type`: string, one of (offline, online, both)
- `status`: string, one of (active, expired, cancelled, suspended, pending)
- `autoRenew`: boolean
- `paymentMethod`: string
- `transactionReference`: string
- `notes`: string

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Subscription updated successfully.",
  "data": {
    "id": "uuid",
    "plan": {
      "value": "enterprise",
      "label": "Enterprise Plan"
    }
  }
}
```

---

### 6. Cancel Subscription
Cancel an active subscription.

**Endpoint:** `POST /api/shops/{shopId}/subscriptions/{subscriptionId}/cancel`

**Request Body:**
```json
{
  "reason": "No longer needed"
}
```

**Validation Rules:**
- `reason`: optional, string, max 500 characters

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Subscription cancelled successfully.",
  "data": {
    "id": "uuid",
    "status": {
      "value": "cancelled",
      "label": "Cancelled"
    },
    "cancelledAt": "2025-11-07T10:30:00Z",
    "cancelledReason": "No longer needed"
  }
}
```

---

### 7. Renew Subscription
Renew a subscription.

**Endpoint:** `POST /api/shops/{shopId}/subscriptions/{subscriptionId}/renew`

**Request Body:**
```json
{
  "durationDays": 30,
  "paymentMethod": "card",
  "transactionReference": "TXN456789"
}
```

**Validation Rules:**
- `durationDays`: optional, integer, min 1, max 365 (defaults to plan duration)
- `paymentMethod`: optional, string
- `transactionReference`: optional, string

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Subscription renewed successfully.",
  "data": {
    "id": "uuid",
    "status": {
      "value": "active",
      "label": "Active"
    },
    "startsAt": "2025-11-07T00:00:00Z",
    "expiresAt": "2025-12-07T00:00:00Z",
    "daysRemaining": 30
  }
}
```

---

### 8. Suspend Subscription
Suspend a subscription temporarily.

**Endpoint:** `POST /api/shops/{shopId}/subscriptions/{subscriptionId}/suspend`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Subscription suspended successfully.",
  "data": {
    "id": "uuid",
    "status": {
      "value": "suspended",
      "label": "Suspended"
    }
  }
}
```

---

### 9. Activate Subscription
Activate a suspended subscription.

**Endpoint:** `POST /api/shops/{shopId}/subscriptions/{subscriptionId}/activate`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Subscription activated successfully.",
  "data": {
    "id": "uuid",
    "status": {
      "value": "active",
      "label": "Active"
    }
  }
}
```

---

### 10. Get Subscription Statistics
Get subscription statistics for a shop.

**Endpoint:** `GET /api/shops/{shopId}/subscriptions/statistics`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "totalSubscriptions": 5,
    "activeSubscriptions": 1,
    "expiredSubscriptions": 2,
    "cancelledSubscriptions": 1,
    "expiringSoonSubscriptions": 0,
    "currentSubscription": {
      "id": "uuid",
      "plan": {...},
      "type": {...},
      "expiresAt": "2025-12-07T00:00:00Z"
    },
    "totalSpent": 149.95
  }
}
```

---

## Subscription Types

### Offline
- **Value:** `offline`
- **Description:** Shop operates offline only
- **Features:** Local inventory management, no online sales

### Online
- **Value:** `online`
- **Description:** Shop operates online only
- **Features:** E-commerce capabilities, online ordering

### Both
- **Value:** `both`
- **Description:** Shop operates both online and offline
- **Features:** Complete access to all platform features

---

## Subscription Status

### Active
- **Value:** `active`
- Subscription is currently active and valid

### Expired
- **Value:** `expired`
- Subscription has expired

### Cancelled
- **Value:** `cancelled`
- Subscription was cancelled by user

### Suspended
- **Value:** `suspended`
- Subscription is temporarily suspended

### Pending
- **Value:** `pending`
- Subscription is pending activation

---

## Error Responses

### 403 Forbidden
```json
{
  "success": false,
  "code": 403,
  "message": "This subscription does not belong to the specified shop.",
  "data": null
}
```

### 404 Not Found
```json
{
  "success": false,
  "code": 404,
  "message": "No active subscription found for this shop.",
  "data": null
}
```

### 409 Conflict
```json
{
  "success": false,
  "code": 409,
  "message": "This subscription is already cancelled.",
  "data": {...}
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "code": 500,
  "message": "Failed to create subscription.",
  "error": "Error details..."
}
```

---

## Notes

1. **CamelCase Convention:** All response keys use camelCase as per your application standard for Kotlin compatibility.

2. **Plan Limitations:**
   - **Free Plan:** 1 user, 50 products, offline only
   - **Basic Plan:** 3 users, 500 products, online or offline
   - **Premium Plan:** 10 users, unlimited products, both modes
   - **Enterprise Plan:** Unlimited users and products, both modes

3. **Auto-Renewal:** When enabled, subscriptions will automatically renew before expiration.

4. **Grace Period:** Consider implementing a grace period after expiration before restricting features.

5. **Notifications:** Implement notifications when subscriptions are expiring soon (7 days before).

---

## Example Usage Flow

### 1. User wants to subscribe to premium plan
```bash
# Step 1: Check available plans
GET /api/subscription-plans

# Step 2: Create subscription
POST /api/shops/{shopId}/subscriptions
{
  "plan": "premium",
  "type": "both",
  "autoRenew": true,
  "paymentMethod": "card",
  "transactionReference": "TXN123456"
}

# Step 3: Verify subscription
GET /api/shops/{shopId}/subscriptions/current
```

### 2. Check subscription status
```bash
# Get current subscription
GET /api/shops/{shopId}/subscriptions/current

# Get all statistics
GET /api/shops/{shopId}/subscriptions/statistics
```

### 3. Renew subscription before expiry
```bash
POST /api/shops/{shopId}/subscriptions/{subscriptionId}/renew
{
  "paymentMethod": "card",
  "transactionReference": "TXN789012"
}
```

