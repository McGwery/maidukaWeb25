<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Models\Shop;
use Bryceandy\Beem\Facades\Beem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendSubscriptionExpiredJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Subscription $subscription)
    {
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

            $planName = $this->subscription->plan->label();
            $shopName = $shop->name;
            $price = number_format($this->subscription->price, 2);

            $text = "MaiDuka: Mpango wako wa '{$planName}' kwa duka '{$shopName}' UMEISHA. Baadhi ya huduma zimesimamishwa. Fanya malipo ya TSh {$price} ili kuendelea kutumia huduma zote.";

            Log::info("Sending subscription expired SMS: {$text}");

            // Send SMS via Beem
            Beem::sms($text, [$senderPhone], "MaiDuka");

            Log::info("Subscription expired notification sent to: {$owner->phone}");

        } catch (\Throwable $th) {
            Log::error("Failed to send subscription expired notification: {$th->getMessage()}");
        }
    }
}

