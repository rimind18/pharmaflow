<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\TransactionItem;
use App\Models\SalesBatchUsage;

class AdvancedOwnerAnalyticsController extends Controller
{
    public function index()
    {
        $products = Medicine::all();

        $profitData = [];

        foreach ($products as $medicine) {

            $qtySold =
                TransactionItem::where(
                    'medicine_id',
                    $medicine->id
                )->sum('quantity');

            $revenue =
                TransactionItem::where(
                    'medicine_id',
                    $medicine->id
                )
                ->sum('subtotal');

            $cost =
                SalesBatchUsage::whereHas(
                    'transactionItem',
                    function ($q) use ($medicine) {

                        $q->where(
                            'medicine_id',
                            $medicine->id
                        );
                    }
                )
                ->sum('total_cost');

            $profit =
                $revenue - $cost;

            $profitData[] = [

                'medicine_name' =>
                $medicine->name,

                'qty_sold' =>
                $qtySold,

                'revenue' =>
                round($revenue, 2),

                'cost' =>
                round($cost, 2),

                'profit' =>
                round($profit, 2)
            ];
        }
        $topProfitProducts =
            collect($profitData)
            ->filter(function ($item) {

                return $item['profit'] > 0;
            })
            ->sortByDesc('profit')
            ->take(5)
            ->values();

        $worstProfitProducts =
            collect($profitData)

            ->filter(function ($item) {

                return $item['qty_sold'] > 0;
            })

            ->sortBy('profit')

            ->take(5)

            ->values();

        $fastMovingProducts =
            collect($profitData)
            ->filter(function ($item) {

                return $item['qty_sold'] > 0;
            })
            ->sortByDesc('qty_sold')
            ->take(5)
            ->values();

        $slowMovingProducts =
            collect($profitData)
            ->filter(function ($item) {

                return $item['qty_sold'] > 0;
            })
            ->sortBy('qty_sold')
            ->take(5)
            ->values();

        $totalStock =
            \App\Models\Stock::sum(
                'quantity'
            );

        $totalSold =
            TransactionItem::sum(
                'quantity'
            );

        $inventoryEfficiency =
            [
                'total_stock' =>
                $totalStock,

                'total_sold' =>
                $totalSold,

                'efficiency_percent' =>
                $totalStock > 0
                    ? round(
                        ($totalSold / $totalStock)
                            * 100,
                        2
                    )
                    : 0
            ];

        return response()->json([

            'success' => true,

            'data' => [

                'top_profit_products' =>
                $topProfitProducts,

                'worst_profit_products' =>
                $worstProfitProducts,

                'fast_moving_products' =>
                $fastMovingProducts,

                'slow_moving_products' =>
                $slowMovingProducts,

                'inventory_efficiency' =>
                $inventoryEfficiency
            ]
        ]);
    }
}
