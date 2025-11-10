<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Models\Shop;
use Bryceandy\Beem\Facades\Beem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendSubscriptionCreatedJob implements ShouldQueue
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
            $expiryDate = $this->subscription->expires_at->format('d/m/Y');
            $price = number_format($this->subscription->price, 2);
            $typeName = $this->subscription->type->label();

            $text = "MaiDuka: Hongera! Duka lako '{$shopName}' limejiandikisha mpango wa '{$planName}' ({$typeName}). Kiasi: TSh {$price}. Utaisha: {$expiryDate}. Karibu MaiDuka!";

            Log::info("Sending subscription created SMS: {$text}");

            // Send SMS via Beem
            Beem::sms($text, [$senderPhone], "MaiDuka");

            Log::info("Subscription created notification sent to: {$owner->phone}");

        } catch (\Throwable $th) {
            Log::error("Failed to send subscription created notification: {$th->getMessage()}");
        }
    }
}
