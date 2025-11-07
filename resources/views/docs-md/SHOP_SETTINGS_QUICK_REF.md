# Shop Settings - Quick Reference

## 🚀 Quick Start

### Get Settings
```bash
GET /api/shops/{shopId}/settings
```

### Update Settings
```bash
PUT /api/shops/{shopId}/settings
{
  "notifyLowStock": true,
  "lowStockThreshold": 5,
  "language": "sw"
}
```

### Reset to Defaults
```bash
POST /api/shops/{shopId}/settings/reset
```

---

## 📋 Settings Categories

| Category | Settings Count | Key Settings |
|----------|----------------|--------------|
| **General** | 4 | Email, Website, Tax ID, Registration |
| **Notifications** | 6 | SMS, Email, Low Stock Alerts |
| **Sales & POS** | 6 | Credit Sales, Discounts, Receipts |
| **Inventory** | 4 | Stock Tracking, Valuation Method |
| **Receipt** | 5 | Headers, Footers, Tax Display |
| **Working Hours** | 3 | Opening/Closing, Working Days |
| **Regional** | 4 | Language, Timezone, Formats |
| **Security** | 3 | PIN Requirements, 2FA |
| **Backup** | 2 | Auto Backup, Frequency |

---

## ⚙️ Key Settings

### Most Used Settings

```json
{
  "notifyLowStock": true,
  "lowStockThreshold": 10,
  "allowCreditSales": true,
  "creditLimitDays": 30,
  "allowDiscounts": true,
  "maxDiscountPercentage": 20.00,
  "trackStock": true,
  "language": "sw",
  "openingTime": "08:00",
  "closingTime": "20:00"
}
```

---

## 🎨 UI Categories

Use `/api/settings-categories` to get organized structure:

```json
{
  "categories": [
    {
      "key": "general",
      "label": "General Information",
      "icon": "info",
      "fields": ["businessEmail", "businessWebsite", ...]
    },
    ...
  ]
}
```

---

## 💡 Common Scenarios

### 1. Setup New Shop
```json
{
  "businessEmail": "shop@example.com",
  "language": "sw",
  "openingTime": "08:00",
  "closingTime": "20:00",
  "workingDays": ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday"]
}
```

### 2. Enable Notifications
```json
{
  "enableSmsNotifications": true,
  "notifyLowStock": true,
  "lowStockThreshold": 5
}
```

### 3. Configure Credit Sales
```json
{
  "allowCreditSales": true,
  "creditLimitDays": 14,
  "requireCustomerForCredit": true
}
```

### 4. Add Tax to Receipts
```json
{
  "showTaxOnReceipt": true,
  "taxPercentage": 18.00,
  "receiptHeader": "Shop Name Here",
  "receiptFooter": "Thank you!"
}
```

---

## 🔧 Default Values

| Setting | Default |
|---------|---------|
| Language | `sw` (Swahili) |
| Timezone | `Africa/Dar_es_Salaam` |
| Low Stock Threshold | `10` |
| Credit Days | `30` |
| Max Discount | `20%` |
| Opening Time | `08:00` |
| Closing Time | `20:00` |
| Stock Valuation | `fifo` |

---

## 📱 Kotlin Example

```kotlin
// Get settings
val settings = api.get("/api/shops/$shopId/settings")

// Update
val updates = mapOf(
    "notifyLowStock" to true,
    "lowStockThreshold" to 5,
    "language" to "en"
)
api.put("/api/shops/$shopId/settings", updates)

// Reset
api.post("/api/shops/$shopId/settings/reset")

// Check if shop is open
if (settings.isCurrentlyOpen) {
    // Shop is open
}
```

---

## 🎯 Best Practices

1. **Update Only What Changed** - Send only modified fields
2. **Validate Client-Side** - Use validation rules before sending
3. **Cache Settings** - Cache locally to reduce API calls
4. **Reset Wisely** - Confirm before resetting to defaults
5. **Test Working Hours** - Verify isCurrentlyOpen logic

---

## ✅ Validation Rules

- **Email**: Valid email format
- **Website**: Valid URL
- **Low Stock**: 0-1000
- **Discount**: 0-100%
- **Credit Days**: 1-365
- **Time Format**: HH:MM (e.g., "09:00")
- **Language**: `sw` or `en`
- **Stock Method**: `fifo`, `lifo`, or `average`

---

## 📊 Response Format (CamelCase)

All responses use camelCase for Kotlin compatibility:

```json
{
  "success": true,
  "code": 200,
  "data": {
    "shopId": "uuid",
    "businessEmail": "...",
    "enableSmsNotifications": true,
    "lowStockThreshold": 10,
    "isCurrentlyOpen": true
  }
}
```

---

## 🛠️ Endpoints Summary

| Method | Endpoint | Purpose |
|--------|----------|---------|
| `GET` | `/shops/{id}/settings` | Get settings |
| `PUT` | `/shops/{id}/settings` | Update settings |
| `POST` | `/shops/{id}/settings/reset` | Reset to defaults |
| `GET` | `/settings-categories` | Get UI categories |

---

## 📁 Files Reference

- **Model**: `app/Models/ShopSettings.php`
- **Controller**: `app/Http/Controllers/Api/ShopSettingsController.php`
- **Resource**: `app/Http/Resources/ShopSettingsResource.php`
- **Request**: `app/Http/Requests/UpdateShopSettingsRequest.php`
- **Migration**: `database/migrations/*_create_shop_settings_table.php`

---

**Status:** ✅ Production Ready  
**Last Updated:** November 7, 2025

