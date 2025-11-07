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
                // Products & Inventory
                'manage_inventory',
                'view_inventory',
                'manage_products',
                'update_stock',
                'view_stock_adjustments',
                'view_inventory_analysis',

                // Sales & POS
                'manage_sales',
                'process_sales',
                'view_sales',
                'refund_sales',
                'view_sales_analytics',

                // Customers
                'manage_customers',
                'view_customers',

                // Purchase Orders
                'manage_purchases',
                'approve_purchases',
                'record_purchase_payments',
                'transfer_stock',
                'view_purchases',

                // Expenses
                'manage_expenses',
                'view_expenses',
                'view_expense_summary',

                // Reports
                'view_reports',
                'view_sales_report',
                'view_products_report',
                'view_financial_report',
                'view_employees_report',

                // Employees & Members
                'manage_employees',
                'view_employees',

                // Settings
                'manage_settings',
                'view_settings',

                // Savings
                'manage_savings',
                'view_savings',

                // Ads
                'manage_ads',
                'view_ads',

                // Chat
                'use_chat',
                'view_conversations',
                'send_messages',
            ],
            self::CASHIER => [
                // Sales & POS
                'process_sales',
                'view_sales',
                'view_sales_analytics',

                // Customers
                'manage_customers',
                'view_customers',

                // Inventory (view only)
                'view_inventory',
                'view_products',

                // Chat
                'use_chat',
                'view_conversations',
                'send_messages',
            ],
            self::SALES => [
                // Sales & POS
                'process_sales',
                'view_sales',
                'view_sales_analytics',

                // Customers
                'manage_customers',
                'view_customers',

                // Inventory (view only)
                'view_inventory',
                'view_products',

                // Chat
                'use_chat',
                'view_conversations',
                'send_messages',
            ],
            self::INVENTORY => [
                // Products & Inventory
                'manage_inventory',
                'view_inventory',
                'manage_products',
                'update_stock',
                'view_stock_adjustments',
                'view_inventory_analysis',

                // Purchase Orders
                'manage_purchases',
                'record_purchase_payments',
                'transfer_stock',
                'view_purchases',

                // Reports (limited)
                'view_reports',
                'view_products_report',

                // Chat
                'use_chat',
                'view_conversations',
                'send_messages',
            ],
            self::EMPLOYEE => [
                // Inventory (view only)
                'view_inventory',
                'view_products',

                // Sales (view only)
                'view_sales',

                // Chat
                'use_chat',
                'view_conversations',
                'send_messages',
            ],
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
