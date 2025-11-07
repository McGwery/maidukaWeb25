# Savings & Goals Feature - Complete Documentation

## Overview
A comprehensive savings management system that helps shops automatically save a percentage or fixed amount from daily profits and set financial goals. This feature enables shops to grow and achieve their business objectives.

---

## 🎯 Key Features

### 1. **Automatic Daily Savings**
- Save percentage (e.g., 10%) or fixed amount from daily profits
- Runs automatically every night at 3 AM
- Tracks daily profit and savings amount

### 2. **Manual Savings Management**
- Manual deposits to savings
- Manual withdrawals when needed
- Full transaction history

### 3. **Savings Goals**
- Set multiple financial goals
- Track progress towards each goal
- Auto-complete when goal is reached

### 4. **Auto-Withdrawal**
- Scheduled withdrawals (weekly, bi-weekly, monthly, quarterly)
- Withdraw when goal amount is reached
- Minimum withdrawal amount setting

### 5. **Complete Analytics**
- Current balance tracking
- Monthly savings breakdown
- Automatic vs manual savings
- Progress towards goals

---

## 📊 API Endpoints

### Settings Management

#### Get Savings Settings
```
GET /api/shops/{shop}/savings/settings
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "shopId": "uuid",
    "isEnabled": true,
    "savingsType": "percentage",
    "savingsPercentage": 10.00,
    "fixedAmount": 0.00,
    "targetAmount": 5000000.00,
    "targetDate": "2025-12-31",
    "withdrawalFrequency": "monthly",
    "autoWithdraw": false,
    "minimumWithdrawalAmount": 100000.00,
    "currentBalance": 850000.00,
    "totalSaved": 1200000.00,
    "totalWithdrawn": 350000.00,
    "lastSavingsDate": "2025-11-05",
    "lastWithdrawalDate": "2025-10-30",
    "progressPercentage": 17
  }
}
```

#### Update Savings Settings
```
PUT /api/shops/{shop}/savings/settings
```

**Request Body:**
```json
{
  "isEnabled": true,
  "savingsType": "percentage",
  "savingsPercentage": 15.00,
  "targetAmount": 5000000.00,
  "targetDate": "2025-12-31",
  "withdrawalFrequency": "monthly",
  "autoWithdraw": false,
  "minimumWithdrawalAmount": 100000.00
}
```

**Validation:**
- `isEnabled`: boolean
- `savingsType`: `percentage` or `fixed_amount`
- `savingsPercentage`: 0-100
- `fixedAmount`: >= 0
- `targetAmount`: >= 0
- `targetDate`: after today
- `withdrawalFrequency`: `none`, `weekly`, `bi_weekly`, `monthly`, `quarterly`, `when_goal_reached`
- `autoWithdraw`: boolean
- `minimumWithdrawalAmount`: >= 0

---

### Transactions

#### Manual Deposit
```
POST /api/shops/{shop}/savings/deposit
```

**Request Body:**
```json
{
  "amount": 50000.00,
  "description": "Extra savings from bonus sales",
  "savingsGoalId": "uuid" // optional
}
```

**Response:**
```json
{
  "success": true,
  "message": "Deposit successful",
  "data": {
    "currentBalance": 900000.00,
    "totalSaved": 1250000.00
  }
}
```

#### Withdraw
```
POST /api/shops/{shop}/savings/withdraw
```

**Request Body:**
```json
{
  "amount": 200000.00,
  "description": "Equipment purchase",
  "notes": "Bought new shelving units"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Withdrawal successful",
  "data": {
    "currentBalance": 700000.00,
    "totalWithdrawn": 550000.00
  }
}
```

#### Get Transaction History
```
GET /api/shops/{shop}/savings/transactions?type=deposit&limit=50
```

