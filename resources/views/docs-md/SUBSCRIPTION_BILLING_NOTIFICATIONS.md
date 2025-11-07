# Subscription Billing Notifications & Schedulers Documentation

## 🎯 Overview

A comprehensive automated billing notification system that sends SMS alerts to shop owners about their subscription status using **Beem SMS Gateway**. The system includes:

- ✅ Automated schedulers (Laravel Task Scheduler)
- ✅ SMS notifications via Beem
- ✅ Multiple notification triggers
- ✅ Auto-renewal processing
- ✅ Expiry tracking and reminders

---

## 📱 SMS Notifications (Jobs)

### 1. SendSubscriptionExpiryReminderJob
**Purpose:** Send reminder SMS before subscription expires

**File:** `app/Jobs/SendSubscriptionExpiryReminderJob.php`

**When Triggered:**
- 7 days before expiry
- 3 days before expiry
- 1 day before expiry

**SMS Example (Swahili):**
```
MaiDuka: Duka lako 'Duka la Mama' lina mpango wa 'Premium Plan' 
utaisha siku 3 (15/11/2025). Kiasi: TSh 29,990.00. 
Fanya malipo mapema.
```

---

### 2. SendSubscriptionExpiredJob
**Purpose:** Notify shop owner when subscription has expired

**File:** `app/Jobs/SendSubscriptionExpiredJob.php`

**When Triggered:**
- When subscription status changes to "expired"

**SMS Example (Swahili):**
```
MaiDuka: Mpango wako wa 'Premium Plan' kwa duka 'Duka la Mama' 
UMEISHA. Baadhi ya huduma zimesimamishwa. 
Fanya malipo ya TSh 29,990.00 ili kuendelea kutumia huduma zote.
```

---

### 3. SendSubscriptionRenewedJob
**Purpose:** Confirm successful subscription renewal

**File:** `app/Jobs/SendSubscriptionRenewedJob.php`

**When Triggered:**
- After manual renewal via API
- After auto-renewal processing

**SMS Example (Swahili):**
```
MaiDuka: Hongera! Mpango wako wa 'Premium Plan' kwa duka 
'Duka la Mama' umehuishwa. Kiasi: TSh 29,990.00. 
Utaisha: 15/12/2025. Asante kwa kuendelea kutumia MaiDuka!
```

---

### 4. SendSubscriptionCreatedJob
**Purpose:** Welcome message when new subscription is created

**File:** `app/Jobs/SendSubscriptionCreatedJob.php`

**When Triggered:**
- After creating new subscription via API

**SMS Example (Swahili):**
```
MaiDuka: Hongera! Duka lako 'Duka la Mama' limejiandikisha 
mpango wa 'Premium Plan' (Both Online and Offline). 
Kiasi: TSh 29,990.00. Utaisha: 15/12/2025. Karibu MaiDuka!
```

---

## ⏰ Automated Schedulers (Console Commands)

### 1. Check Expiring Subscriptions
**Command:** `subscriptions:check-expiring`

**File:** `app/Console/Commands/CheckExpiringSubscriptions.php`

**Description:** Checks for subscriptions expiring in X days and sends SMS reminders

**Usage:**
```bash
# Check subscriptions expiring in 7 days (default)
php artisan subscriptions:check-expiring

# Check subscriptions expiring in 3 days
php artisan subscriptions:check-expiring --days=3

# Check subscriptions expiring tomorrow
php artisan subscriptions:check-expiring --days=1
```

**Schedule:**
- 7 days before: Daily at 9:00 AM
- 3 days before: Daily at 10:00 AM
- 1 day before: Daily at 8:00 AM

**Output Example:**
```
Checking for subscriptions expiring in 7 days...
Found 5 subscription(s) expiring in 7 days.
✓ Queued reminder for shop: Duka la Mama
✓ Queued reminder for shop: Biashara Yangu
✓ Queued reminder for shop: Shop Online

Summary:
+-----------------------+-------+
| Status                | Count |
+-----------------------+-------+
| Reminders Queued      | 5     |
| Failed                | 0     |
| Total                 | 5     |
+-----------------------+-------+
```

---

### 2. Check Expired Subscriptions
**Command:** `subscriptions:check-expired`

**File:** `app/Console/Commands/CheckExpiredSubscriptions.php`

**Description:** Finds expired subscriptions, updates their status, and sends notifications

**Usage:**
```bash
php artisan subscriptions:check-expired
```

**Schedule:** Runs every hour

