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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    /**
     * Get all shops owned by the user.
     */
    public function ownedShops(): HasMany
    {
        return $this->hasMany(Shop::class, 'owner_id');
    }

    /**
     * Get all shops where the user is a member.
     */
    public function memberShops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'shop_members')
            ->withPivot(['role', 'permissions', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Get all active shops where the user is a member.
     */
    public function activeShops(): BelongsToMany
    {
        return $this->memberShops()->wherePivot('is_active', true);
    }

    /**
     * Get the currently selected shop for the user.
     */
    public function activeShop(): HasOne
    {
        return $this->hasOne(ActiveShop::class)->latestOfMany('selected_at');
    }

    /**
     * Switch to a different shop.
     */
    public function switchShop(Shop $shop): ActiveShop
    {
        if (!$this->canAccessShop($shop)) {
            throw new \Exception('You do not have access to this shop.');
        }

        $this->activeShop()->delete();

        return ActiveShop::create([
            'user_id' => $this->id,
            'shop_id' => $shop->id,
            'selected_at' => now(),
        ]);
    }

    /**
     * Check if user can access a shop.
     */
    public function canAccessShop(Shop $shop): bool
    {
        return $shop->isOwner($this) || 
               $shop->members()->where('user_id', $this->id)
                    ->where('is_active', true)
                    ->exists();
    }

    /**
     * Get the user's role in a specific shop.
     */
    public function getRoleInShop(Shop $shop): ?string
    {
        if ($shop->isOwner($this)) {
            return 'owner';
        }

        $member = $shop->members()->where('user_id', $this->id)->first();
        return $member?->pivot?->role;
    }

    /**
     * Check if user has specific permission in the shop.
     */
    public function hasShopPermission(Shop $shop, string $permission): bool
    {
        if ($shop->isOwner($this)) {
            return true;
        }

        $member = $shop->members()->where('user_id', $this->id)->first();
        return in_array($permission, $member?->pivot?->permissions ?? []);
    }
}
