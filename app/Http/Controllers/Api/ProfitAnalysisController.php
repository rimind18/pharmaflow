<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ProfitReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ProfitAnalysisController extends Controller
{
    public function index(Request $request)
    {
        try {

            $query = TransactionItem::query()

                ->join(
                    'medicines',
                    'transaction_items.medicine_id',
                    '=',
                    'medicines.id'
                )

                ->join(
                    'transactions',
                    'transaction_items.transaction_id',
                    '=',
                    'transactions.id'
                )

                ->leftJoin(
                    'sales_batch_usages',
                    'transaction_items.id',
                    '=',
                    'sales_batch_usages.transaction_item_id'
                );

            if (
                $request->filled('start_date') &&
                $request->filled('end_date')
            ) {

                $query->whereBetween(
                    'transactions.created_at',
                    [
                        $request->start_date,
                        $request->end_date
                    ]
                );
            }

            $profits = $query
                ->selectRaw('
                    medicines.id as medicine_id,
                    medicines.name as medicine_name,

                    SUM(transaction_items.quantity)
                        as qty_sold,

                    SUM(transaction_items.subtotal)
                        as revenue,

                    COALESCE(
                        SUM(sales_batch_usages.total_cost),
                        0
                    ) as cost
                ')
                     ->groupBy(
                    'medicines.id',
                    'medicines.name'
                )
                ->get()
                ->map(function ($item) {

                    $profit =
                        $item->revenue -
                        $item->cost;

                    $margin =
                        $item->revenue > 0
                        ? round(
                            ($profit / $item->revenue) * 100,
                            2
                        )
                        : 0;

                    return [

                        'medicine_id' =>
                        $item->medicine_id,

                        'medicine_name' =>
                        $item->medicine_name,

                        'qty_sold' =>
                        (int) $item->qty_sold,

                        'revenue' =>
                        (float) $item->revenue,

                        'cost' =>
                        (float) $item->cost,

                        'profit' =>
                        (float) $profit,

                        'margin_percent' =>
                        $margin,
                    ];
                })
                ->sortByDesc('profit')
                ->values();

            return response()->json([

                'success' => true,

                'summary' => [

                    'total_revenue' =>
                    $profits->sum('revenue'),

                    'total_cost' =>
                    $profits->sum('cost'),

                    'total_profit' =>
                    $profits->sum('profit'),
                ],

                'data' => $profits
            ]);
        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' => $e->getMessage()

            ], 500);
        }
    }
    public function export()
    {
        return Excel::download(
            new ProfitReportExport(),
            'profit_report.xlsx'
        );
    }
}
