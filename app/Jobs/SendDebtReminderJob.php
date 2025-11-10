<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Shop;
use Bryceandy\Beem\Facades\Beem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendDebtReminderJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Customer $customer,
        public Shop $shop
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!$this->customer->phone) {
                logger("Customer {$this->customer->name} has no phone number");
                return;
            }

            $formattedPhoneNumber = str($this->customer->phone)
                ->replace('+', '')
                ->replace(' ', '')
                ->toString();

            // Format for Tanzania phone numbers
            if (strlen($formattedPhoneNumber) === 9) {
                $formattedPhoneNumber = '255' . $formattedPhoneNumber;
            } elseif (str_starts_with($formattedPhoneNumber, '0') && strlen($formattedPhoneNumber) === 10) {
                $formattedPhoneNumber = '255' . substr($formattedPhoneNumber, 1);
            }

            $senderPhone = [
                'recipient_id' => (string) $this->customer->id,
                'dest_addr' => (string) $formattedPhoneNumber,
            ];

            $debtAmount = number_format($this->customer->current_debt, 0);

            $text = "Habari {$this->customer->name}, hii ni ukumbusho wa kirafiki kutoka {$this->shop->name}. " .
                    "Una deni la TZS {$debtAmount}. Tafadhali lipa mapema iwezekanavyo. " .
                    "Kwa maswali wasiliana nasi kwa {$this->shop->phone_number}. Asante!";

            logger("Sending debt reminder to {$this->customer->name}: {$text}");

            // Uncomment when ready to send actual SMS
            // Beem::sms($text, [$senderPhone], $this->shop->name);

        } catch (\Throwable $th) {
            logger("Failed to send debt reminder to customer {$this->customer->id}: {$th->getMessage()}");
        }
    }
}
