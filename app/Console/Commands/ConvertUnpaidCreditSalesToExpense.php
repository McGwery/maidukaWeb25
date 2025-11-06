<?php

namespace App\Console\Commands;

use App\Enums\ExpenseCategory;
use App\Models\Expense;
use App\Models\Sale;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertUnpaidCreditSalesToExpense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:convert-unpaid-to-expense';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert credit sales unpaid for 1 year into bad debt expenses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting conversion of unpaid credit sales to expenses...');

        $oneYearAgo = now()->subYear();

        // Find all credit sales with debt that are older than 1 year and not yet converted
        $unpaidSales = Sale::whereNull('converted_to_expense_at')
            ->where('debt_amount', '>', 0)
            ->where('sale_date', '<=', $oneYearAgo)
            ->with(['customer', 'shop'])
            ->get();

        if ($unpaidSales->isEmpty()) {
            $this->info('No unpaid credit sales found to convert.');
            return 0;
        }

        $this->info("Found {$unpaidSales->count()} unpaid credit sales to convert.");

        $convertedCount = 0;

        DB::transaction(function () use ($unpaidSales, &$convertedCount) {
            foreach ($unpaidSales as $sale) {
                try {
                    // Create expense record
                    $expense = Expense::create([
                        'shop_id' => $sale->shop_id,
                        'sale_id' => $sale->id,
                        'title' => "Bad Debt - Sale #{$sale->sale_number}",
                        'description' => "Unpaid credit sale from " .
                            ($sale->customer ? $sale->customer->name : 'Unknown Customer') .
                            " converted to bad debt expense after 1 year.",
                        'category' => ExpenseCategory::BAD_DEBT,
                        'amount' => $sale->debt_amount,
                        'expense_date' => now(),
                        'payment_method' => null,
                        'receipt_number' => null,
                        'attachment_url' => null,
                        'recorded_by' => null,
                    ]);

                    // Mark sale as converted
                    $sale->update([
                        'converted_to_expense_at' => now(),
                    ]);

                    $convertedCount++;
                    $this->line("✓ Converted Sale #{$sale->sale_number} - Amount: {$sale->debt_amount}");
                } catch (\Exception $e) {
                    $this->error("✗ Failed to convert Sale #{$sale->sale_number}: {$e->getMessage()}");
                }
            }
        });

        $this->info("Successfully converted {$convertedCount} sales to bad debt expenses.");

        return 0;
    }
}
