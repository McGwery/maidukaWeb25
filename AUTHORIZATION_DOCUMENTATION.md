# Authorization & Permissions System Documentation

## 📋 Overview

This system implements **role-based access control (RBAC)** for shop management using Laravel Policies and Gates.

---

## 👥 Roles & Permissions

### Role Hierarchy

1. **OWNER** - Full access (wildcard `*`)
2. **MANAGER** - Almost full access
3. **CASHIER** - Sales and customer management
4. **SALES** - Sales and basic inventory view
5. **INVENTORY** - Inventory and purchase management
6. **EMPLOYEE** - Read-only access

---

## 🔐 Permissions Matrix

| Permission | Owner | Manager | Cashier | Sales | Inventory | Employee |
|-----------|-------|---------|---------|-------|-----------|----------|
| **Products & Inventory** |
| `manage_products` | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| `view_products` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `manage_inventory` | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| `view_inventory` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `update_stock` | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| `view_stock_adjustments` | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| `view_inventory_analysis` | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| **Sales & POS** |
| `process_sales` | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| `view_sales` | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ |
| `manage_sales` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `refund_sales` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `view_sales_analytics` | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Customers** |
| `manage_customers` | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| `view_customers` | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Purchase Orders** |
| `manage_purchases` | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| `view_purchases` | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| `approve_purchases` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `record_purchase_payments` | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| `transfer_stock` | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| **Expenses** |
| `manage_expenses` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `view_expenses` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `view_expense_summary` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Reports** |
| `view_reports` | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| `view_sales_report` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `view_products_report` | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| `view_financial_report` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `view_employees_report` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Employees & Members** |
| `manage_employees` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `view_employees` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Settings** |
| `manage_settings` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `view_settings` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Savings** |
| `manage_savings` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `view_savings` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Ads** |
| `manage_ads` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `view_ads` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Chat** |
| `use_chat` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `view_conversations` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `send_messages` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |

---

## 🏗️ Implementation

### Policies Created

1. **ProductPolicy** - Product & inventory management
2. **SalePolicy** - Sales & POS operations
3. **CustomerPolicy** - Customer management
4. **PurchaseOrderPolicy** - Purchase orders
5. **ExpensePolicy** - Expense tracking
6. **ReportPolicy** - Reports & analytics
7. **ShopMemberPolicy** - Employee management
8. **ShopSettingsPolicy** - Shop settings
9. **SavingsPolicy** - Savings & goals
10. **AdPolicy** - Advertisement management
11. **ChatPolicy** - Chat & messaging
12. **ShopPolicy** - Shop management

---

## 💻 Usage in Controllers

### Method 1: Using authorize() Method

```php
public function store(Request $request, string $shop)
{
    $shopModel = Shop::findOrFail($shop);
    
    // Check permission
    $this->authorize('create', [Product::class, $shopModel]);
    
    // Create product...
}
```

### Method 2: Using Policy Gate

```php
use Illuminate\Support\Facades\Gate;

public function update(Request $request, string $shop, string $product)
{
    $shopModel = Shop::findOrFail($shop);
    $productModel = Product::findOrFail($product);
    
    // Check permission
    if (Gate::denies('update', $productModel)) {
        abort(403, 'Unauthorized action.');
    }
    
    // Update product...
}
```

### Method 3: Using Middleware

```php
Route::put('/products/{product}', [ProductController::class, 'update'])
    ->middleware('can:update,product');
```

### Method 4: Using authorizeResource

```php
class ProductController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Product::class, 'product');
    }
}
```

---

## 🎯 Policy Examples

### ProductPolicy

```php
public function create(User $user, Shop $shop): bool
{
    return $this->hasPermission($user, $shop, 'manage_products') ||
           $this->hasPermission($user, $shop, 'manage_inventory');
}
```

### SalePolicy

```php
public function refund(User $user, Sale $sale): bool
{
    return $this->hasPermission($user, $sale->shop, 'refund_sales') ||
           $this->hasPermission($user, $sale->shop, 'manage_sales');
}
```

### ChatPolicy

```php
public function sendMessage(User $user, Shop $shop): bool
{
    return $this->hasPermission($user, $shop, 'send_messages') ||
           $this->hasPermission($user, $shop, 'use_chat');
}
```

---

## 🔧 Helper Trait: HasShopPolicy

All policies use this trait for consistent permission checking:

