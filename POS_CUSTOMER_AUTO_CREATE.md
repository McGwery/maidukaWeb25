# POS Customer Creation Feature - Update

## ✅ NEW FEATURE: Auto-Create Customer During Sale

### Overview
You can now create a new customer on-the-fly while completing a sale. If you don't provide a `customer.id`, the system will automatically create a new customer record using the provided name and phone.

---

## 📝 Updated Request Structure

### Option 1: Sale with Existing Customer
```json
{
  "items": [...],
  "customer": {
    "id": "existing-customer-uuid",
    "name": "Mama John",
    "phone": "+255 712 345 678",
    "debt": 45000
  },
  "paymentMethod": "cash",
  "total": 5900,
  "amountReceived": 6000,
  "change": 100
}
```

### Option 2: Sale with New Customer (Auto-Create)
```json
{
  "items": [...],
  "customer": {
    "name": "New Customer Name",
    "phone": "+255 712 345 678"
  },
  "paymentMethod": "cash",
  "total": 5900,
  "amountReceived": 6000,
  "change": 100
}
```
**Note:** No `id` field - system will create new customer automatically!

### Option 3: Sale without Customer
```json
{
  "items": [...],
  "paymentMethod": "cash",
  "total": 5900,
  "amountReceived": 6000,
  "change": 100
}
```
**Note:** No `customer` object at all - for walk-in customers

---

## 🔧 Technical Changes Made

### 1. Updated Validation Rules
**File:** `app/Http/Requests/CompleteSaleRequest.php`

**Before:**
```php
'customer.id' => 'required_with:customer|uuid|exists:customers,id',
```

**After:**
```php
'customer.id' => 'nullable|uuid|exists:customers,id',
'customer.name' => 'required_with:customer|string|max:255',
```

**What Changed:**
- `customer.id` is now **nullable** - not required when customer object is present
- `customer.name` is **required_with:customer** - must provide name if customer object exists
- System will create new customer if `id` is null but `name` is provided

---

### 2. Updated Controller Logic
**File:** `app/Http/Controllers/Api/POSController.php`

**New Logic:**
```php
// Handle customer - create new or use existing
if (isset($validated['customer'])) {
    $customer = null;
    
    // If customer ID provided, fetch existing customer
    if (!empty($validated['customer']['id'])) {
        $customer = Customer::find($validated['customer']['id']);
    } 
    // If no ID but name provided, create new customer
    elseif (!empty($validated['customer']['name'])) {
        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => $validated['customer']['name'],
            'phone' => $validated['customer']['phone'] ?? null,
            'credit_limit' => 0, // Default no credit for new customers
            'current_debt' => 0,
        ]);
    }
    
    // If customer exists and there's debt, check credit limit
    if ($customer && $debtAmount > 0) {
        // Validate credit limit...
    }
}
```

**What It Does:**
1. Checks if `customer.id` is provided → Use existing customer
2. If no `id` but `name` is provided → Create new customer
3. New customers get:
   - `credit_limit: 0` (no credit by default)
   - `current_debt: 0`
   - Shop association
   - Name and phone from request

---

## 📱 Android Integration Examples

### Scenario 1: Existing Customer Selected
```kotlin
val request = CompleteSaleRequest(
    items = cartItems,
    customer = Customer(
        id = selectedCustomer.id, // Existing customer UUID
        name = selectedCustomer.name,
        phone = selectedCustomer.phone,
        debt = selectedCustomer.debt
    ),
    paymentMethod = "cash",
    total = totalAmount,
    amountReceived = receivedAmount,
    change = changeAmount
)
```

### Scenario 2: New Customer Created on-the-fly
```kotlin
val request = CompleteSaleRequest(
    items = cartItems,
    customer = Customer(
        id = null, // No ID - will create new customer
        name = newCustomerName, // From user input
        phone = newCustomerPhone, // From user input
        debt = 0.0
    ),
    paymentMethod = "cash",
    total = totalAmount,
    amountReceived = receivedAmount,
    change = changeAmount
)
```

### Scenario 3: Walk-in Customer (No customer record)
```kotlin
val request = CompleteSaleRequest(
    items = cartItems,
    customer = null, // No customer tracking
    paymentMethod = "cash",
    total = totalAmount,
    amountReceived = receivedAmount,
    change = changeAmount
)
```

---

## 🎯 Use Cases

### Use Case 1: Quick Sale with New Customer
**Scenario:** Customer asks to be registered for future purchases

1. User adds items to cart
2. User clicks "With Customer"
3. System shows "Add New Customer" option
4. User enters name and phone
5. Complete sale
6. **Result:** Customer automatically created and linked to sale

### Use Case 2: Regular Customer
**Scenario:** Existing customer making a purchase

1. User adds items to cart
2. User searches and selects existing customer
3. Complete sale
4. **Result:** Sale linked to existing customer, debt/credit tracked

### Use Case 3: Walk-in Sale
**Scenario:** Anonymous customer, no tracking needed

