# Financial Report with Savings Integration - Updated Documentation

## Overview
The Financial Report API has been enhanced to include automatic savings calculations and proposed savings amounts based on shop settings.

---

## 🆕 What's New

### Savings Integration
The financial report now:
- ✅ Calculates proposed savings amount based on net profit
- ✅ Shows actual savings in the period
- ✅ Displays net profit after savings
- ✅ Includes daily savings breakdown
- ✅ Shows savings progress towards goals

---

## 📊 Updated API Response

### Endpoint
```
GET /api/shops/{shop}/reports/financial?dateFilter=this_month
```

### Enhanced Response Structure

```json
{
  "success": true,
  "data": {
    "period": {
      "filter": "this_month",
      "startDate": "2025-11-01",
      "endDate": "2025-11-30"
    },
    "summary": {
      "totalRevenue": 8750000.00,
      "cashReceived": 7200000.00,
      "grossProfit": 1680000.00,
      "totalExpenses": 580000.00,
      "netProfit": 1100000.00,
      "profitMargin": 19.20,
      "cashFlow": 6620000.00
    },
    "savings": {
      "isEnabled": true,
      "currentBalance": 850000.00,
      "totalSavedInPeriod": 165000.00,
      "proposedSavingsAmount": 110000.00,
      "savingsType": "percentage",
      "savingsPercentage": 10.00,
      "fixedAmount": null,
      "netProfitAfterSavings": 990000.00,
      "targetAmount": 5000000.00,
      "progressPercentage": 17
    },
    "expenses": { ... },
    "receivables": { ... },
    "cashFlow": { ... },
    "dailyBreakdown": [
      {
        "date": "2025-11-01",
        "revenue": 350000.00,
        "cashIn": 300000.00,
        "expenses": 25000.00,
        "grossProfit": 68000.00,
        "netProfit": 43000.00,
        "proposedSavings": 4300.00,
        "actualSavings": 4300.00,
        "netProfitAfterSavings": 38700.00
      }
    ]
  }
}
```

---

## 🎯 New Fields Explained

### Summary Level - No Changes
The main summary fields remain the same:
- `netProfit` - Total net profit before savings
- All other financial metrics unchanged

### Savings Section (NEW)

| Field | Type | Description |
|-------|------|-------------|
| `isEnabled` | boolean | Whether savings is enabled for shop |
| `currentBalance` | float | Current total savings balance |
| `totalSavedInPeriod` | float | Actual amount saved in the report period |
| `proposedSavingsAmount` | float | **Calculated savings based on net profit** |
| `savingsType` | string | `percentage` or `fixed_amount` |
| `savingsPercentage` | float | Percentage if type is percentage (null otherwise) |
| `fixedAmount` | float | Fixed amount if type is fixed (null otherwise) |
| `netProfitAfterSavings` | float | Net profit minus proposed savings |
| `targetAmount` | float | Savings goal target |
| `progressPercentage` | int | Progress towards goal (0-100) |

### Daily Breakdown - Enhanced

Each day now includes:
- `proposedSavings` - Calculated savings for that day
- `actualSavings` - What was actually saved
- `netProfitAfterSavings` - Daily profit after proposed savings

---

## 💡 How Proposed Savings is Calculated

### Formula
```javascript
if (savingsType === 'percentage') {
  proposedSavings = (netProfit * savingsPercentage) / 100
} else if (savingsType === 'fixed_amount') {
  proposedSavings = fixedAmount
}
```

### Example Calculation

**Scenario:**
- Net Profit (for period): TZS 1,100,000
- Savings Type: Percentage
- Savings Percentage: 10%

**Calculation:**
```
Proposed Savings = (1,100,000 × 10) / 100
Proposed Savings = TZS 110,000

Net Profit After Savings = 1,100,000 - 110,000
Net Profit After Savings = TZS 990,000
```

---

## 🔄 Daily Breakdown Calculation

Each day's proposed savings is calculated individually:

**Day 1:**
- Gross Profit: TZS 68,000
- Expenses: TZS 25,000
- Net Profit: TZS 43,000
- **Proposed Savings (10%):** TZS 4,300
- **Net After Savings:** TZS 38,700

