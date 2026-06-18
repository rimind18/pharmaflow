<?php

namespace App\Http\Controllers\Api;

use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Medicine;
use App\Models\Stock;
use App\Models\StockMutation;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Http\Controllers\Controller;
use App\Services\StockMutationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockOpnameController extends Controller
{
    public function index()
    {
        return response()->json(
            StockOpname::with([
                'items.medicine',
                'warehouse'
            ])
                ->latest()
                ->paginate(20)
        );
    }

    public function show($id)
    {
        return response()->json(

            StockOpname::with([
                'items.medicine',
                'warehouse'
            ])
                ->findOrFail($id)

        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([

            'warehouse_id' =>
            'required|exists:warehouses,id',

            'notes' =>
            'nullable|string',

            'items' =>
            'required|array|min:1',

            'items.*.medicine_id' =>
            'required|exists:medicines,id',

            'items.*.physical_quantity' =>
            'required|integer|min:0',
        ]);
        DB::beginTransaction();

        try {

            $stockOpname = StockOpname::create([

                'reference_number' =>
                'SO-' . now()->format('YmdHis'),

                'warehouse_id' =>
                $validated['warehouse_id'],

                'status' =>
                'pending',

                'created_by' =>
                Auth::id(),

                'notes' =>
                $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {

                $systemQty =
                    Stock::where(
                        'medicine_id',
                        $item['medicine_id']
                    )
                    ->where(
                        'warehouse_id',
                        $validated['warehouse_id']
                    )
                    ->sum('quantity');

                StockOpnameItem::create([

                    'stock_opname_id' =>
                    $stockOpname->id,

                    'medicine_id' =>
                    $item['medicine_id'],

                    'system_quantity' =>
                    $systemQty,

                    'physical_quantity' =>
                    $item['physical_quantity'],

                    'difference' =>
                    $item['physical_quantity']
                        - $systemQty,

                    'notes' =>
                    $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([

                'success' => true,

                'message' =>
                'Stock opname created',

                'data' =>
                $stockOpname->load(
                    'items.medicine'
                )

            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'success' => false,

                'message' => $e->getMessage()

            ], 500);
        }
    }
    public function approve($id)
    {
        DB::beginTransaction();

        try {

            $stockOpname =
                StockOpname::with('items')
                ->findOrFail($id);

            if (
                $stockOpname->status !== 'pending'
            ) {

                throw new \Exception(
                    'Stock opname already processed'
                );
            }

            foreach (
                $stockOpname->items
                as $item
            ) {

                $stock =
                    Stock::where(
                        'medicine_id',
                        $item->medicine_id
                    )
                    ->where(
                        'warehouse_id',
                        $stockOpname->warehouse_id
                    )
                    ->first();

                if (!$stock) {
                    continue;
                }

                $beforeQty =
                    $stock->quantity;

                $afterQty =
                    $item->physical_quantity;

                $stock->update([
                    'quantity' => $afterQty
                ]);

                StockMutation::create([

                    'stock_id' =>
                    $stock->id,

                    'type' =>
                    'opname_adjustment',

                    'quantity_before' =>
                    $beforeQty,

                    'quantity_change' =>
                    $item->difference,

                    'quantity_after' =>
                    $afterQty,

                    'reference_type' =>
                    StockOpname::class,

                    'reference_id' =>
                    $stockOpname->id,

                    'notes' =>
                    'Stock Opname Approval'
                ]);
            }

            $stockOpname->update([

                'status' =>
                'approved',

                'approved_by' =>
                Auth::id(),

                'approved_at' =>
                now()
            ]);

            Notification::create([
                'user_id' => $stockOpname->created_by,
                'title' => 'Stock Opname Approved',
                'message' =>
                $stockOpname->reference_number .
                    ' has been approved',
                'type' => 'stock_opname_approved',
                'severity' => 'info',
                'is_read' => false
            ]);

            AuditLog::create([

                'user_id' => Auth::id(),

                'action' => 'approve_stock_opname',

                'model_type' => StockOpname::class,

                'model_id' => $stockOpname->id,

                'old_values' => [
                    'status' => 'pending'
                ],

                'new_values' => [
                    'status' => 'approved'
                ],

                'ip_address' => request()->ip()
            ]);

            DB::commit();

            return response()->json([

                'success' => true,

                'message' =>
                'Stock opname approved'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'success' => false,

                'message' =>
                $e->getMessage()

            ], 500);
        }
    }
    public function reject($id)
    {
        DB::beginTransaction();

        try {

            $stockOpname =
                StockOpname::findOrFail($id);

            if (
                $stockOpname->status !== 'pending'
            ) {

                throw new \Exception(
                    'Stock opname already processed'
                );
            }

            $stockOpname->update([

                'status' => 'rejected',

                'approved_by' =>
                Auth::id(),

                'approved_at' =>
                now()
            ]);

            AuditLog::create([

                'user_id' =>
                Auth::id(),

                'action' =>
                'reject_stock_opname',

                'model_type' =>
                StockOpname::class,

                'model_id' =>
                $stockOpname->id,

                'old_values' => [
                    'status' => 'pending'
                ],

                'new_values' => [
                    'status' => 'rejected'
                ],

                'ip_address' =>
                request()->ip()
            ]);

            DB::commit();

            return response()->json([

                'success' => true,

                'message' =>
                'Stock opname rejected'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'success' => false,

                'message' =>
                $e->getMessage()

            ], 500);
        }
    }
}
