<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\TransactionItem;
use App\Models\SalesBatchUsage;
use App\Models\Stock;

class OwnerAnalyticsController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | FAST MOVING
        |--------------------------------------------------------------------------
        */

        $fastMoving = TransactionItem::selectRaw(
            'medicine_id, SUM(quantity) as total_sold'
        )
        ->groupBy('medicine_id')
        ->orderByDesc('total_sold')
        ->with('medicine')
        ->take(5)
        ->get();

        /*
        |--------------------------------------------------------------------------
        | SLOW MOVING
        |--------------------------------------------------------------------------
        */

        $slowMoving = TransactionItem::selectRaw(
            'medicine_id, SUM(quantity) as total_sold'
        )
        ->groupBy('medicine_id')
        ->orderBy('total_sold')
        ->with('medicine')
        ->take(5)
        ->get();

        /*
        |--------------------------------------------------------------------------
        | PROFIT MARGIN
        |--------------------------------------------------------------------------
        */

        $revenue =
            TransactionItem::sum('subtotal');

        $cogs =
            SalesBatchUsage::sum('total_cost');

        $profit =
            $revenue - $cogs;

        $profitMargin =
            $revenue > 0
            ? ($profit / $revenue) * 100
            : 0;

        return response()->json([

            'success' => true,

            'data' => [

                'profit_margin_percent' =>
                    round(
                        $profitMargin,
                        2
                    ),

                'fast_moving_products' =>
                    $fastMoving->map(function ($item) {

                        return [

                            'medicine' =>
                                $item->medicine?->name,

                            'qty_sold' =>
                                $item->total_sold
                        ];
                    }),

                'slow_moving_products' =>
                    $slowMoving->map(function ($item) {

                        return [

                            'medicine' =>
                                $item->medicine?->name,

                            'qty_sold' =>
                                $item->total_sold
                        ];
                    })
            ]
        ]);
    }
}