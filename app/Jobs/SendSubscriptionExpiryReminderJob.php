<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Models\Shop;
use Bryceandy\Beem\Facades\Beem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendSubscriptionExpiryReminderJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Subscription $subscription,
        public int $daysRemaining
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $shop = $this->subscription->shop;
            $owner = $shop->owner;

            if (!$owner || !$owner->phone) {
                Log::warning("No owner or phone number for shop: {$shop->id}");
                return;
            }

            // Format phone number for Tanzania (255)
            $formattedPhoneNumber = str($owner->phone)->substr(-9)->prepend('255')->toString();

            $senderPhone = [
                'recipient_id' => (string) $owner->id,
                'dest_addr' => (string) $formattedPhoneNumber,
            ];

            // Get plan details
            $planName = $this->subscription->plan->label();
            $shopName = $shop->name;
            $expiryDate = $this->subscription->expires_at->format('d/m/Y');
            $price = number_format($this->subscription->price, 2);

            // Create message based on days remaining
            $text = match (true) {
                $this->daysRemaining == 1 =>
                "MaiDuka: Duka lako '{$shopName}' lina mpango wa '{$planName}' utaisha kesho ({$expiryDate}). Kiasi: TSh {$price}. Fanya malipo mapema ili kukwepa mkato wa huduma.",

                $this->daysRemaining <= 3 =>
                "MaiDuka: Duka lako '{$shopName}' lina mpango wa '{$planName}' utaisha siku {$this->daysRemaining} ({$expiryDate}). Kiasi: TSh {$price}. Fanya malipo mapema.",

                $this->daysRemaining == 7 =>
                "MaiDuka: Mpango wako wa '{$planName}' kwa duka '{$shopName}' utaisha wiki ijayo ({$expiryDate}). Kiasi cha kuhuisha: TSh {$price}.",

                default =>
                "MaiDuka: Mpango wako wa '{$planName}' utaisha siku {$this->daysRemaining}. Fanya malipo ya TSh {$price} ili kuendelea kupata huduma.",
            };

            Log::info("Sending subscription expiry reminder SMS: {$text}");

            // Send SMS via Beem
            Beem::sms($text, [$senderPhone], "MaiDuka");

            Log::info("Subscription expiry reminder sent to: {$owner->phone}");

        } catch (\Throwable $th) {
            Log::error("Failed to send subscription expiry reminder: {$th->getMessage()}");
        }
    }
}

