# POS System API Documentation

## Overview
A complete Point of Sale (POS) system with advanced features including customer management, credit tracking, sales analytics, refunds, split payments, and automatic stock management.

---

## Features

### ✅ Core POS Features
- **Complete Sale Transaction** - Process sales with multiple items
- **Multiple Payment Methods** - Cash, Mobile Money, Card, Credit, Cheque
- **Customer Tracking** - Link sales to customers
- **Credit Management** - Allow customers to buy on credit with limits
- **Split Payments** - Multiple payment methods for one sale
- **Automatic Stock Updates** - Inventory automatically reduced on sale
- **VAT/Tax Calculation** - Configurable tax rates
- **Discounts** - Percentage or fixed amount discounts
- **Change Calculation** - Automatic change for cash payments

### ✅ Advanced Features
- **Sales History** - Complete transaction history with filters
- **Sales Analytics** - Revenue, profit, and performance metrics
- **Refund Processing** - Full or partial refunds with optional restocking
- **Debt Payment** - Record payments for credit sales
- **Customer Management** - Full CRUD for customer records
- **Credit Limit Tracking** - Automatic enforcement of credit limits
- **Profit Tracking** - Real-time profit calculation per sale
- **Daily Reports** - Sales by day, payment method, top products
- **Top Selling Products** - Identify best performers
- **Search & Filters** - Find sales by customer, date, status, payment method

---

## API Endpoints

### 1. Complete Sale Transaction
**Endpoint:** `POST /api/shops/{shop}/pos/sales`

**Description:** Process a complete sale with cart items and payment

**Request Body:**
```json
{
  "items": [
    {
      "id": "product-uuid",
      "name": "Coca Cola 500ml",
      "originalPrice": 2500,
      "currentPrice": 2500,
      "quantity": 3,
      "total": 7500,
      "unit": "bottle",
      "allowFractional": false,
      "negotiable": true
    }
  ],
  "customer": {
    "id": "customer-uuid",
    "name": "Mama John",
    "phone": "+255 712 345 678",
    "debt": 45000
  },
  "paymentMethod": "cash",
  "total": 5900,
  "amountReceived": 6000,
  "change": 100,
  "taxRate": 18,
  "discountAmount": 0,
  "discountPercentage": 0,
  "notes": "Customer paid with exact change"
}
```

**Field Descriptions:**
- `items` (required): Array of cart items
  - `id`: Product UUID
  - `name`: Product name
  - `originalPrice`: Original price before negotiation
  - `currentPrice`: Final negotiated price
  - `quantity`: Quantity to sell (can be fractional)
  - `total`: Line total (currentPrice × quantity)
  - `unit`: Unit of measurement
  - `allowFractional`: Whether fractional quantities allowed
  - `negotiable`: Whether price was negotiated
- `customer` (optional): Customer object if sale is linked to customer
- `paymentMethod` (required): cash, mobile_money, bank_transfer, credit, cheque
- `total` (required): Grand total amount
- `amountReceived` (required): Amount customer paid
- `change` (optional): Change to give back (for cash)
- `taxRate` (optional): Tax percentage (e.g., 18 for 18% VAT)
- `discountAmount` (optional): Fixed discount amount
- `discountPercentage` (optional): Percentage discount
- `notes` (optional): Additional notes

**Response:**
```json
{
  "success": true,
  "message": "Sale completed successfully",
  "code": 201,
  "data": {
    "sale": {
      "id": "sale-uuid",
      "shopId": "shop-uuid",
      "customerId": "customer-uuid",
      "customer": {
        "id": "customer-uuid",
        "name": "Mama John",
        "phone": "+255 712 345 678",
        "currentDebt": 45000,
        "creditLimit": 100000,
        "availableCredit": 55000
      },
      "userId": "user-uuid",
      "userName": "John Doe",
      "saleNumber": "SAL-20251105-0001",
      "subtotal": 5000,
      "taxRate": 18,
      "taxAmount": 900,
      "discountAmount": 0,
      "discountPercentage": 0,
      "totalAmount": 5900,
      "amountPaid": 6000,
      "changeAmount": 100,
      "debtAmount": 0,
      "profitAmount": 1500,
      "status": "completed",
      "statusLabel": "Completed",
      "statusColor": "green",
      "paymentStatus": "paid",
      "notes": "Customer paid with exact change",
      "saleDate": "2025-11-05T14:30:00.000000Z",
      "items": [
        {
          "id": "item-uuid",
          "productId": "product-uuid",
          "productName": "Coca Cola 500ml",
          "quantity": 3,
          "originalPrice": 2500,
          "sellingPrice": 2500,
          "costPrice": 2000,
          "subtotal": 7500,
          "total": 7500,
          "profit": 1500
        }
      ],
      "payments": [
        {
          "id": "payment-uuid",
          "paymentMethod": "cash",
          "paymentMethodLabel": "Cash",
          "amount": 6000,
          "paymentDate": "2025-11-05T14:30:00.000000Z"
        }
      ],
      "itemsCount": 1,
      "createdAt": "2025-11-05T14:30:00.000000Z"
    }
  }
}
```