**Query Parameters:**
- `type`: `deposit`, `withdrawal`, or omit for all
- `limit`: Number of records (default: 50)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "type": "deposit",
      "amount": 50000.00,
      "balanceBefore": 800000.00,
      "balanceAfter": 850000.00,
      "transactionDate": "2025-11-05",
      "dailyProfit": 500000.00,
      "isAutomatic": true,
      "description": "Automatic savings from daily profit (percentage: 10%)",
      "notes": null,
      "processedBy": null,
      "savingsGoal": null,
      "createdAt": "2025-11-06T03:00:00+00:00"
    }
  ]
}
```

#### Get Savings Summary
```
GET /api/shops/{shop}/savings/summary
```

**Response:**
```json
{
  "success": true,
  "data": {
    "currentBalance": 850000.00,
    "totalSaved": 1200000.00,
    "totalWithdrawn": 350000.00,
    "automaticSavings": 900000.00,
    "manualSavings": 300000.00,
    "targetAmount": 5000000.00,
    "progressPercentage": 17,
    "isEnabled": true,
    "savingsType": "percentage",
    "withdrawalFrequency": "monthly",
    "monthlyBreakdown": [
      {
        "month": "2025-06",
        "deposits": 150000.00,
        "withdrawals": 0.00,
        "netSavings": 150000.00
      },
      {
        "month": "2025-07",
        "deposits": 180000.00,
        "withdrawals": 100000.00,
        "netSavings": 80000.00
      }
    ]
  }
}
```

---

### Goals Management

#### Create Savings Goal
```
POST /api/shops/{shop}/savings/goals
```

**Request Body:**
```json
{
  "name": "New Equipment",
  "description": "Purchase new refrigerator and shelving",
  "targetAmount": 2000000.00,
  "targetDate": "2026-03-31",
  "icon": "shopping_cart",
  "color": "#4CAF50",
  "priority": 1
}
```

**Response:**
```json
{
  "success": true,
  "message": "Savings goal created successfully",
  "data": {
    "id": "uuid",
    "name": "New Equipment",
    "targetAmount": 2000000.00,
    "currentAmount": 0.00,
    "progressPercentage": 0
  }
}
```

#### Get All Goals
```
GET /api/shops/{shop}/savings/goals?status=active
```

**Query Parameters:**
- `status`: `active`, `completed`, `cancelled`, `paused`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "New Equipment",
      "description": "Purchase new refrigerator and shelving",
      "targetAmount": 2000000.00,
      "targetDate": "2026-03-31",
      "currentAmount": 450000.00,
      "amountWithdrawn": 0.00,
      "progressPercentage": 23,
      "remainingAmount": 1550000.00,
      "status": "active",
      "completedAt": null,
      "startedAt": "2025-11-01",
      "icon": "shopping_cart",
      "color": "#4CAF50",
      "priority": 1,
      "isAchieved": false
    }
  ]
}
```

#### Update Goal
```
PUT /api/shops/{shop}/savings/goals/{goal}
```

**Request Body:**
```json
{
  "name": "New Equipment & Renovation",
  "targetAmount": 2500000.00,
  "status": "active"
}
```

#### Delete Goal
```
DELETE /api/shops/{shop}/savings/goals/{goal}
```

---

## 🔄 Automatic Processing

### Daily Savings Command
**Command:** `php artisan savings:process-daily`

**Scheduled:** Daily at 3:00 AM

**What it does:**
1. Finds all shops with savings enabled
2. Calculates yesterday's net profit (sales profit - expenses)
3. Calculates savings amount based on settings
4. Creates automatic deposit transaction
5. Updates savings balance
6. Checks if auto-withdrawal is due
7. Processes auto-withdrawal if conditions met

**Manual Testing:**
```bash
php artisan savings:process-daily
```

---

## 💡 How It Works

### Savings Types

#### 1. Percentage-Based
Shop saves a percentage of daily profit.

**Example:**
- Daily Profit: TZS 500,000
- Savings Percentage: 10%
- Amount Saved: TZS 50,000

#### 2. Fixed Amount
Shop saves a fixed amount daily regardless of profit.

**Example:**
- Daily Profit: TZS 500,000
- Fixed Amount: TZS 30,000
- Amount Saved: TZS 30,000 (if profit >= 30,000)

### Daily Profit Calculation

```
Daily Profit = Sales Profit - Daily Expenses
```

**Example:**
- Sales Revenue: TZS 2,000,000
- Cost of Goods: TZS 1,200,000
- Sales Profit: TZS 800,000
- Expenses (rent, utilities, etc.): TZS 150,000
- **Net Daily Profit: TZS 650,000**

If savings is 10%, shop saves: **TZS 65,000**

### Withdrawal Frequencies

| Frequency | Description | Auto-Withdraw Trigger |
|-----------|-------------|----------------------|
| `none` | No auto-withdrawal | Never |
| `weekly` | Every 7 days | After 7 days from last withdrawal |
| `bi_weekly` | Every 14 days | After 14 days from last withdrawal |
| `monthly` | Every 30 days | After 30 days from last withdrawal |
| `quarterly` | Every 90 days | After 90 days from last withdrawal |
| `when_goal_reached` | When target met | When currentBalance >= targetAmount |

---

## 📈 Use Cases

### Use Case 1: Shop Expansion
**Goal:** Save TZS 5,000,000 for opening a second location

**Setup:**
```json
{
  "isEnabled": true,
  "savingsType": "percentage",
  "savingsPercentage": 15.00,
  "targetAmount": 5000000.00,
  "targetDate": "2026-12-31",
  "withdrawalFrequency": "when_goal_reached"
}
```

**Result:**
- Saves 15% of daily profit automatically
- Tracks progress towards TZS 5,000,000
- Auto-withdraws when goal is reached

### Use Case 2: Emergency Fund
**Goal:** Build emergency reserve of TZS 1,000,000

**Setup:**
```json
{
  "isEnabled": true,
  "savingsType": "fixed_amount",
  "fixedAmount": 20000.00,
  "targetAmount": 1000000.00,
  "withdrawalFrequency": "none",
  "autoWithdraw": false
}
```

