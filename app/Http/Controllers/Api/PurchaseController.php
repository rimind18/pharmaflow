<?php

namespace App\Http\Controllers\Api;


use App\Services\AuditLogService;
use App\Http\Controllers\Controller;
use App\Models\StockMutation;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Medicine;
use App\Models\Stock;
use App\Models\Cashflow;
use App\Models\Notification;
use App\Services\StockCostLayerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseController extends Controller
{
    protected StockCostLayerService $stockCostLayerService;
    protected AuditLogService $auditLogService;

    public function __construct(
        AuditLogService $auditLogService,
        StockCostLayerService $stockCostLayerService
    ) {
        $this->auditLogService = $auditLogService;
        $this->stockCostLayerService = $stockCostLayerService;
    }

    public function approve($id)
    {
        try {

            $purchase =
                Purchase::findOrFail($id);

            if (
                !Auth::check() ||
                Auth::user()->role !== 'owner'
            ) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            if (
                $purchase->status !== 'draft'
            ) {
                throw new \Exception(
                    'Only draft purchase can be approved'
                );
            }

            $purchase->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            Notification::firstOrCreate(
                [
                    'user_id' => $purchase->created_by,
                    'type' => 'purchase_approved',
                    'title' => 'Purchase Approved'
                ],
                [
                    'message' =>
                    'PO ' . $purchase->po_number . ' has been approved',
                    'severity' => 'info',
                    'is_read' => false
                ]
            );

            $purchase->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Purchase approved',
                'data' => $purchase
            ]);
        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                $e->getMessage()

            ], 400);
        }
    }

    public function markOrdered($id)
    {

        try {

            $purchase =
                Purchase::findOrFail($id);

            if (
                !Auth::check() ||
                !in_array(
                    Auth::user()->role,
                    ['owner', 'staff']
                )
            ) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            if (
                $purchase->status !== 'approved'
            ) {
                return response()->json([
                    'message' => 'Purchase must be approved first'
                ], 422);
            }

            $purchase->update([
                'status' => 'ordered'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Purchase ordered'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }


    /**
     * Get all purchases
     */
    public function index(Request $request)
    {
        try {

            $query = Purchase::with([
                'supplier',
                'items.medicine'
            ]);

            if (
                !Auth::check() ||
                !in_array(
                    Auth::user()->role,
                    ['owner', 'staff']
                )
            ) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Filter supplier
            if ($request->filled('supplier_id')) {
                $query->where(
                    'supplier_id',
                    $request->supplier_id
                );
            }

            // Filter status
            if ($request->filled('status')) {
                $query->where(
                    'status',
                    $request->status
                );
            }

            // Filter date range
            if (
                $request->filled('start_date') &&
                $request->filled('end_date')
            ) {
                $query->whereBetween('created_at', [
                    $request->start_date,
                    $request->end_date
                ]);
            }

            $purchases = $query
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'message' => 'Purchases retrieved successfully',
                'data' => $purchases
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create purchase
     */
    public function store(Request $request)
    {
        try {

            if (
                !Auth::check() ||
                !in_array(
                    Auth::user()->role,
                    ['owner', 'staff']
                )
            ) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validated = $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'items' => 'required|array|min:1',
                'items.*.medicine_id' => 'required|exists:medicines,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.expired_date' => 'nullable|date',
                'notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $totalAmount = 0;

            // Create purchase
            $purchase = Purchase::create([
                'created_by' => Auth::id(),
                'po_number' =>
                'PO-' .
                    now()->format('YmdHis') .
                    '-' .
                    Str::upper(Str::random(4)),

                'supplier_id' => $validated['supplier_id'],

                'subtotal' => 0,
                'tax_amount' => 0,
                'shipping_cost' => 0,
                'total_amount' => 0,

                'purchase_date' => now(),

                'expected_delivery_date' => now()->addDays(7),

                'status' => 'draft',

                'notes' => $validated['notes'] ?? null,

                'items_total' => count($validated['items']),
            ]);

            // Create items
            foreach ($validated['items'] as $item) {

                $subtotal =
                    $item['quantity'] *
                    $item['unit_price'];

                $totalAmount += $subtotal;

                PurchaseItem::create([
                    'purchase_id' =>
                    $purchase->id,

                    'medicine_id' =>
                    $item['medicine_id'],

                    'quantity' =>
                    $item['quantity'],

                    'unit_price' =>
                    $item['unit_price'],

                    'subtotal' =>
                    $subtotal,

                    'expired_date' =>
                    $item['expired_date'] ?? null,

                    'quantity_received' => 0,
                ]);
            }

            // Update total
            $purchase->update([
                'total_amount' => $totalAmount
            ]);

            DB::commit();

            return response()->json([
                'message' =>
                'Purchase created successfully',

                'data' => $purchase->load([
                    'supplier',
                    'items.medicine'
                ])
            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Show purchase detail
     */
    public function show($id)
    {
        try {

            if (
                !Auth::check() ||
                !in_array(
                    Auth::user()->role,
                    ['owner', 'staff']
                )
            ) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            $purchase = Purchase::with([
                'supplier',
                'items.medicine',
                'receivedByUser'
            ])->findOrFail($id);

            return response()->json([
                'message' =>
                'Purchase retrieved successfully',

                'data' => $purchase
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'message' =>
                'Purchase not found'
            ], 404);
        }
    }

    /**
     * Update purchase
     */
    public function update(Request $request, $id)
    {
        try {

            $purchase =
                Purchase::findOrFail($id);

            if (
                !Auth::check() ||
                !in_array(
                    Auth::user()->role,
                    ['owner', 'staff']
                )
            ) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Only pending can edit
            if (
                $purchase->status !== 'draft'
            ) {
                return response()->json([
                    'message' =>
                    'Can only edit pending purchases'
                ], 422);
            }

            $validated = $request->validate([
                'supplier_id' =>
                'required|exists:suppliers,id',

                'items' =>
                'required|array|min:1',

                'items.*.medicine_id' =>
                'required|exists:medicines,id',

                'items.*.quantity' =>
                'required|integer|min:1',

                'items.*.unit_price' =>
                'required|numeric|min:0',

                'items.*.expired_date' =>
                'nullable|date',

                'notes' =>
                'nullable|string',
            ]);

            DB::beginTransaction();

            // Delete old items
            $purchase->items()->delete();

            $totalAmount = 0;

            // Insert new items
            foreach ($validated['items'] as $item) {

                $subtotal =
                    $item['quantity'] *
                    $item['unit_price'];

                $totalAmount += $subtotal;

                PurchaseItem::create([
                    'purchase_id' =>
                    $purchase->id,

                    'medicine_id' =>
                    $item['medicine_id'],

                    'quantity' =>
                    $item['quantity'],

                    'unit_price' =>
                    $item['unit_price'],

                    'subtotal' =>
                    $subtotal,

                    'expired_date' =>
                    $item['expired_date'] ?? null,
                    'quantity_received' => 0,
                ]);
            }

            // Update purchase
            $purchase->update([
                'supplier_id' =>
                $validated['supplier_id'],

                'total_amount' =>
                $totalAmount,

                'notes' =>
                $validated['notes'] ?? null,

                'items_total' =>
                count($validated['items']),
            ]);

            DB::commit();

            return response()->json([
                'message' =>
                'Purchase updated successfully',

                'data' =>
                $purchase->load([
                    'supplier',
                    'items.medicine'
                ])
            ], 200);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' =>
                $e->getMessage()
            ], 400);
        }
    }

    public function generateFromRecommendation(
        Request $request
    ) {
        try {

            if (
                !Auth::check() ||
                Auth::user()->role !== 'owner'
            ) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validated =
                $request->validate([

                    'supplier_id' =>
                    'required|exists:suppliers,id',

                    'items' =>
                    'required|array|min:1',

                    'items.*.medicine_id' =>
                    'required|exists:medicines,id',

                    'items.*.quantity' =>
                    'required|integer|min:1'
                ]);

            DB::beginTransaction();

            $purchase = Purchase::create([
                'created_by' => Auth::id(),

                'po_number' =>
                'PO-' .
                    now()->format('YmdHis') .
                    '-' .
                    Str::upper(Str::random(4)),

                'supplier_id' =>
                $validated['supplier_id'],

                'purchase_date' =>
                now(),

                'expected_delivery_date' =>
                now()->addDays(7),

                'status' =>
                'draft',

                'subtotal' => 0,
                'tax_amount' => 0,
                'shipping_cost' => 0,
                'total_amount' => 0,

                'items_total' =>
                count(
                    $validated['items']
                ),

                'notes' =>
                'Auto Generated From Reorder Recommendation'
            ]);

            $totalAmount = 0;

            foreach (
                $validated['items']
                as $item
            ) {

                $medicine =
                    Medicine::findOrFail(
                        $item['medicine_id']
                    );

                $subtotal =
                    $medicine->base_price *
                    $item['quantity'];

                PurchaseItem::create([

                    'purchase_id' =>
                    $purchase->id,

                    'medicine_id' =>
                    $medicine->id,

                    'quantity' =>
                    $item['quantity'],

                    'unit_price' =>
                    $medicine->base_price,

                    'subtotal' =>
                    $subtotal,

                    'quantity_received' => 0
                ]);

                $totalAmount +=
                    $subtotal;
            }

            $purchase->update([
                'total_amount' =>
                $totalAmount
            ]);

            DB::commit();

            return response()->json([

                'success' => true,

                'message' =>
                'Purchase draft generated',

                'data' =>
                $purchase->load(
                    'items.medicine'
                )
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'success' => false,

                'message' =>
                $e->getMessage()

            ], 400);
        }
    }
    /**
     * Receive Purchase
     * Input stock + create cashflow
     */
    public function receive(Request $request, $id)
    {
        try {

            $purchase = Purchase::with([
                'items',
                'supplier'
            ])->findOrFail($id);

            if (
                !Auth::check() ||
                !in_array(
                    Auth::user()->role,
                    ['owner', 'staff']
                )
            ) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Already received
            if ($purchase->status === 'received') {
                throw new \Exception(
                    'Purchase already received'
                );
            }

            if (
                !in_array(
                    $purchase->status,
                    ['ordered', 'partial']
                )
            ) {
                throw new \Exception(
                    'Purchase must be ordered first'
                );
            }
            $validated =
                $request->validate([

                    'warehouse_id' =>
                    'required|exists:warehouses,id',

                    'shelf_id' =>
                    'nullable|exists:shelves,id',

                    'received_items' =>
                    'required|array',

                    'received_items.*.purchase_item_id' =>
                    'required|exists:purchase_items,id',

                    'received_items.*.quantity_received' =>
                    'required|integer|min:0',
                ]);

            DB::beginTransaction();

            $totalReceived = 0;

            foreach (
                $validated['received_items']
                as $item
            ) {

                $purchaseItem =
                    PurchaseItem::where(
                        'purchase_id',
                        $purchase->id
                    )
                    ->findOrFail(
                        $item['purchase_item_id']
                    );
                if (
                    $purchaseItem->purchase_id
                    !=
                    $purchase->id
                ) {
                    throw new \Exception(
                        'Purchase item does not belong to purchase'
                    );
                }
                if (
                    $item['quantity_received']
                    >
                    $purchaseItem->quantity
                ) {
                    throw new \Exception(
                        'Quantity received melebihi quantity purchase'
                    );
                }

                // Update received qty
                $newReceived =
                    $purchaseItem->quantity_received +
                    $item['quantity_received'];

                if (
                    $newReceived >
                    $purchaseItem->quantity
                ) {
                    throw new \Exception(
                        'Quantity received melebihi quantity purchase'
                    );
                }

                $purchaseItem->update([
                    'quantity_received' => $newReceived
                ]);


                if (
                    $purchaseItem->expired_date &&
                    now()->diffInDays(
                        $purchaseItem->expired_date,
                        false
                    ) < 30
                ) {
                    throw new \Exception(
                        'Obat dengan expiry kurang dari 30 hari tidak boleh diterima'
                    );
                }

                // Input stock
                if (
                    $item['quantity_received'] > 0
                ) {

                    $stock =
                        Stock::firstOrCreate(
                            [
                                'medicine_id' =>
                                $purchaseItem->medicine_id,

                                'warehouse_id' =>
                                $validated['warehouse_id'],

                                'expired_date' =>
                                $purchaseItem->expired_date,
                            ],
                            [
                                'quantity' => 0,

                                'shelf_id' =>
                                $validated['shelf_id']
                                    ?? null,
                            ]
                        );

                    $beforeQty = $stock->quantity;

                    $stock->increment(
                        'quantity',
                        $item['quantity_received']
                    );

                    $afterQty =
                        $beforeQty +
                        $item['quantity_received'];

                    StockMutation::create([

                        'stock_id' =>
                        $stock->id,

                        'type' =>
                        'purchase',

                        'quantity_before' =>
                        $beforeQty,

                        'quantity_change' =>
                        $item['quantity_received'],

                        'quantity_after' =>
                        $afterQty,

                        'reference_type' =>
                        Purchase::class,

                        'reference_id' =>
                        $purchase->id,

                        'notes' =>
                        'Purchase Receive #' .
                            $purchase->po_number
                    ]);

                    $this->stockCostLayerService->createLayer(

                        $purchaseItem->medicine_id,

                        $purchaseItem->id,

                        $item['quantity_received'],

                        $purchaseItem->unit_price
                    );

                    $totalReceived +=
                        $item['quantity_received'];
                }
            }
            $oldData = $purchase->toArray();

            $totalOrdered =
                $purchase->items()
                ->sum('quantity');

            $totalReceived =
                $purchase->items()
                ->sum('quantity_received');

            $status =
                $totalReceived >= $totalOrdered
                ? 'received'
                : 'partial';

            $purchase->update([
                'status' => $status,
                'items_received' => $totalReceived,
                'received_at' => now(),
                'received_by' => Auth::id(),
            ]);

            $this->auditLogService->record(
                'purchase_receive',
                Purchase::class,
                $purchase->id,
                $oldData,
                $purchase->fresh()->toArray()
            );

            // Create cashflow
            $this->recordPurchaseCashflow(
                $purchase
            );

            DB::commit();

            return response()->json([
                'message' =>
                'Purchase received successfully',

                'data' =>
                $purchase->load(
                    'items.medicine'
                )
            ], 200);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' =>
                $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete purchase
     */
    public function destroy($id)
    {
        try {

            $purchase =
                Purchase::findOrFail($id);

            if (
                !Auth::check() ||
                Auth::user()->role !== 'owner'
            ) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            if (
                $purchase->status !== 'draft'
            ) {
                return response()->json([
                    'message' =>
                    'Can only delete pending purchases'
                ], 422);
            }

            $purchase->delete();

            return response()->json([
                'message' =>
                'Purchase deleted successfully'
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'message' =>
                $e->getMessage()
            ], 400);
        }
    }

    /**
     * Record purchase cashflow
     */
    private function recordPurchaseCashflow($purchase)
    {
        // Prevent duplicate cashflow
        $exists = Cashflow::where(
            'reference_type',
            Purchase::class
        )
            ->where(
                'reference_id',
                $purchase->id
            )
            ->exists();

        if ($exists) {
            return;
        }

        Cashflow::create([
            'transaction_date' =>
            $purchase->received_at
                ?? now(),

            'type' =>
            'expense',

            'category' =>
            'pembelian_obat',

            'amount' =>
            $purchase->total_amount,

            'description' =>
            "Pembelian dari {$purchase->supplier->name}",

            'reference_type' => Purchase::class,

            'reference_id' =>
            $purchase->id,

            'notes' =>
            "Purchase #: {$purchase->po_number}",
        ]);
    }
}
