# Shop with Subscription Information - Response Examples

This document shows examples of how subscription information is included in shop responses across different endpoints.

## Shop Resource Structure

Every shop returned by the API includes the following subscription information (when `activeSubscription` relationship is loaded):

```json
{
  "id": "shop-uuid-123",
  "name": "My Shop",
  "businessType": {
    "value": "retail",
    "label": "Retail Store"
  },
  "phoneNumber": "+255712345678",
  "address": "123 Main Street, Dar es Salaam",
  "agentCode": "AGENT001",
  "currency": {
    "code": "TZS",
    "symbol": "TSh",
    "label": "Tanzanian Shilling"
  },
  "imageUrl": "https://example.com/shop.jpg",
  "isActive": true,
  "isCurrentSelected": true,
  "owner": {
    "id": "user-uuid-456",
    "name": "John Doe",
    "phone": "+255712345678"
  },
  "activeSubscription": {
    "id": "subscription-uuid-789",
    "plan": "pro",
    "planLabel": "Pro Plan",
    "type": "monthly",
    "expiresAt": "2025-12-09T10:00:00.000000Z",
    "daysRemaining": 30,
    "isExpiringSoon": false
  },
  "createdAt": "2025-01-15T10:00:00.000000Z",
  "updatedAt": "2025-11-09T14:30:00.000000Z"
}
```

## Subscription Information Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | string | Subscription UUID |
| `plan` | string | Plan identifier: `free`, `basic`, `pro`, `enterprise` |
| `planLabel` | string | Human-readable plan name |
| `type` | string | Subscription type: `monthly`, `yearly` |
| `expiresAt` | string (ISO 8601) | When the subscription expires |
| `daysRemaining` | integer | Number of days until expiration |
| `isExpiringSoon` | boolean | `true` if expiring within 7 days |

## Example: Shop Without Active Subscription

If a shop doesn't have an active subscription (new shop, expired subscription, or cancelled):

```json
{
  "id": "shop-uuid-123",
  "name": "My New Shop",
  "businessType": {
    "value": "retail",
    "label": "Retail Store"
  },
  "phoneNumber": "+255712345678",
  "address": "123 Main Street",
  "isActive": true,
  "owner": {
    "id": "user-uuid-456",
    "name": "John Doe"
  },
  "activeSubscription": null,
  "createdAt": "2025-11-09T10:00:00.000000Z",
  "updatedAt": "2025-11-09T10:00:00.000000Z"
}
```

## Example: Shop with Expiring Subscription

When a subscription is expiring soon (within 7 days):

```json
{
  "id": "shop-uuid-123",
  "name": "My Shop",
  "businessType": {
    "value": "wholesale",
    "label": "Wholesale Business"
  },
  "activeSubscription": {
    "id": "subscription-uuid-789",
    "plan": "basic",
    "planLabel": "Basic Plan",
    "type": "monthly",
    "expiresAt": "2025-11-14T23:59:59.000000Z",
    "daysRemaining": 5,
    "isExpiringSoon": true
  }
}
```

## Endpoints That Include Subscription Information

All shop-related endpoints now include subscription information:

### 1. GET /api/shops (List All Shops)
```json
{
  "success": true,
  "message": "Shops retrieved successfully.",
  "responseTime": 45.67,
  "data": {
    "shops": [
      {
        "id": "shop-1",
        "name": "Shop One",
        "activeSubscription": { /* subscription data */ }
      },
      {
        "id": "shop-2",
        "name": "Shop Two",
        "activeSubscription": null
      }
    ],
    "activeShop": {
      "id": "shop-1",
      "name": "Shop One",
      "activeSubscription": { /* subscription data */ }
    },
    "totalShops": 2,
    "activeShops": 2
  }
}
```

### 2. POST /api/shops (Create Shop)
```json
{
  "success": true,
  "message": "Shop created successfully.",
  "responseTime": 78.90,
  "data": {
    "shop": {
      "id": "new-shop-uuid",
      "name": "New Shop",
      "activeSubscription": null,
      "createdAt": "2025-11-09T15:00:00.000000Z"
    }
  }
}
```

### 3. GET /api/shops/{shop} (Get Shop Details)
```json
{
  "success": true,
  "message": "Shop retrieved successfully.",
  "responseTime": 34.56,
  "data": {
    "shop": {
      "id": "shop-uuid-123",
      "name": "Detailed Shop",
      "owner": { /* owner data */ },
      "members": [ /* member data */ ],
      "activeSubscription": {
        "id": "subscription-uuid-789",
        "plan": "enterprise",
        "planLabel": "Enterprise Plan",
        "type": "yearly",
        "expiresAt": "2026-11-09T10:00:00.000000Z",
        "daysRemaining": 365,
        "isExpiringSoon": false
      }
    }
  }
}
```

### 4. PUT /api/shops/{shop} (Update Shop)
```json
{
  "success": true,
  "message": "Shop updated successfully.",
  "responseTime": 45.23,
  "data": {
    "shop": {
      "id": "shop-uuid-123",
      "name": "Updated Shop Name",
      "activeSubscription": { /* subscription data */ }
    }
  }
}
```

