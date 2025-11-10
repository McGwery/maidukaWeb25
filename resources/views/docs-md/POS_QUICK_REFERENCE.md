# POS System - Quick Reference Guide

## 🚀 Quick Start

### Complete a Sale (Main Endpoint)
```
POST /api/shops/{shopId}/pos/sales
```

**Minimal Request:**
```json
{
  "items": [
    {
      "id": "product-uuid",
      "name": "Product Name",
      "originalPrice": 2500,
      "currentPrice": 2500,
      "quantity": 1,
      "total": 2500,
      "unit": "piece",
      "allowFractional": false,
      "negotiable": false
    }
  ],
  "paymentMethod": "cash",
  "total": 2500,
  "amountReceived": 2500,
  "change": 0
}
```

### With Customer (Credit Sale)
```json
{
  "items": [...],
  "customer": {
    "id": "customer-uuid",
    "name": "Customer Name",
    "phone": "+255 XXX XXX XXX",
    "debt": 0
  },
  "paymentMethod": "credit",
  "total": 2500,
  "amountReceived": 0,
  "change": 0
}
```

### With Tax & Discount
```json
{
  "items": [...],
  "paymentMethod": "cash",
  "total": 2950,
  "amountReceived": 3000,
  "change": 50,
  "taxRate": 18,
  "discountPercentage": 10
}
```

---

## 📋 Common Operations

### 1. Get Today's Sales
```
GET /api/shops/{shopId}/pos/sales?fromDate=2025-11-05&toDate=2025-11-05
```

### 2. Get Sales with Debt
```
GET /api/shops/{shopId}/pos/sales?paymentStatus=debt
```

### 3. Search Sale
```
GET /api/shops/{shopId}/pos/sales?search=SAL-20251105
```

### 4. Get Analytics (This Month)
```
GET /api/shops/{shopId}/pos/analytics
```

### 5. Get Analytics (Custom Period)
```
GET /api/shops/{shopId}/pos/analytics?fromDate=2025-11-01&toDate=2025-11-05
```

### 6. Add Payment to Debt
```
POST /api/shops/{shopId}/pos/sales/{saleId}/payments

{
  "paymentMethod": "cash",
  "amount": 1000,
  "notes": "Partial payment"
}
```

### 7. Process Refund
```
POST /api/shops/{shopId}/pos/sales/{saleId}/refund

{
  "amount": 2500,
  "reason": "Product defective",
  "restockItems": true
}
```

### 8. Create Customer
```
POST /api/shops/{shopId}/customers

{
  "name": "Customer Name",
  "phone": "+255 712 345 678",
  "creditLimit": 50000
}
```

### 9. Search Customers
```
GET /api/shops/{shopId}/customers?search=mama
```

### 10. Get Customers with Debt
```
GET /api/shops/{shopId}/customers?hasDebt=true
```

---

## 💡 Response Keys (camelCase)

### Sale Object
```
id, shopId, customerId, userId, userName
saleNumber, subtotal, taxRate, taxAmount
discountAmount, discountPercentage
totalAmount, amountPaid, changeAmount, debtAmount
profitAmount, status, statusLabel, paymentStatus
saleDate, createdAt, updatedAt
```

### Customer Object
```
id, shopId, name, phone, email, address
creditLimit, currentDebt, totalPurchases, totalPaid
availableCredit, notes, createdAt, updatedAt
```

### Sale Item Object
```
id, saleId, productId, productName, productSku
quantity, unitType, originalPrice, sellingPrice, costPrice
discountAmount, subtotal, total, profit
```

---

## 🎯 Payment Methods
- `cash` - Cash payment
- `mobile_money` - M-Pesa, Tigo Pesa, etc.
- `bank_transfer` - Bank transfer
- `credit` - Customer credit (debt)
- `cheque` - Cheque payment

---

## 📊 Sale Status
- `completed` - Sale completed successfully
- `pending` - Payment pending
- `cancelled` - Sale cancelled
- `refunded` - Fully refunded
- `partially_refunded` - Partially refunded

---

## 💳 Payment Status
- `paid` - Fully paid
- `partially_paid` - Partially paid (has debt)
- `pending` - Payment not processed
- `debt` - Full amount on credit

---

## ⚠️ Important Notes

1. **Stock Check**: System automatically validates stock availability
2. **Credit Limit**: Enforced automatically for credit sales
3. **Sale Numbers**: Auto-generated (SAL-YYYYMMDD-XXXX)
4. **Profit**: Calculated automatically based on cost price
5. **Tax**: Applied to subtotal before discount
6. **Discount**: Applied after tax calculation
7. **Change**: Only for cash payments
8. **Restock**: Optional when processing refunds
9. **Debt Payment**: Can be added incrementally
10. **Customer Delete**: Blocked if customer has debt

---

## 🔍 Search & Filter Examples

### Sales with Multiple Filters
```
GET /api/shops/{shopId}/pos/sales?
  status=completed&
  paymentStatus=paid&
  fromDate=2025-11-01&
  toDate=2025-11-05&
  search=mama&
  perPage=20
```

### Customer Search
```
GET /api/shops/{shopId}/customers?search=712345&perPage=10
```

---

## 📈 Analytics Metrics

### Summary Metrics
- `totalSales` - Number of sales
- `totalRevenue` - Total sales amount
- `totalProfit` - Total profit earned
- `totalDebt` - Outstanding debt
- `averageSale` - Average sale value
- `profitMargin` - Profit percentage

### Breakdown
- `salesByPaymentMethod` - Revenue by payment type
- `topProducts` - Best selling products
- `salesByDay` - Daily sales breakdown

---

## 🛠️ Error Handling

### Insufficient Stock
```json
{
  "success": false,
  "message": "Insufficient stock for Product X. Available: 5"
}
```

### Credit Limit Exceeded
```json
{
  "success": false,
  "message": "Customer does not have sufficient credit limit.",
  "data": {
    "requiredCredit": 5000,
    "availableCredit": 2000
  }
}
```

### Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "items": ["At least one item is required."]
  }
}
```

---

## 🎨 Kotlin Example

```kotlin
// Data Classes
data class CompleteSaleRequest(
    val items: List<CartItem>,
    val customer: Customer?,
    val paymentMethod: String,
    val total: Double,
    val amountReceived: Double,
    val change: Double,
    val taxRate: Double? = 18.0,
    val discountAmount: Double? = null,
    val notes: String? = null
)

// API Call
suspend fun completeSale(
    shopId: String,
    request: CompleteSaleRequest
): Result<SaleResponse> {
    return apiService.completeSale(shopId, request)
}

// Usage
val result = completeSale(
    shopId = activeShop.id,
    request = CompleteSaleRequest(
        items = cartItems,
        customer = selectedCustomer,
        paymentMethod = "cash",
        total = totalAmount,
        amountReceived = receivedAmount,
        change = changeAmount
    )
)
```

---

## ✅ Testing Checklist

- [ ] Complete sale without customer (cash)
- [ ] Complete sale with customer
- [ ] Credit sale (debt)
- [ ] Partial payment
- [ ] Sale with tax
- [ ] Sale with discount
- [ ] Process refund
- [ ] Add debt payment
- [ ] Get sales history
- [ ] View analytics
- [ ] Create customer
- [ ] Update customer
- [ ] Search customers
- [ ] Stock validation works
- [ ] Credit limit enforcement works

---

## 🚀 Production Ready!

All endpoints tested and working. All responses in camelCase for Kotlin compatibility.

**Happy Selling!** 💰🎉

