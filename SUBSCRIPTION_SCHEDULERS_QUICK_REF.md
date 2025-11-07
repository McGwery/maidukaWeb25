# Subscription Schedulers & SMS Quick Reference

## 🚀 Quick Commands

```bash
# Check subscriptions expiring in 7 days
php artisan subscriptions:check-expiring --days=7

# Check subscriptions expiring in 3 days
php artisan subscriptions:check-expiring --days=3

# Check subscriptions expiring tomorrow
php artisan subscriptions:check-expiring --days=1

# Check and update expired subscriptions
php artisan subscriptions:check-expired

# Process auto-renewals
php artisan subscriptions:process-auto-renewal
```

---

## 📅 Automatic Schedule

| Time | Command | Description |
|------|---------|-------------|
| **08:00 AM** | `check-expiring --days=1` | Tomorrow expiry reminder |
| **09:00 AM** | `check-expiring --days=7` | 7-day advance notice |
| **10:00 AM** | `check-expiring --days=3` | 3-day urgent reminder |
| **Every Hour** | `check-expired` | Mark expired & notify |
| **Every 6 Hours** | `process-auto-renewal` | Auto-renew subscriptions |

---

## 📱 SMS Examples

### 7 Days Before Expiry
```
MaiDuka: Mpango wako wa 'Premium Plan' kwa duka 
'Duka la Mama' utaisha wiki ijayo (15/11/2025). 
Kiasi cha kuhuisha: TSh 29,990.00.
```

### 1 Day Before Expiry
```
MaiDuka: Duka lako 'Duka la Mama' lina mpango wa 
'Premium Plan' utaisha kesho (15/11/2025). 
Kiasi: TSh 29,990.00. Fanya malipo mapema ili 
kukwepa mkato wa huduma.
```

### Expired
```
MaiDuka: Mpango wako wa 'Premium Plan' kwa duka 
'Duka la Mama' UMEISHA. Baadhi ya huduma 
zimesimamishwa. Fanya malipo ya TSh 29,990.00 
ili kuendelea kutumia huduma zote.
```

### Renewed
```
MaiDuka: Hongera! Mpango wako wa 'Premium Plan' 
kwa duka 'Duka la Mama' umehuishwa. 
Kiasi: TSh 29,990.00. Utaisha: 15/12/2025. 
Asante kwa kuendelea kutumia MaiDuka!
```

---

## 🛠️ Setup (One-Time)

### 1. Add to Crontab
```bash
crontab -e
```

Add this line:
```
* * * * * cd /path-to-maiduka25 && php artisan schedule:run >> /dev/null 2>&1
```

### 2. Start Queue Worker
```bash
php artisan queue:work --tries=3
```

### 3. Configure Beem (.env)
```env
BEEM_API_KEY=your_api_key
BEEM_SECRET_KEY=your_secret_key
BEEM_SENDER_NAME=MaiDuka
```

---

## 📊 Notification Flow

```
Subscription Created
    ↓
Welcome SMS Sent
    ↓
[7 days before expiry] → Reminder SMS
    ↓
[3 days before expiry] → Urgent Reminder SMS
    ↓
[1 day before expiry] → Final Reminder SMS
    ↓
[Expiry Date]
    ├─ Auto-Renew Enabled? → Renew → Success SMS
    └─ Auto-Renew Disabled → Mark Expired → Expired SMS
```

---

## 🔍 Monitoring

### View Logs
```bash
tail -f storage/logs/laravel.log | grep "subscription"
```

### Check Schedule
```bash
php artisan schedule:list
```

### View Failed Jobs
```bash
php artisan queue:failed
```

### Retry Failed
```bash
php artisan queue:retry all
```

---

## 📁 Files Created

### Jobs (SMS Sending)
1. `app/Jobs/SendSubscriptionExpiryReminderJob.php`
2. `app/Jobs/SendSubscriptionExpiredJob.php`
3. `app/Jobs/SendSubscriptionRenewedJob.php`
4. `app/Jobs/SendSubscriptionCreatedJob.php`

### Commands (Schedulers)
1. `app/Console/Commands/CheckExpiringSubscriptions.php`
2. `app/Console/Commands/CheckExpiredSubscriptions.php`
3. `app/Console/Commands/ProcessAutoRenewalSubscriptions.php`

### Modified Files
1. `routes/console.php` - Added scheduler configuration
2. `app/Http/Controllers/Api/SubscriptionController.php` - Added SMS dispatch

---

## ✅ Status

✅ **All schedulers implemented**  
✅ **SMS notifications configured**  
✅ **Beem integration complete**  
✅ **Auto-renewal working**  
✅ **Production ready**

---

## 💡 Pro Tips

1. **Test before deployment:**
   ```bash
   php artisan subscriptions:check-expiring --days=1
   ```

2. **Monitor queue status:**
   ```bash
   php artisan queue:monitor
   ```

3. **Use Supervisor for production queue worker:**
   Keeps queue worker running even if it crashes

4. **Review logs daily:**
   Check for failed SMS or errors

5. **Adjust timing as needed:**
   Edit `routes/console.php` to change schedule

---

**Last Updated:** November 7, 2025  
**Status:** ✅ Production Ready

