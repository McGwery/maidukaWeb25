<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Models\Expense;
use App\Models\SavingsTransaction;
use App\Models\ShopSavingsSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessDailySavings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'savings:process-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process automatic daily savings from shop profits';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing daily savings for all shops...');

        $yesterday = now()->subDay()->toDateString();

        // Get all shops with savings enabled
        $enabledSettings = ShopSavingsSetting::where('is_enabled', true)->get();

        if ($enabledSettings->isEmpty()) {
            $this->info('No shops with savings enabled.');
            return 0;
        }

        $this->info("Found {$enabledSettings->count()} shops with savings enabled.");

        $processedCount = 0;
        $totalSaved = 0;

        foreach ($enabledSettings as $settings) {
            try {
                // Calculate yesterday's profit for this shop
                $dailyProfit = $this->calculateDailyProfit($settings->shop_id, $yesterday);

                if ($dailyProfit <= 0) {
                    $this->line("  Shop {$settings->shop_id}: No profit yesterday (Profit: {$dailyProfit})");
                    continue;
                }

                // Calculate savings amount
                $savingsAmount = $settings->calculateSavingsAmount($dailyProfit);

                if ($savingsAmount <= 0) {
                    $this->line("  Shop {$settings->shop_id}: Savings amount is 0");
                    continue;
                }

                // Process the savings
                DB::transaction(function () use ($settings, $savingsAmount, $dailyProfit, $yesterday) {
                    $balanceBefore = $settings->current_balance;
                    $balanceAfter = $balanceBefore + $savingsAmount;

                    // Create transaction
                    SavingsTransaction::create([
                        'shop_id' => $settings->shop_id,
                        'type' => 'deposit',
                        'amount' => $savingsAmount,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceAfter,
                        'transaction_date' => $yesterday,
                        'daily_profit' => $dailyProfit,
                        'is_automatic' => true,
                        'description' => "Automatic savings from daily profit ({$settings->savings_type}: " .
                            ($settings->savings_type === 'percentage' ? "{$settings->savings_percentage}%" : "TZS {$settings->fixed_amount}") . ")",
                    ]);

                    // Update settings
                    $settings->update([
                        'current_balance' => $balanceAfter,
                        'total_saved' => $settings->total_saved + $savingsAmount,
                        'last_savings_date' => now(),
                    ]);
                });

                $this->line("  ✓ Shop {$settings->shop_id}: Saved TZS {$savingsAmount} from profit TZS {$dailyProfit}");
                $processedCount++;
                $totalSaved += $savingsAmount;

                // Check if auto-withdrawal is due
                if ($settings->auto_withdraw && $settings->isWithdrawalDue() && $settings->canWithdraw()) {
                    $this->processAutoWithdrawal($settings);
                }

            } catch (\Exception $e) {
                $this->error("  ✗ Shop {$settings->shop_id}: Failed - {$e->getMessage()}");
            }
        }

        $this->info("Processed {$processedCount} shops, Total saved: TZS {$totalSaved}");

        return 0;
    }

    /**
     * Calculate daily profit for a shop
     */
    private function calculateDailyProfit(string $shopId, string $date): float
    {
        // Get sales profit
        $salesProfit = Sale::where('shop_id', $shopId)
            ->whereDate('sale_date', $date)
            ->sum('profit_amount');

        // Get expenses
        $expenses = Expense::where('shop_id', $shopId)
            ->whereDate('expense_date', $date)
            ->sum('amount');

        // Net profit = Gross profit - Expenses
        return max(0, $salesProfit - $expenses);
    }

    /**
     * Process automatic withdrawal
     */
    private function processAutoWithdrawal(ShopSavingsSetting $settings): void
    {
        try {
            $withdrawalAmount = $settings->current_balance;

            // Apply minimum withdrawal check
            if ($settings->minimum_withdrawal_amount && $withdrawalAmount < $settings->minimum_withdrawal_amount) {
                return;
            }

            DB::transaction(function () use ($settings, $withdrawalAmount) {
                $balanceBefore = $settings->current_balance;
                $balanceAfter = 0;

                // Create withdrawal transaction
                SavingsTransaction::create([
                    'shop_id' => $settings->shop_id,
                    'type' => 'withdrawal',
                    'amount' => $withdrawalAmount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'transaction_date' => now(),
                    'is_automatic' => true,
                    'description' => "Automatic withdrawal ({$settings->withdrawal_frequency})",
                ]);

                // Update settings
                $settings->update([
                    'current_balance' => $balanceAfter,
                    'total_withdrawn' => $settings->total_withdrawn + $withdrawalAmount,
                    'last_withdrawal_date' => now(),
                ]);
            });

            $this->line("    → Auto-withdrawal: TZS {$withdrawalAmount}");
        } catch (\Exception $e) {
            $this->error("    → Auto-withdrawal failed: {$e->getMessage()}");
        }
    }
}
