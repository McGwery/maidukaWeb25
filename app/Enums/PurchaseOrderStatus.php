<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::CANCELLED => 'Cancelled',
            self::COMPLETED => 'Completed',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::APPROVED => 'info',
            self::REJECTED => 'destructive',
            self::CANCELLED => 'secondary',
            self::COMPLETED => 'success',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function canTransitionTo(self $status): bool
    {
        return match($this) {
            self::PENDING => in_array($status, [self::APPROVED, self::REJECTED, self::CANCELLED]),
            self::APPROVED => in_array($status, [self::COMPLETED, self::CANCELLED]),
            self::REJECTED => false,
            self::CANCELLED => false,
            self::COMPLETED => false,
        };
    }
}