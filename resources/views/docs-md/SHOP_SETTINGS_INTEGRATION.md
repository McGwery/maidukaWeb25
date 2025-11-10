# Shop Settings Integration - Implementation Guide

## 🎯 Overview

Shop settings have been successfully integrated across **ALL major features** of the MaiDuka25 platform. This document shows how settings affect each feature.

---

## ✅ Features Integrated with Settings

### 1. **POS & Sales (POSController)**

#### Settings Applied:
- ✅ **Credit Sales Control** (`allowCreditSales`)
- ✅ **Discount Control** (`allowDiscounts`, `maxDiscountPercentage`)
- ✅ **Customer Requirements** (`requireCustomerForCredit`)
- ✅ **Tax Configuration** (`showTaxOnReceipt`, `taxPercentage`)
- ✅ **Stock Tracking** (`trackStock`, `allowNegativeStock`)
- ✅ **Auto Stock Deduction** (`autoDeductStockOnSale`)
- ✅ **Low Stock Alerts** (`notifyLowStock`, `lowStockThreshold`)

#### How It Works:

**Credit Sales Validation:**
```php
// Checks if credit sales are allowed
if ($debtAmount > 0 && !$settings->allow_credit_sales) {
    return error('Credit sales are not allowed');
}

// Checks if customer is required for credit
if ($debtAmount > 0 && $settings->require_customer_for_credit && !$customer) {
    return error('Customer information is required for credit sales');
}
```

**Discount Validation:**
```php
// Checks if discounts are allowed
if ($discountAmount > 0 && !$settings->allow_discounts) {
    return error('Discounts are not allowed');
}

// Checks maximum discount percentage
if ($discountPercentage > $settings->max_discount_percentage) {
    return error("Discount cannot exceed {$settings->max_discount_percentage}%");
}
```

**Stock Management:**
```php
// Checks stock based on settings
if ($settings->track_stock && !$settings->allow_negative_stock) {
    if ($product->current_stock < $quantity) {
        return error('Insufficient stock');
    }
}

// Auto-deduct stock if enabled
if ($settings->auto_deduct_stock_on_sale) {
    $product->update(['current_stock' => $newStock]);
}

// Trigger low stock alert
if ($settings->isStockLow($newStock)) {
    Log::info("Low stock alert for product");
}
```

**Tax Application:**
```php
// Apply tax from settings if enabled
if ($settings->show_tax_on_receipt && $taxRate === 0) {
    $taxRate = $settings->tax_percentage;
}
```

---

### 2. **Product Management (ProductController)**

#### Settings Applied:
- ✅ **Low Stock Filtering** (`lowStockThreshold`)
- ✅ **Inventory Analysis** (uses threshold for counts)
- ✅ **Stock Tracking** (respects tracking settings)

#### How It Works:

**Low Stock Products:**
```php
// Uses shop's configured threshold instead of product's
GET /api/shops/{id}/products?low_stock=true

// Returns products where current_stock <= settings->low_stock_threshold
```

**Inventory Analysis:**
```json
{
  "summary": {
    "lowStockCount": 5,
    "lowStockThreshold": 10  // From shop settings
  },
  "products": [
    {
      "productName": "Product A",
      "currentStock": 8,
      "isLowStock": true  // Based on shop threshold
    }
  ]
}
```

---

### 3. **Sales Receipts (SaleResource)**

#### Settings Applied:
- ✅ **Receipt Header** (`receiptHeader`)
- ✅ **Receipt Footer** (`receiptFooter`)
- ✅ **Logo Display** (`showShopLogoOnReceipt`)
- ✅ **Tax Display** (`showTaxOnReceipt`)
- ✅ **Auto Print** (`autoPrintReceipt`)

#### How It Works:

**Receipt Data in Sale Response:**
```json
{
  "saleNumber": "SALE-001",
  "totalAmount": 50000,
  "receiptSettings": {
    "header": "Karibu Duka Letu!",
    "footer": "Asante kwa kununua. Karibu tena!",
    "showLogo": true,
    "showTax": true,
    "autoPrint": false
  }
}
```

