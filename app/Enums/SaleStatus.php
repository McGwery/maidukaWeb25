<?php
namespace App\Enums;
enum SaleStatus: string
{
    case COMPLETED = 'completed';
    case PENDING = 'pending';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
    public function label(): string
    {
        return match($this) {
            self::COMPLETED => 'Completed',
            self::PENDING => 'Pending',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
            self::PARTIALLY_REFUNDED => 'Partially Refunded',
        };
    }
    public function color(): string
    {
        return match($this) {
            self::COMPLETED => 'green',
            self::PENDING => 'yellow',
            self::CANCELLED => 'red',
            self::REFUNDED => 'orange',
            self::PARTIALLY_REFUNDED => 'orange',
        };
    }
}
