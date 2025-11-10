# Subscription API - Postman Collection Examples

## Environment Variables
```json
{
  "baseUrl": "http://your-domain.com/api",
  "token": "your-auth-token",
  "shopId": "shop-uuid"
}
```

---

## 1. Get Available Subscription Plans

### Request
```http
GET {{baseUrl}}/subscription-plans
Authorization: Bearer {{token}}
```

### Response (200 OK)
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

## 2. Create New Subscription

### Request
```http
POST {{baseUrl}}/shops/{{shopId}}/subscriptions
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "plan": "premium",
  "type": "both",
  "autoRenew": true,
  "paymentMethod": "card",
  "transactionReference": "TXN123456789",
  "notes": "Premium subscription for online and offline operations"
}
```

### Response (201 Created)
```json
{
  "success": true,
  "code": 201,
  "message": "Subscription created successfully.",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "shopId": "550e8400-e29b-41d4-a716-446655440001",
    "plan": {
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
    "transactionReference": "TXN123456789",
    "features": [...],
    "maxUsers": 10,
    "maxProducts": null,
    "notes": "Premium subscription for online and offline operations",
    "cancelledAt": null,
    "cancelledReason": null,
    "isActive": true,
    "isExpired": false,
    "isExpiringSoon": false,
    "daysRemaining": 30,
    "createdAt": "2025-11-07T00:00:00Z",
    "updatedAt": "2025-11-07T00:00:00Z"
  }
}
```

---

## 3. Get Current Active Subscription

### Request
```http
GET {{baseUrl}}/shops/{{shopId}}/subscriptions/current
Authorization: Bearer {{token}}
```

### Response (200 OK)
```json
{
  "success": true,
  "code": 200,
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "shopId": "550e8400-e29b-41d4-a716-446655440001",
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
    "daysRemaining": 30
  }
}
```

---

## 4. Get All Subscriptions (with filters)

### Request
```http
GET {{baseUrl}}/shops/{{shopId}}/subscriptions?status=active&plan=premium&perPage=10
Authorization: Bearer {{token}}
```

### Response (200 OK)
```json
{
  "success": true,
  "code": 200,
  "data": {
    "subscriptions": [
      {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "shopId": "550e8400-e29b-41d4-a716-446655440001",
        "plan": {...},
        "type": {...},
        "status": {...},
        "startsAt": "2025-11-07T00:00:00Z",
        "expiresAt": "2025-12-07T00:00:00Z"
      }
    ],
    "pagination": {
      "total": 5,
      "currentPage": 1,
      "lastPage": 1,
      "perPage": 10
    }
  }
}
```

---

## 5. Get Specific Subscription

### Request
```http
GET {{baseUrl}}/shops/{{shopId}}/subscriptions/550e8400-e29b-41d4-a716-446655440000
Authorization: Bearer {{token}}
```

### Response (200 OK)
```json
{
  "success": true,
  "code": 200,
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "shopId": "550e8400-e29b-41d4-a716-446655440001",
    "plan": {...},
    "type": {...},
    "status": {...}
  }
}
```

---

## 6. Update Subscription

### Request
```http
PUT {{baseUrl}}/shops/{{shopId}}/subscriptions/550e8400-e29b-41d4-a716-446655440000
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "plan": "enterprise",
  "autoRenew": true,
  "notes": "Upgraded to enterprise plan"
}
```

### Response (200 OK)
```json
{
  "success": true,
  "code": 200,
  "message": "Subscription updated successfully.",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "plan": {
      "value": "enterprise",
      "label": "Enterprise Plan",
      "price": 99.99,
      "durationDays": 30,
      "features": [...]
    },
    "autoRenew": true,
    "notes": "Upgraded to enterprise plan"
  }
}
```

---

## 7. Renew Subscription

### Request
```http
POST {{baseUrl}}/shops/{{shopId}}/subscriptions/550e8400-e29b-41d4-a716-446655440000/renew
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "durationDays": 30,
  "paymentMethod": "mobile_money",
  "transactionReference": "TXN987654321"
}
```

### Response (200 OK)
```json
{
  "success": true,
  "code": 200,
  "message": "Subscription renewed successfully.",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "status": {
      "value": "active",
      "label": "Active"
    },
    "startsAt": "2025-11-07T00:00:00Z",
    "expiresAt": "2025-12-07T00:00:00Z",
    "daysRemaining": 30,
    "paymentMethod": "mobile_money",
    "transactionReference": "TXN987654321"
  }
}
```

---

## 8. Cancel Subscription

