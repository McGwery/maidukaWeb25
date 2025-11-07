<?php

namespace App\Console\Commands;

use App\Jobs\SendSubscriptionExpiryReminderJob;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpiringSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-expiring {--days=7 : Number of days before expiry to send notification}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expiring subscriptions and send SMS reminders to shop owners';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');

        $this->info("Checking for subscriptions expiring in {$days} days...");

        // Get subscriptions expiring in specified days
        $expiringSubscriptions = Subscription::query()
            ->where('status', 'active')
            ->whereDate('expires_at', now()->addDays($days)->toDateString())
            ->with(['shop.owner'])
            ->get();

        if ($expiringSubscriptions->isEmpty()) {
            $this->info("No subscriptions found expiring in {$days} days.");
            return Command::SUCCESS;
        }

        $this->info("Found {$expiringSubscriptions->count()} subscription(s) expiring in {$days} days.");

        $sent = 0;
        $failed = 0;

        foreach ($expiringSubscriptions as $subscription) {
            try {
                // Dispatch job to send SMS reminder
                SendSubscriptionExpiryReminderJob::dispatch($subscription, $days);

                $shopName = $subscription->shop->name;
                $this->line("✓ Queued reminder for shop: {$shopName}");
                $sent++;

            } catch (\Exception $e) {
                $this->error("✗ Failed to queue reminder: {$e->getMessage()}");
                Log::error("Failed to queue subscription reminder", [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage()
                ]);
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->table(
            ['Status', 'Count'],
            [
                ['Reminders Queued', $sent],
                ['Failed', $failed],
                ['Total', $expiringSubscriptions->count()],
            ]
        );

        Log::info("Checked expiring subscriptions", [
            'days' => $days,
            'total' => $expiringSubscriptions->count(),
            'sent' => $sent,
            'failed' => $failed
        ]);

        return Command::SUCCESS;
    }
}
