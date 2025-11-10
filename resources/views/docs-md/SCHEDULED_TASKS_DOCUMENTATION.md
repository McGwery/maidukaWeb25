# Scheduled Tasks Documentation

This document describes the automated scheduled tasks for managing credit sales and customer debt reminders.

## Overview

Two main scheduled tasks have been implemented:
1. **Convert Unpaid Credit Sales to Expenses** - Converts credit sales that remain unpaid for 1 year into bad debt expenses
2. **Send Debt Reminders** - Sends SMS reminders to customers with outstanding debts

---

## 1. Convert Unpaid Credit Sales to Expenses

### Purpose
Automatically converts credit sales that have remained unpaid for 1 year into bad debt expenses for proper accounting.

### Command
```bash
php artisan sales:convert-unpaid-to-expense
```

### Schedule
- **Frequency**: Daily
- **Time**: 2:00 AM
- **Configuration**: `routes/console.php`

### How It Works
1. Finds all credit sales with `debt_amount > 0`
2. Checks if the sale is older than 1 year (`sale_date <= 1 year ago`)
3. Verifies the sale hasn't already been converted (`converted_to_expense_at` is null)
4. Creates an expense record with:
   - Category: `BAD_DEBT`
   - Amount: The outstanding `debt_amount`
   - Title: "Bad Debt - Sale #{sale_number}"
   - Description: Details about the customer and conversion
5. Marks the sale with `converted_to_expense_at` timestamp

### Database Changes

#### Sales Table
- Added column: `converted_to_expense_at` (timestamp, nullable)

#### Expenses Table
- Added column: `sale_id` (uuid, nullable, foreign key)

#### Expense Categories
- Added category: `BAD_DEBT` = "Bad Debt"

### Manual Execution
```bash
php artisan sales:convert-unpaid-to-expense
```

---

## 2. Send Debt Reminders

### Purpose
Sends automated SMS reminders to customers who have outstanding debts to encourage payment.

### Command
```bash
php artisan customers:send-debt-reminders [--min-debt=1000]
```

### Options
- `--min-debt`: Minimum debt amount to trigger reminder (default: 1000)

### Schedule
- **Frequency**: Weekly
- **Day**: Monday
- **Time**: 9:00 AM
- **Configuration**: `routes/console.php`

### How It Works
1. Finds all customers with `current_debt > min-debt`
2. Filters customers who have a phone number
3. Dispatches `SendDebtReminderJob` for each customer
4. Job sends SMS via Beem SMS API with:
   - Customer name
   - Current debt amount
   - Shop contact information
   - Polite reminder message in Swahili

### SMS Message Format (Swahili)
```
Habari {customer_name}, hii ni ukumbusho wa kirafiki kutoka {shop_name}. 
Una deni la TZS {debt_amount}. Tafadhali lipa mapema iwezekanavyo. 
Kwa maswali wasiliana nasi kwa {shop_phone}. Asante!
```

### Manual Execution
```bash
# Send reminders to customers with debt > 1000 (default)
php artisan customers:send-debt-reminders

# Send reminders to customers with debt > 5000
php artisan customers:send-debt-reminders --min-debt=5000
```

---

## Implementation Details

### Files Created/Modified

#### Commands
- `app/Console/Commands/ConvertUnpaidCreditSalesToExpense.php`
- `app/Console/Commands/SendDebtReminders.php`

#### Jobs
- `app/Jobs/SendDebtReminderJob.php`

#### Models
- `app/Models/Sale.php` - Added `converted_to_expense_at` field
- `app/Models/Expense.php` - Added `sale_id` field and relationship

#### Enums
- `app/Enums/ExpenseCategory.php` - Added `BAD_DEBT` category

#### Migrations
- `database/migrations/2025_11_06_130729_add_sale_id_to_expenses_table.php`
- `database/migrations/2025_11_06_130738_add_converted_to_expense_at_to_sales_table.php`

#### Configuration
- `routes/console.php` - Schedule definitions

---

## Setting Up Scheduler

To enable scheduled tasks, you need to add a cron entry on your server:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Or for development, run:
```bash
php artisan schedule:work
```

---

## SMS Configuration

The debt reminders use the Beem SMS service. Ensure you have configured:

1. `.env` file with Beem credentials:
```env
BEEM_KEY=your_api_key
BEEM_SECRET=your_secret_key
```

2. Uncomment the actual SMS sending line in `SendDebtReminderJob.php`:
```php
// Line 57 - Uncomment this when ready for production
Beem::sms($text, [$senderPhone], $this->shop->name);
```

---

## Monitoring

### Check Scheduled Tasks
```bash
php artisan schedule:list
```

### View Logs
```bash
tail -f storage/logs/laravel.log
```

### Test Commands Manually
```bash
# Test conversion
php artisan sales:convert-unpaid-to-expense

# Test reminders
php artisan customers:send-debt-reminders --min-debt=100
```

---

## Benefits

### For Business Accounting
- ✅ Automatic tracking of bad debts
- ✅ Accurate expense reporting
- ✅ Better financial insights
- ✅ Compliance with accounting standards

### For Cash Flow Management
- ✅ Automated customer reminders
- ✅ Improved debt collection rates
- ✅ Reduced manual follow-up effort
- ✅ Professional communication with customers

---

## API Response Format (camelCase)

All API responses follow camelCase convention for Kotlin app compatibility:

### Sale Response
```json
{
  "id": "uuid",
  "saleNumber": "20251106001",
  "debtAmount": "50000.00",
  "convertedToExpenseAt": "2025-11-06T02:00:00Z",
  // ...other fields
}
```

### Expense Response
```json
{
  "id": "uuid",
  "saleId": "uuid",
  "title": "Bad Debt - Sale #20251106001",
  "category": "bad_debt",
  "amount": "50000.00",
  "expenseDate": "2025-11-06",
  // ...other fields
}
```

### Customer Response
```json
{
  "id": "uuid",
  "name": "John Doe",
  "phone": "255712345678",
  "currentDebt": "50000.00",
  // ...other fields
}
```

---

## Future Enhancements

- Add notification dashboard for shop owners
- Generate weekly/monthly debt reports
- Track SMS delivery status
- Allow custom reminder schedules per shop
- Add payment link in SMS
- Multi-language support for SMS