**Day 2:**
- Gross Profit: TZS 85,000
- Expenses: TZS 30,000
- Net Profit: TZS 55,000
- **Proposed Savings (10%):** TZS 5,500
- **Net After Savings:** TZS 49,500

---

## 📊 Use Cases

### Use Case 1: Financial Planning
**Goal:** See how savings impact available cash

```bash
GET /api/shops/{shop}/reports/financial?dateFilter=this_month
```

**Result:**
- Total Net Profit: TZS 1,100,000
- Proposed Savings: TZS 110,000
- **Available for Operations:** TZS 990,000

### Use Case 2: Goal Progress Tracking
**Goal:** Monitor savings progress

**Response shows:**
- Current Balance: TZS 850,000
- Target Amount: TZS 5,000,000
- Progress: 17%
- **Amount Saved This Month:** TZS 165,000

### Use Case 3: Daily Performance Analysis
**Goal:** See savings impact day by day

**Daily Breakdown shows:**
- Each day's net profit
- Proposed savings amount
- What's left for operations
- Actual vs proposed savings

---

## 🎨 When Savings is Disabled

If savings is not enabled for the shop:

```json
{
  "savings": {
    "isEnabled": false,
    "currentBalance": 0.00,
    "totalSavedInPeriod": 0.00,
    "proposedSavingsAmount": 0.00,
    "savingsType": null,
    "savingsPercentage": null,
    "fixedAmount": null,
    "netProfitAfterSavings": 1100000.00
  }
}
```

**Note:** `netProfitAfterSavings` equals `netProfit` when savings is disabled.

---

## 📈 Business Benefits

### Better Financial Planning
- See true available profit after savings commitment
- Plan operations with savings factored in
- Avoid over-spending

### Goal Motivation
- Visual progress towards savings goals
- See daily contributions
- Track consistency

### Cash Flow Management
- Understand real available cash
- Plan withdrawals strategically
- Maintain healthy reserves

---

## 🎯 Frontend Integration

### Display Net Profit Card
```javascript
// Main profit card
{
  title: "Net Profit",
  value: formatCurrency(data.summary.netProfit),
  subtitle: savings.isEnabled 
    ? `After savings: ${formatCurrency(savings.netProfitAfterSavings)}`
    : null
}
```

### Display Savings Card
```javascript
// Savings card
{
  title: "Proposed Savings",
  value: formatCurrency(savings.proposedSavingsAmount),
  percentage: savings.savingsPercentage,
  progress: savings.progressPercentage,
  subtitle: `${savings.progressPercentage}% of ${formatCurrency(savings.targetAmount)}`
}
```

### Daily Chart Enhancement
```javascript
// Add savings line to profit chart
{
  labels: dailyBreakdown.map(d => d.date),
  datasets: [
    {
      label: 'Net Profit',
      data: dailyBreakdown.map(d => d.netProfit),
      color: 'green'
    },
    {
      label: 'Proposed Savings',
      data: dailyBreakdown.map(d => d.proposedSavings),
      color: 'blue'
    },
    {
      label: 'Available',
      data: dailyBreakdown.map(d => d.netProfitAfterSavings),
      color: 'orange'
    }
  ]
}
```

---

## 🔐 Important Notes

### Calculation Logic
1. ✅ Savings calculated on **NET profit** (after expenses)
2. ✅ Only positive profits trigger savings calculation
3. ✅ Proposed amount shows what **would be saved** based on settings
4. ✅ Actual savings shows what **was actually saved** in period

### Difference Between Proposed vs Actual
- **Proposed:** Calculated based on current settings
- **Actual:** Real savings transactions in the period
- They may differ if:
  - Settings were changed during period
  - Manual deposits were made
  - Automatic process had issues

### Zero Profit Days
If a day has zero or negative profit:
- `proposedSavings` = 0
- `actualSavings` = (only manual deposits)
- `netProfitAfterSavings` = netProfit

---

## 🎨 Kotlin Integration Example

