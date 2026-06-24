<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Medicine;
use App\Models\Order;
use App\Models\Stock;
use App\Models\SalesBatchUsage;
use App\Exports\DailyReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->date
            ? Carbon::parse($request->date)
            : now();

        /*
    |--------------------------------------------------------------------------
    | SUMMARY KPI
    |--------------------------------------------------------------------------
    */

        $transactions = Transaction::whereDate(
            'created_at',
            $date
        );

        $transactionCount = $transactions->count();

        $totalSales = $transactions->sum('final_amount');

        $totalDiscount = $transactions->sum('discount_amount');

        $totalItemsSold =
            TransactionItem::whereDate(
                'created_at',
                $date
            )->sum('quantity');

        $averageTransaction =
            $transactionCount > 0
            ? $totalSales / $transactionCount
            : 0;

        $totalCost =
            SalesBatchUsage::whereDate(
                'created_at',
                $date
            )->sum('total_cost');

        $totalProfit =
            $totalSales - $totalCost;

        $profitMargin =
            $totalSales > 0
            ? round(
                ($totalProfit / $totalSales) * 100,
                2
            )
            : 0;

        $summary = [
            'transaction_count' => $transactionCount,
            'total_sales' => $totalSales,
            'total_discount' => $totalDiscount,
            'total_items_sold' => $totalItemsSold,
            'average_transaction' => round($averageTransaction),

            'total_cost' => $totalCost,
            'total_profit' => $totalProfit,
            'profit_margin' => $profitMargin,
        ];
        /*
    |--------------------------------------------------------------------------
    | PAYMENT METHODS
    |--------------------------------------------------------------------------
    */

        $paymentMethods =
            Transaction::select(
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(final_amount) as amount')
            )
            ->whereDate('created_at', $date)
            ->groupBy('payment_method')
            ->get();

        /*
    |--------------------------------------------------------------------------
    | CASHIER PERFORMANCE
    |--------------------------------------------------------------------------
    */

        $cashierPerformance =
            Transaction::with('kasir')
            ->select(
                'kasir_id',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(final_amount) as total_sales')
            )
            ->whereDate('created_at', $date)
            ->groupBy('kasir_id')
            ->get();

        /*
    |--------------------------------------------------------------------------
    | TOP PRODUCTS
    |--------------------------------------------------------------------------
    */

        $topProducts =
            TransactionItem::with(
                'medicine.category'
            )
            ->select(
                'medicine_id',
                DB::raw(
                    'SUM(quantity) as qty_sold'
                ),
                DB::raw(
                    'SUM(subtotal) as revenue'
                )
            )
            ->whereDate(
                'created_at',
                $date
            )
            ->groupBy('medicine_id')
            ->orderByDesc('qty_sold')
            ->take(10)
            ->get();

        /*
    |--------------------------------------------------------------------------
    | LOW STOCK
    |--------------------------------------------------------------------------
    */

        $lowStock = Medicine::with('stocks')
            ->get()
            ->map(function ($medicine) {

                $stock =
                    $medicine->stocks->sum('quantity');

                return [
                    'medicine' => $medicine,
                    'stock' => $stock
                ];
            })
            ->filter(function ($item) {

                return
                    $item['stock']
                    <=
                    $item['medicine']->stock_minimum;
            })
            ->values();

        $totalQtySold =
            TransactionItem::whereDate(
                'created_at',
                $date
            )->sum('quantity');

        $topProducts->transform(function ($item)
        use ($totalQtySold) {

            $item->percentage =
                $totalQtySold > 0
                ? round(
                    ($item->qty_sold /
                        $totalQtySold) * 100,
                    2
                )
                : 0;

            return $item;
        });

        /*
    |--------------------------------------------------------------------------
    | EXPIRING MEDICINES
    |--------------------------------------------------------------------------
    */

        $expiringMedicines =
            Stock::with('medicine')
            ->whereDate(
                'expired_date',
                '<=',
                now()->addDays(30)
            )
            ->whereDate(
                'expired_date',
                '>=',
                now()
            )
            ->orderBy('expired_date')
            ->get();

        /*
    |--------------------------------------------------------------------------
    | ONLINE ORDER SUMMARY
    |--------------------------------------------------------------------------
    */

        $onlineOrders = [

            'pending' =>
            Order::where(
                'status',
                'pending'
            )->count(),

            'diproses' =>
            Order::where(
                'status',
                'diproses'
            )->count(),

            'dikirim' =>
            Order::where(
                'status',
                'dikirim'
            )->count(),

            'selesai' =>
            Order::where(
                'status',
                'selesai'
            )->count(),

            'dibatalkan' =>
            Order::where(
                'status',
                'dibatalkan'
            )->count(),
        ];

        return response()->json([

            'success' => true,

            'data' => [

                'summary' => $summary,

                'payment_methods' =>
                $paymentMethods,

                'cashier_performance' =>
                $cashierPerformance,

                'top_products' =>
                $topProducts,

                'low_stock' =>
                $lowStock,

                'expiring_medicines' =>
                $expiringMedicines,

                'online_orders' =>
                $onlineOrders
            ]
        ]);
    }
    public function export(Request $request)
    {
        $date = $request->date
            ? Carbon::parse($request->date)
            : now();

        $data = $this->index($request)->getData(true);

        return Excel::download(
            new DailyReportExport($data['data']),
            'daily_report_' . $date->format('Y-m-d') . '.xlsx'
        );
    }
}