**Validation Rules:**
- At least 1 item required
- All products must exist in database
- Stock must be sufficient (if tracking enabled)
- Customer must have sufficient credit (if buying on credit)
- Amount received must cover total (unless customer has credit)

**Error Responses:**

Insufficient Stock:
```json
{
  "success": false,
  "message": "Insufficient stock for Coca Cola 500ml. Available: 10",
  "data": {
    "productName": "Coca Cola 500ml",
    "requestedQuantity": 15,
    "availableStock": 10
  }
}
```

Insufficient Credit:
```json
{
  "success": false,
  "message": "Customer does not have sufficient credit limit.",
  "data": {
    "requiredCredit": 5000,
    "availableCredit": 3000
  }
}
```

---

### 2. Get Sales History
**Endpoint:** `GET /api/shops/{shop}/pos/sales`

**Description:** Retrieve sales history with filters and pagination

**Query Parameters:**
- `status` (optional): completed, pending, cancelled, refunded
- `paymentStatus` (optional): paid, partially_paid, pending, debt
- `customerId` (optional): Filter by customer UUID
- `fromDate` (optional): Start date (YYYY-MM-DD)
- `toDate` (optional): End date (YYYY-MM-DD)
- `search` (optional): Search by sale number or customer name/phone
- `perPage` (optional): Results per page (default: 15)

**Example:** `GET /api/shops/{shop}/pos/sales?status=completed&fromDate=2025-11-01&perPage=20`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "sales": [
      {
        "id": "sale-uuid",
        "saleNumber": "SAL-20251105-0001",
        "totalAmount": 5900,
        "amountPaid": 6000,
        "debtAmount": 0,
        "profitAmount": 1500,
        "status": "completed",
        "paymentStatus": "paid",
        "customer": {
          "id": "customer-uuid",
          "name": "Mama John"
        },
        "itemsCount": 3,
        "saleDate": "2025-11-05T14:30:00.000000Z"
      }
    ],
    "pagination": {
      "total": 150,
      "currentPage": 1,
      "lastPage": 8,
      "perPage": 20
    }
  }
}
```

---

### 3. Get Single Sale Details
**Endpoint:** `GET /api/shops/{shop}/pos/sales/{sale}`

**Description:** Get complete details of a specific sale including items, payments, and refunds

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "sale": {
      "id": "sale-uuid",
      "saleNumber": "SAL-20251105-0001",
      "totalAmount": 5900,
      "profitAmount": 1500,
      "status": "completed",
      "customer": { ... },
      "items": [ ... ],
      "payments": [ ... ],
      "refunds": [ ... ]
    }
  }
}
```

---

### 4. Get Sales Analytics
**Endpoint:** `GET /api/shops/{shop}/pos/analytics`

**Description:** Comprehensive sales analytics and performance metrics

