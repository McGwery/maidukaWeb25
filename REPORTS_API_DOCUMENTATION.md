# Reports API Documentation

## Overview
Professional reporting system for shop analytics with date filtering capabilities. All responses are in **camelCase** format for Kotlin app integration.

---

## Date Filters

All report endpoints support the following date filter parameters:

### Filter Types
- `today` - Today's data (default)
- `this_week` - Current week (Monday to Sunday)
- `this_month` - Current month
- `custom` - Custom date range (requires startDate and endDate)

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `dateFilter` | string | No | Filter type: `today`, `this_week`, `this_month`, `custom` |
| `startDate` | date | Conditional | Required when `dateFilter=custom`. Format: `YYYY-MM-DD` |
| `endDate` | date | Conditional | Required when `dateFilter=custom`. Format: `YYYY-MM-DD` |

---

## Endpoints

### 1. Overview Report
Get a comprehensive summary of all key metrics.

**Endpoint:** `GET /api/shops/{shop}/reports/overview`

**Query Parameters:**
```
?dateFilter=today
?dateFilter=this_week
?dateFilter=this_month
?dateFilter=custom&startDate=2024-01-01&endDate=2024-01-31
```

**Response:**
```json
{
  "success": true,
  "data": {
    "period": {
      "filter": "today",
      "startDate": "2025-11-06",
      "endDate": "2025-11-06"
    },
    "sales": {
      "totalSales": 45,
      "totalRevenue": 2500000.00,
      "totalProfit": 450000.00
    },
    "products": {
      "totalProducts": 150,
      "lowStockProducts": 12
    },
    "financial": {
      "totalRevenue": 2500000.00,
      "grossProfit": 450000.00,
      "totalExpenses": 180000.00,
      "netProfit": 270000.00
    },
    "employees": {
      "totalMembers": 5,
      "activeMembers": 4
    },
    "customers": {
      "totalCustomers": 230,
      "customersWithDebt": 15
    }
  }
}
```

---

### 2. Sales Report
Detailed sales analytics and breakdown.

**Endpoint:** `GET /api/shops/{shop}/reports/sales`

**Query Parameters:** Same as above

**Response:**
```json
{
  "success": true,
  "data": {
    "period": {
      "filter": "this_week",
      "startDate": "2025-11-03",
      "endDate": "2025-11-09"
    },
    "summary": {
      "totalSales": 156,
      "totalRevenue": 8750000.00,
      "totalPaid": 7200000.00,
      "totalDebt": 1550000.00,
      "totalProfit": 1680000.00,
      "averageSaleValue": 56089.74
    },
    "salesByStatus": [
      {
        "status": "completed",
        "statusLabel": "Completed",
        "count": 145,
        "total": 8200000.00
      },
      {
        "status": "pending",
        "statusLabel": "Pending",
        "count": 11,
        "total": 550000.00
      }
    ],
    "salesByPaymentStatus": [
      {
        "paymentStatus": "paid",
        "count": 120,
        "total": 6500000.00
      },
      {
        "paymentStatus": "partial",
        "count": 25,
        "total": 1700000.00
      },
      {
        "paymentStatus": "unpaid",
        "count": 11,
        "total": 550000.00
      }
    ],
    "topCustomers": [
      {
        "customerId": "uuid",
        "customerName": "John Doe",
        "customerPhone": "255712345678",
        "purchaseCount": 15,
        "totalSpent": 850000.00
      }
    ],
    "dailyBreakdown": [
      {
        "date": "2025-11-03",
        "salesCount": 25,
        "totalRevenue": 1250000.00,
        "totalProfit": 240000.00
      }
    ]
  }
}
```

---

### 3. Products Report
Product performance, inventory analysis, and top sellers.

**Endpoint:** `GET /api/shops/{shop}/reports/products`

**Query Parameters:** Same as above

