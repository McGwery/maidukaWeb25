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

