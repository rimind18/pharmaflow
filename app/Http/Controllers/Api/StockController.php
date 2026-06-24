<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockMutation;
use App\Models\Stock;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    protected AuditLogService $auditLogService;
    public function __construct(
        AuditLogService $auditLogService
    ) {
        $this->auditLogService =
            $auditLogService;
    }
    /**
     * Stock Adjustment (Opname/Rusak/Hilang)
     */
    public function adjustment(Request $request)
    {
        try {

            $validated = $request->validate([
                'stock_id' => 'required|exists:stocks,id',
                'quantity_after' => 'required|integer|min:0',
                'type' => 'required|in:penambahan,pengurangan,koreksi',
                'reason' => 'required|string',
                'notes' => 'nullable|string',
            ]);

            $stock = Stock::findOrFail(
                $validated['stock_id']
            );

            $quantity_before =
                $stock->quantity;

            $adjustment_qty =
                $validated['quantity_after']
                - $quantity_before;

            // Create adjustment record
            $adjustment =
                StockAdjustment::create([

                    'stock_id' =>
                    $stock->id,

                    'quantity_before' =>
                    $quantity_before,

                    'quantity_after' =>
                    $validated['quantity_after'],

                    'adjustment_qty' =>
                    $adjustment_qty,

                    'type' =>
                    $validated['type'],

                    'reason' =>
                    $validated['reason'],

                    'notes' =>
                    $validated['notes']
                        ?? null,

                    'adjusted_by' =>
                    Auth::id(),
                ]);

            // Update stock
            $stock->update([
                'quantity' =>
                $validated['quantity_after']
            ]);

            \App\Models\StockMutation::create([

                'stock_id' => $stock->id,

                'type' => 'adjustment',

                'quantity_before' => $quantity_before,

                'quantity_change' => $adjustment_qty,

                'quantity_after' => $validated['quantity_after'],

                'reference_type' => \App\Models\StockAdjustment::class,

                'reference_id' => $adjustment->id,

                'notes' => 'Stock Adjustment - ' . $validated['reason']

            ]);

            $this->auditLogService
                ->record(

                    'stock_adjustment',

                    Stock::class,

                    $stock->id,

                    [
                        'quantity' =>
                        $quantity_before
                    ],

                    [
                        'quantity' =>
                        $validated['quantity_after']
                    ]

                );

            return response()->json([
                'message' =>
                'Stock adjustment recorded successfully',

                'data' =>
                $adjustment->load(
                    'stock.medicine',
                    'adjustedBy'
                )
            ], 201);
        } catch (\Exception $e) {

            return response()->json([
                'message' =>
                $e->getMessage()
            ], 400);
        }
    }

    /**
     * Stock Opname (Fisik Inventory)
     */
    public function opname(Request $request)
    {
        try {

            $validated = $request->validate([
                'warehouse_id' => 'required|exists:warehouses,id',
                'adjustments' => 'required|array|min:1',
                'adjustments.*.stock_id' => 'required|exists:stocks,id',
                'adjustments.*.quantity_after' => 'required|integer|min:0',
            ]);

            $adjustments = [];
            $stockOpnames = [];

            foreach ($validated['adjustments'] as $adj) {

                $stock = Stock::findOrFail(
                    $adj['stock_id']
                );

                $quantity_before =
                    $stock->quantity;

                $quantity_after =
                    $adj['quantity_after'];

                $difference =
                    $quantity_after -
                    $quantity_before;

                // VALIDASI:
                // kalau tidak ada perubahan skip
                if ($difference == 0) {
                    continue;
                }

                // simpan ke stock_opname
                $opname = \App\Models\StockOpname::create([
                    'reference_number' =>
                    'OPN-' . now()->format('YmdHis'),

                    'warehouse_id' =>
                    $validated['warehouse_id'],

                    'medicine_id' =>
                    $stock->medicine_id,

                    'system_quantity' =>
                    $quantity_before,

                    'physical_quantity' =>
                    $quantity_after,

                    'difference' =>
                    $difference,

                    'status' =>
                    'approved',

                    'created_by' =>
                    Auth::id(),

                    'approved_by' =>
                    Auth::id(),

                    'approved_at' =>
                    now(),

                    'notes' =>
                    'Stock opname system'
                ]);

                $stockOpnames[] = $opname;

                // simpan adjustment
                $adjustment =
                    StockAdjustment::create([

                        'stock_id' =>
                        $stock->id,

                        'quantity_before' =>
                        $quantity_before,

                        'quantity_after' =>
                        $quantity_after,

                        'adjustment_qty' =>
                        $difference,

                        'type' =>
                        'koreksi',

                        'reason' =>
                        'Stock opname',

                        'adjusted_by' =>
                        Auth::id(),
                    ]);

                // update stock
                $stock->update([
                    'quantity' =>
                    $quantity_after
                ]);

                \App\Models\StockMutation::create([

                    'stock_id' => $stock->id,

                    'type' => 'opname',

                    'quantity_before' => $quantity_before,

                    'quantity_change' => $difference,

                    'quantity_after' => $quantity_after,

                    'reference_type' => \App\Models\StockOpname::class,

                    'reference_id' => $opname->id,

                    'notes' => 'Stock Opname'

                ]);

                $this->auditLogService
                    ->record(

                        'stock_opname',

                        Stock::class,

                        $stock->id,

                        [
                            'quantity' =>
                            $quantity_before
                        ],

                        [
                            'quantity' =>
                            $quantity_after
                        ]

                    );

                $adjustments[] =
                    $adjustment;
            }

            // kalau tidak ada perubahan
            if (count($adjustments) == 0) {

                return response()->json([
                    'message' =>
                    'Tidak ada perubahan stok'
                ], 422);
            }

            return response()->json([
                'message' =>
                'Stock opname berhasil',

                'adjustments' =>
                $adjustments,

                'stock_opname' =>
                $stockOpnames

            ], 201);
        } catch (\Exception $e) {

            return response()->json([
                'message' =>
                $e->getMessage()
            ], 500);
        }
    }
    /**
     * Get Low Stock Items
     */
    public function lowStock(Request $request)
    {
        try {

            $stocks = Stock::lowStock()
                ->with([
                    'medicine',
                    'warehouse'
                ])
                ->paginate(20);

            return response()->json([
                'message' =>
                'Low stock items retrieved',

                'data' =>
                $stocks
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'message' =>
                $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Expired Items
     */
    public function expired(Request $request)
    {
        try {

            $stocks = Stock::expired()
                ->with([
                    'medicine',
                    'warehouse'
                ])
                ->paginate(20);

            return response()->json([
                'message' =>
                'Expired items retrieved',

                'data' =>
                $stocks
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'message' =>
                $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Expiring Soon Items (30 days)
     */
    public function expiringSoon(Request $request)
    {
        try {

            $stocks =
                Stock::expiringSoon()
                ->with([
                    'medicine',
                    'warehouse'
                ])
                ->orderBy(
                    'expired_date',
                    'asc'
                )
                ->paginate(20);

            return response()->json([
                'message' =>
                'Items expiring soon retrieved',

                'data' =>
                $stocks
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'message' =>
                $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all stocks
     */
    public function index(Request $request)
    {
        try {

            $query = Stock::with([
                'medicine',
                'warehouse',
                'shelf'
            ]);

            // filter warehouse
            if ($request->warehouse_id) {
                $query->where(
                    'warehouse_id',
                    $request->warehouse_id
                );
            }

            // search medicine
            if ($request->search) {
                $query->whereHas(
                    'medicine',
                    function ($q) use ($request) {
                        $q->where(
                            'name',
                            'like',
                            '%' . $request->search . '%'
                        )
                            ->orWhere(
                                'code',
                                'like',
                                '%' . $request->search . '%'
                            );
                    }
                );
            }

            $stocks = $query
                ->latest()
                ->paginate(
                    $request->per_page ?? 20
                );

            return response()->json([
                'message' => 'Stocks retrieved successfully',
                'data' => $stocks
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock detail
     */
 public function show($id)
{
    try {

        $stock = Stock::with([
            'medicine',
            'warehouse',
            'shelf',
            'mutations',
            'adjustments'
        ])->findOrFail($id);

        return response()->json([
            'message' => 'Stock detail retrieved',
            'data' => $stock
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'message' => $e->getMessage()
        ], 404);
    }
}
}