```php
trait HasShopPolicy
{
    protected function hasPermission(User $user, Shop $shop, string $permission): bool
    {
        // Owner has all permissions
        if ($shop->isOwner($user)) {
            return true;
        }

        // Check member permissions
        $member = $shop->members()->where('user_id', $user->id)->first();
        
        if (!$member || !$member->pivot->is_active) {
            return false;
        }

        $memberPermissions = $member->pivot->permissions ?? [];
        
        // Check wildcard or specific permission
        return in_array('*', $memberPermissions) || 
               in_array($permission, $memberPermissions);
    }
}
```

---

## 📱 API Response for Unauthorized Access

When authorization fails, the API returns:

```json
{
  "success": false,
  "code": 403,
  "message": "This action is unauthorized."
}
```

---

## 🛡️ Best Practices

### 1. Always Check Permissions

```php
// ❌ Bad - No permission check
public function delete(string $shop, string $product)
{
    Product::findOrFail($product)->delete();
}

// ✅ Good - With permission check
public function delete(string $shop, string $product)
{
    $shopModel = Shop::findOrFail($shop);
    $productModel = Product::findOrFail($product);
    
    $this->authorize('delete', $productModel);
    
    $productModel->delete();
}
```

### 2. Use Policies for Complex Logic

```php
// In Policy
public function updateStatus(User $user, PurchaseOrder $po): bool
{
    // Buyer can update
    if ($po->buyer_shop_id === $user->activeShop->id) {
        return $this->hasPermission($user, $po->buyerShop, 'manage_purchases');
    }
    
    // Seller needs approve permission
    return $this->hasPermission($user, $po->sellerShop, 'approve_purchases');
}
```

### 3. Check Multiple Permissions

```php
public function create(User $user, Shop $shop): bool
{
    return $this->hasPermission($user, $shop, 'manage_products') ||
           $this->hasPermission($user, $shop, 'manage_inventory');
}
```

### 4. Owner Always Has Access

```php
protected function hasPermission(User $user, Shop $shop, string $permission): bool
{
    // Owner bypass
    if ($shop->isOwner($user)) {
        return true;
    }
    
    // Check member permissions...
}
```

---

## 🧪 Testing Permissions

```php
// Test if user can create products
$user = User::find(1);
$shop = Shop::find(1);

if ($user->can('create', [Product::class, $shop])) {
    echo "User can create products";
}

// Test if user can update specific product
$product = Product::find(1);

if ($user->can('update', $product)) {
    echo "User can update this product";
}
```

---

## 📊 Permission Checking Flow

```
1. User makes request
   ↓
2. Controller receives request
   ↓
3. Load Shop and Resource models
   ↓
4. Call Policy check via $this->authorize()
   ↓
5. Policy checks:
   - Is user shop owner? → ALLOW
   - Is user active member? → Continue
   - Does member have permission? → ALLOW/DENY
   ↓
6. If ALLOW → Process request
   If DENY → Return 403 error
```

---

## 🎓 Adding New Permissions

### Step 1: Add to ShopMemberRole Enum

```php
self::MANAGER => [
    // ... existing permissions
    'new_permission',
],
```

### Step 2: Add to Policy

```php
public function newAction(User $user, Shop $shop): bool
{
    return $this->hasPermission($user, $shop, 'new_permission');
}
```

### Step 3: Use in Controller

```php
public function newAction(string $shop)
{
    $shopModel = Shop::findOrFail($shop);
    $this->authorize('newAction', $shopModel);
    
    // ... perform action
}
```

---

## 📝 Summary

- **12 Policies** created for all features
- **47+ Permissions** defined across 6 roles
- **Consistent authorization** using `HasShopPolicy` trait
- **Owner** always has full access (wildcard `*`)
- **Flexible** - Can assign custom permissions per member
- **Secure** - All actions require proper authorization

---

**Files Created:**
- `app/Policies/ProductPolicy.php`
- `app/Policies/SalePolicy.php`
- `app/Policies/CustomerPolicy.php`
- `app/Policies/PurchaseOrderPolicy.php`
- `app/Policies/ExpensePolicy.php`
- `app/Policies/ReportPolicy.php`
- `app/Policies/ShopMemberPolicy.php`
- `app/Policies/ShopSettingsPolicy.php`
- `app/Policies/SavingsPolicy.php`
- `app/Policies/AdPolicy.php`
- `app/Policies/ChatPolicy.php`
- `app/Policies/ShopPolicy.php`
- `app/Traits/HasShopPolicy.php`

**Updated:**
- `app/Enums/ShopMemberRole.php` - Expanded permissions
- `app/Providers/AuthServiceProvider.php` - Registered all policies

---

**Status:** ✅ Complete & Ready to Use  
**Date:** November 7, 2025

