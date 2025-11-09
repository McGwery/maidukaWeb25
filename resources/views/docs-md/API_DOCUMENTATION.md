# Maiduka25 API Documentation

**Version:** 1.0.0  
**Base URL:** `/api`  
**Last Updated:** November 9, 2025

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Response Format](#response-format)
4. [Error Handling](#error-handling)
5. [API Endpoints](#api-endpoints)
   - [Authentication](#authentication-endpoints)
   - [Shop Management](#shop-management)
   - [Categories](#categories)
   - [Shop Members](#shop-members)
   - [Products](#products)
   - [Purchase Orders](#purchase-orders)
   - [POS & Sales](#pos--sales)
   - [Customers](#customers)
   - [Expenses](#expenses)
   - [Reports & Analytics](#reports--analytics)
   - [Shop Settings](#shop-settings)
   - [Savings & Goals](#savings--goals)
   - [Subscriptions](#subscriptions)
   - [Ads & Promotions](#ads--promotions)
   - [Chat & Messaging](#chat--messaging)

---

## Overview

The Maiduka25 API is a RESTful API built with Laravel that provides comprehensive shop management capabilities including inventory, sales, expenses, messaging, and more.

### Key Features
- Phone-based authentication with OTP
- Multi-shop management
- Inventory and stock management
- Point of Sale (POS) system
- Purchase order management
- Real-time chat and messaging
- Financial reports and analytics
- Savings goals tracking
- Subscription management
- Advertising platform

---

## Authentication

The API uses Laravel Sanctum for authentication. All protected endpoints require a Bearer token in the Authorization header.

### Headers
```
Authorization: Bearer {access_token}
Content-Type: application/json
Accept: application/json
```

---

## Response Format

All API responses follow a standardized format:

### Success Response
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "responseTime": 45.32,
  "data": {
    // Response data
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "responseTime": 23.15,
  "data": {
    "errors": {
      "field": ["Error description"]
    }
  }
}
```

### Paginated Response
```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "responseTime": 67.89,
  "data": {
    "items": [],
    "pagination": {
      "total": 100,
      "currentPage": 1,
      "lastPage": 10,
      "perPage": 10,
      "from": 1,
      "to": 10
    }
  }
}
```

---

## Error Handling

### HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | OK - Request succeeded |
| 201 | Created - Resource created successfully |
| 400 | Bad Request - Invalid request parameters |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation failed |
| 500 | Internal Server Error |

---

## API Endpoints

## Authentication Endpoints

### Register User

**POST** `/api/auth/register`

Register a new user with phone number.

**Request Body:**
```json
{
  "name": "John Doe",
  "phone": "+255712345678",
  "password": "SecurePass123",
  "password_confirmation": "SecurePass123"
}
```

**Response:** `201 Created`
```json
{
  "success": true,
  "message": "Registration successful. Please verify your phone number.",
  "responseTime": 45.32,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "phone": "+255712345678",
      "phoneVerifiedAt": null,
      "isPhoneLoginEnabled": true,
      "activeShop": null
    },
    "meta": {
      "requiresVerification": true,
      "verificationMethod": "otp"
    }
  }
}
```

---

### Verify Phone Number

**POST** `/api/auth/verify-phone`

Verify phone number with OTP code.

**Request Body:**
```json
{
  "phone": "+255712345678",
  "otp": "123456"
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Phone number verified successfully.",
  "responseTime": 32.15,
  "data": {
    "token": {
      "accessToken": "1|abcdef123456...",
      "tokenType": "Bearer"
    },
    "user": {
      "id": 1,
      "name": "John Doe",
      "phone": "+255712345678",
      "phoneVerifiedAt": "2025-11-09T10:30:00.000000Z",
      "isPhoneLoginEnabled": true
    }
  }
}
```

---

### Login with Password

**POST** `/api/auth/login`

Login with phone number and password.

**Request Body:**
```json
{
  "phone": "+255712345678",
  "password": "SecurePass123"
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Login successful.",
  "responseTime": 28.45,
  "data": {
    "token": {
      "accessToken": "2|xyz789abc...",
      "tokenType": "Bearer"
    },
    "user": {
      "id": 1,
      "name": "John Doe",
      "phone": "+255712345678",
      "activeShop": {
        "id": 5,
        "name": "My Shop",
        "type": "retail"
      }
    }
  }
}
```

---

### Request Login OTP

**POST** `/api/auth/login/otp/request`

Request OTP code for passwordless login.

**Request Body:**
```json
{
  "phone": "+255712345678"
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "OTP sent successfully.",
  "responseTime": 156.78,
  "data": {
    "otpExpiresIn": 300,
    "phone": "+255712345678"
  }
}
```

---

### Login with OTP

**POST** `/api/auth/login/otp/verify`

Login with OTP code.

**Request Body:**
```json
{
  "phone": "+255712345678",
  "otp": "123456"
}
```

**Response:** `200 OK` - Same as Login with Password

---

### Request Password Reset

**POST** `/api/auth/password/reset/request`

Request OTP for password reset.

**Request Body:**
```json
{
  "phone": "+255712345678"
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Password reset OTP sent successfully.",
  "responseTime": 125.34,
  "data": {
    "otpExpiresIn": 300
  }
}
```

---

### Reset Password

**POST** `/api/auth/password/reset`

Reset password with OTP.

**Request Body:**
```json
{
  "phone": "+255712345678",
  "otp": "123456",
  "password": "NewSecurePass123",
  "password_confirmation": "NewSecurePass123"
}
```

**Response:** `200 OK`

---

### Logout

**POST** `/api/auth/logout`

🔒 **Authentication Required**

Logout and revoke access token.

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Logged out successfully.",
  "responseTime": 12.45,
  "data": null
}
```

---

## Shop Management

**Note:** All shop endpoints automatically include active subscription information when available. This includes subscription plan, expiry date, days remaining, and whether the subscription is expiring soon.

### Get All Shops

**GET** `/api/shops`

🔒 **Authentication Required**

Get all shops owned by or accessible to the authenticated user. Each shop includes its active subscription details.

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Shops retrieved successfully.",
  "responseTime": 45.67,
  "data": {
    "shops": [
      {
        "id": 1,
        "name": "My Retail Shop",
        "businessType": {
          "value": "retail",
          "label": "Retail Store"
        },
        "phoneNumber": "+255712345678",
        "address": "123 Main St, Dar es Salaam",
        "agentCode": "AGENT001",
        "currency": {
          "code": "TZS",
          "symbol": "TSh",
          "label": "Tanzanian Shilling"
        },
        "imageUrl": "https://example.com/shop-image.jpg",
        "isActive": true,
        "isCurrentSelected": true,
        "owner": {
          "id": 1,
          "name": "John Doe",
          "phone": "+255712345678"
        },
        "activeSubscription": {
          "id": "uuid-123",
          "plan": "pro",
          "planLabel": "Pro Plan",
          "type": "monthly",
          "expiresAt": "2025-12-09T10:00:00.000000Z",
          "daysRemaining": 30,
          "isExpiringSoon": false
        },
        "createdAt": "2025-01-15T10:00:00.000000Z",
        "updatedAt": "2025-11-01T08:30:00.000000Z"
      }
    ],
    "activeShop": {
      "id": 1,
      "name": "My Retail Shop",
      "activeSubscription": {
        "id": "uuid-123",
        "plan": "pro",
        "planLabel": "Pro Plan",
        "type": "monthly",
        "expiresAt": "2025-12-09T10:00:00.000000Z",
        "daysRemaining": 30,
        "isExpiringSoon": false
      }
    },
    "totalShops": 3,
    "activeShops": 2
  }
}
```

---

### Create Shop

**POST** `/api/shops`

🔒 **Authentication Required**

Create a new shop. **A Premium subscription (30 days) is automatically activated for all new shops.**

**Request Body:**
```json
{
  "name": "New Shop Name",
  "type": "retail",
  "description": "Shop description",
  "phone": "+255712345678",
  "email": "shop@example.com",
  "address": "123 Main St",
  "city": "Dar es Salaam",
  "region": "Dar es Salaam",
  "country": "Tanzania"
}
```

**Shop Types:**
- `retail` - Retail Store
- `wholesale` - Wholesale Business
- `service` - Service Provider
- `restaurant` - Restaurant/Cafe
- `other` - Other Business Type

**Response:** `201 Created`
```json
{
  "success": true,
  "message": "Shop created successfully with Premium subscription.",
  "responseTime": 67.89,
  "data": {
    "shop": {
      "id": 2,
      "name": "New Shop Name",
      "businessType": {
        "value": "retail",
        "label": "Retail Store"
      },
      "phoneNumber": "+255712345678",
      "isActive": true,
      "activeSubscription": {
        "id": "subscription-uuid",
        "plan": "premium",
        "planLabel": "Premium Plan",
        "type": "both",
        "expiresAt": "2025-12-09T10:00:00.000000Z",
        "daysRemaining": 30,
        "isExpiringSoon": false
      },
      "createdAt": "2025-11-09T10:00:00.000000Z"
    }
  }
}
```

---

### Get Shop Details

**GET** `/api/shops/{shop}`

🔒 **Authentication Required**

Get detailed information about a specific shop.

**Response:** `200 OK`

---

### Update Shop

**PUT** `/api/shops/{shop}`

🔒 **Authentication Required**

Update shop information.

**Request Body:** Same as Create Shop

**Response:** `200 OK`

---

### Delete Shop

**DELETE** `/api/shops/{shop}`

🔒 **Authentication Required**

Delete a shop (soft delete).

**Response:** `200 OK`

---

### Switch Active Shop

**POST** `/api/shops/{shop}/switch`

🔒 **Authentication Required**

Switch the active shop for the current user.

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Active shop switched successfully.",
  "responseTime": 23.45,
  "data": {
    "activeShop": {
      "id": 2,
      "name": "Another Shop"
    }
  }
}
```

---

### Set Active Shop

**POST** `/api/shops/{shop}/active`

🔒 **Authentication Required**

Set a shop as the active shop.

**Response:** `200 OK`

---

## Categories

### Get All Categories

**GET** `/api/shops/categories/ctx`

🔒 **Authentication Required**

Get all product categories.

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Categories retrieved successfully.",
  "responseTime": 34.56,
  "data": {
    "categories": [
      {
        "id": 1,
        "name": "Electronics",
        "description": "Electronic items",
        "productsCount": 45
      }
    ]
  }
}
```

---

## Shop Members

### Get Shop Members

**GET** `/api/shops/{shop}/members`

🔒 **Authentication Required**

Get all members of a shop.

**Query Parameters:**
- `search` (optional) - Search by name or phone
- `role` (optional) - Filter by role: `owner`, `manager`, `employee`, `viewer`
- `perPage` (optional) - Items per page (default: 15)

**Response:** `200 OK` (Paginated)

---

### Add Shop Member

**POST** `/api/shops/{shop}/members`

🔒 **Authentication Required**

Add a new member to the shop.

**Request Body:**
```json
{
  "phone": "+255712345678",
  "role": "employee",
  "permissions": ["view_products", "create_sales"]
}
```

**Available Roles:**
- `owner` - Full access
- `manager` - Management access
- `employee` - Limited access
- `viewer` - Read-only access

**Response:** `201 Created`

---

### Get Member Details

**GET** `/api/shops/{shop}/members/{member}`

🔒 **Authentication Required**

Get details of a specific shop member.

**Response:** `200 OK`

---

### Update Member

**PUT** `/api/shops/{shop}/members/{member}`

🔒 **Authentication Required**

Update member role and permissions.

**Request Body:**
```json
{
  "role": "manager",
  "permissions": ["view_products", "create_sales", "manage_inventory"]
}
```

**Response:** `200 OK`

---

### Remove Member

**DELETE** `/api/shops/{shop}/members/{member}`

🔒 **Authentication Required**

Remove a member from the shop.

**Response:** `200 OK`

---

## Products

### Get Products

**GET** `/api/shops/{shop}/products`

🔒 **Authentication Required**

Get all products for a shop with filtering and search.

**Query Parameters:**
- `search` (optional) - Search by name, SKU, or barcode
- `category_id` (optional) - Filter by category
- `product_type` (optional) - `physical` or `service`
- `low_stock` (optional) - Boolean, filter low stock items
- `sort_by` (optional) - Field to sort by
- `sort_direction` (optional) - `asc` or `desc`
- `per_page` (optional) - Items per page (default: 15)

**Response:** `200 OK` (Paginated)
```json
{
  "success": true,
  "message": "Products retrieved successfully.",
  "responseTime": 56.78,
  "data": {
    "items": [
      {
        "id": 1,
        "productName": "Laptop Dell XPS 15",
        "sku": "LAP-DELL-001",
        "barcode": "1234567890123",
        "productType": "physical",
        "category": {
          "id": 1,
          "name": "Electronics"
        },
        "pricePerUnit": 2500000,
        "costPerUnit": 2000000,
        "currentStock": 10,
        "lowStockThreshold": 5,
        "trackInventory": true,
        "unitType": "piece",
        "description": "High-performance laptop",
        "createdAt": "2025-10-01T10:00:00.000000Z"
      }
    ],
    "pagination": {
      "total": 100,
      "currentPage": 1,
      "lastPage": 7,
      "perPage": 15,
      "from": 1,
      "to": 15
    }
  }
}
```

---

### Create Product

**POST** `/api/shops/{shop}/products`

🔒 **Authentication Required**

Create a new product.

**Request Body (Physical Product):**
```json
{
  "product_name": "Laptop Dell XPS 15",
  "sku": "LAP-DELL-001",
  "barcode": "1234567890123",
  "product_type": "physical",
  "category_id": 1,
  "price_per_unit": 2500000,
  "cost_per_unit": 2000000,
  "current_stock": 10,
  "low_stock_threshold": 5,
  "track_inventory": true,
  "unit_type": "piece",
  "description": "High-performance laptop",
  "supplier_name": "Tech Supplies Ltd",
  "supplier_contact": "+255712345678"
}
```

**Request Body (Service):**
```json
{
  "product_name": "Computer Repair",
  "product_type": "service",
  "category_id": 2,
  "price_per_unit": 50000,
  "hourly_rate": 25000,
  "service_duration": 2.0,
  "description": "Professional computer repair service"
}
```

**Unit Types:**
- `piece`, `box`, `carton`, `pack`, `dozen`, `kg`, `gram`, `liter`, `ml`, `meter`, `cm`, `hour`, `day`, `month`, `other`

**Response:** `201 Created`

---

### Get Product Details

**GET** `/api/shops/{shop}/products/{product}`

🔒 **Authentication Required**

Get detailed information about a specific product.

**Response:** `200 OK`

---

### Update Product

**PUT** `/api/shops/{shop}/products/{product}`

🔒 **Authentication Required**

Update product information.

**Request Body:** Same fields as Create Product

**Response:** `200 OK`

---

### Delete Product

**DELETE** `/api/shops/{shop}/products/{product}`

🔒 **Authentication Required**

Delete a product (soft delete).

**Response:** `200 OK`

---

### Update Stock

**PATCH** `/api/shops/{shop}/products/{product}/stock`

🔒 **Authentication Required**

Adjust product stock levels.

**Request Body:**
```json
{
  "adjustment_type": "add",
  "quantity": 50,
  "reason": "New stock arrival",
  "reference_number": "PO-2025-001",
  "notes": "Supplier: Tech Supplies Ltd"
}
```

**Adjustment Types:**
- `add` - Add stock
- `remove` - Remove stock
- `set` - Set absolute stock level
- `damage` - Stock damaged/lost
- `return` - Customer return
- `adjustment` - Manual adjustment

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Stock updated successfully.",
  "responseTime": 34.56,
  "data": {
    "product": {
      "id": 1,
      "productName": "Laptop Dell XPS 15",
      "previousStock": 10,
      "currentStock": 60,
      "difference": 50
    },
    "adjustment": {
      "id": 123,
      "adjustmentType": "add",
      "quantity": 50,
      "reason": "New stock arrival",
      "createdAt": "2025-11-09T15:30:00.000000Z"
    }
  }
}
```

---

### Get Stock Adjustment History

**GET** `/api/shops/{shop}/products/{product}/adjustments`

🔒 **Authentication Required**

Get stock adjustment history for a product.

**Query Parameters:**
- `adjustment_type` (optional) - Filter by type
- `start_date` (optional) - From date (YYYY-MM-DD)
- `end_date` (optional) - To date (YYYY-MM-DD)
- `per_page` (optional) - Items per page (default: 15)

**Response:** `200 OK` (Paginated)

---

### Inventory Analysis

**GET** `/api/shops/{shop}/inventory/analysis`

🔒 **Authentication Required**

Get comprehensive inventory analysis.

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Inventory analysis retrieved successfully.",
  "responseTime": 89.45,
  "data": {
    "summary": {
      "totalProducts": 150,
      "totalValue": 125000000,
      "lowStockItems": 12,
      "outOfStockItems": 3
    },
    "topProducts": [],
    "lowStockProducts": [],
    "categoryBreakdown": []
  }
}
```

---

### Get Adjustments Summary

**GET** `/api/shops/{shop}/inventory/adjustments`

🔒 **Authentication Required**

Get summary of all stock adjustments.

**Query Parameters:**
- `start_date` (optional)
- `end_date` (optional)
- `per_page` (optional)

**Response:** `200 OK` (Paginated)

---

## Purchase Orders

### Get Purchase Orders (As Buyer)

**GET** `/api/shops/{shop}/purchase-orders/buyer`

🔒 **Authentication Required**

Get purchase orders created by this shop.

**Query Parameters:**
- `status` (optional) - Filter by status
- `supplier_shop_id` (optional) - Filter by supplier
- `start_date` (optional)
- `end_date` (optional)
- `per_page` (optional)

**Response:** `200 OK` (Paginated)

---

### Get Purchase Orders (As Seller)

**GET** `/api/shops/{shop}/purchase-orders/seller`

🔒 **Authentication Required**

Get purchase orders where this shop is the supplier.

**Query Parameters:** Same as buyer endpoint

**Response:** `200 OK` (Paginated)

---

### Create Purchase Order

**POST** `/api/shops/{shop}/purchase-orders`

🔒 **Authentication Required**

Create a new purchase order.

**Request Body:**
```json
{
  "supplier_shop_id": 5,
  "order_date": "2025-11-09",
  "expected_delivery_date": "2025-11-15",
  "items": [
    {
      "product_id": 10,
      "quantity": 100,
      "unit_price": 50000,
      "notes": "Urgent delivery"
    },
    {
      "product_id": 15,
      "quantity": 50,
      "unit_price": 75000
    }
  ],
  "payment_terms": "Net 30",
  "shipping_cost": 50000,
  "tax_amount": 180000,
  "discount_amount": 0,
  "notes": "First order with this supplier"
}
```

**Response:** `201 Created`

---

### Get Purchase Order Details

**GET** `/api/shops/{shop}/purchase-orders/{purchaseOrder}`

🔒 **Authentication Required**

Get detailed information about a purchase order.

**Response:** `200 OK`

---

### Update Purchase Order

**PUT** `/api/shops/{shop}/purchase-orders/{purchaseOrder}`

🔒 **Authentication Required**

Update purchase order details.

**Request Body:** Same as Create Purchase Order

**Response:** `200 OK`

---

### Delete Purchase Order

**DELETE** `/api/shops/{shop}/purchase-orders/{purchaseOrder}`

🔒 **Authentication Required**

Delete a purchase order.

**Response:** `200 OK`

---

### Update Purchase Order Status

**PATCH** `/api/shops/{shop}/purchase-orders/{purchaseOrder}/status`

🔒 **Authentication Required**

Update the status of a purchase order.

**Request Body:**
```json
{
  "status": "confirmed",
  "notes": "Order confirmed by supplier"
}
```

**Statuses:**
- `draft` - Order being prepared
- `pending` - Awaiting supplier confirmation
- `confirmed` - Confirmed by supplier
- `shipped` - Items shipped
- `delivered` - Items delivered
- `completed` - Order completed
- `cancelled` - Order cancelled

**Response:** `200 OK`

---

### Record Payment

**POST** `/api/shops/{shop}/purchase-orders/{purchaseOrder}/payments`

🔒 **Authentication Required**

Record a payment for a purchase order.

**Request Body:**
```json
{
  "amount": 500000,
  "payment_method": "bank_transfer",
  "payment_date": "2025-11-09",
  "reference_number": "TXN-123456",
  "notes": "First installment"
}
```

**Payment Methods:**
- `cash`, `bank_transfer`, `mobile_money`, `cheque`, `credit_card`, `other`

**Response:** `201 Created`

---

### Transfer Stock

**POST** `/api/shops/{shop}/purchase-orders/{purchaseOrder}/transfer-stock`

🔒 **Authentication Required**

Transfer stock from purchase order to inventory.

**Request Body:**
```json
{
  "transfer_all": true,
  "items": [
    {
      "purchase_order_item_id": 1,
      "quantity": 50
    }
  ],
  "notes": "Partial delivery received"
}
```

**Response:** `200 OK`

---

## POS & Sales

### Complete Sale

**POST** `/api/shops/{shop}/pos/sales`

🔒 **Authentication Required**

Process a sale transaction.

**Request Body:**
```json
{
  "customer_id": 5,
  "items": [
    {
      "product_id": 10,
      "quantity": 2,
      "unit_price": 50000,
      "discount": 5000,
      "notes": "Special discount applied"
    }
  ],
  "payments": [
    {
      "method": "cash",
      "amount": 95000,
      "reference_number": null
    }
  ],
  "subtotal": 100000,
  "tax_amount": 0,
  "discount_amount": 5000,
  "total_amount": 95000,
  "notes": "Walk-in customer",
  "sale_date": "2025-11-09T14:30:00Z"
}
```

**Response:** `201 Created`
```json
{
  "success": true,
  "message": "Sale completed successfully.",
  "responseTime": 78.90,
  "data": {
    "sale": {
      "id": 456,
      "saleNumber": "SALE-2025-456",
      "totalAmount": 95000,
      "status": "completed",
      "customer": {
        "id": 5,
        "name": "Jane Smith"
      },
      "items": [],
      "payments": [],
      "createdAt": "2025-11-09T14:30:00.000000Z"
    }
  }
}
```

---

### Get Sales

**GET** `/api/shops/{shop}/pos/sales`

🔒 **Authentication Required**

Get sales history with filtering.

**Query Parameters:**
- `status` (optional) - `completed`, `pending`, `refunded`, `cancelled`
- `customer_id` (optional)
- `start_date` (optional)
- `end_date` (optional)
- `search` (optional) - Sale number or customer name
- `per_page` (optional)

**Response:** `200 OK` (Paginated)

---

### Get Sale Details

**GET** `/api/shops/{shop}/pos/sales/{sale}`

🔒 **Authentication Required**

Get detailed information about a specific sale.

**Response:** `200 OK`

---

### Get Sales Analytics

**GET** `/api/shops/{shop}/pos/analytics`

🔒 **Authentication Required**

Get sales analytics and metrics.

**Query Parameters:**
- `period` (optional) - `today`, `week`, `month`, `year`, `custom`
- `start_date` (optional) - For custom period
- `end_date` (optional) - For custom period

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Sales analytics retrieved successfully.",
  "responseTime": 123.45,
  "data": {
    "summary": {
      "totalSales": 50,
      "totalRevenue": 5000000,
      "averageSaleValue": 100000,
      "totalProfit": 1200000
    },
    "topProducts": [],
    "salesByDay": [],
    "salesByPaymentMethod": []
  }
}
```

---

### Refund Sale

**POST** `/api/shops/{shop}/pos/sales/{sale}/refund`

🔒 **Authentication Required**

Process a sale refund.

**Request Body:**
```json
{
  "refund_type": "full",
  "items": [
    {
      "sale_item_id": 1,
      "quantity": 1,
      "reason": "Defective product"
    }
  ],
  "refund_amount": 50000,
  "refund_method": "cash",
  "reason": "Product defect",
  "notes": "Customer returned within 7 days"
}
```

**Refund Types:**
- `full` - Full refund
- `partial` - Partial refund

**Response:** `200 OK`

---

### Add Payment to Sale

**POST** `/api/shops/{shop}/pos/sales/{sale}/payments`

🔒 **Authentication Required**

Add a payment to an existing sale (for pending sales).

**Request Body:**
```json
{
  "amount": 50000,
  "method": "mobile_money",
  "reference_number": "MP123456789",
  "notes": "Second installment"
}
```

**Response:** `201 Created`

---

## Customers

### Get Customers

**GET** `/api/shops/{shop}/customers`

🔒 **Authentication Required**

Get all customers for a shop.

**Query Parameters:**
- `search` (optional) - Search by name or phone
- `per_page` (optional)

**Response:** `200 OK` (Paginated)

---

### Create Customer

**POST** `/api/shops/{shop}/customers`

🔒 **Authentication Required**

Create a new customer.

**Request Body:**
```json
{
  "name": "John Customer",
  "phone": "+255712345678",
  "email": "customer@example.com",
  "address": "123 Customer St",
  "city": "Dar es Salaam",
  "notes": "VIP customer"
}
```

**Response:** `201 Created`

---

### Get Customer Details

**GET** `/api/shops/{shop}/customers/{customer}`

🔒 **Authentication Required**

Get detailed customer information including purchase history.

**Response:** `200 OK`

---

### Update Customer

**PUT** `/api/shops/{shop}/customers/{customer}`

🔒 **Authentication Required**

Update customer information.

**Request Body:** Same as Create Customer

**Response:** `200 OK`

---

### Delete Customer

**DELETE** `/api/shops/{shop}/customers/{customer}`

🔒 **Authentication Required**

Delete a customer.

**Response:** `200 OK`

---

## Expenses

### Get Expenses

**GET** `/api/shops/{shop}/expenses`

🔒 **Authentication Required**

Get all expenses for a shop.

**Query Parameters:**
- `category` (optional) - Filter by expense category
- `start_date` (optional)
- `end_date` (optional)
- `search` (optional)
- `per_page` (optional)

**Response:** `200 OK` (Paginated)

---

### Create Expense

**POST** `/api/shops/{shop}/expenses`

🔒 **Authentication Required**

Record a new expense.

**Request Body:**
```json
{
  "category": "utilities",
  "amount": 150000,
  "expense_date": "2025-11-09",
  "description": "Monthly electricity bill",
  "payment_method": "bank_transfer",
  "reference_number": "BILL-2025-11",
  "notes": "November electricity"
}
```

**Expense Categories:**
- `rent`, `utilities`, `salaries`, `supplies`, `marketing`, `maintenance`, `transport`, `tax`, `insurance`, `other`

**Response:** `201 Created`

---

### Get Expense Summary

**GET** `/api/shops/{shop}/expenses/summary`

🔒 **Authentication Required**

Get expense summary and analytics.

**Query Parameters:**
- `start_date` (optional)
- `end_date` (optional)
- `group_by` (optional) - `category`, `month`, `week`

**Response:** `200 OK`

---

### Get Expense Categories

**GET** `/api/shops/{shop}/expenses/categories`

🔒 **Authentication Required**

Get list of expense categories with totals.

**Response:** `200 OK`

---

### Get Expense Details

**GET** `/api/shops/{shop}/expenses/{expense}`

🔒 **Authentication Required**

Get detailed information about an expense.

**Response:** `200 OK`

---

### Update Expense

**PUT** `/api/shops/{shop}/expenses/{expense}`

🔒 **Authentication Required**

Update expense information.

**Request Body:** Same as Create Expense

**Response:** `200 OK`

---

### Delete Expense

**DELETE** `/api/shops/{shop}/expenses/{expense}`

🔒 **Authentication Required**

Delete an expense.

**Response:** `200 OK`

---

## Reports & Analytics

### Overview Report

**GET** `/api/shops/{shop}/reports/overview`

🔒 **Authentication Required**

Get comprehensive business overview report.

**Query Parameters:**
- `period` (optional) - `today`, `week`, `month`, `year`, `custom`
- `start_date` (optional)
- `end_date` (optional)

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Overview report retrieved successfully.",
  "responseTime": 156.78,
  "data": {
    "sales": {
      "total": 5000000,
      "count": 50,
      "average": 100000
    },
    "expenses": {
      "total": 1500000,
      "count": 25
    },
    "profit": {
      "gross": 2000000,
      "net": 500000,
      "margin": 10.0
    },
    "inventory": {
      "value": 10000000,
      "lowStockItems": 12
    },
    "customers": {
      "total": 150,
      "new": 10
    }
  }
}
```

---

### Sales Report

**GET** `/api/shops/{shop}/reports/sales`

🔒 **Authentication Required**

Get detailed sales report.

**Query Parameters:**
- `period` (optional)
- `start_date` (optional)
- `end_date` (optional)
- `group_by` (optional) - `day`, `week`, `month`, `product`, `category`

**Response:** `200 OK`

---

### Products Report

**GET** `/api/shops/{shop}/reports/products`

🔒 **Authentication Required**

Get product performance report.

**Query Parameters:**
- `sort_by` (optional) - `sales`, `revenue`, `profit`, `stock`
- `period` (optional)
- `start_date` (optional)
- `end_date` (optional)

**Response:** `200 OK`

---

### Financial Report

**GET** `/api/shops/{shop}/reports/financial`

🔒 **Authentication Required**

Get financial report including profit & loss.

**Query Parameters:**
- `period` (optional)
- `start_date` (optional)
- `end_date` (optional)

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Financial report retrieved successfully.",
  "responseTime": 189.45,
  "data": {
    "income": {
      "sales": 5000000,
      "other": 100000,
      "total": 5100000
    },
    "expenses": {
      "cost_of_goods": 3000000,
      "operating": 1500000,
      "total": 4500000
    },
    "profit": {
      "gross": 2000000,
      "net": 600000,
      "margin": 11.76
    }
  }
}
```

---

### Employees Report

**GET** `/api/shops/{shop}/reports/employees`

🔒 **Authentication Required**

Get employee performance report.

**Query Parameters:**
- `period` (optional)
- `start_date` (optional)
- `end_date` (optional)

**Response:** `200 OK`

---

## Shop Settings

### Get Settings

**GET** `/api/shops/{shop}/settings`

🔒 **Authentication Required**

Get shop settings and preferences.

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Settings retrieved successfully.",
  "responseTime": 23.45,
  "data": {
    "settings": {
      "currency": "TZS",
      "taxRate": 18.0,
      "lowStockThreshold": 10,
      "enableInventoryTracking": true,
      "enableCustomerManagement": true,
      "defaultPaymentMethod": "cash",
      "receiptHeader": "My Shop",
      "receiptFooter": "Thank you for your business!"
    }
  }
}
```

---

### Update Settings

**PUT** `/api/shops/{shop}/settings`

🔒 **Authentication Required**

Update shop settings.

**Request Body:**
```json
{
  "currency": "TZS",
  "tax_rate": 18.0,
  "low_stock_threshold": 10,
  "enable_inventory_tracking": true,
  "enable_customer_management": true,
  "default_payment_method": "cash",
  "receipt_header": "My Shop",
  "receipt_footer": "Thank you!"
}
```

**Response:** `200 OK`

---

### Reset Settings

**POST** `/api/shops/{shop}/settings/reset`

🔒 **Authentication Required**

Reset settings to default values.

**Response:** `200 OK`

---

### Get Settings Categories

**GET** `/api/settings-categories`

🔒 **Authentication Required**

Get available settings categories and options.

**Response:** `200 OK`

---

## Savings & Goals

### Get Savings Settings

**GET** `/api/shops/{shop}/savings/settings`

🔒 **Authentication Required**

Get savings account settings.

**Response:** `200 OK`

---

### Update Savings Settings

**PUT** `/api/shops/{shop}/savings/settings`

🔒 **Authentication Required**

Update savings settings.

**Request Body:**
```json
{
  "auto_save_percentage": 10.0,
  "auto_save_enabled": true,
  "target_amount": 10000000
}
```

**Response:** `200 OK`

---

### Deposit to Savings

**POST** `/api/shops/{shop}/savings/deposit`

🔒 **Authentication Required**

Make a deposit to savings account.

**Request Body:**
```json
{
  "amount": 500000,
  "description": "Monthly savings",
  "goal_id": 5
}
```

**Response:** `201 Created`

---

### Withdraw from Savings

**POST** `/api/shops/{shop}/savings/withdraw`

🔒 **Authentication Required**

Withdraw from savings account.

**Request Body:**
```json
{
  "amount": 200000,
  "description": "Emergency withdrawal",
  "reason": "Urgent business expense"
}
```

**Response:** `201 Created`

---

### Get Savings Transactions

**GET** `/api/shops/{shop}/savings/transactions`

🔒 **Authentication Required**

Get savings transaction history.

**Query Parameters:**
- `type` (optional) - `deposit`, `withdrawal`
- `start_date` (optional)
- `end_date` (optional)
- `per_page` (optional)

**Response:** `200 OK` (Paginated)

---

### Get Savings Summary

**GET** `/api/shops/{shop}/savings/summary`

🔒 **Authentication Required**

Get savings account summary.

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Savings summary retrieved successfully.",
  "responseTime": 45.67,
  "data": {
    "currentBalance": 5000000,
    "totalDeposits": 8000000,
    "totalWithdrawals": 3000000,
    "goalProgress": 50.0,
    "monthlyAverage": 500000
  }
}
```

---

### Get Savings Goals

**GET** `/api/shops/{shop}/savings/goals`

🔒 **Authentication Required**

Get all savings goals.

**Response:** `200 OK`

---

### Create Savings Goal

**POST** `/api/shops/{shop}/savings/goals`

🔒 **Authentication Required**

Create a new savings goal.

**Request Body:**
```json
{
  "name": "Equipment Purchase",
  "target_amount": 10000000,
  "target_date": "2025-12-31",
  "description": "Save for new equipment",
  "auto_contribute": true,
  "monthly_contribution": 500000
}
```

**Response:** `201 Created`

---

### Update Savings Goal

**PUT** `/api/shops/{shop}/savings/goals/{goal}`

🔒 **Authentication Required**

Update a savings goal.

**Request Body:** Same as Create Savings Goal

**Response:** `200 OK`

---

### Delete Savings Goal

**DELETE** `/api/shops/{shop}/savings/goals/{goal}`

🔒 **Authentication Required**

Delete a savings goal.

**Response:** `200 OK`

---

## Subscriptions

### Get Subscription Plans

**GET** `/api/subscription-plans`

🔒 **Authentication Required**

Get available subscription plans.

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Subscription plans retrieved successfully.",
  "responseTime": 34.56,
  "data": {
    "plans": [
      {
        "id": "basic",
        "name": "Basic Plan",
        "price": 50000,
        "billingPeriod": "monthly",
        "features": [
          "Up to 100 products",
          "Basic reports",
          "Email support"
        ]
      },
      {
        "id": "pro",
        "name": "Pro Plan",
        "price": 150000,
        "billingPeriod": "monthly",
        "features": [
          "Unlimited products",
          "Advanced reports",
          "Priority support",
          "Multi-user access"
        ]
      }
    ]
  }
}
```

---

### Get Shop Subscriptions

**GET** `/api/shops/{shop}/subscriptions`

🔒 **Authentication Required**

Get all subscriptions for a shop.

**Response:** `200 OK` (Paginated)

---

### Get Current Subscription

**GET** `/api/shops/{shop}/subscriptions/current`

🔒 **Authentication Required**

Get the current active subscription.

**Response:** `200 OK`

---

### Get Subscription Statistics

**GET** `/api/shops/{shop}/subscriptions/statistics`

🔒 **Authentication Required**

Get subscription usage statistics.

**Response:** `200 OK`

---

### Create Subscription

**POST** `/api/shops/{shop}/subscriptions`

🔒 **Authentication Required**

Subscribe to a plan.

**Request Body:**
```json
{
  "plan": "pro",
  "billing_period": "monthly",
  "payment_method": "mobile_money",
  "auto_renew": true
}
```

**Response:** `201 Created`

---

### Get Subscription Details

**GET** `/api/shops/{shop}/subscriptions/{subscription}`

🔒 **Authentication Required**

Get details of a specific subscription.

**Response:** `200 OK`

---

### Update Subscription

**PUT** `/api/shops/{shop}/subscriptions/{subscription}`

🔒 **Authentication Required**

Update subscription details.

**Request Body:**
```json
{
  "plan": "enterprise",
  "auto_renew": true
}
```

**Response:** `200 OK`

---

### Cancel Subscription

**POST** `/api/shops/{shop}/subscriptions/{subscription}/cancel`

🔒 **Authentication Required**

Cancel a subscription.

**Request Body:**
```json
{
  "reason": "Too expensive",
  "feedback": "Looking for cheaper alternatives"
}
```

**Response:** `200 OK`

---

### Renew Subscription

**POST** `/api/shops/{shop}/subscriptions/{subscription}/renew`

🔒 **Authentication Required**

Manually renew a subscription.

**Response:** `200 OK`

---

### Suspend Subscription

**POST** `/api/shops/{shop}/subscriptions/{subscription}/suspend`

🔒 **Authentication Required**

Suspend a subscription temporarily.

**Response:** `200 OK`

---

### Activate Subscription

**POST** `/api/shops/{shop}/subscriptions/{subscription}/activate`

🔒 **Authentication Required**

Activate a suspended subscription.

**Response:** `200 OK`

---

## Ads & Promotions

### Get Ads Feed

**GET** `/api/ads/feed`

🔒 **Authentication Required**

Get promotional ads feed (Deals Tab).

**Query Parameters:**
- `placement` (optional) - `feed`, `banner`, `sidebar`, `popup`
- `category_id` (optional)
- `location` (optional)
- `per_page` (optional)

**Response:** `200 OK` (Paginated)
```json
{
  "success": true,
  "message": "Ads feed retrieved successfully.",
  "responseTime": 67.89,
  "data": {
    "items": [
      {
        "id": 1,
        "title": "50% Off Electronics!",
        "description": "Special discount on all electronics this week",
        "imageUrl": "https://example.com/ad-image.jpg",
        "shop": {
          "id": 5,
          "name": "Tech Store",
          "city": "Dar es Salaam"
        },
        "placement": "feed",
        "status": "active",
        "validUntil": "2025-11-30T23:59:59.000000Z"
      }
    ],
    "pagination": {}
  }
}
```

---

### Get Shop Ads

**GET** `/api/shops/ads`

🔒 **Authentication Required**

Get all ads created by shops owned by the authenticated user.

**Query Parameters:**
- `shop_id` (optional)
- `status` (optional) - `draft`, `pending`, `active`, `paused`, `rejected`, `expired`
- `placement` (optional)
- `per_page` (optional)

**Response:** `200 OK` (Paginated)

---

### Create Ad

**POST** `/api/shops/ads`

🔒 **Authentication Required**

Create a new advertisement.

**Request Body:**
```json
{
  "shop_id": 5,
  "title": "Special Promotion",
  "description": "Get 20% off on all products this weekend!",
  "image_url": "https://example.com/promo.jpg",
  "target_url": "https://myshop.com/promo",
  "placement": "feed",
  "ad_type": "promotion",
  "budget": 500000,
  "daily_budget": 50000,
  "target_location": "Dar es Salaam",
  "target_category_id": 1,
  "start_date": "2025-11-10",
  "end_date": "2025-11-30",
  "auto_approve": false
}
```

**Ad Placements:**
- `feed` - Main feed
- `banner` - Top banner
- `sidebar` - Sidebar
- `popup` - Pop-up

**Ad Types:**
- `promotion` - Product promotion
- `brand` - Brand awareness
- `event` - Event announcement
- `other` - Other

**Response:** `201 Created`

---

### Get Ad Details

**GET** `/api/shops/ads/{ad}`

🔒 **Authentication Required**

Get detailed information about an ad.

**Response:** `200 OK`

---

### Update Ad

**PUT** `/api/shops/ads/{ad}`

🔒 **Authentication Required**

Update advertisement details.

**Request Body:** Same as Create Ad

**Response:** `200 OK`

---

### Delete Ad

**DELETE** `/api/shops/ads/{ad}`

🔒 **Authentication Required**

Delete an advertisement.

**Response:** `200 OK`

---

### Track Ad View

**POST** `/api/shops/ads/{ad}/view`

🔒 **Authentication Required**

Track an ad impression/view.

**Response:** `200 OK`

---

### Track Ad Click

**POST** `/api/shops/ads/{ad}/click`

🔒 **Authentication Required**

Track an ad click.

**Response:** `200 OK`

---

### Get Ad Analytics

**GET** `/api/shops/ads/{ad}/analytics`

🔒 **Authentication Required**

Get analytics for an advertisement.

**Query Parameters:**
- `start_date` (optional)
- `end_date` (optional)

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Ad analytics retrieved successfully.",
  "responseTime": 78.90,
  "data": {
    "impressions": 5000,
    "clicks": 250,
    "clickThroughRate": 5.0,
    "spend": 125000,
    "costPerClick": 500,
    "conversions": 15,
    "byDate": []
  }
}
```

---

### Approve Ad

**POST** `/api/shops/ads/{ad}/approve`

🔒 **Authentication Required** (Admin only)

Approve a pending advertisement.

**Response:** `200 OK`

---

### Reject Ad

**POST** `/api/shops/ads/{ad}/reject`

🔒 **Authentication Required** (Admin only)

Reject an advertisement.

**Request Body:**
```json
{
  "reason": "Content violates policy"
}
```

**Response:** `200 OK`

---

### Toggle Pause Ad

**POST** `/api/shops/ads/{ad}/toggle-pause`

🔒 **Authentication Required**

Pause or resume an advertisement.

**Response:** `200 OK`

---

## Chat & Messaging

### Get Conversations

**GET** `/api/shops/{shop}/chat/conversations`

🔒 **Authentication Required**

Get all conversations for a shop.

**Query Parameters:**
- `archived` (optional) - Boolean, show archived conversations
- `search` (optional) - Search by shop name
- `perPage` (optional)

**Response:** `200 OK` (Paginated)
```json
{
  "success": true,
  "message": "Conversations retrieved successfully.",
  "responseTime": 56.78,
  "data": {
    "items": [
      {
        "id": 1,
        "otherShop": {
          "id": 10,
          "name": "Supplier Shop",
          "type": "wholesale"
        },
        "lastMessage": {
          "content": "Order confirmed",
          "sentAt": "2025-11-09T14:30:00.000000Z",
          "isRead": true
        },
        "unreadCount": 0,
        "isArchived": false,
        "lastMessageAt": "2025-11-09T14:30:00.000000Z"
      }
    ],
    "pagination": {}
  }
}
```

---

### Get Conversation

**GET** `/api/shops/{shop}/chat/conversations/{conversation}`

🔒 **Authentication Required**

Get details of a specific conversation.

**Response:** `200 OK`

---

### Toggle Archive Conversation

**POST** `/api/shops/{shop}/chat/conversations/{conversation}/archive`

🔒 **Authentication Required**

Archive or unarchive a conversation.

**Response:** `200 OK`

---

### Get Messages

**GET** `/api/shops/{shop}/chat/conversations/{conversation}/messages`

🔒 **Authentication Required**

Get messages in a conversation.

**Query Parameters:**
- `before` (optional) - Message ID, get messages before this
- `after` (optional) - Message ID, get messages after this
- `limit` (optional) - Number of messages (default: 50)

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Messages retrieved successfully.",
  "responseTime": 45.67,
  "data": {
    "messages": [
      {
        "id": 123,
        "content": "Hello, I'd like to place an order",
        "messageType": "text",
        "sender": {
          "id": 5,
          "name": "My Shop"
        },
        "isRead": true,
        "readAt": "2025-11-09T14:35:00.000000Z",
        "reactions": [],
        "createdAt": "2025-11-09T14:30:00.000000Z"
      }
    ],
    "hasMore": false
  }
}
```

---

### Send Message

**POST** `/api/shops/{shop}/chat/messages`

🔒 **Authentication Required**

Send a message to another shop.

**Request Body:**
```json
{
  "receiver_shop_id": 10,
  "content": "Hello, I'd like to place an order",
  "message_type": "text",
  "attachment_url": null,
  "metadata": {}
}
```

**Message Types:**
- `text` - Text message
- `image` - Image message
- `file` - File attachment
- `order` - Order reference
- `product` - Product reference

**Response:** `201 Created`

---

### Delete Message

**DELETE** `/api/shops/{shop}/chat/conversations/{conversation}/messages/{message}`

🔒 **Authentication Required**

Delete a message.

**Response:** `200 OK`

---

### Mark as Read

**POST** `/api/shops/{shop}/chat/conversations/{conversation}/mark-read`

🔒 **Authentication Required**

Mark all messages in a conversation as read.

**Response:** `200 OK`

---

### Start Typing

**POST** `/api/shops/{shop}/chat/conversations/{conversation}/typing/start`

🔒 **Authentication Required**

Indicate that user is typing.

**Response:** `200 OK`

---

### Stop Typing

**POST** `/api/shops/{shop}/chat/conversations/{conversation}/typing/stop`

🔒 **Authentication Required**

Indicate that user stopped typing.

**Response:** `200 OK`

---

### Get Typing Status

**GET** `/api/shops/{shop}/chat/conversations/{conversation}/typing`

🔒 **Authentication Required**

Get typing status of other party.

**Response:** `200 OK`

---

### React to Message

**POST** `/api/shops/{shop}/chat/conversations/{conversation}/messages/{message}/react`

🔒 **Authentication Required**

Add a reaction to a message.

**Request Body:**
```json
{
  "emoji": "👍"
}
```

**Response:** `201 Created`

---

### Remove Reaction

**DELETE** `/api/shops/{shop}/chat/conversations/{conversation}/messages/{message}/react`

🔒 **Authentication Required**

Remove a reaction from a message.

**Response:** `200 OK`

---

### Block Shop

**POST** `/api/shops/{shop}/chat/block`

🔒 **Authentication Required**

Block another shop from messaging.

**Request Body:**
```json
{
  "blocked_shop_id": 10,
  "reason": "Spam messages"
}
```

**Response:** `200 OK`

---

### Unblock Shop

**POST** `/api/shops/{shop}/chat/unblock`

🔒 **Authentication Required**

Unblock a shop.

**Request Body:**
```json
{
  "blocked_shop_id": 10
}
```

**Response:** `200 OK`

---

### Get Blocked Shops

**GET** `/api/shops/{shop}/chat/blocked`

🔒 **Authentication Required**

Get list of blocked shops.

**Response:** `200 OK`

---

### Get Unread Count

**GET** `/api/shops/{shop}/chat/unread-count`

🔒 **Authentication Required**

Get total unread message count.

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Unread count retrieved successfully.",
  "responseTime": 12.34,
  "data": {
    "unreadCount": 5
  }
}
```

---

### Get Chat Statistics

**GET** `/api/shops/{shop}/chat/statistics`

🔒 **Authentication Required**

Get chat statistics.

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Chat statistics retrieved successfully.",
  "responseTime": 34.56,
  "data": {
    "totalConversations": 25,
    "activeConversations": 10,
    "totalMessages": 1500,
    "averageResponseTime": 120
  }
}
```

---

### Search Shops

**GET** `/api/shops/{shop}/chat/search-shops`

🔒 **Authentication Required**

Search for shops to start a conversation.

**Query Parameters:**
- `query` (required) - Search term
- `type` (optional) - Shop type filter
- `city` (optional) - City filter

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Shops found successfully.",
  "responseTime": 45.67,
  "data": {
    "shops": [
      {
        "id": 10,
        "name": "Supplier Shop",
        "type": "wholesale",
        "city": "Dar es Salaam",
        "hasConversation": false
      }
    ]
  }
}
```

---

## WebSocket Events

The API uses Laravel Reverb for real-time features. Subscribe to these channels for live updates:

### Private Channels

**Shop Channel:** `private-shop.{shopId}`

Events:
- `MessageSent` - New message received
- `MessageRead` - Message marked as read
- `MessageDeleted` - Message deleted
- `UserTyping` - Someone is typing
- `MessageReactionAdded` - Reaction added to message
- `MessageReactionRemoved` - Reaction removed from message

---

## Rate Limiting

API requests are rate-limited to prevent abuse:

- **Authentication endpoints:** 5 requests per minute
- **General API endpoints:** 60 requests per minute
- **Report endpoints:** 30 requests per minute

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1699545600
```

---

## Pagination

Paginated endpoints accept these query parameters:

- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15, max: 100)

---

## Date Formats

All dates should be in ISO 8601 format:
- Date: `YYYY-MM-DD` (e.g., `2025-11-09`)
- DateTime: `YYYY-MM-DDTHH:mm:ss.sssZ` (e.g., `2025-11-09T14:30:00.000Z`)

---

## Currency

All monetary values are in the shop's configured currency (default: TZS - Tanzanian Shilling).
Values are represented as integers in the smallest currency unit (e.g., cents).

Example: 50000 = 50,000 TZS = 50 thousand shillings

---

## Changelog

### Version 1.0.0 (2025-11-09)
- Initial API release
- Authentication with phone and OTP
- Complete shop management
- Inventory and POS system
- Purchase order management
- Real-time chat and messaging
- Financial reports
- Savings goals
- Subscription management
- Advertising platform

---

## Support

For API support, please contact:
- Email: support@maiduka25.com
- Documentation: https://docs.maiduka25.com
- Status: https://status.maiduka25.com

---

**End of Documentation**

