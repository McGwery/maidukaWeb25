<?php

namespace App\Enums;

enum ShopMemberRole: string
{
    case OWNER = 'owner';
    case MANAGER = 'manager';
    case CASHIER = 'cashier';
    case SALES = 'sales';
    case INVENTORY = 'inventory';
    case EMPLOYEE = 'employee';

    public function label(): string
    {
        return match($this) {
            self::OWNER => 'Shop Owner',
            self::MANAGER => 'Manager',
            self::CASHIER => 'Cashier',
            self::SALES => 'Sales Representative',
            self::INVENTORY => 'Inventory Manager',
            self::EMPLOYEE => 'Employee',
        };
    }

    public function permissions(): array
    {
        return match($this) {
            self::OWNER => ['*'],
            self::MANAGER => [
                'manage_inventory',
                'manage_sales',
                'manage_employees',
                'view_reports',
                'process_sales',
                'manage_products',
                'manage_purchases',
                'approve_purchases',
                'record_purchase_payments',
                'transfer_stock',
            ],
            self::CASHIER => [
                'process_sales',
                'view_inventory',
            ],
            self::SALES => [
                'process_sales',
                'view_inventory',
                'view_products',
            ],
            self::INVENTORY => [
                'manage_inventory',
                'view_inventory',
                'manage_products',
                'view_reports',
                'manage_purchases',
                'record_purchase_payments',
                'transfer_stock',
            ],
            self::EMPLOYEE => [
                'view_inventory',
                'view_products',
            ],
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
