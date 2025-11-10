<?php

namespace App\Console\Commands;

use App\Enums\SubscriptionStatus;
use App\Jobs\SendSubscriptionExpiredJob;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expired subscriptions and update their status, send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Checking for expired subscriptions...");

        // Get active subscriptions that have expired
        $expiredSubscriptions = Subscription::query()
            ->where('status', 'active')
            ->where('expires_at', '<', now())
            ->with(['shop.owner'])
            ->get();

        if ($expiredSubscriptions->isEmpty()) {
            $this->info("No expired subscriptions found.");
            return Command::SUCCESS;
        }

        $this->info("Found {$expiredSubscriptions->count()} expired subscription(s).");

        $updated = 0;
        $notified = 0;
        $failed = 0;

        foreach ($expiredSubscriptions as $subscription) {
            try {
                // Update subscription status to expired
                $subscription->update([
                    'status' => SubscriptionStatus::EXPIRED
                ]);

                $shopName = $subscription->shop->name;
                $this->line("✓ Updated status for shop: {$shopName}");
                $updated++;

                // Send expiry notification
                SendSubscriptionExpiredJob::dispatch($subscription);
                $this->line("  → Queued expiry notification");
                $notified++;

            } catch (\Exception $e) {
                $this->error("✗ Failed to process subscription: {$e->getMessage()}");
                Log::error("Failed to process expired subscription", [
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
                ['Status Updated', $updated],
                ['Notifications Queued', $notified],
                ['Failed', $failed],
                ['Total', $expiredSubscriptions->count()],
            ]
        );

        Log::info("Checked expired subscriptions", [
            'total' => $expiredSubscriptions->count(),
            'updated' => $updated,
            'notified' => $notified,
            'failed' => $failed
        ]);

        return Command::SUCCESS;
    }
}
