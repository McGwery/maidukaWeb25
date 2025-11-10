<?php

namespace App\Console\Commands;

use App\Jobs\SendSubscriptionRenewedJob;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessAutoRenewalSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:process-auto-renewal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process auto-renewal for subscriptions that are expiring soon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Processing auto-renewal subscriptions...");

        // Get subscriptions with auto-renewal enabled that expire within 24 hours
        $autoRenewalSubscriptions = Subscription::query()
            ->where('status', 'active')
            ->where('auto_renew', true)
            ->whereBetween('expires_at', [now(), now()->addDay()])
            ->with(['shop.owner'])
            ->get();

        if ($autoRenewalSubscriptions->isEmpty()) {
            $this->info("No subscriptions found for auto-renewal.");
            return Command::SUCCESS;
        }

        $this->info("Found {$autoRenewalSubscriptions->count()} subscription(s) for auto-renewal.");

        $renewed = 0;
        $failed = 0;

        foreach ($autoRenewalSubscriptions as $subscription) {
            try {
                $shopName = $subscription->shop->name;
                $planName = $subscription->plan->label();

                // Check if already processed (expires_at is in future after renewal)
                if ($subscription->expires_at > now()->addDay()) {
                    $this->line("⊘ Already renewed: {$shopName}");
                    continue;
                }

                // Renew subscription
                $durationDays = $subscription->plan->durationDays();
                $subscription->renew($durationDays);

                $this->line("✓ Renewed subscription for shop: {$shopName} ({$planName})");
                $this->line("  → New expiry date: {$subscription->expires_at->format('Y-m-d')}");
                $renewed++;

                // Send renewal notification
                SendSubscriptionRenewedJob::dispatch($subscription->fresh());
                $this->line("  → Queued renewal notification");

            } catch (\Exception $e) {
                $this->error("✗ Failed to renew subscription: {$e->getMessage()}");
                Log::error("Failed to auto-renew subscription", [
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
                ['Renewed', $renewed],
                ['Failed', $failed],
                ['Total Processed', $autoRenewalSubscriptions->count()],
            ]
        );

        Log::info("Processed auto-renewal subscriptions", [
            'total' => $autoRenewalSubscriptions->count(),
            'renewed' => $renewed,
            'failed' => $failed
        ]);

        return Command::SUCCESS;
    }
}