**Result:**
- Saves TZS 20,000 daily
- Reaches TZS 1,000,000 in ~50 days
- Manual withdrawal only

### Use Case 3: Monthly Equipment Budget
**Goal:** Save for monthly equipment upgrades

**Setup:**
```json
{
  "isEnabled": true,
  "savingsType": "percentage",
  "savingsPercentage": 10.00,
  "withdrawalFrequency": "monthly",
  "autoWithdraw": true,
  "minimumWithdrawalAmount": 100000.00
}
```

**Result:**
- Saves 10% daily
- Auto-withdraws monthly if balance >= TZS 100,000
- Provides consistent equipment budget

---

## 🎨 Kotlin Integration

All responses use **camelCase** for seamless integration:

```kotlin
data class SavingsSettingsResponse(
    val success: Boolean,
    val data: SavingsSettings
)

data class SavingsSettings(
    val id: String,
    val shopId: String,
    val isEnabled: Boolean,
    val savingsType: String,
    val savingsPercentage: Double,
    val fixedAmount: Double,
    val targetAmount: Double,
    val targetDate: String?,
    val withdrawalFrequency: String,
    val autoWithdraw: Boolean,
    val minimumWithdrawalAmount: Double,
    val currentBalance: Double,
    val totalSaved: Double,
    val totalWithdrawn: Double,
    val progressPercentage: Int
)

data class SavingsGoal(
    val id: String,
    val name: String,
    val description: String?,
    val targetAmount: Double,
    val currentAmount: Double,
    val progressPercentage: Int,
    val remainingAmount: Double,
    val status: String,
    val isAchieved: Boolean
)
```

---

## 🗄️ Database Tables

### shop_savings_settings
Main settings for each shop's savings configuration.

**Key Fields:**
- `is_enabled` - Enable/disable savings
- `savings_type` - `percentage` or `fixed_amount`
- `current_balance` - Current savings balance
- `total_saved` - Lifetime total saved
- `total_withdrawn` - Lifetime total withdrawn

### savings_transactions
All deposit and withdrawal transactions.

**Key Fields:**
- `type` - `deposit` or `withdrawal`
- `amount` - Transaction amount
- `balance_before/after` - Balance tracking
- `is_automatic` - Auto or manual transaction
- `daily_profit` - Profit that triggered savings

### savings_goals
Individual savings goals for shops.

**Key Fields:**
- `name` - Goal name
- `target_amount` - Goal target
- `current_amount` - Current progress
- `progress_percentage` - Auto-calculated progress
- `status` - `active`, `completed`, `cancelled`, `paused`

---

## 🔐 Security & Validation

### Authorization
- All endpoints require authentication
- Shop-scoped: Users can only access their active shop's data
- Goal ownership verified on update/delete

### Validation
- Amount fields: positive numbers only
- Percentages: 0-100 range
- Dates: proper format and logic
- Withdrawal: balance check before processing

### Transaction Safety
- All financial operations use database transactions
- Balance calculated atomically
- Rollback on any error

---

## 📊 Financial Reports Integration

The savings data is now included in the **Financial Report**:

```
GET /api/shops/{shop}/reports/financial?dateFilter=this_month
```

**Includes:**
- Savings deposits in cash out calculation
- Net profit after savings deduction
- Savings balance in financial summary

---

## 🎯 Benefits

### For Shop Owners
✅ **Disciplined Saving** - Automatic, no manual effort  
✅ **Goal Tracking** - Visual progress towards objectives  
✅ **Flexible Withdrawal** - Access funds when needed  
✅ **Growth Planning** - Structured financial planning  

### For Business Growth
✅ **Capital Accumulation** - Build reserves for expansion  
✅ **Emergency Preparedness** - Financial safety net  
✅ **Equipment Upgrades** - Regular improvement budget  
✅ **Opportunity Capture** - Funds ready for opportunities  

---

## 🚀 Quick Start

### 1. Enable Savings
```bash
PUT /api/shops/{shop}/savings/settings
{
  "isEnabled": true,
  "savingsType": "percentage",
  "savingsPercentage": 10.00
}
```

### 2. Set a Goal (Optional)
```bash
POST /api/shops/{shop}/savings/goals
{
  "name": "Shop Expansion",
  "targetAmount": 5000000.00,
  "targetDate": "2026-06-30"
}
```

### 3. Monitor Progress
```bash
GET /api/shops/{shop}/savings/summary
```

### 4. Withdraw When Ready
```bash
POST /api/shops/{shop}/savings/withdraw
{
  "amount": 500000.00,
  "description": "Equipment purchase"
}
```

---

## 📝 Notes

- Savings are processed at 3 AM daily
- Only positive profits trigger automatic savings
- Manual deposits can be made anytime
- Multiple goals can be tracked simultaneously
- All amounts in shop's local currency

---

**Status:** ✅ Complete  
**Version:** 1.0  
**Date:** November 6, 2025

