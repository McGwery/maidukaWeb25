<?php

namespace App\Enums;

enum SubscriptionType: string
{
    case OFFLINE = 'offline';
    case ONLINE = 'online';
    case BOTH = 'both';

    public function label(): string
    {
        return match ($this) {
            self::OFFLINE => 'Offline Only',
            self::ONLINE => 'Online Only',
            self::BOTH => 'Both Online and Offline',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::OFFLINE => 'Shop operates offline only',
            self::ONLINE => 'Shop operates online only',
            self::BOTH => 'Shop operates both online and offline',
        };
    }
}

