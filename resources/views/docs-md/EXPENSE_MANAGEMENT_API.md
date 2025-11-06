# Expense Management API Documentation

## Overview
The Expense Management API allows you to track and manage all shop expenses with categorization, summaries, and analytics. All responses follow camelCase naming convention for Kotlin application compatibility.

## Base URL
```
/api/shops/{shopId}/expenses
```

## Authentication
All endpoints require authentication using Bearer token:
```
Authorization: Bearer {token}
```

---

## Endpoints

### 1. Get All Expenses
Retrieve a paginated list of expenses for a shop.

**Endpoint:** `GET /api/shops/{shopId}/expenses`

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| search | string | No | Search in title, description, or receipt number |
| category | string | No | Filter by category (rent, utilities, salaries, etc.) |
| startDate | date | No | Filter expenses from this date (YYYY-MM-DD) |
| endDate | date | No | Filter expenses until this date (YYYY-MM-DD) |
| paymentMethod | string | No | Filter by payment method |
| sortBy | string | No | Field to sort by (default: expense_date) |
| sortDirection | string | No | Sort direction: asc or desc (default: desc) |
| perPage | integer | No | Items per page (default: 15) |

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "expenses": [
      {
        "id": "uuid",
        "shopId": "uuid",
        "title": "Monthly Rent Payment",
        "description": "Office rent for January 2025",
        "category": {
          "value": "rent",
          "label": "Rent"
        },
        "amount": 500000.00,
        "expenseDate": "2025-01-15",
        "paymentMethod": {
          "value": "bank_transfer",
          "label": "Bank Transfer"
        },
        "receiptNumber": "RCP-2025-001",
        "attachmentUrl": "https://example.com/receipts/001.pdf",
        "recordedBy": {
          "id": "uuid",
          "name": "John Doe"
        },
        "createdAt": "2025-01-15T10:30:00Z",
        "updatedAt": "2025-01-15T10:30:00Z"
      }
    ],
    "pagination": {
      "total": 150,
      "currentPage": 1,
      "lastPage": 10,
      "perPage": 15
    }
  }
}
```

---

### 2. Create Expense
Record a new expense.

**Endpoint:** `POST /api/shops/{shopId}/expenses`

**Request Body:**
```json
{
  "title": "Monthly Rent Payment",
  "description": "Office rent for January 2025",
  "category": "rent",
  "amount": 500000.00,
  "expenseDate": "2025-01-15",
  "paymentMethod": "bank_transfer",
  "receiptNumber": "RCP-2025-001",
  "attachmentUrl": "https://example.com/receipts/001.pdf"
}
```

**Validation Rules:**
- `title`: required, string, max 255 characters
- `description`: optional, string, max 1000 characters
- `category`: required, must be valid expense category
- `amount`: required, numeric, minimum 0
- `expenseDate`: required, valid date
- `paymentMethod`: required, must be valid payment method
- `receiptNumber`: optional, string, max 100 characters
- `attachmentUrl`: optional, valid URL, max 500 characters

**Response:**
```json
{
  "success": true,
  "code": 201,
  "message": "Expense recorded successfully.",
  "data": {
    "id": "uuid",
    "shopId": "uuid",
    "title": "Monthly Rent Payment",
    "description": "Office rent for January 2025",
    "category": {
      "value": "rent",
      "label": "Rent"
    },
    "amount": 500000.00,
    "expenseDate": "2025-01-15",
    "paymentMethod": {
      "value": "bank_transfer",
      "label": "Bank Transfer"
    },
    "receiptNumber": "RCP-2025-001",
    "attachmentUrl": "https://example.com/receipts/001.pdf",
    "recordedBy": {
      "id": "uuid",
      "name": "John Doe"
    },
    "createdAt": "2025-01-15T10:30:00Z",
    "updatedAt": "2025-01-15T10:30:00Z"
  }
}
```

---

### 3. Get Single Expense
Retrieve details of a specific expense.

**Endpoint:** `GET /api/shops/{shopId}/expenses/{expenseId}`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "id": "uuid",
    "shopId": "uuid",
    "title": "Monthly Rent Payment",
    "description": "Office rent for January 2025",
    "category": {
      "value": "rent",
      "label": "Rent"
    },
    "amount": 500000.00,
    "expenseDate": "2025-01-15",
    "paymentMethod": {
      "value": "bank_transfer",
      "label": "Bank Transfer"
    },
    "receiptNumber": "RCP-2025-001",
    "attachmentUrl": "https://example.com/receipts/001.pdf",
    "recordedBy": {
      "id": "uuid",
      "name": "John Doe"
    },
    "createdAt": "2025-01-15T10:30:00Z",
    "updatedAt": "2025-01-15T10:30:00Z"
  }
}
```

