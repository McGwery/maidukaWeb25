# Expense Management Quick Reference

## API Endpoints Summary

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/shops/{shopId}/expenses` | List all expenses |
| POST | `/api/shops/{shopId}/expenses` | Create new expense |
| GET | `/api/shops/{shopId}/expenses/{expenseId}` | Get expense details |
| PUT | `/api/shops/{shopId}/expenses/{expenseId}` | Update expense |
| DELETE | `/api/shops/{shopId}/expenses/{expenseId}` | Delete expense |
| GET | `/api/shops/{shopId}/expenses/summary` | Get expense analytics |
| GET | `/api/shops/{shopId}/expenses/categories` | Get available categories |

## Expense Categories
- `rent` - Rent
- `utilities` - Utilities (electricity, water, internet)
- `salaries` - Salaries & wages
- `marketing` - Marketing & advertising
- `transport` - Transportation & delivery
- `maintenance` - Repairs & maintenance
- `supplies` - Office & shop supplies
- `insurance` - Insurance premiums
- `taxes` - Tax payments
- `other` - Other expenses

## Payment Methods
- `cash` - Cash
- `mobile_money` - Mobile Money
- `bank_transfer` - Bank Transfer
- `credit` - Credit
- `cheque` - Cheque

## Quick Create Example
```json
POST /api/shops/{shopId}/expenses
{
  "title": "Monthly Rent",
  "category": "rent",
  "amount": 500000.00,
  "expenseDate": "2025-01-15",
  "paymentMethod": "bank_transfer"
}
```

## Response Format (camelCase)
```json
{
  "success": true,
  "code": 200,
  "data": {
    "id": "uuid",
    "shopId": "uuid",
    "title": "string",
    "category": {
      "value": "string",
      "label": "string"
    },
    "amount": 0.00,
    "expenseDate": "YYYY-MM-DD",
    "paymentMethod": {
      "value": "string",
      "label": "string"
    },
    "recordedBy": {
      "id": "uuid",
      "name": "string"
    }
  }
}
```