**Response:**
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
      "totalProducts": 150,
      "lowStockProducts": 12,
      "outOfStockProducts": 3,
      "inventoryCostValue": 5500000.00,
      "potentialRevenue": 8200000.00,
      "expectedProfit": 2700000.00
    },
    "topSellingByQuantity": [
      {
        "productId": "uuid",
        "productName": "Coca Cola 500ml",
        "quantitySold": 450.00,
        "totalRevenue": 675000.00,
        "totalProfit": 135000.00
      }
    ],
    "topSellingByRevenue": [
      {
        "productId": "uuid",
        "productName": "Laptop HP",
        "quantitySold": 5.00,
        "totalRevenue": 3500000.00,
        "totalProfit": 750000.00
      }
    ],
    "lowStockAlert": [
      {
        "productId": "uuid",
        "productName": "Rice 1kg",
        "sku": "RICE001",
        "currentStock": 15,
        "lowStockThreshold": 50
      }
    ],
    "categoryBreakdown": [
      {
        "categoryId": "uuid",
        "categoryName": "Beverages",
        "productCount": 25,
        "totalStock": 1250
      }
    ]
  }
}
```

---

### 4. Financial Report
Complete financial analysis including revenue, expenses, and profitability.

**Endpoint:** `GET /api/shops/{shop}/reports/financial`

**Query Parameters:** Same as above

**Response:**
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
    "expenses": {
      "total": 580000.00,
      "count": 45,
      "byCategory": [
        {
          "category": "rent",
          "categoryLabel": "Rent",
          "total": 200000.00,
          "count": 1
        },
        {
          "category": "utilities",
          "categoryLabel": "Utilities",
          "total": 85000.00,
          "count": 8
        },
        {
          "category": "salaries",
          "categoryLabel": "Salaries",
          "total": 250000.00,
          "count": 5
        }
      ]
    },
    "receivables": {
      "totalDebtInPeriod": 1550000.00,
      "outstandingDebts": 2300000.00,
      "customersWithDebt": 15
    },
    "cashFlow": {
      "cashIn": 7200000.00,
      "cashOut": 580000.00,
      "netCashFlow": 6620000.00
    },
    "dailyBreakdown": [
      {
        "date": "2025-11-01",
        "revenue": 350000.00,
        "cashIn": 300000.00,
        "expenses": 25000.00,
        "grossProfit": 68000.00,
        "netProfit": 43000.00
      }
    ]
  }
}
```

---

### 5. Employees Report
Team performance and individual employee analytics.

**Endpoint:** `GET /api/shops/{shop}/reports/employees`

**Query Parameters:** Same as above

**Response:**
```json
{
  "success": true,
  "data": {
    "period": {
      "filter": "this_week",
      "startDate": "2025-11-03",
      "endDate": "2025-11-09"
    },
    "summary": {
      "totalMembers": 5,
      "activeMembers": 4,
      "inactiveMembers": 1
    },
    "employeePerformance": [
      {
        "userId": "uuid",
        "userName": "Jane Smith",
        "userEmail": "jane@example.com",
        "salesCount": 45,
        "totalRevenue": 2500000.00,
        "totalProfit": 480000.00,
        "averageSaleValue": 55555.56
      },
      {
        "userId": "uuid",
        "userName": "John Doe",
        "userEmail": "john@example.com",
        "salesCount": 38,
        "totalRevenue": 2100000.00,
        "totalProfit": 420000.00,
        "averageSaleValue": 55263.16
      }
    ],
    "topPerformers": [
      {
        "userId": "uuid",
        "userName": "Jane Smith",
        "userEmail": "jane@example.com",
        "salesCount": 45,
        "totalRevenue": 2500000.00,
        "totalProfit": 480000.00,
        "averageSaleValue": 55555.56
      }
    ],
    "teamMembers": [
      {
        "userId": "uuid",
        "userName": "Jane Smith",
        "userEmail": "jane@example.com",
        "userPhone": "255712345678",
        "role": "manager",
        "isActive": true,
        "joinedAt": "2024-06-15"
      }
    ],
    "dailyPerformance": [
      {
        "date": "2025-11-03",
        "employees": [
          {
            "userId": "uuid",
            "userName": "Jane Smith",
            "salesCount": 8,
            "revenue": 450000.00
          },
          {
            "userId": "uuid",
            "userName": "John Doe",
            "salesCount": 6,
            "revenue": 320000.00
          }
        ]
      }
    ]
  }
}
```

