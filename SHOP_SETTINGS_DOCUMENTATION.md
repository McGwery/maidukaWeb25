# Shop Settings Feature - Complete Documentation

## 🎯 Overview

A comprehensive yet **simple** shop settings system that allows shop owners to configure their shop's behavior, preferences, and operational settings through an easy-to-use API.

---

## ✨ Features

### 📋 Settings Categories

1. **General Information** - Business details (email, website, tax ID, registration)
2. **Notifications** - SMS/Email alerts, low stock warnings, daily summaries
3. **Sales & POS** - Receipt printing, credit sales, discounts
4. **Inventory** - Stock tracking, valuation methods
5. **Receipt & Invoice** - Custom headers/footers, tax configuration
6. **Working Hours** - Opening/closing times, working days
7. **Language & Regional** - Language, timezone, date/time formats
8. **Security** - PIN requirements, two-factor authentication
9. **Backup & Data** - Automatic backup configuration

---

## 📡 API Endpoints

### Base Path: `/api/shops/{shopId}/settings`

### 1. Get Shop Settings

**Endpoint:** `GET /api/shops/{shopId}/settings`

**Description:** Retrieves shop settings. If settings don't exist, creates them with default values.

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "id": "uuid",
    "shopId": "uuid",
    
    // General Information
    "businessEmail": "shop@example.com",
    "businessWebsite": "https://shop.com",
    "taxId": "TIN123456",
    "registrationNumber": "REG789",
    
    // Notifications
    "enableSmsNotifications": true,
    "enableEmailNotifications": false,
    "notifyLowStock": true,
    "lowStockThreshold": 10,
    "notifyDailySalesSummary": false,
    "dailySummaryTime": "18:00",
    
    // Sales & POS
    "autoPrintReceipt": false,
    "allowCreditSales": true,
    "creditLimitDays": 30,
    "requireCustomerForCredit": true,
    "allowDiscounts": true,
    "maxDiscountPercentage": 20.00,
    
    // Inventory
    "trackStock": true,
    "allowNegativeStock": false,
    "autoDeductStockOnSale": true,
    "stockValuationMethod": "fifo",
    
    // Receipt & Invoice
    "receiptHeader": "Welcome to our shop!",
    "receiptFooter": "Thank you for your business",
    "showShopLogoOnReceipt": true,
    "showTaxOnReceipt": false,
    "taxPercentage": 0.00,
    
    // Working Hours
    "openingTime": "08:00",
    "closingTime": "20:00",
    "workingDays": ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday"],
    "isCurrentlyOpen": true,
    
    // Language & Regional
    "language": "sw",
    "timezone": "Africa/Dar_es_Salaam",
    "dateFormat": "d/m/Y",
    "timeFormat": "H:i",
    
    // Security
    "requirePinForRefunds": true,
    "requirePinForDiscounts": false,
    "enableTwoFactorAuth": false,
    
    // Backup
    "autoBackup": false,
    "backupFrequency": "weekly",
    
    "createdAt": "2025-11-07T00:00:00Z",
    "updatedAt": "2025-11-07T00:00:00Z"
  }
}
```

---

### 2. Update Shop Settings

**Endpoint:** `PUT /api/shops/{shopId}/settings`

**Description:** Updates shop settings. Only send fields you want to update.

**Request Body:**
```json
{
  "businessEmail": "newshop@example.com",
  "enableSmsNotifications": true,
  "notifyLowStock": true,
  "lowStockThreshold": 15,
  "allowDiscounts": true,
  "maxDiscountPercentage": 25.00,
  "openingTime": "09:00",
  "closingTime": "21:00",
  "workingDays": ["monday", "tuesday", "wednesday", "thursday", "friday"],
  "language": "sw",
  "receiptHeader": "Karibu Duka Letu!",
  "receiptFooter": "Asante kwa kununua"
}
```

**Validation Rules:**
- `businessEmail`: optional, valid email
- `businessWebsite`: optional, valid URL
- `taxId`: optional, string, max 50 characters
- `lowStockThreshold`: optional, integer, 0-1000
- `maxDiscountPercentage`: optional, numeric, 0-100
- `creditLimitDays`: optional, integer, 1-365
- `stockValuationMethod`: optional, one of: fifo, lifo, average
- `language`: optional, one of: sw (Swahili), en (English)
- `workingDays`: optional, array of: monday, tuesday, wednesday, thursday, friday, saturday, sunday
- `openingTime/closingTime`: optional, format: H:i (e.g., "09:00")
- All boolean fields: optional, true/false

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Shop settings updated successfully.",
  "data": {
    // Updated settings object
  }
}
```

---

### 3. Reset Settings to Defaults

**Endpoint:** `POST /api/shops/{shopId}/settings/reset`

**Description:** Resets all settings to their default values.

**Response:**
```json
{
  "success": true,
  "code": 200,
  "message": "Shop settings reset to defaults successfully.",
  "data": {
    // Default settings object
  }
}
```

---

### 4. Get Settings Categories

**Endpoint:** `GET /api/settings-categories`

**Description:** Get organized list of settings categories for UI rendering.

