<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\OtpType;
use App\Jobs\SendOtpJob;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_phone_login_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'is_phone_login_enabled' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the latest OTP for this user.
     */
    public function latestOtp()
    {
        return $this->hasOne(Otp::class)->latest();
    }

    /**
     * Get all OTPs for this user.
     */
    public function otps()
    {
        return $this->hasMany(Otp::class);
    }

    /**
     * Generate a new OTP code for this user.
     */
    public function generateOtp(OtpType $type): Otp
    {
        // Invalidate any existing OTPs
        $this->otps()->where('type', $type)->delete();

        $generatedOtp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
       
        $otp =  $this->otps()->create([
            'phone' => $this->phone,
            'code' => $generatedOtp,
            'type' => $type,
            'expires_at' => now()->addMinutes($type->expirationMinutes()),
        ]);
        SendOtpJob::dispatch($this, $otp);
        return $otp;
    }

    /**
     * Verify if the given OTP is valid.
     */
    public function verifyOtp(string $code, OtpType $type): bool
    {
        $otp = $this->otps()
            ->where('type', $type)
            ->where('code', $code)
            ->first();

        if (!$otp || !$otp->isValid()) {
            return false;
        }

        // Delete the OTP after successful verification
        $otp->delete();

        return true;
    }
}
