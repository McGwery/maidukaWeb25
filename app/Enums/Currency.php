<?php

namespace App\Enums;

enum Currency: string
{
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case TZS = 'TZS';
    case KES = 'KES';
    case UGX = 'UGX';
    case RWF = 'RWF';

    public function label(): string
    {
        return match($this) {
            self::USD => 'US Dollar',
            self::EUR => 'Euro',
            self::GBP => 'British Pound',
            self::TZS => 'Tanzanian Shilling',
            self::KES => 'Kenyan Shilling',
            self::UGX => 'Ugandan Shilling',
            self::RWF => 'Rwandan Franc',
        };
    }

    public function symbol(): string
    {
        return match($this) {
            self::USD => '$',
            self::EUR => '€',
            self::GBP => '£',
            self::TZS => 'TSh',
            self::KES => 'KSh',
            self::UGX => 'USh',
            self::RWF => 'RF',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}