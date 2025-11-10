<?php

namespace App\Enums;

enum AdType: string
{
    case BANNER = 'banner';
    case CARD = 'card';
    case POPUP = 'popup';
    case NATIVE = 'native';

    public function label(): string
    {
        return match($this) {
            self::BANNER => 'Banner',
            self::CARD => 'Card',
            self::POPUP => 'Popup',
            self::NATIVE => 'Native',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::BANNER => 'Top banner advertisement',
            self::CARD => 'Card in feed (recommended)',
            self::POPUP => 'Modal popup advertisement',
            self::NATIVE => 'Native content advertisement',
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

