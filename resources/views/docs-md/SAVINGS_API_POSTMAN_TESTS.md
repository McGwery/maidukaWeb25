# Savings API - Postman Test JSON Samples

Complete JSON samples for testing all Savings API endpoints in Postman.

---

## 1. Update Savings Settings

**PUT/PATCH** `/api/shops/{shop_id}/savings/settings`

### Full Example
```json
{
  "proposed_amount": 50000,
  "proposed_percentage": 10.5,
  "saving_goal": 1000000,
  "start_date": "2025-01-01",
  "end_date": "2025-12-31",
  "frequency": "monthly",
  "enabled": true,
  "notes": "Savings plan for shop expansion"
}
```

### Minimal Example (Amount-based)
```json
{
  "proposed_amount": 100000,
  "frequency": "weekly",
  "enabled": true
}
```

### Percentage-based Example
```json
{
  "proposed_percentage": 15,
  "saving_goal": 5000000,
  "frequency": "daily",
  "enabled": true
}
```

### Update Only Some Fields
```json
{
  "enabled": false,
  "notes": "Temporarily disabled savings"
}
```

### With Date Range
```json
{
  "proposed_amount": 75000,
  "saving_goal": 2000000,
  "start_date": "2025-11-10",
  "end_date": "2026-11-10",
  "frequency": "monthly",
  "enabled": true,
  "notes": "One year savings plan"
}
```

---

## 2. Make a Deposit

**POST** `/api/shops/{shop_id}/savings/deposit`

### With Goal Reference
```json
{
  "amount": 100000,
  "savingsGoalId": 1,
  "description": "Manual savings deposit for shop expansion"
}
```

### Without Goal Reference
```json
{
  "amount": 50000,
  "description": "General savings deposit"
}
```

### Minimal
```json
{
  "amount": 25000
}
```

### Large Deposit
```json
{
  "amount": 500000,
  "savingsGoalId": 2,
  "description": "Monthly profit deposit - November 2025"
}
```

---

## 3. Make a Withdrawal

**POST** `/api/shops/{shop_id}/savings/withdraw`

### Full Example
```json
{
  "amount": 50000,
  "description": "Emergency withdrawal for equipment repair",
  "notes": "Approved by shop owner on 2025-11-09"
}
```

### Minimal
```json
{
  "amount": 30000,
  "description": "Business expense withdrawal"
}
```

### With Detailed Notes
```json
{
  "amount": 100000,
  "description": "Stock purchase",
  "notes": "Withdrawn to purchase new inventory from supplier ABC Ltd"
}
```

---

## 4. Create Savings Goal

**POST** `/api/shops/{shop_id}/savings/goals`

### Full Example
```json
{
  "name": "Shop Expansion Fund",
  "description": "Save for opening a second branch in Mwanza",
  "targetAmount": 10000000,
  "targetDate": "2025-12-31",
  "icon": "store",
  "color": "#4CAF50",
  "priority": 1
}
```

### Minimal Example
```json
{
  "name": "Equipment Upgrade",
  "targetAmount": 5000000
}
```

### Equipment Purchase Goal
```json
{
  "name": "New POS System",
  "description": "Save for modern point of sale system",
  "targetAmount": 3000000,
  "targetDate": "2026-03-31",
  "icon": "computer",
  "color": "#2196F3",
  "priority": 2
}
```

### Emergency Fund
```json
{
  "name": "Emergency Fund",
  "description": "Build 6-month operating expense reserve",
  "targetAmount": 15000000,
  "targetDate": "2026-06-30",
  "icon": "security",
  "color": "#FF9800",
  "priority": 3
}
```

---

## 5. Update Savings Goal

**PUT** `/api/shops/{shop_id}/savings/goals/{goal_id}`

### Full Update
```json
{
  "name": "Updated Shop Expansion Fund",
  "description": "Save for opening two new branches",
  "targetAmount": 15000000,
  "targetDate": "2026-06-30",
  "status": "active",
  "icon": "store",
  "color": "#4CAF50",
  "priority": 1
}
```

### Partial Update
```json
{
  "targetAmount": 12000000,
  "targetDate": "2026-03-31"
}
```

### Mark as Completed
```json
{
  "status": "completed"
}
```

### Change Priority
```json
{
  "priority": 5
}
```

---

## Field Reference

### Update Savings Settings Fields

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `proposed_amount` | number | No | >= 0 | Fixed amount to save |
| `proposed_percentage` | number | No | 0-100 | Percentage of profit to save |
| `saving_goal` | number | No | >= 0 | Target amount to reach |
| `start_date` | string | No | date (YYYY-MM-DD) | When to start saving |
| `end_date` | string | No | date (YYYY-MM-DD) | When to stop saving |
| `frequency` | string | No | daily, weekly, monthly, yearly | How often to save |
| `enabled` | boolean | No | true/false | Enable/disable automatic savings |
| `notes` | string | No | max 1000 chars | Additional notes |

