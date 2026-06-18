<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Medicine;
use App\Models\User;
use App\Models\Notification;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Helpers\DistanceHelper;
use App\Services\DeliveryFeeService;
use App\Services\ReservationService;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int,\App\Models\OrderItem> $items
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\Payment|null $payment
 */

class OrderController extends Controller
{
    protected ReservationService $reservationService;

    public function __construct(
        ReservationService $reservationService
    ) {
        $this->reservationService = $reservationService;
    }
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $validated = $request->validate([

                'customer_name' => 'required|string',
                'customer_phone' => 'required|string',

                'delivery_address' =>
                'required|string',

                'delivery_latitude' =>
                'required|numeric',

                'delivery_longitude' =>
                'required|numeric',

                'delivery_city' =>
                'nullable|string',

                'notes' =>
                'nullable|string',

                'shipping_method' =>
                'nullable|in:cod,online_payment',

                'payment_method' =>
                'required|in:cod,midtrans',

                'items' =>
                'required|array|min:1',

                'items.*.medicine_id' =>
                'required|exists:medicines,id',

                'items.*.quantity' =>
                'required|integer|min:1',
            ]);

            $user = Auth::user();

            if ($user) {
                $validated['customer_name'] = $user->name;
                $validated['customer_phone'] = $user->phone;
            }

            /*
            |--------------------------------------------------------------------------
            | APOTEK LOCATION
            |--------------------------------------------------------------------------
            */

            $pharmacyLat = -1.610122;
            $pharmacyLng = 103.613355;

            /*
            |--------------------------------------------------------------------------
            | DISTANCE
            |--------------------------------------------------------------------------
            */

            $distance =
                DistanceHelper::calculate(
                    $pharmacyLat,
                    $pharmacyLng,
                    $validated['delivery_latitude'],
                    $validated['delivery_longitude']
                );

            /*
            |--------------------------------------------------------------------------
            | MAX DISTANCE
            |--------------------------------------------------------------------------
            */

