<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case MOBILE_MONEY = 'mobile_money';
    case BANK_TRANSFER = 'bank_transfer';
    case CREDIT = 'credit';
    case CHEQUE = 'cheque';

    public function label(): string
    {
        return match($this) {
            self::CASH => 'Cash',
            self::MOBILE_MONEY => 'Mobile Money',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::CREDIT => 'Credit',
            self::CHEQUE => 'Cheque',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}