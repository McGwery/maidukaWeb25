<?php

namespace App\Console\Commands;

use App\Jobs\SendDebtReminderJob;
use App\Models\Customer;
use Illuminate\Console\Command;

class SendDebtReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:send-debt-reminders {--min-debt=1000 : Minimum debt amount to send reminder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send debt reminder SMS to all customers with outstanding debts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to send debt reminders...');

        $minDebt = $this->option('min-debt');

        // Get all customers with debt greater than minimum
        $customers = Customer::with('shop')
            ->where('current_debt', '>', $minDebt)
            ->whereNotNull('phone')
            ->get();

        if ($customers->isEmpty()) {
            $this->info('No customers with outstanding debts found.');
            return 0;
        }

        $this->info("Found {$customers->count()} customers with outstanding debts.");

        $sentCount = 0;

        foreach ($customers as $customer) {
            try {
                SendDebtReminderJob::dispatch($customer, $customer->shop);
                $sentCount++;
                $this->line("✓ Queued reminder for {$customer->name} - Debt: {$customer->current_debt}");
            } catch (\Exception $e) {
                $this->error("✗ Failed to queue reminder for {$customer->name}: {$e->getMessage()}");
            }
        }

        $this->info("Successfully queued {$sentCount} debt reminder messages.");

        return 0;
    }
}
