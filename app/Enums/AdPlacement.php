<?php

namespace App\Enums;

enum AdPlacement: string
{
    case HOME = 'home';
    case PRODUCTS = 'products';
    case SALES = 'sales';
    case REPORTS = 'reports';
    case ALL = 'all';

    public function label(): string
    {
        return match($this) {
            self::HOME => 'Home Screen',
            self::PRODUCTS => 'Products Screen',
            self::SALES => 'Sales/POS Screen',
            self::REPORTS => 'Reports Screen',
            self::ALL => 'All Screens',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::HOME => 'Display on home screen',
            self::PRODUCTS => 'Display on products screen',
            self::SALES => 'Display on sales/POS screen',
            self::REPORTS => 'Display on reports screen',
            self::ALL => 'Display on all screens',
        };
    }

    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'description' => $case->description(),
        ], self::cases());
    }
}

