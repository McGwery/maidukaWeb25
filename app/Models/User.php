<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

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
    public function generateOtp(string $type): Otp
    {
        // Invalidate any existing OTPs
        $this->otps()->where('type', $type)->delete();

        return $this->otps()->create([
            'phone' => $this->phone,
            'code' => str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'type' => $type,
            'expires_at' => now()->addMinutes(10),
        ]);
    }

    /**
     * Verify if the given OTP is valid.
     */
    public function verifyOtp(string $code, string $type): bool
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
