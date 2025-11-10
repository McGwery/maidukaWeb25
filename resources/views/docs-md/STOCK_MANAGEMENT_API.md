# Stock Management & Inventory Analysis API Documentation

## Overview
This document describes the new stock adjustment tracking and inventory analysis features added to the product management system.

## Features Added

### 1. Stock Adjustment Types
The system now tracks various types of stock adjustments:
- **DAMAGED** - Products that are damaged and can't be sold
- **EXPIRED** - Products that have expired
- **LOST** - Products that went missing
- **THEFT** - Products stolen
- **PERSONAL_USE** - Products used personally
- **DONATION** - Products donated
- **RETURN_TO_SUPPLIER** - Products returned to supplier
- **OTHER** - Other reasons
- **RESTOCK** - Adding new stock
- **ADJUSTMENT** - Manual stock adjustments

### 2. Inventory Calculations
The system now calculates:
- **Inventory Value** (Capital): Total cost of current stock
- **Expected Revenue**: Potential income if all stock is sold
- **Expected Profit**: Expected revenue minus inventory value
- **Profit Margin**: Percentage profit on invested capital
- **Total Losses**: Monetary value of damaged/lost/stolen stock

---

## API Endpoints

### 1. Adjust Product Stock
**Endpoint:** `PATCH /api/shops/{shop}/products/{product}/stock`

**Description:** Record a stock adjustment (add, reduce, or adjust inventory)

**Request Body:**
```json
{
  "type": "damaged",
  "quantity": -5,
  "reason": "Products damaged during storage",
  "notes": "Water damage in storage room"
}
```

**Field Descriptions:**
- `type` (required): One of the StockAdjustmentType values (damaged, expired, lost, theft, personal_use, donation, return_to_supplier, other, restock, adjustment)
- `quantity` (required): Number to adjust (negative for reductions, positive for additions)
- `reason` (required): Brief explanation for the adjustment
- `notes` (optional): Additional details

**Response:**
```json
{
  "success": true,
  "message": "Stock adjusted successfully",
  "code": 200,
  "data": {
    "product": {
      "id": "uuid",
      "productName": "Coca Cola 500ml",
      "currentStock": 45,
      ...
    },
    "adjustment": {
      "id": "uuid",
      "productId": "uuid",
      "productName": "Coca Cola 500ml",
      "userId": "uuid",
      "userName": "John Doe",
      "type": "damaged",
      "typeLabel": "Damaged",
      "quantity": -5,
      "valueAtTime": "15000.00",
      "monetaryImpact": 75000.00,
      "previousStock": 50,
      "newStock": 45,
      "reason": "Products damaged during storage",
      "notes": "Water damage in storage room",
      "isReduction": true,
      "createdAt": "2025-11-05T10:30:00.000000Z",
      "updatedAt": "2025-11-05T10:30:00.000000Z"
    }
  }
}
```

---

### 2. Get Stock Adjustment History
**Endpoint:** `GET /api/shops/{shop}/products/{product}/adjustments`

**Description:** View all stock adjustments for a specific product with filtering options

**Query Parameters:**
- `type` (optional): Filter by adjustment type
- `from_date` (optional): Filter adjustments from this date (YYYY-MM-DD)
- `to_date` (optional): Filter adjustments up to this date (YYYY-MM-DD)
- `per_page` (optional): Number of results per page (default: 15)

**Example:** `GET /api/shops/{shop}/products/{product}/adjustments?type=damaged&from_date=2025-11-01`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "adjustments": [
      {
        "id": "uuid",
        "productId": "uuid",
        "productName": "Coca Cola 500ml",
        "userId": "uuid",
        "userName": "John Doe",
        "type": "damaged",
        "typeLabel": "Damaged",
        "quantity": -5,
        "valueAtTime": "15000.00",
        "monetaryImpact": 75000.00,
        "previousStock": 50,
        "newStock": 45,
        "reason": "Products damaged during storage",
        "isReduction": true,
        "createdAt": "2025-11-05T10:30:00.000000Z"
      }
    ],
    "summary": {
      "totalReductions": -25,
      "totalAdditions": 100,
      "totalLossesValue": 375000.00
    },
    "pagination": {
      "total": 15,
      "currentPage": 1,
      "lastPage": 1,
      "perPage": 15
    }
  }
}
```

---

### 3. Get Inventory Analysis
**Endpoint:** `GET /api/shops/{shop}/inventory/analysis`

**Description:** Get comprehensive inventory value and profit analysis for the entire shop

**Query Parameters:**
- `include_products` (optional): Set to `true` to include per-product breakdown

**Example:** `GET /api/shops/{shop}/inventory/analysis?include_products=true`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "summary": {
      "totalInventoryValue": 5000000.00,
      "totalExpectedRevenue": 7500000.00,
      "totalExpectedProfit": 2500000.00,
      "overallProfitMarginPercentage": 50.00,
      "totalLosses": 375000.00,
      "netExpectedProfit": 2125000.00,
      "productsCount": 45,
      "lowStockCount": 5
    },
    "products": [
      {
        "productId": "uuid",
        "productName": "Coca Cola 500ml",
        "currentStock": 45,
        "costPerUnit": "15000.00",
        "inventoryValue": 675000.00,
        "expectedRevenue": 1012500.00,
        "expectedProfit": 337500.00,
        "expectedProfitMargin": 50.00,
        "totalLosses": 75000.00
      }
    ]
  }
}
```

