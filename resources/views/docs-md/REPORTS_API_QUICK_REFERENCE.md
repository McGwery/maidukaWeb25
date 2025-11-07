# Reports API - Quick Reference

## 📊 Endpoints

| Report | Endpoint | Description |
|--------|----------|-------------|
| **Overview** | `GET /api/shops/{shop}/reports/overview` | All key metrics summary |
| **Sales** | `GET /api/shops/{shop}/reports/sales` | Sales analytics & breakdown |
| **Products** | `GET /api/shops/{shop}/reports/products` | Product performance & inventory |
| **Financial** | `GET /api/shops/{shop}/reports/financial` | Financial analysis & profitability |
| **Employees** | `GET /api/shops/{shop}/reports/employees` | Team performance & analytics |

---

## 📅 Date Filters

### Quick Filters
```bash
?dateFilter=today           # Today's data
?dateFilter=this_week       # Current week (Mon-Sun)
?dateFilter=this_month      # Current month
```

### Custom Range
```bash
?dateFilter=custom&startDate=2024-01-01&endDate=2024-01-31
```

---

## 🚀 Quick Examples

### Today's Sales
```bash
GET /api/shops/{shop_id}/reports/sales?dateFilter=today
```

### This Week's Financial Report
```bash
GET /api/shops/{shop_id}/reports/financial?dateFilter=this_week
```

### Custom Date Range Products
```bash
GET /api/shops/{shop_id}/reports/products?dateFilter=custom&startDate=2024-01-01&endDate=2024-01-31
```

### This Month's Employees Performance
```bash
GET /api/shops/{shop_id}/reports/employees?dateFilter=this_month
```

### Dashboard Overview
```bash
GET /api/shops/{shop_id}/reports/overview?dateFilter=today
```

---

## 📦 Response Structure

All reports follow this structure:
```json
{
  "success": true,
  "data": {
    "period": {
      "filter": "today",
      "startDate": "2025-11-06",
      "endDate": "2025-11-06"
    },
    "summary": { /* Key metrics */ },
    /* Report-specific data */
  }
}
```

---

## 📈 Key Metrics by Report

### Overview Report
- Sales summary (count, revenue, profit)
- Products summary (total, low stock)
- Financial summary (revenue, expenses, net profit)
- Employees summary (total, active)
- Customers summary (total, with debt)

### Sales Report
- Total sales, revenue, profit, debt
- Sales by status (completed, pending, cancelled)
- Sales by payment status (paid, partial, unpaid)
- Top customers by purchase amount
- Daily sales breakdown

### Products Report
- Total products, low stock, out of stock
- Inventory value (cost, potential revenue, expected profit)
- Top selling products by quantity
- Top selling products by revenue
- Low stock alerts
- Category breakdown

### Financial Report
- Revenue, expenses, profit (gross & net)
- Profit margin percentage
- Cash flow analysis
- Expenses by category
- Outstanding debts & receivables
- Daily financial breakdown

### Employees Report
- Total team members (active & inactive)
- Employee performance (sales, revenue, profit)
- Top performers (top 5)
- Team members list with roles
- Daily employee performance

---

## ✅ Response Format

### camelCase Keys
All responses use camelCase for Kotlin compatibility:
```json
{
  "totalSales": 45,
  "totalRevenue": 2500000.00,
  "startDate": "2025-11-06",
  "averageSaleValue": 55555.56
}
```

### Typed Values
- **Integers**: counts, quantities
- **Floats**: money amounts, averages
- **Strings**: names, dates, status
- **Booleans**: isActive, etc.

---

## 🔐 Authentication

Required header:
```
Authorization: Bearer {your_sanctum_token}
```

User must have an active shop selected.

---

## ⚠️ Error Responses

### No Active Shop
```json
{
  "success": false,
  "message": "No active shop selected"
}
```

### Invalid Date Filter
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "dateFilter": ["The date filter field must be one of: today, this_week, this_month, custom"]
  }
}
```

---

## 💡 Pro Tips

1. **Default Filter**: If no `dateFilter` is provided, defaults to `today`
2. **Cache Overview**: Perfect for dashboard widgets
3. **Custom Ranges**: Use for monthly/quarterly reports
4. **Top Performers**: Limited to top 10 for performance
5. **Daily Breakdown**: Great for trend charts

---

## 📱 Kotlin Integration

All responses are in camelCase - perfect for Kotlin data classes:

```kotlin
data class SalesReportResponse(
    val success: Boolean,
    val data: SalesReportData
)

data class SalesReportData(
    val period: Period,
    val summary: SalesSummary,
    val salesByStatus: List<SalesByStatus>,
    val topCustomers: List<TopCustomer>,
    val dailyBreakdown: List<DailySales>
)
```

---

## 📄 Files Created

- ✅ `app/Http/Controllers/Api/ReportsController.php` - Main controller
- ✅ `app/Traits/HasDateRangeFilter.php` - Date filtering trait
- ✅ `routes/api.php` - API routes (updated)
- ✅ `REPORTS_API_DOCUMENTATION.md` - Full documentation
- ✅ `REPORTS_API_QUICK_REFERENCE.md` - This file

---

## 🎯 Use Cases

### Dashboard Widgets
```bash
GET /reports/overview?dateFilter=today
```

### Sales Performance Review
```bash
GET /reports/sales?dateFilter=this_month
```

### Inventory Management
```bash
GET /reports/products?dateFilter=today
```

### Financial Analysis
```bash
GET /reports/financial?dateFilter=custom&startDate=2024-01-01&endDate=2024-12-31
```

### Team Management
```bash
GET /reports/employees?dateFilter=this_week
```

---

**Status:** ✅ Complete  
**Date:** November 6, 2025  
**Version:** 1.0

