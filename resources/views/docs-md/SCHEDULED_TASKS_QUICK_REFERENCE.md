# Scheduled Tasks - Quick Reference

## Commands

### 1. Convert Unpaid Credit Sales to Expenses
```bash
php artisan sales:convert-unpaid-to-expense
```
- Converts credit sales unpaid for 1+ year into bad debt expenses
- Runs: Daily at 2:00 AM
- Category: `BAD_DEBT`

### 2. Send Debt Reminders
```bash
php artisan customers:send-debt-reminders [--min-debt=1000]
```
- Sends SMS reminders to customers with outstanding debts
- Runs: Every Monday at 9:00 AM
- Default minimum debt: TZS 1,000

## Schedule

| Task | Frequency | Time | Day |
|------|-----------|------|-----|
| Convert Unpaid Sales | Daily | 02:00 | Every day |
| Send Debt Reminders | Weekly | 09:00 | Monday |

## Database Changes

### Sales Table
- `converted_to_expense_at` - Timestamp when sale was converted to expense

### Expenses Table  
- `sale_id` - Foreign key linking to original sale (for bad debts)

### Expense Categories
- Added: `BAD_DEBT` = "Bad Debt"

## Setup Scheduler

### Production (Cron)
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### Development
```bash
php artisan schedule:work
```

## Monitor

```bash
# List scheduled tasks
php artisan schedule:list

# View logs
tail -f storage/logs/laravel.log

# Check queue status
php artisan queue:work
```

## SMS Configuration

Enable SMS in `.env`:
```env
BEEM_KEY=your_api_key
BEEM_SECRET=your_secret_key
```

Uncomment line 57 in `app/Jobs/SendDebtReminderJob.php`:
```php
Beem::sms($text, [$senderPhone], $this->shop->name);
```

## Files Modified

### Commands
- ✅ `app/Console/Commands/ConvertUnpaidCreditSalesToExpense.php`
- ✅ `app/Console/Commands/SendDebtReminders.php`

### Jobs
- ✅ `app/Jobs/SendDebtReminderJob.php`

### Models
- ✅ `app/Models/Sale.php` - Added `converted_to_expense_at`
- ✅ `app/Models/Expense.php` - Added `sale_id` and relationship

### Enums
- ✅ `app/Enums/ExpenseCategory.php` - Added `BAD_DEBT`

### Migrations
- ✅ `2025_11_06_130729_add_sale_id_to_expenses_table.php`
- ✅ `2025_11_06_130738_add_converted_to_expense_at_to_sales_table.php`

### Config
- ✅ `routes/console.php` - Schedule definitions

## Testing

```bash
# Test with real data
php artisan sales:convert-unpaid-to-expense
php artisan customers:send-debt-reminders --min-debt=100

# Check results
php artisan tinker
>>> \App\Models\Expense::where('category', 'bad_debt')->count()
>>> \App\Models\Sale::whereNotNull('converted_to_expense_at')->count()
```

## API Response Format (camelCase)

✅ All responses use camelCase for Kotlin app compatibility:
- `convertedToExpenseAt`
- `saleId`
- `currentDebt`
- `debtAmount`
- `expenseDate`