---

### 4. Update Expense
Update an existing expense.

**Endpoint:** `PUT /api/shops/{shopId}/expenses/{expenseId}`

**Request Body:** (all fields optional, only send what needs to be updated)
```json
{
  "title": "Updated Rent Payment",
  "description": "Updated description",
  "category": "rent",
  "amount": 550000.00,
  "expenseDate": "2025-01-15",
  "paymentMethod": "cash",
  "receiptNumber": "RCP-2025-001-UPDATED",
  "attachmentUrl": "https://example.com/receipts/001-updated.pdf"
}
```

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Expense updated successfully.",
  "data": {
    "id": "uuid",
    "shopId": "uuid",
    "title": "Updated Rent Payment",
    "description": "Updated description",
    "category": {
      "value": "rent",
      "label": "Rent"
    },
    "amount": 550000.00,
    "expenseDate": "2025-01-15",
    "paymentMethod": {
      "value": "cash",
      "label": "Cash"
    },
    "receiptNumber": "RCP-2025-001-UPDATED",
    "attachmentUrl": "https://example.com/receipts/001-updated.pdf",
    "recordedBy": {
      "id": "uuid",
      "name": "John Doe"
    },
    "createdAt": "2025-01-15T10:30:00Z",
    "updatedAt": "2025-01-15T14:45:00Z"
  }
}
```

---

### 5. Delete Expense
Soft delete an expense.

**Endpoint:** `DELETE /api/shops/{shopId}/expenses/{expenseId}`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Expense deleted successfully."
}
```

---

### 6. Get Expense Summary
Get comprehensive expense analytics grouped by category, payment method, and monthly trend.

**Endpoint:** `GET /api/shops/{shopId}/expenses/summary`

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| startDate | date | No | Start date for analysis (YYYY-MM-DD) |
| endDate | date | No | End date for analysis (YYYY-MM-DD) |

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "totalExpenses": 2500000.00,
    "categoryBreakdown": [
      {
        "category": {
          "value": "rent",
          "label": "Rent"
        },
        "totalAmount": 800000.00,
        "count": 12,
        "percentage": 32.00
      },
      {
        "category": {
          "value": "utilities",
          "label": "Utilities"
        },
        "totalAmount": 450000.00,
        "count": 24,
        "percentage": 18.00
      },
      {
        "category": {
          "value": "salaries",
          "label": "Salaries"
        },
        "totalAmount": 950000.00,
        "count": 18,
        "percentage": 38.00
      },
      {
        "category": {
          "value": "transport",
          "label": "Transport"
        },
        "totalAmount": 150000.00,
        "count": 45,
        "percentage": 6.00
      },
      {
        "category": {
          "value": "other",
          "label": "Other"
        },
        "totalAmount": 150000.00,
        "count": 32,
        "percentage": 6.00
      }
    ],
    "paymentMethodBreakdown": [
      {
        "paymentMethod": {
          "value": "cash",
          "label": "Cash"
        },
        "totalAmount": 850000.00,
        "count": 45
      },
      {
        "paymentMethod": {
          "value": "bank_transfer",
          "label": "Bank Transfer"
        },
        "totalAmount": 1200000.00,
        "count": 35
      },
      {
        "paymentMethod": {
          "value": "mobile_money",
          "label": "Mobile Money"
        },
        "totalAmount": 450000.00,
        "count": 51
      }
    ],
    "monthlyTrend": [
      {
        "month": "2025-01",
        "totalAmount": 450000.00,
        "count": 23
      },
      {
        "month": "2024-12",
        "totalAmount": 520000.00,
        "count": 28
      },
      {
        "month": "2024-11",
        "totalAmount": 380000.00,
        "count": 19
      }
    ],
    "dateRange": {
      "startDate": "2024-01-01",
      "endDate": "2025-01-31"
    }
  }
}
```

---

### 7. Get Expense Categories
Get all available expense categories for dropdown selection.

**Endpoint:** `GET /api/shops/{shopId}/expenses/categories`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "categories": [
      {
        "value": "rent",
        "label": "Rent"
      },
      {
        "value": "utilities",
        "label": "Utilities"
      },
      {
        "value": "salaries",
        "label": "Salaries"
      },
      {
        "value": "marketing",
        "label": "Marketing"
      },
      {
        "value": "transport",
        "label": "Transport"
      },
      {
        "value": "maintenance",
        "label": "Maintenance"
      },
      {
        "value": "supplies",
        "label": "Supplies"
      },
      {
        "value": "insurance",
        "label": "Insurance"
      },
      {
        "value": "taxes",
        "label": "Taxes"
      },
      {
        "value": "other",
        "label": "Other"
      }
    ]
  }
}
```

