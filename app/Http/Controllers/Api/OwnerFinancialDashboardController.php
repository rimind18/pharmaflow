<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cashflow;
use App\Models\StockCostLayer;
use App\Models\SalesBatchUsage;
use App\Models\Transaction;
use App\Models\Medicine;
use App\Models\Supplier;
use App\Models\Stock;

class OwnerFinancialDashboardController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | SALES
        |--------------------------------------------------------------------------
        */

        $totalSales =
            Transaction::sum('final_amount');

        /*
        |--------------------------------------------------------------------------
        | FIFO PROFIT
        |--------------------------------------------------------------------------
        */

        $revenue =
            Transaction::sum('final_amount');

        $cogs =
            SalesBatchUsage::sum('total_cost');

        $profit =
            $revenue - $cogs;

        /*
        |--------------------------------------------------------------------------
        | CASH
        |--------------------------------------------------------------------------
        */

        $cashBalance =
            Cashflow::income()->sum('amount')
            -
            Cashflow::expense()->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | INVENTORY
        |--------------------------------------------------------------------------
        */

        $inventoryValue =
            StockCostLayer::selectRaw(
                'SUM(quantity_remaining * unit_cost) as value'
            )->value('value') ?? 0;

        /*
        |--------------------------------------------------------------------------
        | BALANCE SHEET
        |--------------------------------------------------------------------------
        */

        $assets =
            $cashBalance +
            $inventoryValue;

        $liabilities = 0;

        $equity =
            $assets -
            $liabilities;

        /*
        |--------------------------------------------------------------------------
        | CASH FLOW
        |--------------------------------------------------------------------------
        */

        $netCashFlow =
            Cashflow::income()->sum('amount')
            -
            Cashflow::expense()->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | TOP PRODUCT
        |--------------------------------------------------------------------------
        */

        $topProduct =
            Medicine::withSum(
                'transactionItems',
                'quantity'
            )
            ->orderByDesc(
                'transaction_items_sum_quantity'
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | TOP SUPPLIER
        |--------------------------------------------------------------------------
        */

        $topSupplier =
            Supplier::withCount(
                'purchases'
            )
            ->orderByDesc(
                'purchases_count'
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | LOW STOCK
        |--------------------------------------------------------------------------
        */

        $lowStockCount =
            Stock::where(
                'quantity',
                '<=',
                10
            )->count();

        return response()->json([

            'success' => true,

            'data' => [

                'sales' =>
                    round($totalSales, 2),

                'profit' =>
                    round($profit, 2),

                'cash_balance' =>
                    round($cashBalance, 2),

                'inventory_value' =>
                    round($inventoryValue, 2),

                'assets' =>
                    round($assets, 2),

                'liabilities' =>
                    round($liabilities, 2),

                'equity' =>
                    round($equity, 2),

                'net_cash_flow' =>
                    round($netCashFlow, 2),

                'top_product' =>
                    $topProduct?->name,

                'top_supplier' =>
                    $topSupplier?->name,

                'low_stock_count' =>
                    $lowStockCount,
            ]
        ]);
    }
}