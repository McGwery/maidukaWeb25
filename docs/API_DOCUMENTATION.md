# Maiduka25 API Documentation

**Version:** 1.0.0  
**Last Updated:** January 6, 2026  
**Base URL:** `/api`

---

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Shop Management](#shop-management)
4. [Shop Members](#shop-members)
5. [Categories](#categories)
6. [Products & Inventory](#products--inventory)
7. [Purchase Orders](#purchase-orders)
8. [POS & Sales](#pos--sales)
9. [Customers](#customers)
10. [Expenses](#expenses)
11. [Reports & Analytics](#reports--analytics)
12. [Shop Settings](#shop-settings)
13. [Savings & Goals](#savings--goals)
14. [Subscriptions](#subscriptions)
15. [Ads & Promotions](#ads--promotions)
16. [Chat & Messaging](#chat--messaging)

---

## Overview

This API provides a comprehensive backend for a multi-shop retail management system with features including:

- Phone-based authentication with OTP verification
- Multi-shop management with member roles
- Product and inventory management
- Point of Sale (POS) operations
- Purchase order management between shops
- Financial tracking (expenses, savings, reports)
- Subscription management
- Advertising and promotions
- Real-time chat between shops

### Authentication Method

All authenticated endpoints require a valid **Sanctum** token passed in the `Authorization` header:

```
Authorization: Bearer {token}
```

### Response Format

All responses follow a consistent JSON structure:

```json
{
  "success": true,
  "message": "Operation successful",
  "responseTime": 45.23,
  "data": { ... }
}
```

### Paginated Response Format

For endpoints that return lists, the response includes pagination metadata:

```json
{
  "success": true,
  "message": "Items retrieved successfully.",
  "responseTime": 52.18,
  "data": {
    "items": [ ... ],
    "pagination": {
      "total": 150,
      "currentPage": 1,
      "lastPage": 10,
      "perPage": 15,
      "from": 1,
      "to": 15
    }
  }
}
```

### Error Response

```json
{
  "success": false,
  "message": "Error description",
  "responseTime": 12.45,
  "data": {
    "errors": {
      "field_name": ["Error message"]
    }
  }
}
```

---

## Authentication

Base path: `/api/auth`

### Register User

Creates a new user account with phone number.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /auth/register` |
| **Auth Required** | No |

**Request Body:**
```json
{
  "name": "string",
  "phone": "string",
  "password": "string",
  "password_confirmation": "string"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Registration successful. Please verify your phone number.",
  "responseTime": 125.45,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "phone": "+255712345678",
      "isPhoneVerified": false,
      "isPhoneLoginEnabled": true,
      "activeShop": null,
      "createdAt": "2026-01-06T10:30:00.000000Z",
      "updatedAt": "2026-01-06T10:30:00.000000Z"
    },
    "meta": {
      "requiresVerification": true,
      "verificationMethod": "otp"
    }
  }
}
```

---

### Verify Phone

Verifies user's phone number using OTP.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /auth/verify-phone` |
| **Auth Required** | No |

**Request Body:**
```json
{
  "phone": "string",
  "otp": "string"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Phone number verified successfully.",
  "responseTime": 89.32,
  "data": {
    "token": {
      "accessToken": "1|abc123xyz...",
      "tokenType": "Bearer"
    },
    "user": {
      "id": 1,
      "name": "John Doe",
      "phone": "+255712345678",
      "isPhoneVerified": true,
      "isPhoneLoginEnabled": true,
      "activeShop": null,
      "createdAt": "2026-01-06T10:30:00.000000Z",
      "updatedAt": "2026-01-06T10:35:00.000000Z"
    }
  }
}
```

---

### Login with Password

Authenticates user with phone and password.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /auth/login` |
| **Auth Required** | No |

**Request Body:**
```json
{
  "phone": "string",
  "password": "string"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful.",
  "responseTime": 156.78,
  "data": {
    "token": {
      "accessToken": "1|abc123xyz...",
      "tokenType": "Bearer"
    },
    "user": {
      "id": 1,
      "name": "John Doe",
      "phone": "+255712345678",
      "isPhoneVerified": true,
      "isPhoneLoginEnabled": true,
      "activeShop": {
        "id": 1,
        "name": "My Shop",
        "businessType": {
          "value": "retail",
          "label": "Retail"
        },
        "phoneNumber": "+255712345678",
        "address": "Dar es Salaam",
        "agentCode": "SHP001",
        "currency": {
          "code": "TZS",
          "symbol": "TSh",
          "label": "Tanzanian Shilling"
        },
        "imageUrl": "https://example.com/logo.png",
        "isActive": true,
        "isCurrentSelected": true,
        "activeSubscription": {
          "id": 1,
          "plan": "premium",
          "planLabel": "Premium",
          "type": "both",
          "expiresAt": "2026-02-06T10:30:00.000000Z",
          "daysRemaining": 31,
          "isActive": true,
          "isExpired": false,
          "isExpiringSoon": false
        },
        "createdAt": "2026-01-01T10:30:00.000000Z",
        "updatedAt": "2026-01-06T10:30:00.000000Z"
      },
      "createdAt": "2026-01-01T10:30:00.000000Z",
      "updatedAt": "2026-01-06T10:30:00.000000Z"
    }
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "The provided credentials are incorrect.",
  "responseTime": 45.12,
  "data": {
    "errors": {
      "credentials": ["Invalid phone number or password"]
    }
  }
}
```

---

### Request Login OTP

Requests an OTP for passwordless login.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /auth/login/otp/request` |
| **Auth Required** | No |

**Request Body:**
```json
{
  "phone": "string"
}
```

**Response:**
```json
{
  "success": true,
  "message": "OTP sent successfully.",
  "responseTime": 234.56,
  "data": {
    "otpExpiresIn": 300,
    "phone": "+255712345678"
  }
}
```

**Error Response (Account not found):**
```json
{
  "success": false,
  "message": "Account does not exist, Please register first.",
  "responseTime": 23.45,
  "data": {
    "errors": {
      "credentials": ["Invalid phone number or password"]
    }
  }
}
```

**Error Response (OTP login disabled):**
```json
{
  "success": false,
  "message": "Phone login is not enabled for this account.",
  "responseTime": 18.32,
  "data": {
    "errors": {
      "phone": ["OTP login is disabled for this account"]
    }
  }
}
```

---

### Login with OTP

Authenticates user using OTP.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /auth/login/otp/verify` |
| **Auth Required** | No |

**Request Body:**
```json
{
  "phone": "string",
  "otp": "string"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful.",
  "responseTime": 145.67,
  "data": {
    "token": {
      "accessToken": "1|abc123xyz...",
      "tokenType": "Bearer"
    },
    "user": {
      "id": 1,
      "name": "John Doe",
      "phone": "+255712345678",
      "isPhoneVerified": true,
      "isPhoneLoginEnabled": true,
      "activeShop": {
        "id": 1,
        "name": "My Shop",
        "businessType": {
          "value": "retail",
          "label": "Retail"
        },
        "phoneNumber": "+255712345678",
        "address": "Dar es Salaam",
        "agentCode": "SHP001",
        "currency": {
          "code": "TZS",
          "symbol": "TSh",
          "label": "Tanzanian Shilling"
        },
        "imageUrl": null,
        "isActive": true,
        "isCurrentSelected": true,
        "activeSubscription": {
          "id": 1,
          "plan": "premium",
          "planLabel": "Premium",
          "type": "both",
          "expiresAt": "2026-02-06T10:30:00.000000Z",
          "daysRemaining": 31,
          "isActive": true,
          "isExpired": false,
          "isExpiringSoon": false
        },
        "createdAt": "2026-01-01T10:30:00.000000Z",
        "updatedAt": "2026-01-06T10:30:00.000000Z"
      },
      "createdAt": "2026-01-01T10:30:00.000000Z",
      "updatedAt": "2026-01-06T10:30:00.000000Z"
    }
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Invalid or expired OTP.",
  "responseTime": 34.21,
  "data": {
    "errors": {
      "otp": ["The OTP is invalid or has expired"]
    }
  }
}
```

---

### Request Password Reset OTP

Requests an OTP for password reset.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /auth/password/reset/request` |
| **Auth Required** | No |

**Request Body:**
```json
{
  "phone": "string"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password reset OTP sent successfully.",
  "responseTime": 198.45,
  "data": {
    "otpExpiresIn": 300,
    "phone": "+255712345678"
  }
}
```

---

### Reset Password

Resets user password using OTP.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /auth/password/reset` |
| **Auth Required** | No |

**Request Body:**
```json
{
  "phone": "string",
  "otp": "string",
  "password": "string",
  "password_confirmation": "string"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password reset successfully.",
  "responseTime": 112.34,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "phone": "+255712345678",
      "isPhoneVerified": true,
      "isPhoneLoginEnabled": true,
      "activeShop": null,
      "createdAt": "2026-01-01T10:30:00.000000Z",
      "updatedAt": "2026-01-06T10:45:00.000000Z"
    }
  }
}
```

---

### Logout

Invalidates the current authentication token.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /auth/logout` |
| **Auth Required** | Yes |

**Response:**
```json
{
  "success": true,
  "message": "Successfully logged out.",
  "responseTime": 23.45,
  "data": null
}
```

---

## Shop Management

Base path: `/api/shops`

All shop endpoints require authentication.

### List Shops

Returns all shops accessible by the authenticated user.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops` |
| **Auth Required** | Yes |

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `page` | integer | Page number for pagination |
| `per_page` | integer | Items per page |

**Response:**
```json
{
  "success": true,
  "message": "Shops retrieved successfully.",
  "responseTime": 67.89,
  "data": {
    "shops": [
      {
        "id": 1,
        "name": "My Shop",
        "businessType": {
          "value": "retail",
          "label": "Retail"
        },
        "phoneNumber": "+255712345678",
        "address": "Dar es Salaam",
        "agentCode": "SHP001",
        "currency": {
          "code": "TZS",
          "symbol": "TSh",
          "label": "Tanzanian Shilling"
        },
        "imageUrl": "https://example.com/logo.png",
        "isActive": true,
        "isCurrentSelected": true,
        "owner": {
          "id": 1,
          "name": "John Doe"
        },
        "activeSubscription": {
          "id": 1,
          "plan": "premium",
          "planLabel": "Premium",
          "type": "both",
          "expiresAt": "2026-02-06T10:30:00.000000Z",
          "daysRemaining": 31,
          "isActive": true,
          "isExpired": false,
          "isExpiringSoon": false
        },
        "createdAt": "2026-01-01T10:30:00.000000Z",
        "updatedAt": "2026-01-06T10:30:00.000000Z",
        "deletedAt": null
      }
    ],
    "activeShop": {
      "id": 1,
      "name": "My Shop",
      "businessType": {
        "value": "retail",
        "label": "Retail"
      },
      "phoneNumber": "+255712345678",
      "address": "Dar es Salaam",
      "agentCode": "SHP001",
      "currency": {
        "code": "TZS",
        "symbol": "TSh",
        "label": "Tanzanian Shilling"
      },
      "imageUrl": "https://example.com/logo.png",
      "isActive": true,
      "isCurrentSelected": true,
      "createdAt": "2026-01-01T10:30:00.000000Z",
      "updatedAt": "2026-01-06T10:30:00.000000Z"
    },
    "totalShops": 1,
    "activeShops": 1
  }
}
```

---

### Create Shop

Creates a new shop.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "name": "string",
  "business_type": "string",
  "phone_number": "string",
  "address": "string",
  "currency": "string",
  "image_url": "base64_string (optional)"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Shop created successfully with Premium subscription.",
  "responseTime": 234.56,
  "data": {
    "shop": {
      "id": 2,
      "name": "New Shop",
      "businessType": {
        "value": "retail",
        "label": "Retail"
      },
      "phoneNumber": "+255712345679",
      "address": "Arusha",
      "agentCode": "SHP002",
      "currency": {
        "code": "TZS",
        "symbol": "TSh",
        "label": "Tanzanian Shilling"
      },
      "imageUrl": null,
      "isActive": true,
      "isCurrentSelected": false,
      "owner": {
        "id": 1,
        "name": "John Doe"
      },
      "activeSubscription": {
        "id": 2,
        "plan": "premium",
        "planLabel": "Premium",
        "type": "both",
        "expiresAt": "2026-02-06T12:00:00.000000Z",
        "daysRemaining": 31,
        "isActive": true,
        "isExpired": false,
        "isExpiringSoon": false
      },
      "createdAt": "2026-01-06T12:00:00.000000Z",
      "updatedAt": "2026-01-06T12:00:00.000000Z",
      "deletedAt": null
    }
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Failed to create shop.",
  "responseTime": 45.12,
  "data": {
    "error": "Error message details"
  }
}
```

---

### Get Shop Details

Returns details of a specific shop.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}` |
| **Auth Required** | Yes |

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `shop` | integer | Shop ID |

**Response:**
```json
{
  "success": true,
  "message": "Shop retrieved successfully.",
  "responseTime": 45.23,
  "data": {
    "shop": {
      "id": 1,
      "name": "My Shop",
      "businessType": {
        "value": "retail",
        "label": "Retail"
      },
      "phoneNumber": "+255712345678",
      "address": "Dar es Salaam",
      "agentCode": "SHP001",
      "currency": {
        "code": "TZS",
        "symbol": "TSh",
        "label": "Tanzanian Shilling"
      },
      "imageUrl": "https://example.com/logo.png",
      "isActive": true,
      "isCurrentSelected": true,
      "owner": {
        "id": 1,
        "name": "John Doe"
      },
      "members": [
        {
          "id": 1,
          "userId": 2,
          "userName": "Jane Smith",
          "role": "manager",
          "permissions": ["manage_products", "view_reports"]
        }
      ],
      "activeSubscription": {
        "id": 1,
        "plan": "premium",
        "planLabel": "Premium",
        "type": "both",
        "expiresAt": "2026-02-06T10:30:00.000000Z",
        "daysRemaining": 31,
        "isActive": true,
        "isExpired": false,
        "isExpiringSoon": false
      },
      "createdAt": "2026-01-01T10:30:00.000000Z",
      "updatedAt": "2026-01-06T10:30:00.000000Z",
      "deletedAt": null
    }
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "You do not have access to this shop.",
  "responseTime": 12.34,
  "data": null
}
```

---

### Update Shop

Updates shop information.

| Property | Value |
|----------|-------|
| **Endpoint** | `PUT /shops/{shop}` |
| **Auth Required** | Yes |

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `shop` | integer | Shop ID |

**Request Body:**
```json
{
  "name": "string (optional)",
  "business_type": "string (optional)",
  "phone_number": "string (optional)",
  "address": "string (optional)",
  "currency": "string (optional)"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Shop updated successfully.",
  "responseTime": 89.45,
  "data": {
    "shop": {
      "id": 1,
      "name": "Updated Shop Name",
      "businessType": {
        "value": "retail",
        "label": "Retail"
      },
      "phoneNumber": "+255712345678",
      "address": "Dar es Salaam",
      "agentCode": "SHP001",
      "currency": {
        "code": "TZS",
        "symbol": "TSh",
        "label": "Tanzanian Shilling"
      },
      "imageUrl": "https://example.com/logo.png",
      "isActive": true,
      "isCurrentSelected": true,
      "owner": {
        "id": 1,
        "name": "John Doe"
      },
      "activeSubscription": {
        "id": 1,
        "plan": "premium",
        "planLabel": "Premium",
        "type": "both",
        "expiresAt": "2026-02-06T10:30:00.000000Z",
        "daysRemaining": 31,
        "isActive": true,
        "isExpired": false,
        "isExpiringSoon": false
      },
      "createdAt": "2026-01-01T10:30:00.000000Z",
      "updatedAt": "2026-01-06T11:00:00.000000Z",
      "deletedAt": null
    }
  }
}
```

---

### Delete Shop

Deletes a shop.

| Property | Value |
|----------|-------|
| **Endpoint** | `DELETE /shops/{shop}` |
| **Auth Required** | Yes |

**Response:**
```json
{
  "success": true,
  "message": "Shop removed successfully.",
  "responseTime": 67.89,
  "data": null
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Cannot delete the shop while it is set as active for a user.",
  "responseTime": 23.45,
  "data": null
}
```

---

### Switch Shop

Switches the active shop context.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/switch` |
| **Auth Required** | Yes |

---

### Set Active Shop

Sets a shop as the active shop.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/active` |
| **Auth Required** | Yes |

---

## Shop Members

Base path: `/api/shops/{shop}/members`

### List Members

Returns all members of a shop.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/members` |
| **Auth Required** | Yes |

---

### Add Member

Adds a new member to the shop.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/members` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "user_id": "integer",
  "role": "string",
  "permissions": ["array"]
}
```

---

### Get Member Details

Returns details of a specific member.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/members/{member}` |
| **Auth Required** | Yes |

---

### Update Member

Updates member role/permissions.

| Property | Value |
|----------|-------|
| **Endpoint** | `PUT /shops/{shop}/members/{member}` |
| **Auth Required** | Yes |

---

### Remove Member

Removes a member from the shop.

| Property | Value |
|----------|-------|
| **Endpoint** | `DELETE /shops/{shop}/members/{member}` |
| **Auth Required** | Yes |

---

## Categories

Base path: `/api/shops/categories/ctx`

### List Categories

Returns all available categories.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/categories/ctx` |
| **Auth Required** | Yes |

---

## Products & Inventory

Base path: `/api/shops/{shop}/products`

### List Products

Returns all products for a shop.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/products` |
| **Auth Required** | Yes |

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Search by name/SKU |
| `category_id` | integer | Filter by category |
| `low_stock` | boolean | Filter low stock items |
| `page` | integer | Page number |
| `per_page` | integer | Items per page |

---

### Create Product

Creates a new product.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/products` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "name": "string",
  "sku": "string",
  "description": "string",
  "category_id": "integer",
  "unit_type": "string",
  "cost_price": "decimal",
  "selling_price": "decimal",
  "stock_quantity": "integer",
  "low_stock_threshold": "integer",
  "image": "file"
}
```

---

### Get Product Details

Returns details of a specific product.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/products/{product}` |
| **Auth Required** | Yes |

---

### Update Product

Updates product information.

| Property | Value |
|----------|-------|
| **Endpoint** | `PUT /shops/{shop}/products/{product}` |
| **Auth Required** | Yes |

---

### Delete Product

Deletes a product.

| Property | Value |
|----------|-------|
| **Endpoint** | `DELETE /shops/{shop}/products/{product}` |
| **Auth Required** | Yes |

---

### Update Stock

Adjusts product stock quantity.

| Property | Value |
|----------|-------|
| **Endpoint** | `PATCH /shops/{shop}/products/{product}/stock` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "quantity": "integer",
  "adjustment_type": "string",
  "reason": "string"
}
```

---

### Stock Adjustment History

Returns stock adjustment history for a product.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/products/{product}/adjustments` |
| **Auth Required** | Yes |

---

### Inventory Analysis

Returns inventory analysis for the shop.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/inventory/analysis` |
| **Auth Required** | Yes |

---

### Adjustments Summary

Returns summary of all stock adjustments.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/inventory/adjustments` |
| **Auth Required** | Yes |

---

## Purchase Orders

Base path: `/api/shops/{shop}/purchase-orders`

### List Orders (As Buyer)

Returns purchase orders created by the shop.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/purchase-orders/buyer` |
| **Auth Required** | Yes |

---

### List Orders (As Seller)

Returns purchase orders received from other shops.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/purchase-orders/seller` |
| **Auth Required** | Yes |

---

### Create Purchase Order

Creates a new purchase order.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/purchase-orders` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "seller_shop_id": "integer",
  "items": [
    {
      "product_id": "integer",
      "quantity": "integer",
      "unit_price": "decimal"
    }
  ],
  "notes": "string"
}
```

---

### Get Purchase Order

Returns details of a specific purchase order.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/purchase-orders/{purchaseOrder}` |
| **Auth Required** | Yes |

---

### Update Purchase Order

Updates a purchase order.

| Property | Value |
|----------|-------|
| **Endpoint** | `PUT /shops/{shop}/purchase-orders/{purchaseOrder}` |
| **Auth Required** | Yes |

---

### Delete Purchase Order

Deletes a purchase order.

| Property | Value |
|----------|-------|
| **Endpoint** | `DELETE /shops/{shop}/purchase-orders/{purchaseOrder}` |
| **Auth Required** | Yes |

---

### Update Order Status

Updates the status of a purchase order.

| Property | Value |
|----------|-------|
| **Endpoint** | `PATCH /shops/{shop}/purchase-orders/{purchaseOrder}/status` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "status": "string"
}
```

---

### Record Payment

Records a payment for a purchase order.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/purchase-orders/{purchaseOrder}/payments` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "amount": "decimal",
  "payment_method": "string",
  "reference": "string"
}
```

---

### Transfer Stock

Transfers stock from purchase order to inventory.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/purchase-orders/{purchaseOrder}/transfer-stock` |
| **Auth Required** | Yes |

---

## POS & Sales

Base path: `/api/shops/{shop}/pos`

### Complete Sale

Processes a new sale transaction.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/pos/sales` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "customer_id": "integer|null",
  "items": [
    {
      "product_id": "integer",
      "quantity": "integer",
      "unit_price": "decimal",
      "discount": "decimal"
    }
  ],
  "payment_method": "string",
  "amount_paid": "decimal",
  "notes": "string"
}
```

---

### Get Sales History

Returns sales history for the shop.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/pos/sales` |
| **Auth Required** | Yes |

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `start_date` | date | Filter from date |
| `end_date` | date | Filter to date |
| `customer_id` | integer | Filter by customer |
| `status` | string | Filter by status |
| `page` | integer | Page number |

---

### Get Sale Details

Returns details of a specific sale.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/pos/sales/{sale}` |
| **Auth Required** | Yes |

---

### Get Sales Analytics

Returns sales analytics and statistics.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/pos/analytics` |
| **Auth Required** | Yes |

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `period` | string | daily, weekly, monthly |
| `start_date` | date | Start date |
| `end_date` | date | End date |

---

### Refund Sale

Processes a refund for a sale.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/pos/sales/{sale}/refund` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "reason": "string",
  "items": [
    {
      "sale_item_id": "integer",
      "quantity": "integer"
    }
  ]
}
```

---

### Add Payment to Sale

Adds additional payment to a sale.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/pos/sales/{sale}/payments` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "amount": "decimal",
  "payment_method": "string"
}
```

---

## Customers

Base path: `/api/shops/{shop}/customers`

### List Customers

Returns all customers for a shop.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/customers` |
| **Auth Required** | Yes |

---

### Create Customer

Creates a new customer.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/customers` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "name": "string",
  "phone": "string",
  "email": "string",
  "address": "string"
}
```

---

### Get Customer Details

Returns details of a specific customer.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/customers/{customer}` |
| **Auth Required** | Yes |

---

### Update Customer

Updates customer information.

| Property | Value |
|----------|-------|
| **Endpoint** | `PUT /shops/{shop}/customers/{customer}` |
| **Auth Required** | Yes |

---

### Delete Customer

Deletes a customer.

| Property | Value |
|----------|-------|
| **Endpoint** | `DELETE /shops/{shop}/customers/{customer}` |
| **Auth Required** | Yes |

---

## Expenses

Base path: `/api/shops/{shop}/expenses`

### List Expenses

Returns all expenses for a shop.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/expenses` |
| **Auth Required** | Yes |

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `category` | string | Filter by category |
| `start_date` | date | Filter from date |
| `end_date` | date | Filter to date |
| `page` | integer | Page number |

---

### Create Expense

Records a new expense.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/expenses` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "category": "string",
  "amount": "decimal",
  "description": "string",
  "date": "date",
  "payment_method": "string",
  "receipt": "file"
}
```

---

### Get Expense Summary

Returns expense summary and statistics.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/expenses/summary` |
| **Auth Required** | Yes |

---

### Get Expense Categories

Returns available expense categories.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/expenses/categories` |
| **Auth Required** | Yes |

---

### Get Expense Details

Returns details of a specific expense.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/expenses/{expense}` |
| **Auth Required** | Yes |

---

### Update Expense

Updates an expense record.

| Property | Value |
|----------|-------|
| **Endpoint** | `PUT /shops/{shop}/expenses/{expense}` |
| **Auth Required** | Yes |

---

### Delete Expense

Deletes an expense record.

| Property | Value |
|----------|-------|
| **Endpoint** | `DELETE /shops/{shop}/expenses/{expense}` |
| **Auth Required** | Yes |

---

## Reports & Analytics

Base path: `/api/shops/{shop}/reports`

### Overview Report

Returns a general business overview report.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/reports/overview` |
| **Auth Required** | Yes |

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `period` | string | daily, weekly, monthly, yearly |
| `start_date` | date | Start date |
| `end_date` | date | End date |

---

### Sales Report

Returns detailed sales report.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/reports/sales` |
| **Auth Required** | Yes |

---

### Products Report

Returns product performance report.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/reports/products` |
| **Auth Required** | Yes |

---

### Financial Report

Returns financial summary report.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/reports/financial` |
| **Auth Required** | Yes |

---

### Employees Report

Returns employee performance report.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/reports/employees` |
| **Auth Required** | Yes |

---

## Shop Settings

Base path: `/api/shops/{shop}/settings`

### Get Settings

Returns shop settings.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/settings` |
| **Auth Required** | Yes |

---

### Update Settings

Updates shop settings.

| Property | Value |
|----------|-------|
| **Endpoint** | `PUT /shops/{shop}/settings` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "currency": "string",
  "tax_rate": "decimal",
  "receipt_header": "string",
  "receipt_footer": "string",
  "low_stock_alert": "boolean",
  "notification_preferences": { ... }
}
```

---

### Reset Settings

Resets shop settings to defaults.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/settings/reset` |
| **Auth Required** | Yes |

---

### Get Settings Categories

Returns available settings categories.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/settings/categories` |
| **Auth Required** | Yes |

---

## Savings & Goals

Base path: `/api/shops/{shop}/savings`

### Get Savings Settings

Returns savings configuration.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/savings/settings` |
| **Auth Required** | Yes |

---

### Update Savings Settings

Updates savings configuration.

| Property | Value |
|----------|-------|
| **Endpoint** | `PUT /shops/{shop}/savings/settings` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "auto_save_percentage": "decimal",
  "auto_save_enabled": "boolean"
}
```

---

### Deposit

Makes a savings deposit.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/savings/deposit` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "amount": "decimal",
  "notes": "string"
}
```

---

### Withdraw

Makes a savings withdrawal.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/savings/withdraw` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "amount": "decimal",
  "reason": "string"
}
```

---

### Get Transactions

Returns savings transaction history.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/savings/transactions` |
| **Auth Required** | Yes |

---

### Get Summary

Returns savings summary.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/savings/summary` |
| **Auth Required** | Yes |

---

### List Goals

Returns all savings goals.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/savings/goals` |
| **Auth Required** | Yes |

---

### Create Goal

Creates a new savings goal.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/savings/goals` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "name": "string",
  "target_amount": "decimal",
  "target_date": "date",
  "description": "string"
}
```

---

### Update Goal

Updates a savings goal.

| Property | Value |
|----------|-------|
| **Endpoint** | `PUT /shops/{shop}/savings/goals/{goal}` |
| **Auth Required** | Yes |

---

### Delete Goal

Deletes a savings goal.

| Property | Value |
|----------|-------|
| **Endpoint** | `DELETE /shops/{shop}/savings/goals/{goal}` |
| **Auth Required** | Yes |

---

## Subscriptions

Base path: `/api/shops/{shop}/subscriptions`

### List Subscriptions

Returns all subscriptions for a shop.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/subscriptions` |
| **Auth Required** | Yes |

---

### Get Current Subscription

Returns the active subscription.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/subscriptions/current` |
| **Auth Required** | Yes |

---

### Get Subscription Statistics

Returns subscription statistics.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/subscriptions/statistics` |
| **Auth Required** | Yes |

---

### Create Subscription

Creates a new subscription.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/subscriptions` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "plan": "string",
  "payment_method": "string",
  "billing_cycle": "string"
}
```

---

### Get Subscription Details

Returns details of a specific subscription.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/subscriptions/{subscription}` |
| **Auth Required** | Yes |

---

### Update Subscription

Updates a subscription.

| Property | Value |
|----------|-------|
| **Endpoint** | `PUT /shops/{shop}/subscriptions/{subscription}` |
| **Auth Required** | Yes |

---

### Cancel Subscription

Cancels a subscription.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/subscriptions/{subscription}/cancel` |
| **Auth Required** | Yes |

---

### Renew Subscription

Renews a subscription.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/subscriptions/{subscription}/renew` |
| **Auth Required** | Yes |

---

### Suspend Subscription

Suspends a subscription.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/subscriptions/{subscription}/suspend` |
| **Auth Required** | Yes |

---

### Activate Subscription

Activates a suspended subscription.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/subscriptions/{subscription}/activate` |
| **Auth Required** | Yes |

---

### Get Subscription Plans

Returns available subscription plans.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/subscription/plans` |
| **Auth Required** | Yes |

---

## Ads & Promotions

Base path: `/api/shops/manage/ads`

### List Ads

Returns all ads for the shop.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/manage/ads` |
| **Auth Required** | Yes |

---

### Create Ad

Creates a new advertisement.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/manage/ads` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "title": "string",
  "description": "string",
  "type": "string",
  "placement": "string",
  "image": "file",
  "target_url": "string",
  "start_date": "date",
  "end_date": "date",
  "budget": "decimal"
}
```

---

### Get Ad Details

Returns details of a specific ad.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/manage/ads/{ad}` |
| **Auth Required** | Yes |

---

### Update Ad

Updates an advertisement.

| Property | Value |
|----------|-------|
| **Endpoint** | `PUT /shops/manage/ads/{ad}` |
| **Auth Required** | Yes |

---

### Delete Ad

Deletes an advertisement.

| Property | Value |
|----------|-------|
| **Endpoint** | `DELETE /shops/manage/ads/{ad}` |
| **Auth Required** | Yes |

---

### Track Ad View

Records an ad view impression.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/manage/ads/{ad}/view` |
| **Auth Required** | Yes |

---

### Track Ad Click

Records an ad click.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/manage/ads/{ad}/click` |
| **Auth Required** | Yes |

---

### Get Ad Analytics

Returns analytics for an ad.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/manage/ads/{ad}/analytics` |
| **Auth Required** | Yes |

---

### Approve Ad (Admin)

Approves an ad for display.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/manage/ads/{ad}/approve` |
| **Auth Required** | Yes (Admin) |

---

### Reject Ad (Admin)

Rejects an ad.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/manage/ads/{ad}/reject` |
| **Auth Required** | Yes (Admin) |

**Request Body:**
```json
{
  "reason": "string"
}
```

---

### Toggle Pause Ad

Pauses or resumes an ad.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/manage/ads/{ad}/toggle-pause` |
| **Auth Required** | Yes |

---

### Get Ads Feed

Returns the ads feed (Deals Tab).

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/ads/feed` |
| **Auth Required** | Yes |

---

## Chat & Messaging

Base path: `/api/shops/{shop}/chat`

### Get Conversations

Returns all conversations for the shop.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/chat/conversations` |
| **Auth Required** | Yes |

---

### Get Conversation

Returns a specific conversation.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/chat/conversations/{conversation}` |
| **Auth Required** | Yes |

---

### Toggle Archive Conversation

Archives or unarchives a conversation.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/chat/conversations/{conversation}/archive` |
| **Auth Required** | Yes |

---

### Get Messages

Returns messages in a conversation.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/chat/conversations/{conversation}/messages` |
| **Auth Required** | Yes |

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `before` | integer | Message ID for pagination |
| `limit` | integer | Number of messages |

---

### Send Message

Sends a new message.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/chat/messages` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "recipient_shop_id": "integer",
  "content": "string",
  "type": "string",
  "attachments": ["files"]
}
```

---

### Delete Message

Deletes a message.

| Property | Value |
|----------|-------|
| **Endpoint** | `DELETE /shops/{shop}/chat/conversations/{conversation}/messages/{message}` |
| **Auth Required** | Yes |

---

### Mark as Read

Marks messages in a conversation as read.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/chat/conversations/{conversation}/mark-read` |
| **Auth Required** | Yes |

---

### Start Typing

Indicates the user started typing.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/chat/conversations/{conversation}/typing/start` |
| **Auth Required** | Yes |

---

### Stop Typing

Indicates the user stopped typing.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/chat/conversations/{conversation}/typing/stop` |
| **Auth Required** | Yes |

---

### Get Typing Status

Returns who is currently typing in a conversation.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/chat/conversations/{conversation}/typing` |
| **Auth Required** | Yes |

---

### React to Message

Adds a reaction to a message.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/chat/conversations/{conversation}/messages/{message}/react` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "reaction": "string"
}
```

---

### Remove Reaction

Removes a reaction from a message.

| Property | Value |
|----------|-------|
| **Endpoint** | `DELETE /shops/{shop}/chat/conversations/{conversation}/messages/{message}/react` |
| **Auth Required** | Yes |

---

### Block Shop

Blocks a shop from sending messages.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/chat/block` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "shop_id": "integer"
}
```

---

### Unblock Shop

Unblocks a shop.

| Property | Value |
|----------|-------|
| **Endpoint** | `POST /shops/{shop}/chat/unblock` |
| **Auth Required** | Yes |

**Request Body:**
```json
{
  "shop_id": "integer"
}
```

---

### Get Blocked Shops

Returns list of blocked shops.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/chat/blocked` |
| **Auth Required** | Yes |

---

### Get Unread Count

Returns unread message count.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/chat/unread-count` |
| **Auth Required** | Yes |

---

### Get Chat Statistics

Returns chat statistics.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/chat/statistics` |
| **Auth Required** | Yes |

---

### Search Shops

Searches for shops to start a conversation.

| Property | Value |
|----------|-------|
| **Endpoint** | `GET /shops/{shop}/chat/search-shops` |
| **Auth Required** | Yes |

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `query` | string | Search term |

---

## HTTP Status Codes

| Code | Description |
|------|-------------|
| `200` | Success |
| `201` | Created |
| `204` | No Content |
| `400` | Bad Request |
| `401` | Unauthorized |
| `403` | Forbidden |
| `404` | Not Found |
| `422` | Validation Error |
| `429` | Too Many Requests |
| `500` | Server Error |

---

## Rate Limiting

API requests are rate-limited to prevent abuse. Standard limits:

- **Authentication endpoints:** 5 requests per minute
- **General endpoints:** 60 requests per minute
- **Search endpoints:** 30 requests per minute

When rate limited, the API returns a `429 Too Many Requests` response.

---

## Changelog

### Version 1.0.0 (January 2026)
- Initial API release
- Phone-based authentication with OTP
- Multi-shop management
- Product and inventory management
- POS and sales processing
- Purchase order management
- Expense tracking
- Reports and analytics
- Savings and goals management
- Subscription management
- Advertising platform
- Real-time chat system

