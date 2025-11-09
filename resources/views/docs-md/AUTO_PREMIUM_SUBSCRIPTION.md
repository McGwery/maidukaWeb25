# Automatic Premium Subscription on Shop Creation

## Overview

Every new shop created through the API automatically receives a **Premium subscription** that is active for **30 days**. This ensures all new shops have access to advanced features immediately.

## Implementation Details

### What Happens When a Shop is Created

1. **Shop is created** with basic information
2. **Default settings are applied** via ShopSettings
3. **Premium subscription is automatically created** with the following details:
   - Plan: **Premium**
   - Type: **Both** (Online & Offline)
   - Status: **Active**
   - Duration: **30 days**
   - Price: **12,000 TZS**
   - Payment Method: `free_trial`
   - Transaction Reference: `SHOP_CREATION_{UNIQUE_ID}`

### Subscription Configuration

```php
$subscription = Subscription::create([
    'shop_id' => $shop->id,
    'plan' => SubscriptionPlan::PREMIUM,
    'type' => SubscriptionType::BOTH,
    'status' => SubscriptionStatus::ACTIVE,
    'price' => 12000.00,
    'currency' => Currency::TZS,
    'starts_at' => now(),
    'expires_at' => now()->addDays(30),
    'auto_renew' => false,
    'payment_method' => 'free_trial',
    'transaction_reference' => 'SHOP_CREATION_XXX',
    'features' => [
        'Unlimited products',
        'Both online and offline mode',
        'Up to 10 users',
        'Advanced reports and analytics',
        'Multi-location support',
        'Priority support',
    ],
    'max_users' => 10,
    'max_products' => null, // Unlimited
    'notes' => 'Premium subscription activated on shop creation',
]);
```

## Premium Plan Features

All new shops receive these features for 30 days:

✅ **Unlimited Products** - No limit on inventory items
✅ **Both Online & Offline Mode** - Full flexibility in operations
✅ **Up to 10 Users** - Team collaboration support
✅ **Advanced Reports & Analytics** - Comprehensive business insights
✅ **Multi-location Support** - Manage multiple shop locations
✅ **Priority Support** - Faster customer service response

## API Response

When creating a new shop, the response includes the subscription information:

### Request

```bash
POST /api/shops
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "My New Shop",
  "business_type": "retail",
  "phone_number": "+255712345678",
  "address": "123 Main Street, Dar es Salaam"
}
```

### Response

```json
{
  "success": true,
  "message": "Shop created successfully with Premium subscription.",
  "responseTime": 78.90,
  "data": {
    "shop": {
      "id": "shop-uuid-123",
      "name": "My New Shop",
      "businessType": {
        "value": "retail",
        "label": "Retail Store"
      },
      "phoneNumber": "+255712345678",
      "address": "123 Main Street, Dar es Salaam",
      "currency": {
        "code": "TZS",
        "symbol": "TSh",
        "label": "Tanzanian Shilling"
      },
      "isActive": true,
      "owner": {
        "id": "user-uuid-456",
        "name": "John Doe",
        "phone": "+255712345678"
      },
      "activeSubscription": {
        "id": "subscription-uuid-789",
        "plan": "premium",
        "planLabel": "Premium Plan",
        "type": "both",
        "expiresAt": "2025-12-09T10:00:00.000000Z",
        "daysRemaining": 30,
        "isExpiringSoon": false
      },
      "createdAt": "2025-11-09T10:00:00.000000Z",
      "updatedAt": "2025-11-09T10:00:00.000000Z"
    }
  }
}
```

## Subscription Lifecycle

### Day 1-23 (Normal Operation)
- Full access to all Premium features
- `isExpiringSoon`: `false`
- `daysRemaining`: 30 → 8

### Day 24-30 (Expiring Soon)
- Full access continues
- `isExpiringSoon`: `true`
- `daysRemaining`: 7 → 1
- ⚠️ Frontend should show renewal reminder

### After Day 30 (Expired)
- `activeSubscription`: `null`
- Shop owner needs to renew subscription
- Limited access based on business rules

## Frontend Implementation

### Check Subscription Status

```typescript
interface Shop {
  id: string;
  name: string;
  activeSubscription: {
    id: string;
    plan: string;
    planLabel: string;
    type: string;
    expiresAt: string;
    daysRemaining: number;
    isExpiringSoon: boolean;
  } | null;
}

function hasActiveSubscription(shop: Shop): boolean {
  return shop.activeSubscription !== null;
}

function isPremiumPlan(shop: Shop): boolean {
  return shop.activeSubscription?.plan === 'premium';
}

function isFreeTrial(shop: Shop): boolean {
  // Check if it's the initial 30-day Premium subscription
  const createdDate = new Date(shop.createdAt);
  const now = new Date();
  const daysSinceCreation = Math.floor((now.getTime() - createdDate.getTime()) / (1000 * 60 * 60 * 24));
  
  return shop.activeSubscription?.plan === 'premium' && daysSinceCreation <= 30;
}
```

### Display Subscription Banner

```tsx
function SubscriptionBanner({ shop }: { shop: Shop }) {
  if (!shop.activeSubscription) {
    return (
      <Alert severity="warning">
        <AlertTitle>Subscription Expired</AlertTitle>
        <p>Your Premium trial has ended. Renew your subscription to continue using advanced features.</p>
        <Button variant="contained" href={`/shops/${shop.id}/subscribe`}>
          Renew Subscription
        </Button>
      </Alert>
    );
  }

  if (shop.activeSubscription.isExpiringSoon) {
    return (
      <Alert severity="info">
        <AlertTitle>Subscription Expiring Soon</AlertTitle>
        <p>
          Your {shop.activeSubscription.planLabel} expires in {shop.activeSubscription.daysRemaining} days.
        </p>
        <Button variant="outlined" href={`/shops/${shop.id}/subscribe`}>
          Renew Now
        </Button>
      </Alert>
    );
  }

  return (
    <Alert severity="success">
      <AlertTitle>{shop.activeSubscription.planLabel}</AlertTitle>
      <p>{shop.activeSubscription.daysRemaining} days remaining</p>
    </Alert>
  );
}
```