### Deposit Fields

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `amount` | number | Yes | > 0 | Amount to deposit |
| `savingsGoalId` | integer | No | exists in savings_goals | Link to specific goal |
| `description` | string | No | - | Deposit description |

### Withdrawal Fields

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `amount` | number | Yes | > 0, <= balance | Amount to withdraw |
| `description` | string | No | - | Withdrawal reason |
| `notes` | string | No | - | Additional notes |

### Savings Goal Fields

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `name` | string | Yes | max 255 chars | Goal name |
| `description` | string | No | - | Goal description |
| `targetAmount` | number | Yes | > 0 | Target amount to reach |
| `targetDate` | string | No | date (YYYY-MM-DD) | Target completion date |
| `icon` | string | No | - | Icon identifier |
| `color` | string | No | hex color | Display color (#RRGGBB) |
| `priority` | integer | No | >= 0 | Goal priority (higher = more important) |
| `status` | string | No | active, completed, cancelled | Goal status |

---

## Frequency Options

- `daily` - Save every day
- `weekly` - Save every week
- `monthly` - Save every month
- `yearly` - Save every year

---

## Status Options (for Goals)

- `active` - Goal is active and accepting deposits
- `completed` - Goal target has been reached
- `cancelled` - Goal has been cancelled

---

## Common Test Scenarios

### Scenario 1: Enable Percentage-Based Savings
```json
{
  "proposed_percentage": 10,
  "saving_goal": 5000000,
  "frequency": "monthly",
  "enabled": true,
  "notes": "Save 10% of monthly profit"
}
```

### Scenario 2: Enable Fixed Amount Savings
```json
{
  "proposed_amount": 100000,
  "saving_goal": 3000000,
  "frequency": "weekly",
  "enabled": true,
  "notes": "Save 100,000 TZS every week"
}
```

### Scenario 3: Disable Savings
```json
{
  "enabled": false,
  "notes": "Temporarily disabled due to cash flow issues"
}
```

### Scenario 4: Update Target Only
```json
{
  "saving_goal": 8000000,
  "end_date": "2026-12-31"
}
```

### Scenario 5: Create Multiple Goals
```json
// Goal 1 - Emergency Fund
{
  "name": "Emergency Fund",
  "targetAmount": 2000000,
  "priority": 1,
  "color": "#F44336"
}

// Goal 2 - Equipment
{
  "name": "New Equipment",
  "targetAmount": 5000000,
  "priority": 2,
  "color": "#2196F3"
}

// Goal 3 - Expansion
{
  "name": "Shop Expansion",
  "targetAmount": 10000000,
  "priority": 3,
  "color": "#4CAF50"
}
```

---

## Response Examples

### Successful Settings Update
```json
{
  "success": true,
  "message": "Savings settings updated successfully.",
  "responseTime": 45.23,
  "data": {
    "id": 1,
    "proposedAmount": 50000.0,
    "proposedPercentage": 10.5,
    "savingGoal": 1000000.0,
    "startDate": "2025-01-01",
    "endDate": "2025-12-31",
    "frequency": "monthly",
    "enabled": true,
    "currentBalance": 250000.0
  }
}
```

### Successful Deposit
```json
{
  "success": true,
  "message": "Deposit successful.",
  "responseTime": 67.89,
  "data": {
    "currentBalance": 350000.0,
    "totalSaved": 500000.0
  }
}
```

### Successful Withdrawal
```json
{
  "success": true,
  "message": "Withdrawal successful.",
  "responseTime": 54.32,
  "data": {
    "currentBalance": 300000.0,
    "totalWithdrawn": 50000.0
  }
}
```

### Insufficient Balance Error
```json
{
  "success": false,
  "message": "Insufficient savings balance.",
  "responseTime": 23.45,
  "data": null
}
```

### Validation Error
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "responseTime": 12.34,
  "data": {
    "errors": {
      "proposed_percentage": ["The proposed percentage must be between 0 and 100."],
      "end_date": ["End date must be the same as or after the start date."]
    }
  }
}
```

---

## Postman Collection Variables

Set these variables in your Postman collection:

```
base_url: http://localhost:8000/api
token: {your_auth_token}
shop_id: {your_shop_id}
goal_id: {savings_goal_id}
```

---

## cURL Examples

### Update Settings
```bash
curl -X PUT http://localhost:8000/api/shops/1/savings/settings \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "proposed_amount": 50000,
    "proposed_percentage": 10.5,
    "saving_goal": 1000000,
    "frequency": "monthly",
    "enabled": true
  }'
```

### Make Deposit
```bash
curl -X POST http://localhost:8000/api/shops/1/savings/deposit \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 100000,
    "savingsGoalId": 1,
    "description": "Manual deposit"
  }'
```

### Make Withdrawal
```bash
curl -X POST http://localhost:8000/api/shops/1/savings/withdraw \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 50000,
    "description": "Emergency withdrawal"
  }'
```

---

**Last Updated:** November 9, 2025

