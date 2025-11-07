<?php

namespace App\Enums;

enum MessageType: string
{
    case TEXT = 'text';
    case IMAGE = 'image';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case DOCUMENT = 'document';
    case PRODUCT = 'product';
    case LOCATION = 'location';

    public function label(): string
    {
        return match($this) {
            self::TEXT => 'Text Message',
            self::IMAGE => 'Image',
            self::VIDEO => 'Video',
            self::AUDIO => 'Audio',
            self::DOCUMENT => 'Document',
            self::PRODUCT => 'Product Share',
            self::LOCATION => 'Location',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::TEXT => '💬',
            self::IMAGE => '🖼️',
            self::VIDEO => '🎥',
            self::AUDIO => '🎵',
            self::DOCUMENT => '📄',
            self::PRODUCT => '🏷️',
            self::LOCATION => '📍',
        };
    }

    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'icon' => $case->icon(),
        ], self::cases());
    }
}