### Welcome Message for New Shops

```tsx
function NewShopWelcome({ shop }: { shop: Shop }) {
  const isNewShop = () => {
    const createdDate = new Date(shop.createdAt);
    const now = new Date();
    const hoursSinceCreation = (now.getTime() - createdDate.getTime()) / (1000 * 60 * 60);
    return hoursSinceCreation < 24; // Within 24 hours
  };

  if (!isNewShop()) return null;

  return (
    <Card>
      <CardContent>
        <Typography variant="h5">🎉 Welcome to Maiduka25!</Typography>
        <Typography variant="body1" sx={{ mt: 2 }}>
          Your shop has been created successfully with a <strong>30-day Premium subscription</strong>!
        </Typography>
        <Box sx={{ mt: 2 }}>
          <Typography variant="body2" color="text.secondary">
            You now have access to:
          </Typography>
          <ul>
            <li>✅ Unlimited products</li>
            <li>✅ Both online and offline mode</li>
            <li>✅ Up to 10 users</li>
            <li>✅ Advanced reports and analytics</li>
            <li>✅ Multi-location support</li>
            <li>✅ Priority support</li>
          </ul>
        </Box>
        <Button variant="contained" sx={{ mt: 2 }} href="/getting-started">
          Get Started
        </Button>
      </CardContent>
    </Card>
  );
}
```

## Business Rules

### Auto-Renewal
- Default: **Disabled** (`auto_renew: false`)
- Users must manually renew after 30 days
- Can be enabled by user through subscription settings

### Grace Period
- Consider implementing a grace period (e.g., 3-7 days)
- Allow limited access to export data before complete lockout

### Downgrade Strategy
After Premium expires, you can:
1. **Lock all features** - Require immediate renewal
2. **Downgrade to Free plan** - Basic features only
3. **Grace period** - Limited time to renew

## Transaction Tracking

Each auto-created subscription has:
- **Payment Method**: `free_trial`
- **Transaction Reference**: `SHOP_CREATION_{UNIQUE_ID}`
- **Notes**: "Premium subscription activated on shop creation"

This makes it easy to identify and track promotional subscriptions in reports.

## Database Structure

```sql
-- Example subscription record
{
  id: 'uuid-xxx',
  shop_id: 'shop-uuid',
  plan: 'premium',
  type: 'both',
  status: 'active',
  price: 12000.00,
  currency: 'TZS',
  starts_at: '2025-11-09 10:00:00',
  expires_at: '2025-12-09 10:00:00',
  auto_renew: false,
  payment_method: 'free_trial',
  transaction_reference: 'SHOP_CREATION_655ABC123',
  features: JSON,
  max_users: 10,
  max_products: null,
  notes: 'Premium subscription activated on shop creation',
  created_at: '2025-11-09 10:00:00',
  updated_at: '2025-11-09 10:00:00'
}
```

## Analytics & Reporting

Track subscription metrics:

### Key Metrics
- **Total Free Trials Created**: Count of `payment_method: 'free_trial'`
- **Trial Conversion Rate**: % of trials that convert to paid
- **Average Trial Duration Used**: How long users actively use trial
- **Trial Expiration Rate**: % of trials that expire without renewal

### Query Examples

```sql
-- Count active free trials
SELECT COUNT(*) 
FROM subscriptions 
WHERE payment_method = 'free_trial' 
  AND status = 'active';

-- Trial conversion rate
SELECT 
  COUNT(CASE WHEN payment_method = 'free_trial' THEN 1 END) as trials,
  COUNT(CASE WHEN payment_method != 'free_trial' AND plan = 'premium' THEN 1 END) as conversions,
  (COUNT(CASE WHEN payment_method != 'free_trial' AND plan = 'premium' THEN 1 END) * 100.0 / 
   COUNT(CASE WHEN payment_method = 'free_trial' THEN 1 END)) as conversion_rate
FROM subscriptions
WHERE plan = 'premium';
```

## Best Practices

### For Shop Owners
1. ✅ Explore all Premium features during trial
2. ✅ Set up your shop completely within 30 days
3. ✅ Enable notifications for subscription expiry
4. ✅ Plan for renewal or choose appropriate plan

### For Developers
1. ✅ Always check subscription status before allowing premium features
2. ✅ Show clear subscription status in UI
3. ✅ Send renewal reminders starting 7 days before expiry
4. ✅ Provide easy upgrade/downgrade options
5. ✅ Handle expired subscriptions gracefully

## Testing

### Test Scenarios

1. **Create New Shop**
   ```bash
   POST /api/shops
   # Verify: activeSubscription is not null
   # Verify: plan is 'premium'
   # Verify: daysRemaining is 30
   ```

2. **Check Subscription After Creation**
   ```bash
   GET /api/shops/{shop}
   # Verify: activeSubscription exists
   # Verify: expiresAt is 30 days from now
   ```

3. **List All Shops**
   ```bash
   GET /api/shops
   # Verify: All new shops have activeSubscription
   ```

## Support & Documentation

- **API Documentation**: See `API_DOCUMENTATION.md`
- **Response Examples**: See `SHOP_SUBSCRIPTION_RESPONSE_EXAMPLES.md`
- **Integration Guide**: See `SHOP_SUBSCRIPTION_INTEGRATION.md`

---

**Last Updated**: November 9, 2025  
**Feature Version**: 1.0.0  
**Status**: ✅ Active and Production-Ready

