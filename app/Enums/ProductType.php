<?php

namespace App\Enums;

enum ProductType: string
{
    case PHYSICAL = 'physical';
    case SERVICE = 'service';
    case DIGITAL = 'digital';

    public function label(): string
    {
        return match($this) {
            self::PHYSICAL => 'Physical Product',
            self::SERVICE => 'Service',
            self::DIGITAL => 'Digital Product',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::PHYSICAL => 'Tangible products that require inventory tracking',
            self::SERVICE => 'Services that don\'t require inventory tracking',
            self::DIGITAL => 'Digital products that don\'t require physical inventory',
        };
    }

    public function requiresInventory(): bool
    {
        return match($this) {
            self::PHYSICAL => true,
            self::SERVICE => false,
            self::DIGITAL => false,
        };
    }

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}

