<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Cashflow;
use App\Models\SalesBatchUsage;

class ProfitLossReportController extends Controller
{
    public function index(Request $request)
    {
        try {

            $startDate = Carbon::parse(
                $request->get(
                    'start_date',
                    now()->startOfMonth()
                )
            )->startOfDay();

            $endDate = Carbon::parse(
                $request->get(
                    'end_date',
                    now()
                )
            )->endOfDay();

            /*
            |--------------------------------------------------------------------------
            | REVENUE
            |--------------------------------------------------------------------------
            */

            $revenue =
                Transaction::whereBetween(
                    'created_at',
                    [$startDate, $endDate]
                )
                ->sum('final_amount');

            /*
            |--------------------------------------------------------------------------
            | COST OF GOODS SOLD (COGS)
            |--------------------------------------------------------------------------
            */

            $cogs =
                SalesBatchUsage::join(
                    'transaction_items',
                    'sales_batch_usages.transaction_item_id',
                    '=',
                    'transaction_items.id'
                )
                ->join(
                    'transactions',
                    'transaction_items.transaction_id',
                    '=',
                    'transactions.id'
                )
                ->whereBetween(
                    'transactions.created_at',
                    [$startDate, $endDate]
                )
                ->sum('sales_batch_usages.total_cost');

            /*
            |--------------------------------------------------------------------------
            | GROSS PROFIT
            |--------------------------------------------------------------------------
            */

            $grossProfit =
                $revenue - $cogs;

            /*
            |--------------------------------------------------------------------------
            | OPERATING EXPENSES
            |--------------------------------------------------------------------------
            */

            $operatingExpenses =
                Cashflow::expense()
                ->whereNotIn(
                    'category',
                    [
                        'pembelian_obat'
                    ]
                )
                ->between(
                    $startDate,
                    $endDate
                )
                ->sum('amount');

            /*
            |--------------------------------------------------------------------------
            | NET PROFIT
            |--------------------------------------------------------------------------
            */

            $netProfit =
                $grossProfit -
                $operatingExpenses;

            /*
            |--------------------------------------------------------------------------
            | MARGIN
            |--------------------------------------------------------------------------
            */

            $grossMarginPercent =
                $revenue > 0
                ? round(
                    (
                        $grossProfit /
                        $revenue
                    ) * 100,
                    2
                )
                : 0;

            $netMarginPercent =
                $revenue > 0
                ? round(
                    (
                        $netProfit /
                        $revenue
                    ) * 100,
                    2
                )
                : 0;

            /*
            |--------------------------------------------------------------------------
            | RESPONSE
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'success' => true,

                'data' => [

                    'period' => [

                        'start_date' =>
                        $startDate->format('Y-m-d'),

                        'end_date' =>
                        $endDate->format('Y-m-d'),
                    ],

                    'revenue' =>
                    round($revenue, 2),

                    'cogs' =>
                    round($cogs, 2),

                    'gross_profit' =>
                    round($grossProfit, 2),

                    'operating_expenses' =>
                    round($operatingExpenses, 2),

                    'net_profit' =>
                    round($netProfit, 2),

                    'gross_margin_percent' =>
                    $grossMarginPercent,

                    'net_margin_percent' =>
                    $netMarginPercent,
                ]
            ]);
        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                $e->getMessage()

            ], 500);
        }
    }
}