**What It Does:**
1. Finds active subscriptions with `expires_at < now()`
2. Updates status to "expired"
3. Sends expiry notification SMS
4. Logs all actions

**Output Example:**
```
Checking for expired subscriptions...
Found 3 expired subscription(s).
✓ Updated status for shop: Duka la Mama
  → Queued expiry notification
✓ Updated status for shop: Biashara Yangu
  → Queued expiry notification

Summary:
+-----------------------+-------+
| Status                | Count |
+-----------------------+-------+
| Status Updated        | 3     |
| Notifications Queued  | 3     |
| Failed                | 0     |
| Total                 | 3     |
+-----------------------+-------+
```

---

### 3. Process Auto-Renewal Subscriptions
**Command:** `subscriptions:process-auto-renewal`

**File:** `app/Console/Commands/ProcessAutoRenewalSubscriptions.php`

**Description:** Automatically renews subscriptions with auto-renewal enabled

**Usage:**
```bash
php artisan subscriptions:process-auto-renewal
```

**Schedule:** Runs every 6 hours

**What It Does:**
1. Finds subscriptions with `auto_renew = true` expiring within 24 hours
2. Renews them automatically
3. Updates expiry date
4. Sends renewal confirmation SMS

**Output Example:**
```
Processing auto-renewal subscriptions...
Found 2 subscription(s) for auto-renewal.
✓ Renewed subscription for shop: Duka la Mama (Premium Plan)
  → New expiry date: 2025-12-15
  → Queued renewal notification
✓ Renewed subscription for shop: Shop Online (Basic Plan)
  → New expiry date: 2025-12-10
  → Queued renewal notification

Summary:
+-----------------------+-------+
| Status                | Count |
+-----------------------+-------+
| Renewed               | 2     |
| Failed                | 0     |
| Total Processed       | 2     |
+-----------------------+-------+
```

---

## 🕐 Schedule Configuration

The schedulers are configured in: `routes/console.php`

```php
// Check and mark expired subscriptions (runs every hour)
Schedule::command('subscriptions:check-expired')
    ->hourly()
    ->onOneServer()
    ->withoutOverlapping();

// Send reminder for subscriptions expiring in 7 days (runs daily at 9 AM)
Schedule::command('subscriptions:check-expiring --days=7')
    ->dailyAt('09:00')
    ->onOneServer()
    ->withoutOverlapping();

// Send reminder for subscriptions expiring in 3 days (runs daily at 10 AM)
Schedule::command('subscriptions:check-expiring --days=3')
    ->dailyAt('10:00')
    ->onOneServer()
    ->withoutOverlapping();

// Send reminder for subscriptions expiring tomorrow (runs daily at 8 AM)
Schedule::command('subscriptions:check-expiring --days=1')
    ->dailyAt('08:00')
    ->onOneServer()
    ->withoutOverlapping();

// Process auto-renewal subscriptions (runs every 6 hours)
Schedule::command('subscriptions:process-auto-renewal')
    ->everySixHours()
    ->onOneServer()
    ->withoutOverlapping();
```

---

## 🚀 Setup & Deployment

### 1. Enable Laravel Scheduler

Add this to your server's crontab:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Or for better logging:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /var/log/laravel-scheduler.log 2>&1
```

### 2. Configure Queue Worker

Since jobs are queued, ensure queue worker is running:
```bash
php artisan queue:work --queue=default --tries=3
```

For production (use Supervisor):
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-your-project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/laravel-worker.log
stopwaitsecs=3600
```

### 3. Configure Beem SMS

Ensure Beem credentials are set in `.env`:
```env
BEEM_API_KEY=your_api_key
BEEM_SECRET_KEY=your_secret_key
BEEM_SENDER_NAME=MaiDuka
```

---

## 📊 Notification Timeline

Here's what happens in the lifecycle of a subscription:

```
Day 0 (Creation)
└─ SendSubscriptionCreatedJob → Welcome SMS

Day 23 (7 days before expiry)
└─ CheckExpiringSubscriptions (--days=7) → Reminder SMS

Day 27 (3 days before expiry)
└─ CheckExpiringSubscriptions (--days=3) → Urgent reminder SMS

Day 29 (1 day before expiry)
└─ CheckExpiringSubscriptions (--days=1) → Final reminder SMS

Day 30 (Expiry date)
├─ If auto_renew = true:
│  └─ ProcessAutoRenewalSubscriptions → Auto-renew → Renewal SMS
│
└─ If auto_renew = false:
   └─ CheckExpiredSubscriptions → Mark as expired → Expired SMS

After Expiry
└─ CheckExpiredSubscriptions (hourly) → Keeps checking and updating status
```

