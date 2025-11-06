# Savings Feature - Quick Reference

## 🚀 Quick Setup

### Enable Savings (10% of daily profit)
```bash
PUT /api/shops/{shop}/savings/settings
{
  "isEnabled": true,
  "savingsType": "percentage",
  "savingsPercentage": 10.00
}
```

### Enable Savings (Fixed TZS 20,000 daily)
```bash
PUT /api/shops/{shop}/savings/settings
{
  "isEnabled": true,
  "savingsType": "fixed_amount",
  "fixedAmount": 20000.00
}
```

---

## 📊 Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| **GET** | `/savings/settings` | Get savings configuration |
| **PUT** | `/savings/settings` | Update settings |
| **POST** | `/savings/deposit` | Manual deposit |
| **POST** | `/savings/withdraw` | Withdraw funds |
| **GET** | `/savings/transactions` | Transaction history |
| **GET** | `/savings/summary` | Analytics & summary |
| **GET** | `/savings/goals` | List all goals |
| **POST** | `/savings/goals` | Create new goal |
| **PUT** | `/savings/goals/{id}` | Update goal |
| **DELETE** | `/savings/goals/{id}` | Delete goal |

---

## 💰 Common Operations

### Check Balance
```bash
GET /api/shops/{shop}/savings/settings

Response: { currentBalance: 850000.00 }
```

### Manual Deposit
```bash
POST /api/shops/{shop}/savings/deposit
{
  "amount": 50000.00,
  "description": "Bonus savings"
}
```

### Withdraw Money
```bash
POST /api/shops/{shop}/savings/withdraw
{
  "amount": 100000.00,
  "description": "Equipment purchase"
}
```

### Create Goal
```bash
POST /api/shops/{shop}/savings/goals
{
  "name": "New Freezer",
  "targetAmount": 1500000.00,
  "targetDate": "2026-03-31"
}
```

---

## ⚙️ Settings Options

### Savings Type
- `percentage` - Save % of daily profit
- `fixed_amount` - Save fixed amount daily

### Withdrawal Frequency
- `none` - Manual only
- `weekly` - Every 7 days
- `bi_weekly` - Every 14 days
- `monthly` - Every 30 days
- `quarterly` - Every 90 days
- `when_goal_reached` - When target met

---

## 🎯 Example Configurations

### Configuration 1: Shop Expansion Fund
```json
{
  "isEnabled": true,
  "savingsType": "percentage",
  "savingsPercentage": 15.00,
  "targetAmount": 5000000.00,
  "withdrawalFrequency": "when_goal_reached"
}
```
**Result:** Saves 15% daily, withdraws when TZS 5M reached

### Configuration 2: Monthly Equipment Budget
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
**Result:** Saves 10% daily, auto-withdraws monthly if ≥ TZS 100K

### Configuration 3: Emergency Fund
```json
{
  "isEnabled": true,
  "savingsType": "fixed_amount",
  "fixedAmount": 20000.00,
  "targetAmount": 1000000.00,
  "withdrawalFrequency": "none"
}
```
**Result:** Saves TZS 20K daily, manual withdrawal only

---

## 📈 How Savings Work

### Daily Process (3 AM)
1. Calculate yesterday's profit: `Sales Profit - Expenses`
2. Calculate savings amount based on type
3. Create automatic deposit transaction
4. Update current balance
5. Check auto-withdrawal conditions
6. Process withdrawal if due

### Profit Calculation
```
Net Profit = (Revenue - COGS) - Expenses
```

**Example:**
- Revenue: TZS 2,000,000
- COGS: TZS 1,200,000
- Gross Profit: TZS 800,000
- Expenses: TZS 150,000
- **Net Profit: TZS 650,000**

If saving 10%: **TZS 65,000 saved**

---

## 🔔 Transaction Types

### Automatic Deposits
- Triggered by daily schedule
- Based on profit calculation
- `isAutomatic: true`
- Includes daily profit amount

### Manual Deposits
- User-initiated
- Any amount
- `isAutomatic: false`
- Optional goal linking

### Automatic Withdrawals
- Based on frequency setting
- Requires auto_withdraw enabled
- Checks minimum amount
- Withdraws full balance

### Manual Withdrawals
- User-initiated
- Any amount (≤ balance)
- Requires description
- Optional notes

---

## 💡 Pro Tips

### Tip 1: Start Small
Begin with 5-10% savings to test the system.

### Tip 2: Set Realistic Goals
Calculate how long it takes to reach goals:
```
Days = Target Amount ÷ (Daily Profit × Savings %)
```

### Tip 3: Use Multiple Goals
Track different objectives:
- Emergency fund
- Equipment upgrades  
- Expansion capital
- Employee bonuses

### Tip 4: Monitor Progress
Check summary regularly:
```bash
GET /api/shops/{shop}/savings/summary
```

### Tip 5: Adjust as Needed
Update settings based on business performance:
```bash
PUT /api/shops/{shop}/savings/settings
```

---

## 🎨 Response Format (camelCase)

All responses use camelCase for Kotlin:
```json
{
  "currentBalance": 850000.00,
  "totalSaved": 1200000.00,
  "savingsPercentage": 10.00,
  "withdrawalFrequency": "monthly",
  "progressPercentage": 17
}
```

---

## 🔧 Commands

### Test Daily Savings
```bash
php artisan savings:process-daily
```

### Schedule Status
```bash
php artisan schedule:list | grep savings
```

---

## 📊 Integration with Reports

Financial report now includes savings:
```bash
GET /api/shops/{shop}/reports/financial?dateFilter=this_month
```

Shows:
- Savings deposits
- Impact on cash flow
- Net profit after savings

---

## ⚠️ Important Notes

- Savings processed at **3:00 AM daily**
- Only **positive profits** trigger auto-savings
- **Manual operations** available anytime
- **Multiple goals** can be active simultaneously
- All **amounts** in shop's local currency
- **Transaction history** kept permanently

---

## 🆘 Common Issues

### Issue: Not saving automatically
**Check:**
1. Is `isEnabled` true?
2. Is yesterday's profit > 0?
3. Is scheduler running?

### Issue: Can't withdraw
**Check:**
1. Is balance sufficient?
2. Is amount valid?
3. Is shop active?

### Issue: Goal not progressing
**Check:**
1. Are deposits linked to goal?
2. Is goal status active?
3. Check transaction history

---

## 📱 Mobile App Integration

Perfect for Android/Kotlin apps:

```kotlin
// Get settings
val settings = api.getSavingsSettings(shopId)

// Update settings
api.updateSavingsSettings(
    shopId,
    SavingsSettings(
        isEnabled = true,
        savingsType = "percentage",
        savingsPercentage = 10.0
    )
)

// Deposit
api.depositToSavings(
    shopId,
    DepositRequest(
        amount = 50000.0,
        description = "Bonus savings"
    )
)

// Check balance
val summary = api.getSavingsSummary(shopId)
println("Balance: ${summary.currentBalance}")
```

---

**Files Created:**
- ✅ 3 Migrations
- ✅ 3 Models
- ✅ 1 Controller (11 methods)
- ✅ 1 Command
- ✅ Routes
- ✅ Documentation

**Status:** ✅ Complete  
**Version:** 1.0  
**Date:** November 6, 2025