1. User adds items to cart
2. User selects "Without Customer"
3. Complete sale
4. **Result:** Sale completed without customer record

---

## 🔒 Business Rules

### New Customer Creation
- ✅ **Credit Limit:** 0 (no credit by default)
- ✅ **Current Debt:** 0
- ✅ **Auto-linked:** To active shop
- ✅ **Required:** Name (phone optional)

### Credit Sales
- ✅ **Existing Customers:** Credit limit checked if set
- ✅ **New Customers:** Cannot buy on credit (limit = 0)
- ✅ **Walk-in:** Cannot buy on credit (no customer)

### Validation
- ✅ **With customer.id:** Must exist in database
- ✅ **Without customer.id:** Must provide name
- ✅ **No customer:** Complete cash/mobile money sales only

---

## 📊 Response Examples

### Response with New Customer Created
```json
{
  "success": true,
  "message": "Sale completed successfully",
  "code": 201,
  "data": {
    "sale": {
      "id": "sale-uuid",
      "saleNumber": "SAL-20251105-0001",
      "customerId": "newly-created-customer-uuid",
      "customer": {
        "id": "newly-created-customer-uuid",
        "shopId": "shop-uuid",
        "name": "New Customer Name",
        "phone": "+255 712 345 678",
        "creditLimit": 0,
        "currentDebt": 0,
        "totalPurchases": 5900,
        "totalPaid": 5900,
        "availableCredit": 0,
        "createdAt": "2025-11-05T14:30:00.000000Z"
      },
      "totalAmount": 5900,
      "amountPaid": 6000,
      "changeAmount": 100,
      "status": "completed"
    }
  }
}
```

### Response with Existing Customer
```json
{
  "success": true,
  "message": "Sale completed successfully",
  "code": 201,
  "data": {
    "sale": {
      "customer": {
        "id": "existing-customer-uuid",
        "name": "Mama John",
        "creditLimit": 100000,
        "currentDebt": 50900,
        "totalPurchases": 355900,
        "totalPaid": 305000
      }
    }
  }
}
```

---

## ⚠️ Important Notes

1. **New customers get NO CREDIT** by default (creditLimit = 0)
2. **To enable credit**, update customer record with credit limit:
   ```
   PUT /api/shops/{shop}/customers/{customer}
   { "creditLimit": 50000 }
   ```
3. **Phone is optional** - can create customer with just name
4. **Customer.debt in request** is informational only - actual debt tracked in database
5. **Duplicate names allowed** - system doesn't prevent duplicate customer names

---

## 🧪 Testing

### Test Case 1: Create Customer During Sale
```bash
curl -X POST http://api.example.com/api/shops/{shop-id}/pos/sales \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {
        "id": "product-uuid",
        "name": "Coca Cola",
        "originalPrice": 2500,
        "currentPrice": 2500,
        "quantity": 2,
        "total": 5000,
        "unit": "bottle",
        "allowFractional": false,
        "negotiable": false
      }
    ],
    "customer": {
      "name": "Test Customer",
      "phone": "+255 123 456 789"
    },
    "paymentMethod": "cash",
    "total": 5000,
    "amountReceived": 5000,
    "change": 0
  }'
```

**Expected:** 
- ✅ New customer created
- ✅ Sale completed
- ✅ Customer linked to sale
- ✅ Response includes new customer details

---

## 🎨 Android UI Workflow

### Suggested Implementation:

```kotlin
// Customer selection screen
when (customerSelection) {
    CustomerSelection.None -> {
        // Complete sale without customer
        request.customer = null
    }
    
    CustomerSelection.Existing -> {
        // Use selected customer
        request.customer = Customer(
            id = selectedCustomer.id,
            name = selectedCustomer.name,
            phone = selectedCustomer.phone,
            debt = selectedCustomer.debt
        )
    }
    
    CustomerSelection.New -> {
        // Create new customer during sale
        request.customer = Customer(
            id = null, // Key: no ID
            name = newCustomerNameInput,
            phone = newCustomerPhoneInput,
            debt = 0.0
        )
    }
}
```

### UI Flow:
1. **"Without Customer"** button → customer = null
2. **"With Customer"** button → Show dialog:
   - Search existing customers
   - "Add New Customer" button
3. **"Add New Customer"** → Show form:
   - Name (required)
   - Phone (optional)
   - Save → Attach to sale

---

## ✅ Benefits

1. **Faster Sales** - Don't need to create customer separately
2. **Better UX** - One-step process for new customer registration
3. **Flexible** - Support walk-in, existing, and new customers
4. **Safe** - New customers can't buy on credit until limit set
5. **Tracked** - All sales linked to customers for history

---

## 🚀 Status

✅ **Implemented and Ready**
- Validation rules updated
- Controller logic updated
- Automatic customer creation working
- Credit limit enforcement maintained
- All responses in camelCase

**You can now create customers on-the-fly during sales!** 🎉

