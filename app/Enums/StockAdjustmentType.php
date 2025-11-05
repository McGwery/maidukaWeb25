<?php

namespace App\Enums;

enum StockAdjustmentType: string
{
    case DAMAGED = 'damaged';
    case EXPIRED = 'expired';
    case LOST = 'lost';
    case THEFT = 'theft';
    case PERSONAL_USE = 'personal_use';
    case DONATION = 'donation';
    case RETURN_TO_SUPPLIER = 'return_to_supplier';
    case OTHER = 'other';
    case RESTOCK = 'restock';
    case ADJUSTMENT = 'adjustment';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::DAMAGED => 'Damaged',
            self::EXPIRED => 'Expired',
            self::LOST => 'Lost',
            self::THEFT => 'Theft',
            self::PERSONAL_USE => 'Personal Use',
            self::DONATION => 'Donation',
            self::RETURN_TO_SUPPLIER => 'Return to Supplier',
            self::OTHER => 'Other',
            self::RESTOCK => 'Restock',
            self::ADJUSTMENT => 'Manual Adjustment',
        };
    }

    public function isReduction(): bool
    {
        return in_array($this, [
            self::DAMAGED,
            self::EXPIRED,
            self::LOST,
            self::THEFT,
            self::PERSONAL_USE,
            self::DONATION,
            self::RETURN_TO_SUPPLIER,
        ]);
    }
}

