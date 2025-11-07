<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;
use App\Traits\HasShopPolicy;

class ReportPolicy
{
    use HasShopPolicy;

    /**
     * Determine if the user can view reports.
     */
    public function viewAny(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'view_reports');
    }

    /**
     * Determine if the user can view sales report.
     */
    public function viewSalesReport(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'view_sales_report') ||
               $this->hasPermission($user, $shop, 'view_reports');
    }

    /**
     * Determine if the user can view products report.
     */
    public function viewProductsReport(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'view_products_report') ||
               $this->hasPermission($user, $shop, 'view_reports');
    }

    /**
     * Determine if the user can view financial report.
     */
    public function viewFinancialReport(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'view_financial_report') ||
               $this->hasPermission($user, $shop, 'view_reports');
    }

    /**
     * Determine if the user can view employees report.
     */
    public function viewEmployeesReport(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'view_employees_report') ||
               $this->hasPermission($user, $shop, 'view_reports');
    }

    /**
     * Determine if the user can view overview report.
     */
    public function viewOverviewReport(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'view_reports');
    }
}