### Request
```http
POST {{baseUrl}}/shops/{{shopId}}/subscriptions/550e8400-e29b-41d4-a716-446655440000/cancel
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "reason": "Business closed temporarily"
}
```

### Response (200 OK)
```json
{
  "success": true,
  "code": 200,
  "message": "Subscription cancelled successfully.",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "status": {
      "value": "cancelled",
      "label": "Cancelled"
    },
    "cancelledAt": "2025-11-07T10:30:00Z",
    "cancelledReason": "Business closed temporarily",
    "autoRenew": false
  }
}
```

---

## 9. Suspend Subscription

### Request
```http
POST {{baseUrl}}/shops/{{shopId}}/subscriptions/550e8400-e29b-41d4-a716-446655440000/suspend
Authorization: Bearer {{token}}
```

### Response (200 OK)
```json
{
  "success": true,
  "code": 200,
  "message": "Subscription suspended successfully.",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "status": {
      "value": "suspended",
      "label": "Suspended"
    }
  }
}
```

---

## 10. Activate Subscription

### Request
```http
POST {{baseUrl}}/shops/{{shopId}}/subscriptions/550e8400-e29b-41d4-a716-446655440000/activate
Authorization: Bearer {{token}}
```

### Response (200 OK)
```json
{
  "success": true,
  "code": 200,
  "message": "Subscription activated successfully.",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "status": {
      "value": "active",
      "label": "Active"
    }
  }
}
```

---

## 11. Get Subscription Statistics

### Request
```http
GET {{baseUrl}}/shops/{{shopId}}/subscriptions/statistics
Authorization: Bearer {{token}}
```

### Response (200 OK)
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
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "plan": {
        "value": "premium",
        "label": "Premium Plan"
      },
      "expiresAt": "2025-12-07T00:00:00Z",
      "daysRemaining": 30
    },
    "totalSpent": 149.95
  }
}
```

---

## Error Responses

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
  "message": "Shop already has an active subscription. Please cancel or let it expire before creating a new one.",
  "data": {
    "id": "existing-subscription-uuid"
  }
}
```

### 403 Forbidden
```json
{
  "success": false,
  "code": 403,
  "message": "This subscription does not belong to the specified shop.",
  "data": null
}
```

### 422 Validation Error
```json
{
  "success": false,
  "code": 422,
  "message": "The given data was invalid.",
  "errors": {
    "plan": [
      "The plan field is required."
    ],
    "type": [
      "The selected type is invalid."
    ]
  }
}
```

---

## Postman Collection JSON

You can import this into Postman:

```json
{
  "info": {
    "name": "Subscription API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Get Available Plans",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          }
        ],
        "url": {
          "raw": "{{baseUrl}}/subscription-plans",
          "host": ["{{baseUrl}}"],
          "path": ["subscription-plans"]
        }
      }
    },
    {
      "name": "Create Subscription",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          },
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"plan\": \"premium\",\n  \"type\": \"both\",\n  \"autoRenew\": true,\n  \"paymentMethod\": \"card\",\n  \"transactionReference\": \"TXN123456789\"\n}"
        },
        "url": {
          "raw": "{{baseUrl}}/shops/{{shopId}}/subscriptions",
          "host": ["{{baseUrl}}"],
          "path": ["shops", "{{shopId}}", "subscriptions"]
        }
      }
    },
    {
      "name": "Get Current Subscription",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          }
        ],
        "url": {
          "raw": "{{baseUrl}}/shops/{{shopId}}/subscriptions/current",
          "host": ["{{baseUrl}}"],
          "path": ["shops", "{{shopId}}", "subscriptions", "current"]
        }
      }
    }
  ]
}
```

---

## cURL Examples

### Get Plans
```bash
curl -X GET "http://your-domain.com/api/subscription-plans" \
  -H "Authorization: Bearer your-token"
```

### Create Subscription
```bash
curl -X POST "http://your-domain.com/api/shops/shop-id/subscriptions" \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -d '{
    "plan": "premium",
    "type": "both",
    "autoRenew": true,
    "paymentMethod": "card",
    "transactionReference": "TXN123456"
  }'
```

### Get Current Subscription
```bash
curl -X GET "http://your-domain.com/api/shops/shop-id/subscriptions/current" \
  -H "Authorization: Bearer your-token"
```

### Renew Subscription
```bash
curl -X POST "http://your-domain.com/api/shops/shop-id/subscriptions/subscription-id/renew" \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -d '{
    "durationDays": 30,
    "paymentMethod": "mobile_money",
    "transactionReference": "TXN987654"
  }'
```