**Frontend Usage:**
```kotlin
if (sale.receiptSettings.autoPrint) {
    printReceipt(sale)
}

// Display header
receipt.header = sale.receiptSettings.header

// Show tax if enabled
if (sale.receiptSettings.showTax) {
    receipt.showTaxLine(sale.taxAmount)
}
```

---

### 4. **Shop Information (ShopResource)**

#### Settings Applied:
- ✅ **Working Hours** (`isCurrentlyOpen()`)
- ✅ **Language** (`language`)
- ✅ **Timezone** (`timezone`)
- ✅ **Quick Settings Summary**

#### How It Works:

**Shop Details with Settings:**
```json
GET /api/shops/{id}

{
  "id": "uuid",
  "name": "Duka la Mama",
  "settings": {
    "language": "sw",
    "timezone": "Africa/Dar_es_Salaam",
    "isCurrentlyOpen": true,
    "allowCreditSales": true,
    "allowDiscounts": true,
    "trackStock": true
  },
  "activeSubscription": {
    "plan": "premium",
    "daysRemaining": 15,
    "isExpiringSoon": false
  }
}
```

---

## 🔄 Settings Flow in Business Logic

### Sales Process with Settings

```
┌─────────────────────────────────────┐
│  1. Customer Initiates Sale         │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  2. Load Shop Settings              │
│     - Credit sales allowed?         │
│     - Discounts allowed?            │
│     - Stock tracking enabled?       │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  3. Validate Against Settings       │
│     ✓ Check discount percentage     │
│     ✓ Check credit requirements     │
│     ✓ Check stock availability      │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  4. Apply Settings                  │
│     - Apply tax if enabled          │
│     - Deduct stock if auto-enabled  │
│     - Check low stock threshold     │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  5. Generate Receipt with Settings  │
│     - Include header/footer         │
│     - Show/hide tax                 │
│     - Auto-print if enabled         │
└─────────────────────────────────────┘
```

---

## 📋 Settings Usage Examples

### Example 1: Strict Cash-Only Shop

```json
PUT /api/shops/{id}/settings
{
  "allowCreditSales": false,
  "allowDiscounts": false,
  "trackStock": true,
  "allowNegativeStock": false,
  "requireCustomerForCredit": true
}
```

**Result:**
- ❌ No credit sales allowed
- ❌ No discounts allowed
- ✅ Strict stock tracking
- ✅ Cannot sell if out of stock

---

### Example 2: Flexible Retail Shop

```json
{
  "allowCreditSales": true,
  "creditLimitDays": 30,
  "requireCustomerForCredit": true,
  "allowDiscounts": true,
  "maxDiscountPercentage": 15,
  "lowStockThreshold": 20,
  "notifyLowStock": true
}
```

**Result:**
- ✅ Credit sales with 30-day limit
- ✅ Customer info required for credit
- ✅ Discounts up to 15%
- ✅ Low stock alerts at 20 items

---

### Example 3: Service-Based Shop

```json
{
  "trackStock": false,
  "allowNegativeStock": true,
  "showTaxOnReceipt": true,
  "taxPercentage": 18,
  "receiptHeader": "Professional Services Ltd",
  "receiptFooter": "Tax Invoice"
}
```

**Result:**
- ❌ No stock tracking (services)
- ✅ 18% tax on all sales
- ✅ Professional receipt headers

---

## 🎯 Smart Default Behavior

### Auto-Creation
If a shop doesn't have settings, they're automatically created with smart defaults when accessed:

```php
$settings = $shop->settings;
if (!$settings) {
    $settings = ShopSettings::create([
        'shop_id' => $shop->id,
        ...ShopSettings::defaults()
    ]);
}
```

