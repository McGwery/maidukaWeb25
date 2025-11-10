<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\Shop;
use App\Models\User;
use App\Traits\HasShopPolicy;

class ExpensePolicy
{
    use HasShopPolicy;

    /**
     * Determine if the user can view any expenses.
     */
    public function viewAny(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'view_expenses');
    }

    /**
     * Determine if the user can view the expense.
     */
    public function view(User $user, Expense $expense): bool
    {
        return $this->hasPermission($user, $expense->shop, 'view_expenses');
    }

    /**
     * Determine if the user can create expenses.
     */
    public function create(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'manage_expenses');
    }

    /**
     * Determine if the user can update the expense.
     */
    public function update(User $user, Expense $expense): bool
    {
        return $this->hasPermission($user, $expense->shop, 'manage_expenses');
    }

    /**
     * Determine if the user can delete the expense.
     */
    public function delete(User $user, Expense $expense): bool
    {
        return $this->hasPermission($user, $expense->shop, 'manage_expenses');
    }

    /**
     * Determine if the user can view expense summary.
     */
    public function viewSummary(User $user, Shop $shop): bool
    {
        return $this->hasPermission($user, $shop, 'view_expense_summary') ||
               $this->hasPermission($user, $shop, 'view_expenses');
    }
}

