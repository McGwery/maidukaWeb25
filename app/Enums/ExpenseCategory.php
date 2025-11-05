<?php

namespace App\Enums;

enum ExpenseCategory: string
{
    case RENT = 'rent';
    case UTILITIES = 'utilities';
    case SALARIES = 'salaries';
    case MARKETING = 'marketing';
    case TRANSPORT = 'transport';
    case MAINTENANCE = 'maintenance';
    case SUPPLIES = 'supplies';
    case INSURANCE = 'insurance';
    case TAXES = 'taxes';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::RENT => 'Rent',
            self::UTILITIES => 'Utilities',
            self::SALARIES => 'Salaries',
            self::MARKETING => 'Marketing',
            self::TRANSPORT => 'Transport',
            self::MAINTENANCE => 'Maintenance',
            self::SUPPLIES => 'Supplies',
            self::INSURANCE => 'Insurance',
            self::TAXES => 'Taxes',
            self::OTHER => 'Other',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

