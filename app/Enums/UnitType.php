<?php

namespace App\Enums;

enum UnitType: string
{
    case BOX = 'box';
    case CARTON = 'carton';
    case PIECE = 'piece';
    case PACK = 'pack';
    case BOTTLE = 'bottle';
    case BAG = 'bag';
    case SACHET = 'sachet';
    case KG = 'kg';
    case GRAM = 'gram';
    case LITER = 'liter';
    case MILLILITER = 'ml';

    public function label(): string
    {
        return match($this) {
            self::BOX => 'Box',
            self::CARTON => 'Carton',
            self::PIECE => 'Piece',
            self::PACK => 'Pack',
            self::BOTTLE => 'Bottle',
            self::BAG => 'Bag',
            self::SACHET => 'Sachet',
            self::KG => 'Kilogram',
            self::GRAM => 'Gram',
            self::LITER => 'Liter',
            self::MILLILITER => 'Milliliter',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}