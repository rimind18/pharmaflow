<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\SalesBatchUsage;
use App\Models\Medicine;
use Carbon\Carbon;

class AdvancedOwnerAnalyticsV2Controller extends Controller
{
    public function index()
    {
        $revenueTrend = [];

        for ($i = 5; $i >= 0; $i--) {

            $month = now()->subMonths($i);

            $sales = Transaction::whereYear(
                'created_at',
                $month->year
            )
                ->whereMonth(
                    'created_at',
                    $month->month
                )
                ->sum('total_amount');

            $revenueTrend[] = [

                'month' =>
                $month->format('M Y'),

                'revenue' =>
                (float) $sales
            ];
        }

        $profitTrend = [];

        for ($i = 5; $i >= 0; $i--) {

            $month = now()->subMonths($i);

            $revenue =
                Transaction::whereYear(
                    'created_at',
                    $month->year
                )
                ->whereMonth(
                    'created_at',
                    $month->month
                )
                ->sum('total_amount');

            $cost =
                SalesBatchUsage::whereYear(
                    'created_at',
                    $month->year
                )
                ->whereMonth(
                    'created_at',
                    $month->month
                )
                ->sum('total_cost');

            $profitTrend[] = [

                'month' =>
                $month->format('M Y'),

                'revenue' =>
                round($revenue, 2),

                'cost' =>
                round($cost, 2),

                'profit' =>
                round(
                    $revenue - $cost,
                    2
                )
            ];
        }

        $currentMonthSales =
            Transaction::whereYear(
                'created_at',
                now()->year
            )
            ->whereMonth(
                'created_at',
                now()->month
            )
            ->sum('total_amount');

        $previousMonthSales =
            Transaction::whereYear(
                'created_at',
                now()->subMonth()->year
            )
            ->whereMonth(
                'created_at',
                now()->subMonth()->month
            )
            ->sum('total_amount');

        $growthPercent = 0;

        if ($previousMonthSales > 0) {

            $growthPercent = round(

                (
                    ($currentMonthSales -
                        $previousMonthSales)

                    /

                    $previousMonthSales
                ) * 100,

                2
            );
        }

        $supplierContribution = [];

        $totalRevenue =
            TransactionItem::sum(
                'subtotal'
            );

        $suppliers =
            Medicine::with('supplier')
            ->get()
            ->groupBy(
                fn($medicine) =>
                $medicine->supplier?->name
            );

        foreach (
            $suppliers as
            $supplierName => $medicines
        ) {

            $medicineIds =
                $medicines->pluck('id');

            $sales =
                TransactionItem::whereIn(
                    'medicine_id',
                    $medicineIds
                )
                ->sum('subtotal');

            $supplierContribution[] = [

                'supplier_name' =>
                $supplierName,

                'sales' =>
                round($sales, 2),

                'contribution_percent' =>

                $totalRevenue > 0

                    ? round(
                        ($sales / $totalRevenue)
                            * 100,
                        2
                    )

                    : 0
            ];
        }

        $growingProducts = [];

        foreach (Medicine::all() as $medicine) {

            $currentMonthQty =
                TransactionItem::where(
                    'medicine_id',
                    $medicine->id
                )
                ->whereYear(
                    'created_at',
                    now()->year
                )
                ->whereMonth(
                    'created_at',
                    now()->month
                )
                ->sum('quantity');

            $previousMonthQty =
                TransactionItem::where(
                    'medicine_id',
                    $medicine->id
                )
                ->whereYear(
                    'created_at',
                    now()->subMonth()->year
                )
                ->whereMonth(
                    'created_at',
                    now()->subMonth()->month
                )
                ->sum('quantity');

            if (
                $currentMonthQty <= 0
                &&
                $previousMonthQty <= 0
            ) {
                continue;
            }

            $growthPercent = 0;

            if ($previousMonthQty > 0) {

                $growthPercent = round(

                    (
                        ($currentMonthQty -
                            $previousMonthQty)

                        /

                        $previousMonthQty
                    ) * 100,

                    2
                );
            } elseif ($currentMonthQty > 0) {

                $growthPercent = 100;
            }

            $growingProducts[] = [

                'medicine_name' =>
                $medicine->name,

                'previous_month_qty' =>
                (int) $previousMonthQty,

                'current_month_qty' =>
                (int) $currentMonthQty,

                'growth_percent' =>
                (float) $growthPercent
            ];
        }

        return response()->json([

            'success' => true,

            'data' => [

                'revenue_trend' =>
                $revenueTrend,

                'profit_trend' =>
                $profitTrend,

                'monthly_growth' => [

                    'current_month_sales' =>
                    (float) $currentMonthSales,

                    'previous_month_sales' =>
                    (float) $previousMonthSales,

                    'growth_percent' =>
                    $growthPercent
                ],

                'supplier_contribution' =>

                collect($supplierContribution)
                    ->sortByDesc('sales')
                    ->values(),

                'fastest_growing_products' =>

                collect($growingProducts)
                    ->sortByDesc('growth_percent')
                    ->take(5)
                    ->values()
            ]
        ]);
    }
}