**Response:**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "categories": [
      {
        "key": "general",
        "label": "General Information",
        "icon": "info",
        "fields": ["businessEmail", "businessWebsite", "taxId", "registrationNumber"]
      },
      {
        "key": "notifications",
        "label": "Notifications",
        "icon": "bell",
        "fields": ["enableSmsNotifications", "enableEmailNotifications", "notifyLowStock", "lowStockThreshold", "notifyDailySalesSummary", "dailySummaryTime"]
      },
      {
        "key": "sales",
        "label": "Sales & POS",
        "icon": "shopping-cart",
        "fields": ["autoPrintReceipt", "allowCreditSales", "creditLimitDays", "requireCustomerForCredit", "allowDiscounts", "maxDiscountPercentage"]
      },
      {
        "key": "inventory",
        "label": "Inventory",
        "icon": "package",
        "fields": ["trackStock", "allowNegativeStock", "autoDeductStockOnSale", "stockValuationMethod"]
      },
      {
        "key": "receipt",
        "label": "Receipt & Invoice",
        "icon": "file-text",
        "fields": ["receiptHeader", "receiptFooter", "showShopLogoOnReceipt", "showTaxOnReceipt", "taxPercentage"]
      },
      {
        "key": "hours",
        "label": "Working Hours",
        "icon": "clock",
        "fields": ["openingTime", "closingTime", "workingDays"]
      },
      {
        "key": "regional",
        "label": "Language & Regional",
        "icon": "globe",
        "fields": ["language", "timezone", "dateFormat", "timeFormat"]
      },
      {
        "key": "security",
        "label": "Security",
        "icon": "shield",
        "fields": ["requirePinForRefunds", "requirePinForDiscounts", "enableTwoFactorAuth"]
      },
      {
        "key": "backup",
        "label": "Backup & Data",
        "icon": "database",
        "fields": ["autoBackup", "backupFrequency"]
      }
    ]
  }
}
```

---

## 🔧 Default Settings

When a shop is created or settings are reset, these defaults apply:

```javascript
{
  // Notifications
  enableSmsNotifications: true,
  enableEmailNotifications: false,
  notifyLowStock: true,
  lowStockThreshold: 10,
  notifyDailySalesSummary: false,
  dailySummaryTime: "18:00",
  
  // Sales & POS
  autoPrintReceipt: false,
  allowCreditSales: true,
  creditLimitDays: 30,
  requireCustomerForCredit: true,
  allowDiscounts: true,
  maxDiscountPercentage: 20.00,
  
  // Inventory
  trackStock: true,
  allowNegativeStock: false,
  autoDeductStockOnSale: true,
  stockValuationMethod: "fifo",
  
  // Receipt
  showShopLogoOnReceipt: true,
  showTaxOnReceipt: false,
  taxPercentage: 0.00,
  
  // Working Hours
  openingTime: "08:00",
  closingTime: "20:00",
  workingDays: ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday"],
  
  // Regional
  language: "sw",
  timezone: "Africa/Dar_es_Salaam",
  dateFormat: "d/m/Y",
  timeFormat: "H:i",
  
  // Security
  requirePinForRefunds: true,
  requirePinForDiscounts: false,
  enableTwoFactorAuth: false,
  
  // Backup
  autoBackup: false,
  backupFrequency: "weekly"
}
```

---

## 💡 Common Use Cases

### 1. Enable Low Stock Notifications
```bash
curl -X PUT /api/shops/{shopId}/settings \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "notifyLowStock": true,
    "lowStockThreshold": 5,
    "enableSmsNotifications": true
  }'
```

### 2. Configure Working Hours
```bash
curl -X PUT /api/shops/{shopId}/settings \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "openingTime": "07:00",
    "closingTime": "22:00",
    "workingDays": ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"]
  }'
```

### 3. Set Up Credit Sales
```bash
curl -X PUT /api/shops/{shopId}/settings \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "allowCreditSales": true,
    "creditLimitDays": 14,
    "requireCustomerForCredit": true
  }'
```

### 4. Customize Receipt
```bash
curl -X PUT /api/shops/{shopId}/settings \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "receiptHeader": "Karibu Duka la Mama Rose!",
    "receiptFooter": "Asante kwa kununua. Karibu tena!",
    "showTaxOnReceipt": true,
    "taxPercentage": 18.00
  }'
```

### 5. Change Language to English
```bash
curl -X PUT /api/shops/{shopId}/settings \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "language": "en"
  }'
