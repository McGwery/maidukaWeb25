# Savings API - Resources & Requests Quick Reference

## Form Request Classes

### 1. UpdateSavingsSettingsRequest
**Usage:** Update shop savings configuration
```php
POST /api/savings/settings
```

**Fields:**
- `isEnabled` (boolean, optional)
- `savingsType` (string, optional) - "percentage" or "fixed_amount"
- `savingsPercentage` (numeric, optional) - 0 to 100
- `fixedAmount` (numeric, optional) - min 0
- `targetAmount` (numeric, optional) - min 0
- `targetDate` (date, optional) - must be future
- `withdrawalFrequency` (string, optional) - "none", "weekly", "bi_weekly", "monthly", "quarterly", "when_goal_reached"
- `autoWithdraw` (boolean, optional)
- `minimumWithdrawalAmount` (numeric, optional) - min 0

---

### 2. SavingsDepositRequest
**Usage:** Make a manual deposit to savings
```php
POST /api/savings/deposit
```

**Fields:**
- `amount` (numeric, **required**) - min 0.01
- `description` (string, optional) - max 500 characters
- `savingsGoalId` (uuid, optional) - must exist in savings_goals table

---

### 3. SavingsWithdrawalRequest
**Usage:** Withdraw money from savings
```php
POST /api/savings/withdraw
```

**Fields:**
- `amount` (numeric, **required**) - min 0.01
- `description` (string, optional) - max 500 characters
- `notes` (string, optional) - max 1000 characters

---

### 4. CreateSavingsGoalRequest
**Usage:** Create a new savings goal
```php
POST /api/savings/goals
```

**Fields:**
- `name` (string, **required**) - max 255 characters
- `description` (string, optional) - max 1000 characters
- `targetAmount` (numeric, **required**) - min 1
- `targetDate` (date, optional) - must be future
- `icon` (string, optional) - max 50 characters
- `color` (string, optional) - max 20 characters
- `priority` (integer, optional) - min 0

---

### 5. UpdateSavingsGoalRequest
**Usage:** Update an existing savings goal
```php
PUT /api/savings/goals/{goalId}
```

**Fields:** (all optional)
- `name` (string) - max 255 characters
- `description` (string) - max 1000 characters
- `targetAmount` (numeric) - min 1
- `targetDate` (date)
- `status` (string) - "active", "completed", "cancelled", "paused"
- `icon` (string) - max 50 characters
- `color` (string) - max 20 characters
- `priority` (integer) - min 0

---

## API Resource Classes

### 1. ShopSavingsSettingResource
**Transforms:** ShopSavingsSetting model

**Output Fields:**
```json
{
  "id": "uuid",
  "shopId": "uuid",
  "isEnabled": boolean,
  "savingsType": "percentage|fixed_amount",
  "savingsPercentage": number,
  "fixedAmount": number,
  "targetAmount": number,
  "targetDate": "YYYY-MM-DD",
  "withdrawalFrequency": "string",
  "autoWithdraw": boolean,
  "minimumWithdrawalAmount": number,
  "currentBalance": number,
  "totalSaved": number,
  "totalWithdrawn": number,
  "lastSavingsDate": "YYYY-MM-DD",
  "lastWithdrawalDate": "YYYY-MM-DD",
  "progressPercentage": number,
  "createdAt": "ISO 8601 string",
  "updatedAt": "ISO 8601 string"
}
```

---

### 2. SavingsTransactionResource
**Transforms:** SavingsTransaction model

**Output Fields:**
```json
{
  "id": "uuid",
  "shopId": "uuid",
  "savingsGoalId": "uuid|null",
  "type": "deposit|withdrawal",
  "amount": number,
  "balanceBefore": number,
  "balanceAfter": number,
  "transactionDate": "YYYY-MM-DD",
  "dailyProfit": number,
  "isAutomatic": boolean,
  "description": "string|null",
  "notes": "string|null",
  "processedBy": {
    "id": "uuid",
    "name": "string"
  },
  "savingsGoal": {
    "id": "uuid",
    "name": "string"
  },
  "createdAt": "ISO 8601 string",
  "updatedAt": "ISO 8601 string"
}
```