---

## Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "message": "No active shop selected"
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "dateFilter": ["The date filter field must be one of: today, this_week, this_month, custom"],
    "startDate": ["The start date field is required when date filter is custom"],
    "endDate": ["The end date must be a date after or equal to start date"]
  }
}
```

---

## Usage Examples

### Example 1: Get Today's Sales Report
```bash
GET /api/shops/{shop_id}/reports/sales?dateFilter=today
```

### Example 2: Get This Week's Financial Report
```bash
GET /api/shops/{shop_id}/reports/financial?dateFilter=this_week
```

### Example 3: Get Custom Date Range Products Report
```bash
GET /api/shops/{shop_id}/reports/products?dateFilter=custom&startDate=2024-01-01&endDate=2024-01-31
```

### Example 4: Get This Month's Employee Performance
```bash
GET /api/shops/{shop_id}/reports/employees?dateFilter=this_month
```

### Example 5: Get Overview Dashboard
```bash
GET /api/shops/{shop_id}/reports/overview?dateFilter=today
```

---

## Response Format Standards

### ✅ All Keys in camelCase
Perfect for Kotlin app integration:
- `totalSales` not `total_sales`
- `startDate` not `start_date`
- `profitMargin` not `profit_margin`

### ✅ Consistent Structure
All reports follow the same structure:
```json
{
  "success": true,
  "data": {
    "period": { ... },
    "summary": { ... },
    ...
  }
}
```

### ✅ Typed Values
- Integers: `totalSales`, `count`
- Floats: `totalRevenue`, `profit`
- Booleans: `isActive`
- Strings: `customerName`, `date`

---

## Key Metrics Explained

### Sales Metrics
- **Total Sales**: Number of transactions
- **Total Revenue**: Sum of all sale amounts
- **Total Paid**: Cash received
- **Total Debt**: Outstanding credit sales
- **Total Profit**: Gross profit from sales
- **Average Sale Value**: Revenue / Number of sales

### Financial Metrics
- **Gross Profit**: Revenue - Cost of goods sold
- **Net Profit**: Gross profit - Expenses
- **Profit Margin**: (Gross Profit / Revenue) × 100
- **Cash Flow**: Cash In - Cash Out

### Product Metrics
- **Inventory Cost Value**: Total cost of current stock
- **Potential Revenue**: Expected revenue if all stock is sold
- **Expected Profit**: Potential Revenue - Inventory Cost Value

### Employee Metrics
- **Sales Count**: Number of sales made by employee
- **Total Revenue**: Revenue generated by employee
- **Average Sale Value**: Revenue / Sales Count

---

## Integration Tips

### 1. Default to Today
If no dateFilter is provided, the API defaults to `today`.

### 2. Cache Overview Data
The overview endpoint is perfect for dashboard caching.

### 3. Pagination
For large data sets, consider implementing pagination on the frontend.

### 4. Export Functionality
Use the API data to generate PDF/Excel reports on the client side.

### 5. Real-time Updates
Poll the overview endpoint periodically for real-time dashboard updates.

---

## Authentication

All report endpoints require authentication via Sanctum token:

```bash
Authorization: Bearer {token}
```

The authenticated user must have an active shop selected.

---

## Performance Notes

- Reports are optimized with database indexes
- Complex queries use eager loading
- Date ranges are constrained to prevent performance issues
- Consider caching frequently accessed reports

---

## Coming Soon

- Export to PDF
- Export to Excel
- Email scheduled reports
- Comparison reports (period vs period)
- Graphical chart data endpoints