### 5. POST /api/shops/{shop}/switch (Switch Active Shop)
```json
{
  "success": true,
  "message": "Successfully switched to My Shop.",
  "responseTime": 23.45,
  "data": {
    "shop": {
      "id": "shop-uuid-123",
      "name": "My Shop",
      "activeSubscription": {
        "id": "subscription-uuid-789",
        "plan": "pro",
        "planLabel": "Pro Plan",
        "type": "monthly",
        "expiresAt": "2025-12-09T10:00:00.000000Z",
        "daysRemaining": 30,
        "isExpiringSoon": false
      }
    }
  }
}
```

### 6. POST /api/shops/{shop}/active (Set Active Shop)
```json
{
  "success": true,
  "message": "Shop status updated successfully.",
  "responseTime": 34.12,
  "data": {
    "shop": {
      "id": "shop-uuid-123",
      "name": "My Shop",
      "isActive": true,
      "activeSubscription": { /* subscription data */ }
    }
  }
}
```

## Frontend Usage Examples

### React/TypeScript Example

```typescript
interface ShopSubscription {
  id: string;
  plan: 'free' | 'basic' | 'pro' | 'enterprise';
  planLabel: string;
  type: 'monthly' | 'yearly';
  expiresAt: string;
  daysRemaining: number;
  isExpiringSoon: boolean;
}

interface Shop {
  id: string;
  name: string;
  businessType: {
    value: string;
    label: string;
  };
  activeSubscription: ShopSubscription | null;
  // ... other fields
}

// Check if shop has active subscription
function hasActiveSubscription(shop: Shop): boolean {
  return shop.activeSubscription !== null;
}

// Get subscription status message
function getSubscriptionStatus(shop: Shop): string {
  if (!shop.activeSubscription) {
    return 'No active subscription';
  }
  
  const sub = shop.activeSubscription;
  
  if (sub.isExpiringSoon) {
    return `Subscription expires in ${sub.daysRemaining} days`;
  }
  
  return `${sub.planLabel} (${sub.daysRemaining} days remaining)`;
}

// Display subscription warning
function SubscriptionBanner({ shop }: { shop: Shop }) {
  if (!shop.activeSubscription) {
    return (
      <div className="warning-banner">
        <p>Please subscribe to unlock all features</p>
        <button>View Plans</button>
      </div>
    );
  }
  
  if (shop.activeSubscription.isExpiringSoon) {
    return (
      <div className="alert-banner">
        <p>
          Your {shop.activeSubscription.planLabel} expires in{' '}
          {shop.activeSubscription.daysRemaining} days
        </p>
        <button>Renew Now</button>
      </div>
    );
  }
  
  return null;
}
```

### Vue.js Example

```vue
<template>
  <div class="shop-card">
    <h3>{{ shop.name }}</h3>
    <div v-if="shop.activeSubscription" class="subscription-info">
      <span class="plan-badge" :class="planClass">
        {{ shop.activeSubscription.planLabel }}
      </span>
      <p v-if="shop.activeSubscription.isExpiringSoon" class="warning">
        Expires in {{ shop.activeSubscription.daysRemaining }} days
      </p>
    </div>
    <div v-else class="no-subscription">
      <p>No active subscription</p>
      <button @click="$emit('subscribe')">Subscribe Now</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  shop: Shop;
}

const props = defineProps<Props>();

const planClass = computed(() => {
  if (!props.shop.activeSubscription) return '';
  const plan = props.shop.activeSubscription.plan;
  return `plan-${plan}`;
});
</script>
```

## Subscription Plan Comparison

| Plan | Features | Max Products | Max Users | Price |
|------|----------|--------------|-----------|-------|
| **Free** | Basic features | 50 | 1 | Free |
| **Basic** | Standard features | 500 | 3 | 50,000 TZS/month |
| **Pro** | Advanced features | Unlimited | 10 | 150,000 TZS/month |
| **Enterprise** | All features + Priority support | Unlimited | Unlimited | 500,000 TZS/month |

## Notes

1. **Automatic Loading**: Subscription information is automatically loaded for all shop endpoints
2. **Null Values**: If a shop has no active subscription, the `activeSubscription` field will be `null`
3. **Expiring Soon**: A subscription is considered "expiring soon" when it has 7 or fewer days remaining
4. **Zero Days**: If `daysRemaining` is 0, the subscription has expired but may still be in grace period
5. **Real-time Updates**: Subscription data is calculated in real-time based on the current date

## Related Endpoints

For complete subscription management, see:
- `GET /api/subscription-plans` - List all available plans
- `GET /api/shops/{shop}/subscriptions` - Get all subscriptions (active and historical)
- `POST /api/shops/{shop}/subscriptions` - Create a new subscription
- `POST /api/shops/{shop}/subscriptions/{subscription}/renew` - Renew a subscription

---

**Last Updated:** November 9, 2025

