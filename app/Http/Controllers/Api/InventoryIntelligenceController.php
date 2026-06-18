<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\TransactionItem;
use App\Models\Stock;

class InventoryIntelligenceController extends Controller
{
    public function index()
    {
        $medicines = Medicine::all();

        $result = [];

        foreach ($medicines as $medicine) {

            $qtySold =
                TransactionItem::where(
                    'medicine_id',
                    $medicine->id
                )
                ->where(
                    'created_at',
                    '>=',
                    now()->subDays(30)
                )
                ->sum('quantity');
            $currentStock =
                Stock::where(
                    'medicine_id',
                    $medicine->id
                )->sum('quantity');

            $avgDailySales =
                $qtySold > 0
                ? $qtySold / 30
                : 0;

            $daysRemaining =
                $avgDailySales > 0
                ? $currentStock / $avgDailySales
                : null;
            $recommendedOrderQty = 0;
            if (
                $currentStock <= 0
                &&
                $qtySold == 0
            ) {

                $recommendedOrderQty = 20;
            } elseif ($avgDailySales > 0) {

                $recommendedOrderQty = max(
                    0,
                    ceil(
                        ($avgDailySales * 60)
                            -
                            $currentStock
                    )
                );
            }
            $stockoutDate = null;

            if (
                $daysRemaining !== null
            ) {

                $stockoutDate = now()
                    ->addDays(
                        ceil($daysRemaining)
                    )
                    ->toDateString();
            }
            $overstock =
                $daysRemaining !== null
                &&
                $daysRemaining > 90;
            $deadStock =
                $qtySold == 0
                &&
                $currentStock > 0;
            $riskLevel = 'LOW';

            if ($currentStock <= 0) {

                $riskLevel = 'HIGH';
            } elseif (
                $daysRemaining !== null
                &&
                $daysRemaining <= 7
            ) {

                $riskLevel = 'HIGH';
            } elseif (
                $daysRemaining !== null
                &&
                $daysRemaining <= 14
            ) {

                $riskLevel = 'MEDIUM';
            }

            if ($deadStock) {

                $movementCategory = 'DEAD_STOCK';
            } elseif ($qtySold >= 50) {

                $movementCategory = 'FAST_MOVING';
            } elseif ($qtySold >= 10) {

                $movementCategory = 'NORMAL';
            } elseif ($qtySold > 0) {

                $movementCategory = 'SLOW_MOVING';
            } else {

                $movementCategory = 'NO_SALES';
            }

            $inventoryTurnover =
                $currentStock > 0
                ? round(
                    $qtySold /
                        max($currentStock, 1),
                    2
                )
                : 0;

            $healthScore = 100;

            if ($riskLevel == 'HIGH') {

                $healthScore -= 50;
            }

            if ($deadStock) {

                $healthScore -= 25;
            }

            if ($overstock) {

                $healthScore -= 25;
            }

            $priorityScore = 0;

            /*
|--------------------------------------------------------------------------
| Risk
|--------------------------------------------------------------------------
*/

            if ($riskLevel == 'HIGH') {

                $priorityScore += 40;
            } elseif ($riskLevel == 'MEDIUM') {

                $priorityScore += 20;
            }

            /*
|--------------------------------------------------------------------------
| Stock Empty
|--------------------------------------------------------------------------
*/

            if ($currentStock <= 0) {

                $priorityScore += 30;
            }

            /*
|--------------------------------------------------------------------------
| Demand
|--------------------------------------------------------------------------
*/

            $priorityScore += min(
                20,
                $qtySold
            );

            /*
|--------------------------------------------------------------------------
| Reorder Qty
|--------------------------------------------------------------------------
*/

            $priorityScore += min(
                10,
                floor($recommendedOrderQty / 2)
            );

            $priorityLevel = 'LOW';

            if ($priorityScore >= 80) {

                $priorityLevel = 'URGENT';
            } elseif ($priorityScore >= 60) {

                $priorityLevel = 'HIGH';
            } elseif ($priorityScore >= 40) {

                $priorityLevel = 'MEDIUM';
            }

            $supplierName =
                $medicine->supplier?->name;

            $priorityReason = [];

            if ($currentStock <= 0) {
                $priorityReason[] = 'Out of stock';
            }

            if ($riskLevel == 'HIGH') {
                $priorityReason[] = 'High risk';
            }

            if ($qtySold > 0) {
                $priorityReason[] =
                    'Demand ' . $qtySold . ' units';
            }

            $result[] = [
                'medicine_name' => $medicine->name,

                'current_stock' => $currentStock,

                'qty_sold_30_days' => $qtySold,

                'avg_daily_sales' => $avgDailySales,

                'days_inventory_remaining' => $daysRemaining,

                'estimated_stockout_date' =>
                $stockoutDate,

                'recommended_order_qty' =>
                $recommendedOrderQty,

                'supplier_name' =>
                $supplierName,

                'risk_level' =>
                $riskLevel,

                'movement_category' =>
                $movementCategory,

                'dead_stock' =>
                $deadStock,

                'overstock' =>
                $overstock,

                'inventory_turnover' =>
                $inventoryTurnover,

                'inventory_health_score' =>
                $healthScore,

                'priority_score' =>
                $priorityScore,

                'priority_level' =>
                $priorityLevel,

                'priority_score' =>
                $priorityScore,

                'priority_level' =>
                $priorityLevel,

                'priority_reason' =>
                implode(
                    ', ',
                    $priorityReason
                ),
            ];
        }

        $sorted = collect($result)
            ->sortByDesc(
                'priority_score'
            )
            ->values();

        $rank = 1;

        $sorted = $sorted->map(
            function ($item) use (&$rank) {

                $item['rank'] = $rank++;

                return $item;
            }
        );

        return response()->json([

            'success' => true,

            'data' => $sorted

        ]);
    }
}