---

## Expense Categories

The following expense categories are available:

| Value | Label | Description |
|-------|-------|-------------|
| rent | Rent | Rental payments for shop space |
| utilities | Utilities | Electricity, water, internet, etc. |
| salaries | Salaries | Employee wages and salaries |
| marketing | Marketing | Advertising and promotional expenses |
| transport | Transport | Transportation and delivery costs |
| maintenance | Maintenance | Repairs and maintenance |
| supplies | Supplies | Office and shop supplies |
| insurance | Insurance | Insurance premiums |
| taxes | Taxes | Tax payments |
| other | Other | Miscellaneous expenses |

---

## Payment Methods

The following payment methods are available:

| Value | Label |
|-------|-------|
| cash | Cash |
| mobile_money | Mobile Money |
| bank_transfer | Bank Transfer |
| credit | Credit |
| cheque | Cheque |

---

## Error Responses

### 404 Not Found
```json
{
  "success": false,
  "code": 404,
  "message": "Expense not found."
}
```

### 422 Validation Error
```json
{
  "success": false,
  "code": 422,
  "message": "The given data was invalid.",
  "errors": {
    "title": ["Expense title is required."],
    "amount": ["Expense amount cannot be negative."],
    "category": ["Invalid expense category."]
  }
}
```

### 401 Unauthorized
```json
{
  "success": false,
  "code": 401,
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "success": false,
  "code": 403,
  "message": "You do not have access to this shop."
}
```

---

## Usage Examples

### Example 1: Recording Daily Cash Expenses
```bash
# Record cash payment for supplies
POST /api/shops/123e4567-e89b-12d3-a456-426614174000/expenses
Content-Type: application/json
Authorization: Bearer {token}

{
  "title": "Office Supplies Purchase",
  "description": "Pens, papers, and printer ink",
  "category": "supplies",
  "amount": 45000.00,
  "expenseDate": "2025-01-15",
  "paymentMethod": "cash",
  "receiptNumber": "SUP-2025-045"
}
```

### Example 2: Getting Monthly Summary
```bash
# Get expense summary for January 2025
GET /api/shops/123e4567-e89b-12d3-a456-426614174000/expenses/summary?startDate=2025-01-01&endDate=2025-01-31
Authorization: Bearer {token}
```

### Example 3: Filtering Expenses
```bash
# Get all rent expenses paid via bank transfer
GET /api/shops/123e4567-e89b-12d3-a456-426614174000/expenses?category=rent&paymentMethod=bank_transfer
Authorization: Bearer {token}
```

---

## Best Practices

1. **Date Filtering**: Use `startDate` and `endDate` parameters to get expenses for specific periods
2. **Categorization**: Always use appropriate categories for better analytics
3. **Receipt Numbers**: Include receipt numbers for audit trail
4. **Attachments**: Store receipt images/PDFs for documentation
5. **Regular Reviews**: Use the summary endpoint to review spending patterns
6. **Search**: Use the search parameter to quickly find specific expenses
7. **Pagination**: Use appropriate `perPage` values for optimal performance

---

## Notes

- All monetary amounts are in decimal format with 2 decimal places
- All dates follow ISO 8601 format (YYYY-MM-DD)
- Expenses are soft-deleted, allowing for recovery if needed
- The `recordedBy` field is automatically set to the authenticated user
- Response follows camelCase naming for Kotlin compatibility
- All timestamps are in ISO 8601 format with timezone