```kotlin
data class FinancialReportResponse(
    val success: Boolean,
    val data: FinancialReportData
)

data class FinancialReportData(
    val period: Period,
    val summary: FinancialSummary,
    val savings: SavingsData,
    val expenses: ExpensesData,
    val receivables: ReceivablesData,
    val cashFlow: CashFlowData,
    val dailyBreakdown: List<DailyFinancial>
)

data class SavingsData(
    val isEnabled: Boolean,
    val currentBalance: Double,
    val totalSavedInPeriod: Double,
    val proposedSavingsAmount: Double,
    val savingsType: String?,
    val savingsPercentage: Double?,
    val fixedAmount: Double?,
    val netProfitAfterSavings: Double,
    val targetAmount: Double,
    val progressPercentage: Int
)

data class DailyFinancial(
    val date: String,
    val revenue: Double,
    val cashIn: Double,
    val expenses: Double,
    val grossProfit: Double,
    val netProfit: Double,
    val proposedSavings: Double,
    val actualSavings: Double,
    val netProfitAfterSavings: Double
)
```

---

## 📊 Sample UI Layout

```
┌─────────────────────────────────────────────────┐
│  Financial Report - November 2025               │
├─────────────────────────────────────────────────┤
│                                                 │
│  Net Profit: TZS 1,100,000                     │
│  Proposed Savings: TZS 110,000 (10%)           │
│  Available: TZS 990,000                         │
│                                                 │
│  ┌──────────────────────────────────────────┐  │
│  │  Savings Progress                        │  │
│  │  ▓▓▓▓░░░░░░░░░░░░░░░░ 17%              │  │
│  │  TZS 850,000 / TZS 5,000,000            │  │
│  └──────────────────────────────────────────┘  │
│                                                 │
│  Daily Breakdown:                               │
│  ┌──────────────────────────────────────────┐  │
│  │ Chart showing:                           │  │
│  │ - Net Profit (green line)                │  │
│  │ - Proposed Savings (blue line)           │  │
│  │ - Available (orange area)                │  │
│  └──────────────────────────────────────────┘  │
└─────────────────────────────────────────────────┘
```

---

## ✅ Testing Examples

### Test 1: With Percentage Savings
```bash
# Enable 10% savings
PUT /api/shops/{shop}/savings/settings
{
  "isEnabled": true,
  "savingsType": "percentage",
  "savingsPercentage": 10.00
}

# Get financial report
GET /api/shops/{shop}/reports/financial?dateFilter=today

# Check response
Expected: proposedSavingsAmount = netProfit * 0.10
```

### Test 2: With Fixed Amount Savings
```bash
# Enable fixed amount savings
PUT /api/shops/{shop}/savings/settings
{
  "isEnabled": true,
  "savingsType": "fixed_amount",
  "fixedAmount": 50000.00
}

# Get financial report
GET /api/shops/{shop}/reports/financial?dateFilter=this_week

# Check response
Expected: proposedSavingsAmount = 50000.00 * days_with_profit
```

### Test 3: Savings Disabled
```bash
# Get financial report with savings disabled
GET /api/shops/{shop}/reports/financial?dateFilter=today

# Check response
Expected: savings.isEnabled = false
Expected: savings.proposedSavingsAmount = 0
Expected: savings.netProfitAfterSavings = netProfit
```

---

## 🎉 Summary

### What Changed
- ✅ Added `savings` section to financial report
- ✅ Calculates `proposedSavingsAmount` from net profit
- ✅ Shows `netProfitAfterSavings`
- ✅ Enhanced daily breakdown with savings data
- ✅ Tracks actual vs proposed savings

### Key Benefits
- 📊 Better financial planning
- 💰 See true available profit
- 🎯 Track savings progress
- 📈 Daily savings visualization
- ✅ Motivates consistent saving

### Backward Compatible
- ✅ Existing fields unchanged
- ✅ New `savings` section added
- ✅ Works with savings disabled
- ✅ No breaking changes

---

**Status:** ✅ Complete  
**Version:** 1.1  
**Date:** November 6, 2025  
**camelCase:** ✅ All responses