**Query Parameters:**
- `fromDate` (optional): Start date (default: start of month)
- `toDate` (optional): End date (default: end of month)

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "summary": {
      "totalSales": 145,
      "totalRevenue": 5450000.00,
      "totalProfit": 1890000.00,
      "totalDebt": 125000.00,
      "averageSale": 37586.21,
      "profitMargin": 34.68
    },
    "salesByPaymentMethod": {
      "cash": {
        "count": 98,
        "total": 3250000.00
      },
      "mobile_money": {
        "count": 35,
        "total": 1850000.00
      },
      "credit": {
        "count": 12,
        "total": 350000.00
      }
    },
    "topProducts": [
      {
        "productId": "product-uuid",
        "productName": "Coca Cola 500ml",
        "totalQuantity": 450,
        "totalRevenue": 1125000.00
      }
    ],
    "salesByDay": [
      {
        "date": "2025-11-01",
        "count": 15,
        "revenue": 425000.00
      }
    ],
    "period": {
      "from": "2025-11-01T00:00:00.000000Z",
      "to": "2025-11-05T23:59:59.000000Z"
    }
  }
}
```

---

### 5. Process Refund
**Endpoint:** `POST /api/shops/{shop}/pos/sales/{sale}/refund`

**Description:** Process a full or partial refund for a sale

**Request Body:**
```json
{
  "amount": 5900,
  "reason": "Product damaged",
  "notes": "Customer returned within 7 days",
  "restockItems": true
}
```

**Field Descriptions:**
- `amount` (required): Refund amount (max: amount paid)
- `reason` (required): Reason for refund
- `notes` (optional): Additional notes
- `restockItems` (optional): Whether to return items to inventory (default: false)

**Response:**
```json
{
  "success": true,
  "message": "Refund processed successfully",
  "code": 200,
  "data": {
    "sale": { ... },
    "refund": {
      "id": "refund-uuid",
      "amount": 5900,
      "reason": "Product damaged",
      "refundDate": "2025-11-05T16:00:00.000000Z"
    }
  }
}
```

---

### 6. Add Payment (Debt Payment)
**Endpoint:** `POST /api/shops/{shop}/pos/sales/{sale}/payments`

**Description:** Record a payment for a sale with outstanding debt

**Request Body:**
```json
{
  "paymentMethod": "cash",
  "amount": 2000,
  "referenceNumber": "REF-123456",
  "notes": "Partial payment received"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Payment added successfully",
  "code": 200,
  "data": {
    "sale": { ... },
    "remainingDebt": 3000
  }
}
```

---

## Customer Management

### 7. Get All Customers
**Endpoint:** `GET /api/shops/{shop}/customers`

**Query Parameters:**
- `search` (optional): Search by name, phone, or email
- `hasDebt` (optional): Filter customers with outstanding debt
- `perPage` (optional): Results per page (default: 15)

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "customers": [
      {
        "id": "customer-uuid",
        "name": "Mama John",
        "phone": "+255 712 345 678",
        "email": "mama.john@example.com",
        "creditLimit": 100000,
        "currentDebt": 45000,
        "totalPurchases": 350000,
        "totalPaid": 305000,
        "availableCredit": 55000
      }
    ],
    "pagination": { ... }
  }
}
```

---

### 8. Create Customer
**Endpoint:** `POST /api/shops/{shop}/customers`

**Request Body:**
```json
{
  "name": "Mama John",
  "phone": "+255 712 345 678",
  "email": "mama.john@example.com",
  "address": "Dar es Salaam, Tanzania",
  "creditLimit": 100000,
  "notes": "Regular customer, pays on time"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Customer created successfully",
  "code": 201,
  "data": {
    "customer": { ... }
  }
}
```

---

### 9. Update Customer
**Endpoint:** `PUT /api/shops/{shop}/customers/{customer}`

**Request Body:** Same as Create Customer

---

### 10. Get Customer Details
**Endpoint:** `GET /api/shops/{shop}/customers/{customer}`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "customer": { ... },
    "recentSales": [ ... ],
    "statistics": {
      "totalSales": 25,
      "totalDebt": 45000,
      "totalPurchases": 350000,
      "totalPaid": 305000
    }
  }
}
```

---

### 11. Delete Customer
**Endpoint:** `DELETE /api/shops/{shop}/customers/{customer}`

**Note:** Cannot delete customers with outstanding debt

---

## Business Logic

### Stock Management
- **Automatic Deduction**: Stock is automatically reduced when sale is completed
- **Stock Adjustments**: Each sale creates a stock adjustment record
- **Stock Validation**: System prevents sales if insufficient stock
- **Restock on Refund**: Optional automatic restocking when processing refunds

### Credit Management
- **Credit Limits**: Set maximum credit per customer
- **Automatic Tracking**: System tracks current debt and available credit
- **Payment Enforcement**: Prevents sales if customer exceeds credit limit
- **Debt Reduction**: Automatic debt reduction when payments received

### Profit Calculation
```
Item Profit = (Selling Price - Cost Price) × Quantity
Sale Profit = Sum of all item profits
Profit Margin % = (Total Profit / Total Revenue) × 100
```

### Payment Status Logic
- **paid**: Amount paid >= Total amount
- **partially_paid**: Amount paid > 0 but < Total amount
- **debt**: Amount paid = 0 (full credit)
- **pending**: Payment not yet processed

### Sale Number Format
```
SAL-YYYYMMDD-XXXX
Example: SAL-20251105-0001
```
- Auto-increments daily
- Resets sequence each day
- Unique per shop

---

## Error Handling

### Common Error Codes
- **404**: Sale/Customer not found
- **422**: Validation error or business rule violation
- **500**: Internal server error

### Validation Errors
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "items": ["At least one item is required to complete the sale."],
    "paymentMethod": ["Please select a payment method."]
  }
}
```