            if ($distance > 10000) {

                return response()->json([
                    'message' =>
                    'Lokasi terlalu jauh. Maksimal pengiriman 10 km'
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | SHIPPING COST
            |--------------------------------------------------------------------------
            */

            $shippingCost =
                DeliveryFeeService::calculate(
                    $distance
                );

            /*
            |--------------------------------------------------------------------------
            | SUBTOTAL
            |--------------------------------------------------------------------------
            */

            $subtotal = 0;

            foreach (
                $validated['items']
                as $item
            ) {

                $medicine =
                    Medicine::findOrFail(
                        $item['medicine_id']
                    );

                $subtotal +=
                    $medicine->selling_price
                    *
                    $item['quantity'];
            }

            /*
            |--------------------------------------------------------------------------
            | TOTAL
            |--------------------------------------------------------------------------
            */

            $total =
                $subtotal
                +
                $shippingCost;

            /*
            |--------------------------------------------------------------------------
            | CREATE ORDER
            |--------------------------------------------------------------------------
            */
            $paymentMethod =
                $validated['payment_method'];

            $order = Order::create([

                'order_number' =>
                'ORD-' .
                    now()->format('Ymd') .
                    '-' .
                    strtoupper(Str::random(5)),

                'customer_id' => Auth::id(),

                'customer_name' =>
                $validated['customer_name'],

                'customer_phone' =>
                $validated['customer_phone'],

                'subtotal' =>
                $subtotal,

                'shipping_cost' =>
                $shippingCost,

                'delivery_distance_km' =>
                $distance,

                'discount_amount' =>
                0,

                'total_amount' =>
                $total,

                'status' =>
                'pending',

                'shipping_method' =>
                $validated['payment_method'] === 'cod'
                    ? 'cod'
                    : 'online_payment',

                'payment_method' =>
                $validated['payment_method'],
                'payment_status' => 'pending',

                'delivery_address' =>
                $validated['delivery_address'],

                'delivery_latitude' =>
                $validated['delivery_latitude'],

                'delivery_longitude' =>
                $validated['delivery_longitude'],

                'delivery_city' =>
                $validated['delivery_city'] ?? null,

                'notes' =>
                $validated['notes'] ?? null,
            ]);

            $users = User::whereIn(
                'role',
                ['owner', 'staff']
            )->get();

            foreach ($users as $user) {

                Notification::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'type' => 'new_order',
                        'title' => 'New Order - ' . $order->order_number
                    ],
                    [
                        'message' => 'New customer order has been created',
                        'severity' => 'info',
                        'is_read' => false
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | ORDER ITEMS
            |--------------------------------------------------------------------------
            */

            foreach (
                $validated['items']
                as $item
            ) {

                $medicine =
                    Medicine::findOrFail(
                        $item['medicine_id']
                    );

                OrderItem::create([

                    'order_id' =>
                    $order->id,

                    'medicine_id' =>
                    $medicine->id,

                    'quantity' =>
                    $item['quantity'],

                    'unit_price' =>
                    $medicine->selling_price,

                    'subtotal' =>
                    $medicine->selling_price
                        *
                        $item['quantity'],
                ]);
            }

            /*
|--------------------------------------------------------------------------
| RESERVE STOCK
|--------------------------------------------------------------------------
*/

            foreach (
                $validated['items']
                as $item
            ) {

                $this->reservationService
                    ->checkMedicineAvailability(
                        $item['medicine_id'],
                        $item['quantity']
                    );

                $this->reservationService
                    ->reserveStock(
                        $order,
                        $item['medicine_id'],
                        $item['quantity']
                    );
            }

            DB::commit();

            return response()->json([

                'message' =>
                'Order berhasil dibuat',

                'order' =>
                $order,

                'distance_km' =>
                $distance,

                'shipping_cost' =>
                $shippingCost,

                'total' =>
                $total

            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' =>
                $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {

            $query = Order::with([
                'items.medicine',
                'user'
            ]);

            if ($request->status) {
                $query->where(
                    'status',
                    $request->status
                );
            }

            if ($request->payment_method) {
                $query->where(
                    'payment_method',
                    $request->payment_method
                );
            }

            if ($request->search) {

                $query->where(function ($q) use ($request) {

                    $q->where(
                        'order_number',
                        'like',
                        '%' . $request->search . '%'
                    )
                        ->orWhere(
                            'customer_name',
                            'like',
                            '%' . $request->search . '%'
                        );
                });
            }

            if (
                $request->start_date &&
                $request->end_date
            ) {

                $query->whereBetween(
                    'created_at',
                    [
                        $request->start_date,
                        $request->end_date
                    ]
                );
            }

            $orders = $query
                ->latest()
                ->paginate(
                    $request->per_page ?? 20
                );

            return response()->json([
                'message' => 'Daftar order',
                'data' => $orders
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function trackByPhone(Request $request)
    {
        try {

            $validated = $request->validate([
                'order_number' => 'required|string',
                'phone' => 'required|string'
            ]);

            $order = Order::where(
                'order_number',
                $validated['order_number']
            )
                ->where(
                    'customer_phone',
                    $validated['phone']
                )
                ->first();

            if (!$order) {

                return response()->json([
                    'message' => 'Order tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'message' => 'Order ditemukan',
                'order' => $order
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'message' =>
                $e->getMessage()
            ], 500);
        }
    }

    public function show(string $orderNumber, Request $request)
    {
        try {

            $order = Order::with(
                'items.medicine'
            )
                ->where(
                    'order_number',
                    $orderNumber
                )
                ->first();

            if (!$order) {

                return response()->json([
                    'message' => 'Order tidak ditemukan'
                ], 404);
            }


            /*
        |--------------------------------------------------------------------------
        | LOGIN USER
        |--------------------------------------------------------------------------
        */

            $user = Auth::user();

            if ($user) {

                if (
                    $user->role === 'customer'
                    &&
                    $order->customer_id !== $user->id
                ) {
                    return response()->json([
                        'message' => 'Unauthorized'
                    ], 403);
                }
            }

            /*
        |--------------------------------------------------------------------------
        | GUEST USER
        |--------------------------------------------------------------------------
        */ else {

                $validated = $request->validate([
                    'phone' => 'required|string'
                ]);

                if (
                    $validated['phone']
                    !==
                    $order->customer_phone
                ) {

                    return response()->json([
                        'message' => 'Nomor HP tidak cocok'
                    ], 403);
                }
            }

            /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

            return response()->json([

                'message' => 'Detail order',

                'order' => [

                    'id' => $order->id,

                    'order_number' => $order->order_number,

                    'status' => $order->status,

                    'customer_name' => $order->customer_name,

                    'customer_phone' => $order->customer_phone,

                    'delivery_address' =>
                    $order->delivery_address,

                    'payment_method' =>
                    $order->payment_method,

                    'payment_status' =>
                    $order->payment_status,

                    'shipping_method' =>
                    $order->shipping_method,

                    'subtotal' =>
                    $order->subtotal,

                    'shipping_cost' =>
                    $order->shipping_cost,

                    'discount_amount' =>
                    $order->discount_amount,

                    'total_amount' =>
                    $order->total_amount,

                    'items' => $order->items->map(function ($item) {

                        return [

                            'medicine_id' =>
                            $item->medicine_id,

                            'medicine_name' =>
                            $item->medicine->name,

                            'photo' =>
                            $item->medicine->photo,

                            'quantity' =>
                            $item->quantity,

                            'unit_price' =>
                            $item->unit_price,

                            'subtotal' =>
                            $item->subtotal,
                        ];
                    }),

                    'created_at' =>
                    $order->created_at
                ]
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function cancel(
        Request $request,
        $id
    ) {
        try {

            $order = Order::findOrFail($id);

            $user = Auth::user();

            if ($user) {

                if (
                    $user->role === 'customer'
                    &&
                    $order->customer_id !== $user->id
                ) {
                    return response()->json([
                        'message' => 'Unauthorized'
                    ], 403);
                }
            } else {

                $request->validate([
                    'phone' => 'required'
                ]);

                if (
                    $request->phone !==
                    $order->customer_phone
                ) {

                    return response()->json([
                        'message' => 'Unauthorized'
                    ], 403);
                }
            }


            if (
                in_array(
                    $order->status,
                    [
                        'diproses',
                        'dikirim',
                        'selesai'
                    ]
                )
            ) {
                return response()->json([
                    'message' =>
                    'Order tidak dapat dibatalkan'
                ], 422);
            }

            if ($order->status === 'dibatalkan') {

                return response()->json([
                    'message' =>
                    'Order sudah dibatalkan'
                ], 422);
            }

            $this->reservationService
                ->releaseReservation($order);

            Payment::where(
                'order_id',
                $order->id
            )->where(
                'status',
                'pending'
            )->update([
                'status' => 'cancelled',
                'paid_at' => null
            ]);

            $order->update([
                'status' => 'dibatalkan',
                'payment_status' => 'cancelled'
            ]);

            return response()->json([
                'message' =>
                'Order berhasil dibatalkan',
                'order' =>
                $order->fresh()
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'message' =>
                $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        try {

            $request->validate([
                'status' => 'required|in:pending,diproses,dikirim,selesai,dibatalkan'
            ]);

            $order = Order::findOrFail($id);

            /*
        |--------------------------------------------------------------------------
        | STATUS FLOW VALIDATION
        |--------------------------------------------------------------------------
        */

            $currentStatus = $order->status;
            $newStatus = $request->status;

            $allowedTransitions = [

                'pending' => [
                    'diproses',
                    'dibatalkan'
                ],

                'diproses' => [
                    'dikirim'
                ],

                'dikirim' => [
                    'selesai'
                ],

                'selesai' => [],

                'dibatalkan' => [],
            ];

            if (
                !in_array(
                    $newStatus,
                    $allowedTransitions[$currentStatus] ?? []
                )
            ) {
                return response()->json([
                    'message' => 'Perubahan status tidak valid'
                ], 422);
            }

            /*
        |--------------------------------------------------------------------------
        | CANCEL ORDER
        |--------------------------------------------------------------------------
        */

            if ($newStatus === 'dibatalkan') {

                $this->reservationService
                    ->releaseReservation($order);

                Payment::where(
                    'order_id',
                    $order->id
                )
                    ->where(
                        'status',
                        'pending'
                    )
                    ->update([
                        'status' => 'cancelled'
                    ]);
            }

            /*
        |--------------------------------------------------------------------------
        | UPDATE STATUS
        |--------------------------------------------------------------------------
        */

            $order->update([
                'status' => $newStatus
            ]);

            return response()->json([
                'message' => 'Status berhasil diubah',
                'data' => $order->fresh()
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
