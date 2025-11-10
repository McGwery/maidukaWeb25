# 🚀 Scheduled Tasks - Complete Implementation

## Quick Overview

This implementation adds two critical automated features to your POS system:

1. **📊 Bad Debt Conversion** - Automatically converts unpaid credit sales (1+ year old) into expenses
2. **📱 Debt Reminders** - Sends SMS reminders to customers with outstanding debts

---

## ⚡ Quick Start

```bash
# 1. Run migrations
php artisan migrate

# 2. Test the commands
php artisan sales:convert-unpaid-to-expense
php artisan customers:send-debt-reminders --min-debt=100

# 3. Start scheduler (development)
php artisan schedule:work

# 4. Start queue worker (for SMS)
php artisan queue:work
```

---

## 📚 Documentation Files

| File | Purpose |
|------|---------|
| **FINAL_IMPLEMENTATION_SUMMARY.md** | Complete implementation overview ⭐ START HERE |
| **SCHEDULED_TASKS_DOCUMENTATION.md** | Full detailed documentation |
| **SCHEDULED_TASKS_QUICK_REFERENCE.md** | Quick command reference |
| **SCHEDULED_TASKS_FLOW.md** | Visual flow diagrams |
| **README_SCHEDULED_TASKS.md** | This file |

---

## 🛠️ What Was Built

### Commands (2)
- ✅ `sales:convert-unpaid-to-expense` - Converts old unpaid sales to bad debt
- ✅ `customers:send-debt-reminders` - Sends SMS to customers with debts

### Jobs (1)
- ✅ `SendDebtReminderJob` - Processes SMS sending via Beem API

### Migrations (2)
- ✅ Added `sale_id` to expenses table
- ✅ Added `converted_to_expense_at` to sales table

### Models (2)
- ✅ Updated `Sale` model
- ✅ Updated `Expense` model

### Enums (1)
- ✅ Added `BAD_DEBT` category to `ExpenseCategory`

### API Resources (2)
- ✅ Updated `SaleResource` (added `convertedToExpenseAt`)
- ✅ Updated `ExpenseResource` (added `saleId`)

---

## 📅 Schedules

| Task | When | Time |
|------|------|------|
| Convert Unpaid Sales | Daily | 2:00 AM |
| Send Debt Reminders | Weekly (Monday) | 9:00 AM |

---

## 🎯 Commands

### Convert Unpaid Sales
```bash
# Run manually
php artisan sales:convert-unpaid-to-expense

# What it does:
# - Finds credit sales with debt > 0
# - Older than 1 year
# - Not already converted
# - Creates bad debt expense
# - Marks sale as converted
```

### Send Debt Reminders
```bash
# Run manually (default min debt: 1000 TZS)
php artisan customers:send-debt-reminders

# Run with custom minimum debt
php artisan customers:send-debt-reminders --min-debt=5000

# What it does:
# - Finds customers with debt > min_debt
# - Has phone number
# - Queues SMS job
# - Sends Swahili reminder via Beem
```

---

## 🔧 Setup for Production

### 1. Configure Environment
```env
BEEM_KEY=your_api_key
BEEM_SECRET=your_secret_key
```

### 2. Enable SMS Sending
Edit `app/Jobs/SendDebtReminderJob.php` line 57:
```php
// Uncomment this line:
Beem::sms($text, [$senderPhone], $this->shop->name);
```

### 3. Setup Cron Job
```bash
# Add to crontab
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Setup Queue Worker (Supervisor)
```ini
[program:maiduka-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-project/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path-to-project/storage/logs/worker.log
```

---

## 📱 SMS Message Format

```
Habari {customer_name}, hii ni ukumbusho wa kirafiki kutoka {shop_name}. 
Una deni la TZS {debt_amount}. Tafadhali lipa mapema iwezekanavyo. 
Kwa maswali wasiliana nasi kwa {shop_phone}. Asante!
```

---

## 🔍 Monitoring

### View Logs
```bash
tail -f storage/logs/laravel.log
```

### Check Database
```bash
php artisan tinker

# Count bad debts
>>> \App\Models\Expense::where('category', 'bad_debt')->count()

# Count converted sales
>>> \App\Models\Sale::whereNotNull('converted_to_expense_at')->count()
```

### Check Schedule
```bash
php artisan schedule:list
```

### Check Queue
```bash
php artisan queue:work --once
php artisan queue:failed
```

---

## 📊 API Response Format (camelCase)

All responses use **camelCase** for Kotlin app:

```json
{
  "id": "uuid",
  "saleId": "uuid",
  "convertedToExpenseAt": "2025-11-06T02:00:00Z",
  "debtAmount": "50000.00",
  "currentDebt": "50000.00"
}
```

---

## ✅ Features

- ✅ **Automatic** - No manual intervention needed
- ✅ **Safe** - Prevents duplicates with `converted_to_expense_at` flag
- ✅ **Efficient** - Uses queues for SMS sending
- ✅ **Localized** - SMS in Swahili
- ✅ **Trackable** - Links expenses to original sales
- ✅ **Flexible** - Customizable minimum debt threshold
- ✅ **Kotlin-Ready** - All responses in camelCase

---

## 🎉 You're Done!

Everything is implemented and ready to go. Just:

1. Run migrations ✅
2. Configure Beem SMS ✅
3. Setup cron job ✅
4. Start queue worker ✅
5. Monitor and enjoy! 🚀

---

## 📞 Need Help?

- Check **FINAL_IMPLEMENTATION_SUMMARY.md** for complete details
- Check **SCHEDULED_TASKS_QUICK_REFERENCE.md** for commands
- Check **SCHEDULED_TASKS_FLOW.md** for visual diagrams
- Check logs: `storage/logs/laravel.log`

---

**Status:** ✅ Complete  
**Date:** November 6, 2025  
**Version:** 1.0

