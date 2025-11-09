<?php

namespace App\Jobs;

use App\Models\Otp;
use App\Models\User;
use Bryceandy\Beem\Facades\Beem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendOtpJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public User $user, public Otp $otp)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            //code...

            $formattedPhoneNumber = str($this->user->phone)->substr(-9)->prepend('255')->toString();
            $senderPhone = [
                'recipient_id' => (string) $this->user->id,
                'dest_addr' => (string) $formattedPhoneNumber,
            ];
            $text = match ($this->otp->type) {
                'registration' => "Karibu MaiDuka! Namba yako ya uthibitisho ni {$this->otp->code}. Tafadhali tumia namba hii kuthibitisha akaunti yako.",
                'login' => "Namba yako ya uthibitisho wa kuingia ni {$this->otp->code}.",
                'reset_password' => "Namba yako ya uthibitisho wa kubadilisha nywila ni {$this->otp->code}.",
                default => "Namba yako ya uthibitisho ni {$this->otp->code}.",
            };
             Beem::sms($text, [$senderPhone], config('beem.sender_name'));
        } catch (\Throwable $th) {
            logger($th->getMessage());
        }
    }
}
