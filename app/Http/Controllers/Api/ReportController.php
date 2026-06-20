<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Medicine;
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

    $transactions = Transaction::whereDate(
        'created_at',
        $date
    );

    $transactionCount = $transactions->count();

    $totalSales = $transactions->sum('final_amount');

    $totalDiscount = $transactions->sum('discount_amount');

    $averageTransaction =
        $transactionCount > 0
            ? $totalSales / $transactionCount
            : 0;

    $paymentStatus =
    Transaction::select(
        'payment_status',
        DB::raw('COUNT(*) as count'),
        DB::raw('SUM(final_amount) as total')
    )
    ->whereDate('created_at', $date)
    ->groupBy('payment_status')
    ->get();        

    $topProducts =
        TransactionItem::with('medicine.category')
        ->select(
            'medicine_id',
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM(subtotal) as total_revenue')
        )
        ->whereDate('created_at', $date)
        ->groupBy('medicine_id')
        ->orderByDesc('total_quantity')
        ->take(10)
        ->get();

    return response()->json([
    'success' => true,
    'data' => [
        'transaction_count' => $transactionCount,
        'total_sales' => $totalSales,
        'total_discount' => $totalDiscount,
        'average_transaction' => $averageTransaction,
        'by_payment_status' => $paymentStatus,
        'top_products' => $topProducts,
    ]
]);
}
}
