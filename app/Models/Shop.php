<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Shop extends Model implements HasMedia
{
    use HasFactory, HasUuid, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name',
        'business_type',
        'phone_number',
        'address',
        'agent_code',
        'owner_id',
        'currency',
        'image_url',
        'is_active',
        'status', // active, inactive, suspended
       // 'availability' // ONLINE, OFFLINE, BOTH
    ];

    protected $casts = [
        'business_type' => \App\Enums\ShopType::class,
        'currency' => \App\Enums\Currency::class,
        'is_active' => 'boolean'
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shop_members')
            ->withPivot(['role', 'permissions', 'is_active'])
            ->withTimestamps();
    }

    public function shopMembers(): HasMany
    {
        return $this->hasMany(ShopMember::class);
    }

    public function activeMembers(): BelongsToMany
    {
        return $this->members()->wherePivot('is_active', true);
    }

    public function activeUsers(): HasMany
    {
        return $this->hasMany(ActiveShop::class);
    }

     public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latestOfMany('starts_at');
    }

    public function settings(): HasOne
    {
        return $this->hasOne(ShopSettings::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

     /**
     * Get the currently selected shop for the user.
     */
    public function activeShop(): HasOne
    {
        return $this->hasOne(ActiveShop::class)->latestOfMany('selected_at')->where('user_id', auth()->id());
    }

    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function isMember(User $user): bool
    {
        return $this->members()->wherePivot('user_id', $user->id)->exists();
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'shop_suppliers', 'shop_id', 'supplier_id')
            ->withTimestamps();
    }

   public function clientShops(): BelongsToMany
   {
       return $this->belongsToMany(Shop::class, 'shop_suppliers', 'supplier_id', 'shop_id')
           ->withTimestamps();
   }
}
