<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockMutation;
use Illuminate\Http\Request;

class StockMutationReportController extends Controller
{
    public function index(Request $request)
    {
        $query =
            StockMutation::with(
                'stock.medicine'
            );

        if (
            $request->filled(
                'medicine_id'
            )
        ) {

            $query->whereHas(
                'stock',
                function ($q)
                use ($request) {

                    $q->where(
                        'medicine_id',
                        $request->medicine_id
                    );
                }
            );
        }

        if (
            $request->filled(
                'start_date'
            )
        ) {

            $query->whereDate(
                'created_at',
                '>=',
                $request->start_date
            );
        }

        if (
            $request->filled(
                'end_date'
            )
        ) {

            $query->whereDate(
                'created_at',
                '<=',
                $request->end_date
            );
        }
        $allMutations =
            (clone $query)->get();

        $totalMutations =
            $allMutations->count();

        $stockIn =
            $allMutations
            ->where('quantity_change', '>', 0)
            ->sum('quantity_change');

        $stockOut =
            abs(
                $allMutations
                    ->where('quantity_change', '<', 0)
                    ->sum('quantity_change')
            );
        $data =
            $query
            ->latest()
            ->paginate(20);

        return response()->json([

            'success' => true,

            'summary' => [

                'total_mutations' =>
                $totalMutations,

                'stock_in' =>
                $stockIn,

                'stock_out' =>
                $stockOut,
            ],

            'data' =>
            $data
        ]);
    }
}
