<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PurchaseReportExport;

class PurchaseReportController extends Controller
{
    public function index(Request $request)
    {
        try {

            $query = Purchase::with([
                'supplier',
                'items.medicine'
            ]);

            if (
                $request->filled('start_date') &&
                $request->filled('end_date')
            ) {

                $query->whereBetween(
                    'purchase_date',
                    [
                        $request->start_date,
                        $request->end_date
                    ]
                );
            }

            $purchases =
                $query
                ->orderBy(
                    'purchase_date',
                    'desc'
                )
                ->get();

            /*
            |--------------------------------------------------------------------------
            | SUMMARY
            |--------------------------------------------------------------------------
            */

            $totalPurchaseOrders =
                $purchases->count();

            $totalPurchaseAmount =
                $purchases->sum(
                    'total_amount'
                );

            $totalItemsPurchased =
                PurchaseItem::whereHas(
                    'purchase',
                    function ($q) use ($request) {

                        if (
                            $request->filled('start_date') &&
                            $request->filled('end_date')
                        ) {

                            $q->whereBetween(
                                'purchase_date',
                                [
                                    $request->start_date,
                                    $request->end_date
                                ]
                            );
                        }
                    }
                )

                ->sum('quantity');

            $averagePurchaseValue =
                $totalPurchaseOrders > 0
                ? round(
                    $totalPurchaseAmount /
                        $totalPurchaseOrders,
                    2
                )
                : 0;

            /*
            |--------------------------------------------------------------------------
            | TOP SUPPLIERS
            |--------------------------------------------------------------------------
            */

            $topSuppliers =
                Purchase::selectRaw(
                    '
                    supplier_id,
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_amount
                    '
                )
                ->with('supplier')
                ->groupBy('supplier_id')
                ->orderByDesc(
                    'total_amount'
                )
                ->take(5)
                ->get();

            /*
            |--------------------------------------------------------------------------
            | TOP MEDICINES
            |--------------------------------------------------------------------------
            */

            $topMedicines =
                PurchaseItem::selectRaw(
                    '
                    medicine_id,
                    SUM(quantity) as total_quantity
                    '
                )
                ->with('medicine')
                ->groupBy(
                    'medicine_id'
                )
                ->orderByDesc(
                    'total_quantity'
                )
                ->take(10)
                ->get();

            /*
            |--------------------------------------------------------------------------
            | MONTHLY TREND
            |--------------------------------------------------------------------------
            */

            $monthlyTrend =
                Purchase::selectRaw(
                    '
                    DATE_FORMAT(
                        purchase_date,
                        "%Y-%m"
                    ) as month,

                    COUNT(*) as total_orders,

                    SUM(total_amount)
                    as total_amount
                    '
                )
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            /*
|--------------------------------------------------------------------------
| STATUS BREAKDOWN
|--------------------------------------------------------------------------
*/

            $statusBreakdown =
                Purchase::selectRaw(
                    '
        status,
        COUNT(*) as total
        '
                )
                ->groupBy('status')
                ->get();

            /*
|--------------------------------------------------------------------------
| SUPPLIER PERFORMANCE
|--------------------------------------------------------------------------
*/

            $supplierPerformance =
                Supplier::with('purchases')
                ->get()
                ->map(function ($supplier) {

                    $totalOrders =
                        $supplier->purchases->count();

                    $receivedOrders =
                        $supplier->purchases
                        ->where('status', 'received')
                        ->count();

                    $totalAmount =
                        $supplier->purchases
                        ->sum('total_amount');

                    $completionRate =
                        $totalOrders > 0
                        ? round(
                            ($receivedOrders / $totalOrders) * 100,
                            2
                        )
                        : 0;

                    $deliveryDays = [];

                    foreach (
                        $supplier->purchases
                        as $purchase
                    ) {

                        if (
                            $purchase->received_at &&
                            $purchase->purchase_date
                        ) {

                            $deliveryDays[] =
                                $purchase->purchase_date
                                ->diffInDays(
                                    $purchase->received_at
                                );
                        }
                    }

                    $avgDeliveryDays =
                        count($deliveryDays) > 0
                        ? round(
                            collect($deliveryDays)->avg(),
                            2
                        )
                        : null;

                    return [

                        'supplier_id' =>
                        $supplier->id,

                        'supplier_name' =>
                        $supplier->name,

                        'total_orders' =>
                        $totalOrders,

                        'received_orders' =>
                        $receivedOrders,

                        'completion_rate' =>
                        $completionRate,

                        'total_purchase_amount' =>
                        $totalAmount,

                        'avg_delivery_days' =>
                        $avgDeliveryDays,
                    ];
                })
                ->sortByDesc(
                    'total_purchase_amount'
                )
                ->values();

            return response()->json([

                'success' => true,

                'data' => [

                    'summary' => [

                        'total_purchase_orders' =>
                        $totalPurchaseOrders,

                        'total_purchase_amount' =>
                        $totalPurchaseAmount,

                        'total_items_purchased' =>
                        $totalItemsPurchased,

                        'average_purchase_value' =>
                        $averagePurchaseValue,
                    ],

                    'top_suppliers' =>
                    $topSuppliers,

                    'top_medicines' =>
                    $topMedicines,

                    'monthly_trend' =>
                    $monthlyTrend,

                    'status_breakdown' =>
                    $statusBreakdown,

                    'supplier_performance' =>
                    $supplierPerformance,

                    'purchases' =>
                    $purchases
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
    public function export()
    {
        return Excel::download(
            new PurchaseReportExport,
            'purchase_report.xlsx'
        );
    }
}