---

## Kotlin Data Classes

### Sale Request
```kotlin
data class CompleteSaleRequest(
    val items: List<CartItem>,
    val customer: Customer?,
    val paymentMethod: String,
    val total: Double,
    val amountReceived: Double,
    val change: Double,
    val taxRate: Double? = null,
    val discountAmount: Double? = null,
    val discountPercentage: Double? = null,
    val notes: String? = null
)

data class CartItem(
    val id: String,
    val name: String,
    val originalPrice: Double,
    val currentPrice: Double,
    val quantity: Double,
    val total: Double,
    val unit: String?,
    val allowFractional: Boolean,
    val negotiable: Boolean
)

data class Customer(
    val id: String,
    val name: String,
    val phone: String?,
    val debt: Double
)
```

### Sale Response
```kotlin
data class SaleResponse(
    val success: Boolean,
    val message: String,
    val code: Int,
    val data: SaleData
)

data class SaleData(
    val sale: Sale
)

data class Sale(
    val id: String,
    val shopId: String,
    val customerId: String?,
    val customer: CustomerDetail?,
    val saleNumber: String,
    val subtotal: Double,
    val taxRate: Double,
    val taxAmount: Double,
    val totalAmount: Double,
    val amountPaid: Double,
    val changeAmount: Double,
    val debtAmount: Double,
    val profitAmount: Double,
    val status: String,
    val statusLabel: String,
    val paymentStatus: String,
    val saleDate: String,
    val items: List<SaleItem>,
    val payments: List<Payment>,
    val itemsCount: Int
)
```

---

## Advanced Features

### 1. Multiple Payment Methods
The system supports split payments through multiple payment records per sale.

### 2. Price Negotiation
Track both original and final prices to see negotiation history.

### 3. Fractional Quantities
Support for selling fractional quantities (e.g., 2.5kg of rice).

### 4. Tax Calculation
Automatic VAT/tax calculation with configurable rates.

### 5. Discount Support
Both percentage and fixed amount discounts supported.

### 6. Daily Analytics
- Sales count by day
- Revenue trends
- Top selling products
- Payment method distribution

### 7. Customer Insights
- Purchase history
- Debt tracking
- Credit utilization
- Payment patterns

---

## Best Practices

1. **Always validate stock** before completing sale
2. **Check credit limits** for credit transactions
3. **Record all payments** including partial payments
4. **Use sale numbers** for reference in communications
5. **Enable restocking** when processing refunds
6. **Regular reconciliation** of debt accounts
7. **Monitor top products** for inventory planning
8. **Track profit margins** to optimize pricing

---

## Testing

### Test Sale Request
```bash
curl -X POST https://api.example.com/api/shops/{shop-id}/pos/sales \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {
        "id": "product-uuid",
        "name": "Coca Cola",
        "originalPrice": 2500,
        "currentPrice": 2500,
        "quantity": 2,
        "total": 5000,
        "unit": "bottle",
        "allowFractional": false,
        "negotiable": false
      }
    ],
    "paymentMethod": "cash",
    "total": 5900,
    "amountReceived": 6000,
    "change": 100,
    "taxRate": 18
  }'
```

---

## System Status

✅ **All POS features implemented and tested**
✅ **Database migrations completed**
✅ **All responses in camelCase for Kotlin**
✅ **Automatic stock management**
✅ **Complete customer management**
✅ **Sales analytics ready**
✅ **Refund system operational**
✅ **Credit management active**

**Ready for production use!** 🚀

