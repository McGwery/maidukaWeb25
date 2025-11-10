<?php

namespace App\Enums;

enum SubscriptionPlan: string
{
    case FREE = 'free';
    case BASIC = 'basic';
    case PREMIUM = 'premium';
    case ENTERPRISE = 'enterprise';

    public function label(): string
    {
        return match ($this) {
            self::FREE => 'Free Plan',
            self::BASIC => 'Basic Plan',
            self::PREMIUM => 'Premium Plan',
            self::ENTERPRISE => 'Enterprise Plan',
        };
    }

    public function price(): float
    {
        return match ($this) {
            self::FREE => 0,
            self::BASIC => 9.99,
            self::PREMIUM => 12000.00,
            self::ENTERPRISE => 99.99,
        };
    }

    public function durationDays(): int
    {
        return match ($this) {
            self::FREE => 365, // 1 year
            self::BASIC => 30, // 1 month
            self::PREMIUM => 30, // 1 month
            self::ENTERPRISE => 30, // 1 month
        };
    }

    public function features(): array
    {
        return match ($this) {
            self::FREE => [
                'Basic inventory management',
                'Up to 50 products',
                'Offline mode only',
                'Single user',
            ],
            self::BASIC => [
                'Advanced inventory management',
                'Up to 500 products',
                'Online or Offline mode',
                'Up to 3 users',
                'Basic reports',
                'Customer management',
            ],
            self::PREMIUM => [
                'Unlimited products',
                'Both online and offline mode',
                'Up to 10 users',
                'Advanced reports and analytics',
                'Multi-location support',
                'Priority support',
            ],
            self::ENTERPRISE => [
                'Everything in Premium',
                'Unlimited users',
                'Custom integrations',
                'Dedicated support',
                'API access',
                'Custom features',
            ],
        };
    }
}