**Calculations Explained:**
- **inventoryValue** = currentStock × costPerUnit
- **expectedRevenue** = currentStock × pricePerUnit (or pricePerItem × totalItems)
- **expectedProfit** = expectedRevenue - inventoryValue
- **profitMargin %** = (expectedProfit / inventoryValue) × 100
- **netExpectedProfit** = expectedProfit - totalLosses

---

### 4. Get Shop Adjustments Summary
**Endpoint:** `GET /api/shops/{shop}/inventory/adjustments`

**Description:** Get a summary of all stock adjustments across all products in the shop

**Query Parameters:**
- `from_date` (optional): Filter from this date (YYYY-MM-DD)
- `to_date` (optional): Filter to this date (YYYY-MM-DD)
- `per_page` (optional): Number of results per page (default: 15)

**Example:** `GET /api/shops/{shop}/inventory/adjustments?from_date=2025-11-01&to_date=2025-11-05`

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "adjustments": [
      {
        "id": "uuid",
        "productId": "uuid",
        "productName": "Coca Cola 500ml",
        "userName": "John Doe",
        "type": "damaged",
        "typeLabel": "Damaged",
        "quantity": -5,
        "monetaryImpact": 75000.00,
        "reason": "Products damaged during storage",
        "createdAt": "2025-11-05T10:30:00.000000Z"
      }
    ],
    "summaryByType": {
      "damaged": {
        "label": "Damaged",
        "count": 12,
        "totalQuantity": -60,
        "totalValue": 900000.00
      },
      "expired": {
        "label": "Expired",
        "count": 5,
        "totalQuantity": -25,
        "totalValue": 375000.00
      },
      "restock": {
        "label": "Restock",
        "count": 45,
        "totalQuantity": 500,
        "totalValue": 7500000.00
      }
    },
    "pagination": {
      "total": 62,
      "currentPage": 1,
      "lastPage": 5,
      "perPage": 15
    }
  }
}
```

---

## Usage Examples

### Example 1: Recording Damaged Products
```bash
curl -X PATCH https://api.example.com/api/shops/{shop-id}/products/{product-id}/stock \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "damaged",
    "quantity": -10,
    "reason": "Bottles broken during transport",
    "notes": "Delivery truck had an accident"
  }'
```

### Example 2: Adding New Stock (Restock)
```bash
curl -X PATCH https://api.example.com/api/shops/{shop-id}/products/{product-id}/stock \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "restock",
    "quantity": 100,
    "reason": "New stock received from supplier",
    "notes": "Invoice #12345"
  }'
```

### Example 3: Personal Use
```bash
curl -X PATCH https://api.example.com/api/shops/{shop-id}/products/{product-id}/stock \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "personal_use",
    "quantity": -2,
    "reason": "Used for office consumption"
  }'
```

### Example 4: Get Inventory Analysis
```bash
curl -X GET "https://api.example.com/api/shops/{shop-id}/inventory/analysis?include_products=true" \
  -H "Authorization: Bearer {token}"
```

---

## Business Insights

### What You Can Track:
1. **Capital Invested**: Total money tied up in inventory
2. **Potential Income**: How much you'll earn if you sell everything
3. **Expected Profits**: Your potential profit margins
4. **Losses**: How much money you've lost to damages, theft, etc.
5. **Net Profit**: Expected profit after accounting for losses

### Key Metrics to Monitor:
- **Profit Margin %**: Aim for healthy margins (40-60% is typical for retail)
- **Loss Rate**: Keep track of losses to identify problems
- **Stock Turnover**: Use adjustment history to understand stock movement
- **Low Stock Items**: Identify products that need restocking

---

## Database Schema

### stock_adjustments Table
```sql
-- Database columns (snake_case)
id (uuid, primary)
product_id (uuid, foreign key -> products)
user_id (uuid, foreign key -> users)
type (enum: damaged, expired, lost, theft, personal_use, donation, return_to_supplier, other, restock, adjustment)
quantity (integer: negative for reductions, positive for additions)
value_at_time (decimal: cost per unit at time of adjustment)
previous_stock (integer)
new_stock (integer)
reason (string: required explanation)
notes (text: optional additional details)
created_at (timestamp)
updated_at (timestamp)
deleted_at (timestamp, nullable)
```

**Note:** Database columns use snake_case, but API responses use camelCase for Kotlin compatibility.

---

## Important Notes

1. **Stock cannot go below zero**: The system prevents negative stock levels
2. **All adjustments are tracked**: Every change creates a permanent record
3. **User accountability**: Each adjustment records who made it
4. **Monetary impact**: The system captures the cost value at the time of adjustment
5. **Soft deletes**: Adjustments are never permanently deleted, only soft-deleted

---

## Error Responses

### Stock Below Zero Error
```json
{
  "success": false,
  "message": "Stock cannot be reduced below zero. Current stock: 5"
}
```

### Product Not Found Error
```json
{
  "success": false,
  "message": "Product not found in this shop."
}
```

### Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "type": ["Please specify the type of stock adjustment."],
    "quantity": ["Quantity cannot be zero."],
    "reason": ["Please provide a reason for this adjustment."]
  }
}
```