**Note:** `processedBy` and `savingsGoal` only included when relationships are loaded.

---

### 3. SavingsGoalResource
**Transforms:** SavingsGoal model

**Output Fields:**
```json
{
  "id": "uuid",
  "shopId": "uuid",
  "name": "string",
  "description": "string|null",
  "targetAmount": number,
  "targetDate": "YYYY-MM-DD|null",
  "currentAmount": number,
  "amountWithdrawn": number,
  "progressPercentage": number,
  "remainingAmount": number,
  "status": "active|completed|cancelled|paused",
  "completedAt": "YYYY-MM-DD|null",
  "startedAt": "YYYY-MM-DD|null",
  "icon": "string|null",
  "color": "string|null",
  "priority": number,
  "isAchieved": boolean,
  "createdAt": "ISO 8601 string",
  "updatedAt": "ISO 8601 string"
}
```

---

## Usage Examples

### Creating a Savings Goal
```php
// In your controller or test
$request = CreateSavingsGoalRequest::create([
    'name' => 'New Equipment',
    'targetAmount' => 50000,
    'targetDate' => '2025-12-31',
    'priority' => 1
]);

// The controller will automatically validate
public function createGoal(CreateSavingsGoalRequest $request): JsonResponse
{
    $validated = $request->validated(); // Already validated!
    // ... create goal
}
```

### Formatting Resources
```php
// Single resource
$setting = ShopSavingsSetting::find($id);
return new ShopSavingsSettingResource($setting);

// Collection
$goals = SavingsGoal::where('shop_id', $shopId)->get();
return SavingsGoalResource::collection($goals);
```

### With Eager Loading
```php
$transactions = SavingsTransaction::with(['processedBy', 'savingsGoal'])
    ->where('shop_id', $shopId)
    ->get();
    
return SavingsTransactionResource::collection($transactions);
// Will include processedBy and savingsGoal nested objects
```

---

## Standard API Response Format

All endpoints return this structure:

### Success Response
```json
{
  "success": true,
  "message": "Operation successful message",
  "data": {
    // Resource-formatted data here
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Validation error messages"]
  }
}
```

---

## Testing Examples

### Testing Form Requests
```php
// Feature test
$response = $this->postJson('/api/savings/deposit', [
    'amount' => 1000,
    'description' => 'Manual deposit'
]);

$response->assertStatus(200)
    ->assertJsonStructure([
        'success',
        'message',
        'data' => [
            'id',
            'amount',
            'balanceAfter'
        ]
    ]);
```

### Testing Resources
```php
// Unit test
$transaction = SavingsTransaction::factory()->create();
$resource = new SavingsTransactionResource($transaction);
$array = $resource->toArray(request());

$this->assertArrayHasKey('amount', $array);
$this->assertIsFloat($array['amount']);
```

---

## Benefits Summary

✅ **Type Safety** - IDE autocomplete and type hints  
✅ **Validation** - Centralized, reusable validation logic  
✅ **Consistency** - All responses use camelCase  
✅ **Maintainability** - Easy to update in one place  
✅ **Testability** - Each component can be tested independently  
✅ **Documentation** - Self-documenting code structure  
✅ **Kotlin Ready** - All fields formatted for mobile app consumption  

---

## File Locations

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── SavingsController.php (refactored)
│   ├── Requests/
│   │   ├── CreateSavingsGoalRequest.php
│   │   ├── UpdateSavingsGoalRequest.php
│   │   ├── UpdateSavingsSettingsRequest.php
│   │   ├── SavingsDepositRequest.php
│   │   └── SavingsWithdrawalRequest.php
│   └── Resources/
│       ├── ShopSavingsSettingResource.php
│       ├── SavingsTransactionResource.php
│       └── SavingsGoalResource.php
```

