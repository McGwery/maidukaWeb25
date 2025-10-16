<?php

namespace App\Enums;

enum ShopType: string
{
    case RETAIL = 'retail';
    case CAFE = 'cafe';
    case RESTAURANT = 'restaurant';
    case WHOLESALE = 'wholesale';
    case PHARMACY = 'pharmacy';
    case GROCERY = 'grocery';
    case ELECTRONICS = 'electronics';
    case CLOTHING = 'clothing';
    case SERVICE = 'service';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::RETAIL => 'Retail Store',
            self::CAFE => 'Cafe',
            self::RESTAURANT => 'Restaurant',
            self::WHOLESALE => 'Wholesale Store',
            self::PHARMACY => 'Pharmacy',
            self::GROCERY => 'Grocery Store',
            self::ELECTRONICS => 'Electronics Store',
            self::CLOTHING => 'Clothing Store',
            self::SERVICE => 'Service Business',
            self::OTHER => 'Other',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}