### Defaults Applied:
- ✅ Credit sales: **Allowed** (30 days)
- ✅ Discounts: **Allowed** (max 20%)
- ✅ Stock tracking: **Enabled**
- ✅ Low stock threshold: **10 items**
- ✅ Language: **Swahili**
- ✅ Tax: **Hidden** (0%)

---

## 🔧 Technical Implementation

### Loading Settings in Controllers

```php
// Pattern used across all controllers
public function someMethod(Shop $shop)
{
    // Load or create settings
    $settings = $shop->settings;
    if (!$settings) {
        $settings = ShopSettings::create([
            'shop_id' => $shop->id,
            ...ShopSettings::defaults()
        ]);
    }
    
    // Use settings in business logic
    if (!$settings->allow_credit_sales) {
        // Reject credit sale
    }
    
    if ($settings->track_stock) {
        // Check stock
    }
}
```

### Helper Methods

```php
// In ShopSettings model
$settings->isCurrentlyOpen();     // Check if shop is open now
$settings->isStockLow($quantity);  // Check if stock is low
```

---

## 📊 Settings Impact Summary

| Feature | Settings Used | Impact |
|---------|---------------|---------|
| **POS Sales** | 7 settings | Controls sales behavior completely |
| **Inventory** | 4 settings | Manages stock tracking & alerts |
| **Receipts** | 5 settings | Customizes receipt output |
| **Products** | 2 settings | Filters & analysis |
| **Shop Info** | 3 settings | Display & operations |

---

## 🚀 Frontend Integration

### Kotlin Example

```kotlin
// Check before allowing action
if (!shop.settings.allowCreditSales) {
    showError("Credit sales not allowed")
    return
}

// Validate discount
if (discountPercent > shop.settings.maxDiscountPercentage) {
    showError("Max discount is ${shop.settings.maxDiscountPercentage}%")
    return
}

// Check if shop is open
if (!shop.settings.isCurrentlyOpen) {
    showWarning("Shop is currently closed")
}

// Use receipt settings
if (shop.settings.autoPrintReceipt) {
    printReceipt()
}
```

---

## ✅ Integration Checklist

- [x] POSController - Sales validation
- [x] POSController - Credit sales control
- [x] POSController - Discount validation
- [x] POSController - Stock management
- [x] POSController - Tax application
- [x] ProductController - Low stock filtering
- [x] ProductController - Inventory analysis
- [x] SaleResource - Receipt settings
- [x] ShopResource - Settings summary
- [x] ShopResource - Subscription info
- [x] Auto-creation of settings
- [x] Smart defaults
- [x] Helper methods

---

## 🎓 Best Practices

### 1. Always Load Settings
```php
// Good
$settings = $shop->settings ?? ShopSettings::create([...]);

// Bad
$settings = $shop->settings; // Might be null
```

### 2. Check Before Action
```php
// Check settings before allowing discounts
if ($settings->allow_discounts) {
    // Process discount
}
```

### 3. Use Helper Methods
```php
// Use built-in helpers
if ($settings->isStockLow($product->current_stock)) {
    // Send alert
}
```

### 4. Include in Resources
```php
// Provide settings to frontend
'receiptSettings' => [
    'header' => $settings->receipt_header,
    'autoPrint' => $settings->auto_print_receipt
]
```

---

## 🎉 Summary

**Shop settings are now fully integrated across the platform!**

✅ **Sales:** Credit, discounts, stock all controlled by settings  
✅ **Inventory:** Tracking, thresholds, alerts respect settings  
✅ **Receipts:** Customizable headers, footers, tax display  
✅ **Products:** Low stock based on shop threshold  
✅ **Auto-Creation:** Settings created automatically with smart defaults  

**Result:** Shop owners have complete control over how their shop operates through simple settings!

---

**Implementation Date:** November 7, 2025  
**Status:** ✅ **FULLY INTEGRATED & PRODUCTION READY**  
**Files Modified:** 4 (POSController, ProductController, SaleResource, ShopResource)