```

---

## 🎨 UI Implementation Guide

### Settings Screen Layout

```
┌─────────────────────────────────────┐
│        Shop Settings                │
├─────────────────────────────────────┤
│                                     │
│  📋 General Information             │
│  ├─ Business Email                  │
│  ├─ Business Website                │
│  ├─ Tax ID                         │
│  └─ Registration Number            │
│                                     │
│  🔔 Notifications                   │
│  ├─ [✓] Enable SMS Notifications   │
│  ├─ [ ] Enable Email               │
│  ├─ [✓] Notify Low Stock           │
│  └─ Low Stock Threshold: [10]      │
│                                     │
│  🛒 Sales & POS                     │
│  ├─ [ ] Auto Print Receipt         │
│  ├─ [✓] Allow Credit Sales         │
│  ├─ Credit Limit: [30] days        │
│  └─ Max Discount: [20]%            │
│                                     │
│  📦 Inventory                       │
│  ├─ [✓] Track Stock                │
│  ├─ [ ] Allow Negative Stock       │
│  └─ Valuation: [FIFO ▼]           │
│                                     │
│  🧾 Receipt & Invoice               │
│  ├─ Header: [                 ]    │
│  ├─ Footer: [                 ]    │
│  └─ [✓] Show Tax (18%)             │
│                                     │
│  🕐 Working Hours                   │
│  ├─ Opens: [08:00]                 │
│  ├─ Closes: [20:00]                │
│  └─ Days: [Mon][Tue][Wed]...       │
│                                     │
│  🌍 Language & Regional             │
│  ├─ Language: [Swahili ▼]          │
│  └─ Date Format: [d/m/Y ▼]         │
│                                     │
│  🔒 Security                        │
│  ├─ [✓] PIN for Refunds            │
│  └─ [ ] Two-Factor Auth            │
│                                     │
│  💾 Backup                          │
│  ├─ [ ] Auto Backup                │
│  └─ Frequency: [Weekly ▼]          │
│                                     │
├─────────────────────────────────────┤
│  [Reset to Defaults]  [Save]       │
└─────────────────────────────────────┘
```

---

## 📱 Kotlin/Android Example

```kotlin
data class ShopSettingsResponse(
    val success: Boolean,
    val code: Int,
    val message: String? = null,
    val data: ShopSettings?
)

data class ShopSettings(
    val id: String,
    val shopId: String,
    
    // General
    val businessEmail: String?,
    val businessWebsite: String?,
    val taxId: String?,
    val registrationNumber: String?,
    
    // Notifications
    val enableSmsNotifications: Boolean,
    val notifyLowStock: Boolean,
    val lowStockThreshold: Int,
    
    // Sales
    val allowCreditSales: Boolean,
    val creditLimitDays: Int,
    val allowDiscounts: Boolean,
    val maxDiscountPercentage: Double,
    
    // Inventory
    val trackStock: Boolean,
    val allowNegativeStock: Boolean,
    val stockValuationMethod: String,
    
    // Receipt
    val receiptHeader: String?,
    val receiptFooter: String?,
    val taxPercentage: Double,
    
    // Working Hours
    val openingTime: String,
    val closingTime: String,
    val workingDays: List<String>,
    val isCurrentlyOpen: Boolean,
    
    // Regional
    val language: String,
    val timezone: String,
    val dateFormat: String
)

// Usage
suspend fun getShopSettings(shopId: String): ShopSettings? {
    val response = api.get<ShopSettingsResponse>("/api/shops/$shopId/settings")
    return response.data
}

suspend fun updateSettings(shopId: String, updates: Map<String, Any>): ShopSettings? {
    val response = api.put<ShopSettingsResponse>(
        "/api/shops/$shopId/settings",
        updates
    )
    return response.data
}

// Update specific settings
val updates = mapOf(
    "notifyLowStock" to true,
    "lowStockThreshold" to 5,
    "language" to "en"
)
updateSettings(shopId, updates)
```

---

## 📁 Files Created

### Models
1. `app/Models/ShopSettings.php` - Main settings model with defaults and helper methods

### Controllers
2. `app/Http/Controllers/Api/ShopSettingsController.php` - Settings API controller

### Requests
3. `app/Http/Requests/UpdateShopSettingsRequest.php` - Validation for settings updates

### Resources
4. `app/Http/Resources/ShopSettingsResource.php` - CamelCase response formatting

### Migrations
5. `database/migrations/2025_11_07_124136_create_shop_settings_table.php` - Database schema

### Modified Files
6. `app/Models/Shop.php` - Added settings relationship
7. `routes/api.php` - Added settings routes

---

## ✅ Implementation Checklist

- [x] Database migration created
- [x] ShopSettings model with defaults
- [x] Settings controller with CRUD
- [x] Request validation
- [x] Resource transformation (camelCase)
- [x] Routes configured
- [x] Shop relationship added
- [x] Helper methods (isCurrentlyOpen, isStockLow)
- [x] Categories endpoint for UI
- [x] Reset to defaults functionality
- [x] Documentation complete

---

## 🎯 Key Features

✅ **Simple & Clean** - Organized into 9 clear categories  
✅ **Smart Defaults** - Sensible defaults for all settings  
✅ **Partial Updates** - Update only what you need  
✅ **Auto-Creation** - Settings created automatically if missing  
✅ **Helper Methods** - isCurrentlyOpen(), isStockLow()  
✅ **UI-Ready** - Categories endpoint for easy UI rendering  
✅ **CamelCase** - Kotlin-compatible response format  
✅ **Validation** - Comprehensive input validation  
✅ **Reset Option** - One-click reset to defaults

---

## 🚀 Status: PRODUCTION READY

All shop settings features are implemented and ready to use!

**Implementation Date:** November 7, 2025  
**Status:** ✅ Complete  
**API Standard:** CamelCase (Kotlin-compatible)

