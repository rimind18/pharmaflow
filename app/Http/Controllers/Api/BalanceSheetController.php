<?php

namespace App\Http\Controllers\Api;

use App\Models\Cashflow;
use App\Models\StockCostLayer;
use App\Models\SalesBatchUsage;
use App\Models\Transaction;
use App\Http\Controllers\Controller;

class BalanceSheetController extends Controller
{
    public function index()
    {
        $cashBalance =
            Cashflow::income()->sum('amount')
            -
            Cashflow::expense()
            ->whereNotIn(
                'category',
                ['pembelian_obat']
            )
            ->sum('amount');

        $inventoryValue =
            StockCostLayer::selectRaw(
                'SUM(quantity_remaining * unit_cost) as value'
            )->value('value') ?? 0;

        $totalAssets =
            $cashBalance +
            $inventoryValue;

        $totalLiabilities = 0;

        $revenue =
            Transaction::sum('final_amount');

        $cogs =
            SalesBatchUsage::sum('total_cost');

        $expenses =
            Cashflow::expense()
            ->whereNotIn(
                'category',
                ['pembelian_obat']
            )
            ->sum('amount');

        $netProfit =
            ($revenue - $cogs)
            - $expenses;

        $totalEquity =
            $totalAssets -
            $totalLiabilities;

        $ownerCapital =
            $totalAssets
            -
            $totalLiabilities
            -
            $netProfit;

        return response()->json([
            'success' => true,
            'data' => [

                'assets' => [
                    'cash' => round($cashBalance, 2),
                    'inventory' => round($inventoryValue, 2),
                    'total_assets' => round($totalAssets, 2),
                ],

                'liabilities' => [
                    'accounts_payable' => 0,
                    'total_liabilities' => 0,
                ],

                'equity' => [
                    'owner_capital' =>
                        round($ownerCapital, 2),

                    'retained_earnings' =>
                        round($netProfit, 2),

                    'total_equity' =>
                        round($totalEquity, 2),
                ]
            ]
        ]);
    }
}