---

## 🔧 Manual Testing

### Test Expiring Subscriptions Check
```bash
# Test 7-day reminder
php artisan subscriptions:check-expiring --days=7

# Test 3-day reminder
php artisan subscriptions:check-expiring --days=3

# Test 1-day reminder
php artisan subscriptions:check-expiring --days=1
```

### Test Expired Subscriptions Check
```bash
php artisan subscriptions:check-expired
```

### Test Auto-Renewal
```bash
php artisan subscriptions:process-auto-renewal
```

### Test SMS Jobs Directly
```php
use App\Models\Subscription;
use App\Jobs\SendSubscriptionExpiryReminderJob;

$subscription = Subscription::find('subscription-id');
SendSubscriptionExpiryReminderJob::dispatch($subscription, 7);
```

---

## 📝 SMS Message Translations

All SMS messages are in **Swahili** for better local engagement:

| English | Swahili |
|---------|---------|
| Your plan expires in X days | Mpango wako utaisha siku X |
| Payment amount | Kiasi |
| Renew early | Fanya malipo mapema |
| Expired | Umeisha |
| Renewed | Umehuishwa |
| Congratulations | Hongera |
| Thank you | Asante |
| Services suspended | Huduma zimesimamishwa |

---

## 🎯 Key Features

### Smart Reminders
- ✅ 7 days notice (early warning)
- ✅ 3 days notice (urgent reminder)
- ✅ 1 day notice (final reminder)
- ✅ Immediate expiry notification

### Auto-Renewal
- ✅ Processes renewals 24 hours before expiry
- ✅ Automatic payment processing (if configured)
- ✅ Confirmation SMS sent

### Fail-Safe
- ✅ Hourly expired subscription checks
- ✅ Prevents duplicate notifications
- ✅ Comprehensive logging
- ✅ Error handling and retry logic

### Multi-Language Support
- ✅ Swahili messages (primary)
- ✅ Easy to add English or other languages

---

## 📈 Monitoring & Logs

### View Scheduler Logs
```bash
tail -f storage/logs/laravel.log | grep "subscription"
```

### Check Queue Status
```bash
php artisan queue:monitor
```

### View Failed Jobs
```bash
php artisan queue:failed
```

### Retry Failed Jobs
```bash
php artisan queue:retry all
```

---

## 🔒 Security & Best Practices

1. **Rate Limiting:** SMS sending is queued to prevent API rate limits
2. **Duplicate Prevention:** `onOneServer()` and `withoutOverlapping()` prevent duplicate runs
3. **Error Handling:** All jobs have try-catch blocks
4. **Logging:** Comprehensive logging for debugging
5. **Transaction Safety:** Database transactions for data integrity

---

## 💡 Customization

### Change SMS Messages
Edit the job files to customize messages:
- `SendSubscriptionExpiryReminderJob.php`
- `SendSubscriptionExpiredJob.php`
- `SendSubscriptionRenewedJob.php`
- `SendSubscriptionCreatedJob.php`

### Change Schedule Times
Edit `routes/console.php` to adjust when schedulers run

### Add New Notification Types
Create new job classes following the same pattern

---

## 📞 Troubleshooting

### SMS Not Sending?
1. Check Beem credentials in `.env`
2. Verify queue worker is running
3. Check failed jobs: `php artisan queue:failed`
4. Review logs: `storage/logs/laravel.log`

### Scheduler Not Running?
1. Verify crontab is set up correctly
2. Check cron logs: `/var/log/cron.log`
3. Test manually: `php artisan schedule:run`
4. Check schedule list: `php artisan schedule:list`

### Duplicate Notifications?
1. Ensure `onOneServer()` is in schedule
2. Check for multiple queue workers
3. Review subscription data for duplicates

---

## ✅ Implementation Checklist

- [x] SMS notification jobs created
- [x] Console commands for schedulers created
- [x] Laravel Task Scheduler configured
- [x] SMS integration with Beem
- [x] Auto-renewal logic implemented
- [x] Error handling and logging
- [x] Swahili message templates
- [x] Documentation completed

---

## 🎉 Status: FULLY IMPLEMENTED AND PRODUCTION READY!

All subscription billing notifications and schedulers are now complete and ready to use!

**Implementation Date:** November 7, 2025  
**SMS Gateway:** Beem  
**Language:** Swahili  
**Status:** ✅ Production Ready

