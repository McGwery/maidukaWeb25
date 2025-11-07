# Authorization System - Verification Checklist

## ✅ System Verification

Run these checks to verify your authorization system is working correctly:

---

## 1. Verify Policies are Registered

```bash
php artisan route:list --columns=Method,URI,Action,Middleware
```

Check that routes are protected with `auth:sanctum`.

---

## 2. Test Permission Checking (Laravel Tinker)

```bash
php artisan tinker
```

### Test Owner Permissions
```php
$user = User::first();
$shop = Shop::first();

// Test if owner has all permissions
$shop->isOwner($user);
// Should return: true (if user is owner)

// Test specific permission
$user->hasShopPermission($shop, 'manage_products');
// Should return: true (if owner)
```

### Test Member Permissions
```php
$member = $shop->members()->first();
$memberUser = $member->user;

// Check member's role
$memberUser->getRoleInShop($shop);
// Returns: 'manager', 'cashier', etc.

// Check specific permission
$memberUser->hasShopPermission($shop, 'process_sales');
// Returns: true/false based on role
```

### Test Policy Authorization
```php
use App\Models\Product;
use Illuminate\Support\Facades\Gate;

// Test if user can create products
Gate::forUser($user)->allows('create', [Product::class, $shop]);
// Returns: true/false

// Test if user can view products
Gate::forUser($user)->allows('viewAny', [Product::class, $shop]);
// Returns: true/false
```

---

## 3. Test via API (Postman/cURL)

### Test Authorized Request (Should Succeed)
```bash
curl -X POST "http://localhost/api/shops/{shopId}/products" \
  -H "Authorization: Bearer OWNER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Product",
    "sellingPrice": 10000,
    "quantity": 10
  }'
```

**Expected:** 201 Created

### Test Unauthorized Request (Should Fail)
```bash
curl -X POST "http://localhost/api/shops/{shopId}/products" \
  -H "Authorization: Bearer EMPLOYEE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Product",
    "sellingPrice": 10000,
    "quantity": 10
  }'
```

**Expected:** 403 Forbidden
```json
{
  "success": false,
  "code": 403,
  "message": "This action is unauthorized."
}
```

---

## 4. Verify Policies Exist

```bash
ls -la app/Policies/
```

**Expected files:**
- AdPolicy.php
- ChatPolicy.php
- CustomerPolicy.php
- ExpensePolicy.php
- ProductPolicy.php
- PurchaseOrderPolicy.php
- ReportPolicy.php
- SalePolicy.php
- SavingsPolicy.php
- ShopMemberPolicy.php
- ShopPolicy.php
- ShopSettingsPolicy.php

---

## 5. Check Permissions in Database

```bash
php artisan tinker
```

```php
// Get a shop member
$member = \App\Models\Shop::first()->members()->first();

// Check their permissions
$member->pivot->permissions;
// Should return array of permissions

// Check their role
$member->pivot->role;
// Should return: 'manager', 'cashier', etc.
```

---

## 6. Test Different Scenarios

### Scenario 1: Owner Full Access ✅
```php
$owner = User::find(1); // Assuming owner
$shop = Shop::where('owner_id', $owner->id)->first();

// All should return true
Gate::forUser($owner)->allows('create', [Product::class, $shop]);
Gate::forUser($owner)->allows('delete', Product::first());
Gate::forUser($owner)->allows('viewFinancialReport', [ReportPolicy::class, $shop]);
```

### Scenario 2: Manager Partial Access ✅
```php
// Create manager member
$shop->members()->attach($user->id, [
    'role' => 'manager',
    'permissions' => \App\Enums\ShopMemberRole::MANAGER->permissions(),
    'is_active' => true
]);

// Should return true
Gate::forUser($user)->allows('create', [Product::class, $shop]);
Gate::forUser($user)->allows('process_sales', [Sale::class, $shop]);

// Should return false (owner only)
Gate::forUser($user)->denies('reset', [ShopSettingsPolicy::class, $shop]);
```

### Scenario 3: Cashier Limited Access ✅
```php
// Create cashier member
$shop->members()->attach($user->id, [
    'role' => 'cashier',
    'permissions' => \App\Enums\ShopMemberRole::CASHIER->permissions(),
    'is_active' => true
]);

// Should return true
Gate::forUser($user)->allows('create', [Sale::class, $shop]);
Gate::forUser($user)->allows('viewAny', [Customer::class, $shop]);

// Should return false (no permission)
Gate::forUser($user)->denies('create', [Product::class, $shop]);
Gate::forUser($user)->denies('viewFinancialReport', [ReportPolicy::class, $shop]);
```

### Scenario 4: Employee Read-Only ✅
```php
// Create employee member
$shop->members()->attach($user->id, [
    'role' => 'employee',
    'permissions' => \App\Enums\ShopMemberRole::EMPLOYEE->permissions(),
    'is_active' => true
]);

// Should return true (view only)
Gate::forUser($user)->allows('viewAny', [Product::class, $shop]);

// Should return false (no create/update/delete)
Gate::forUser($user)->denies('create', [Product::class, $shop]);
Gate::forUser($user)->denies('create', [Sale::class, $shop]);
Gate::forUser($user)->denies('delete', Product::first());
```

---

## 7. Common Issues & Solutions

### Issue: "Policy not found"
**Check:**
```bash
grep -r "ProductPolicy" app/Providers/AuthServiceProvider.php
```
**Fix:** Ensure policy is registered in `$policies` array.

### Issue: "This action is unauthorized" (but should be allowed)
**Check:**
```php
// In tinker
$user = User::find(1);
$shop = Shop::find(1);

// Check if user is shop member
$shop->members()->where('user_id', $user->id)->first();

// Check member permissions
$member = $shop->members()->where('user_id', $user->id)->first();
$member->pivot->permissions;
$member->pivot->is_active; // Must be true
```

### Issue: "Undefined method hasPermission"
**Check:** Policy uses `HasShopPolicy` trait:
```php
use App\Traits\HasShopPolicy;

class ProductPolicy
{
    use HasShopPolicy;
    // ...
}
```

---

## 8. Performance Test

Test that authorization doesn't slow down requests:

```bash
# Time an authorized request
time curl -X GET "http://localhost/api/shops/{shopId}/products" \
  -H "Authorization: Bearer TOKEN"

# Should complete in < 200ms
```

---

## ✅ Final Checklist

- [ ] All 12 policies created
- [ ] All policies registered in AuthServiceProvider
- [ ] HasShopPolicy trait exists
- [ ] ShopMemberRole enum has all permissions
- [ ] Composer autoload refreshed
- [ ] No PHP errors
- [ ] Owner has full access
- [ ] Manager has correct permissions
- [ ] Cashier has limited access
- [ ] Employee has read-only access
- [ ] Unauthorized requests return 403
- [ ] Authorized requests succeed
- [ ] API responses in camelCase

---

## 🎯 Success Criteria

Authorization system is working when:

✅ Owner can do everything  
✅ Manager can manage most features  
✅ Cashier can process sales only  
✅ Employee can view only  
✅ Unauthorized users get 403 errors  
✅ No performance degradation  
✅ Policies are clean and maintainable  

---

## 📞 Need Help?

Check documentation:
- `AUTHORIZATION_DOCUMENTATION.md`
- `AUTHORIZATION_IMPLEMENTATION_GUIDE.md`

Review role permissions:
- `app/Enums/ShopMemberRole.php`

---

**System Status:** ✅ Ready for Testing  
**Last Updated:** November 7, 2025

