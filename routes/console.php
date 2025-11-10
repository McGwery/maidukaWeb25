<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule: Convert unpaid credit sales to expenses (runs daily at 2 AM)
Schedule::command('sales:convert-unpaid-to-expense')
    ->dailyAt('02:00')
    ->onOneServer()
    ->withoutOverlapping();

// Schedule: Send debt reminders to customers (runs every Monday at 9 AM)
Schedule::command('customers:send-debt-reminders')
    ->weeklyOn(1, '09:00')
    ->onOneServer()
    ->withoutOverlapping();

// Schedule: Process daily savings from profits (runs daily at 3 AM)
Schedule::command('savings:process-daily')
    ->dailyAt('03:00')
    ->onOneServer()
    ->withoutOverlapping();

// Schedule: Check and mark expired subscriptions (runs every hour)
Schedule::command('subscriptions:check-expired')
    ->hourly()
    ->onOneServer()
    ->withoutOverlapping();

// Schedule: Send reminder for subscriptions expiring in 7 days (runs daily at 9 AM)
Schedule::command('subscriptions:check-expiring --days=7')
    ->dailyAt('09:00')
    ->onOneServer()
    ->withoutOverlapping();

// Schedule: Send reminder for subscriptions expiring in 3 days (runs daily at 10 AM)
Schedule::command('subscriptions:check-expiring --days=3')
    ->dailyAt('10:00')
    ->onOneServer()
    ->withoutOverlapping();

// Schedule: Send reminder for subscriptions expiring tomorrow (runs daily at 8 AM)
Schedule::command('subscriptions:check-expiring --days=1')
    ->dailyAt('08:00')
    ->onOneServer()
    ->withoutOverlapping();

// Schedule: Process auto-renewal subscriptions (runs every 6 hours)
Schedule::command('subscriptions:process-auto-renewal')
    ->everySixHours()
    ->onOneServer()
    ->withoutOverlapping();
