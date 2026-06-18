<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cashflow;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use App\Services\ReservationService;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected ReservationService $reservationService;

    public function __construct(
        ReservationService $reservationService
    ) {
        $this->reservationService = $reservationService;

        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Create Snap Token untuk payment
     * PUBLIC - support guest & auth
     */
    public function createSnapToken(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_id' => 'required|exists:orders,id',
            ]);

            $order = Order::findOrFail($validated['order_id']);

            if ($order->payment_status === 'completed') {
                return response()->json([
                    'message' => 'Order already paid'
                ], 422);
            }

            

            // Para Snap Token
            $snapParams = [
                'transaction_details' => [
                    'order_id' => $order->order_number,
                    'gross_amount' => (int) $order->total_amount,
                ],
                'customer_details' => [
                    'first_name' => $order->customer_name,
                    'phone' => $order->getCustomerPhone(),
                    'email' => $order->user?->email ?? 'guest@farmaflow.local',
                    'address' => $order->getFullAddress(),
                ],

                'item_details' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->medicine_id,
                        'price' => (int) $item->unit_price,
                        'quantity' => $item->quantity,
                        'name' => $item->medicine->name,
                    ];
                })->toArray(),
                'callbacks' => [
                    'finish' => config('app.url') . '/order-success',
                    'error' => config('app.url') . '/order-failed',
                    'pending' => config('app.url') . '/order-pending',
                ],
            ];

            $snapToken = Snap::getSnapToken($snapParams);

            // Save payment record
            Payment::firstOrCreate(
                [
                    'order_id' => $order->id
                ],
                [
                    'payment_code' => 'PAY-' . now()->format('YmdHis'),
                    'order_id' => $order->id,

                    // BELUM ADA TRANSACTION INTERNAL
                    'transaction_id' => null,

                    // SIMPAN ORDER NUMBER ATAU MIDTRANS ORDER ID DI SINI
                    'transaction_id_gateway' => $order->order_number,

                    'payment_method' => 'midtrans',
                    'amount' => $order->total_amount,
                    'status' => 'pending',
                    'notes' => 'Waiting Midtrans Payment',
                ]
            );

            return response()->json([
                'message' => 'Snap token created',
                'snap_token' => $snapToken,
                'redirect_url' => 'https://app.midtrans.com/snap/v2/vtweb/' . $snapToken,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Payment status check
     * PUBLIC - support guest & auth
     */
    public function status(Request $request, $id)
    {
        try {

            if (!auth()->check()) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            $order = Order::findOrFail($id);

            $user = auth()->user();

            if (
                $user->role === 'customer'
                &&
                $order->user_id !== $user->id
            ) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            $payment = Payment::where(
                'order_id',
                $id
            )->first();


            if (!$payment) {
                return response()->json([
                    'message' => 'Payment not found',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'message' => 'Payment status retrieved',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'amount' => $order->total_amount,
                    'status' => $payment->status,
                    'payment_method' => $payment->payment_method,
                    'order_status' => $order->status,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Order not found'
            ], 404);
        }
    }

    /**
     * Confirm COD payment
     * PUBLIC - support guest & auth
     */
    public function confirmCOD(Request $request)
    {
        try {
            if (
                !auth()->check()
                ||
                !in_array(
                    auth()->user()->role,
                    ['owner', 'staff']
                )
            ) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validated = $request->validate([
                'order_id' => 'required|exists:orders,id',
            ]);

            $order = Order::findOrFail($validated['order_id']);

            if ($order->payment_status === 'completed') {
                return response()->json([
                    'message' => 'Order sudah dikonfirmasi',
                    'data' => $order
                ], 200);
            }

            if ($order->payment_method !== 'cod') {
                return response()->json([
                    'message' => 'Order payment method is not COD'
                ], 422);
            }

            // Create payment record
            DB::transaction(function () use ($order) {
                $transaction = \App\Models\Transaction::firstOrCreate(
                    [
                        'reference_number' => 'COD-' . $order->order_number,
                    ],
                    [
                        'kasir_id' => null,
                        'total_amount' => $order->total_amount,
                        'final_amount' => $order->total_amount,
                        'payment_method' => 'cash',
                        'payment_status' => 'lunas',
                    ]
                );
                $this->createTransactionItems(
                    $order,
                    $transaction
                );
                Payment::updateOrCreate(
                    [
                        'order_id' => $order->id
                    ],
                    [
                        'amount' => $order->total_amount,
                        'payment_method' => 'cod',
                        'status' => 'completed',
                        'transaction_id' => $transaction->id,
                        'transaction_id_gateway' => 'COD-' . $order->order_number,
                        'paid_at' => now(),
                    ]
                );

                $order->update([
                    'payment_status' => 'completed',
                    'status' => 'diproses',
                ]);

                $this->sendNotificationToStaff(
                    'cod_confirmed',
                    'COD Confirmed - ' . $order->order_number,
                    'COD payment confirmed for order ' . $order->order_number
                );

                $this->reservationService
                    ->commitReservation($order);

                $this->recordCashflow(
                    $order,
                    'completed'
                );
            });

            return response()->json([
                'message' => 'COD order confirmed',
                'data' => $order->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function notification(Request $request)
    {
        try {
            $json = json_decode(
                $request->getContent()
            );
            if (
                !$json ||
                !isset(
                    $json->order_id,
                    $json->status_code,
                    $json->gross_amount,
                    $json->signature_key
                )
            ) {
                return response()->json([
                    'message' => 'Invalid payload'
                ], 400);
            }

            $serverKey = config('midtrans.server_key');

            $signatureKey =
                hash(
                    'sha512',
                    $json->order_id .
                        $json->status_code .
                        $json->gross_amount .
                        $serverKey
                );

            if (
                $signatureKey !==
                $json->signature_key
            ) {

                return response()->json([
                    'message' => 'Invalid signature'
                ], 403);
            }

            $transactionStatus =
                $json->transaction_status;

            $orderId =
                $json->order_id;

            $fraudStatus =
                $json->fraud_status ?? null;

            $order = Order::where(
                'order_number',
                $orderId
            )->first();

            if (!$order) {

                return response()->json([
                    'message' => 'Order not found'
                ], 404);
            }

            if ($order->payment_status === 'completed') {

                return response()->json([
                    'status' => 'already_processed'
                ]);
            }

            $alreadyPaid = Payment::where(
                'order_id',
                $order->id
            )
                ->where('status', 'paid')
                ->whereNotNull('transaction_id')
                ->exists();

            if ($alreadyPaid) {
                return response()->json([
                    'status' => 'already_processed'
                ]);
            }

            if ($transactionStatus == 'capture') {

                if ($fraudStatus == 'challenge') {

                    $order->update([
                        'payment_status' => 'pending'
                    ]);
                } elseif ($fraudStatus == 'accept') {

                    DB::transaction(function () use ($order) {

                        $transaction =
                            Transaction::firstOrCreate(

                                [
                                    'reference_number' =>
                                    'MID-' .
                                        $order->order_number,
                                ],

                                [
                                    'kasir_id' => null,
                                    'total_amount' =>
                                    $order->total_amount,

                                    'final_amount' =>
                                    $order->total_amount,

                                    'payment_method' =>
                                    'transfer',

                                    'payment_status' =>
                                    'lunas',
                                ]
                            );

                        Payment::where(
                            'order_id',
                            $order->id
                        )->update([

                            'transaction_id' =>
                            $transaction->id,

                            'status' =>
                            'paid',

                            'paid_at' =>
                            now(),
                        ]);

                        $this->createTransactionItems(
                            $order,
                            $transaction
                        );

                        $order->update([
                            'payment_status' => 'completed',
                            'status' => 'diproses',
                        ]);

                        $this->sendNotificationToStaff(
                            'payment_received',
                            'Payment Received - ' . $order->order_number,
                            'Payment received for order ' . $order->order_number
                        );

                        $this->recordCashflow(
                            $order,
                            'completed'
                        );

                        $this->reservationService
                            ->commitReservation(
                                $order
                            );
                    });
                }
            } elseif ($transactionStatus == 'settlement') {

                DB::transaction(function () use ($order) {

                    $transaction =
                        \App\Models\Transaction::firstOrCreate(

                            [
                                'reference_number' =>
                                'MID-' .
                                    $order->order_number,
                            ],

                            [
                                'kasir_id' => null,
                                'total_amount' =>
                                $order->total_amount,

                                'final_amount' =>
                                $order->total_amount,

                                'payment_method' =>
                                'transfer',

                                'payment_status' =>
                                'lunas',
                            ]
                        );

                    Payment::where(
                        'order_id',
                        $order->id
                    )->update([

                        'transaction_id' =>
                        $transaction->id,

                        'status' =>
                        'paid',

                        'paid_at' =>
                        now(),
                    ]);

                    $this->createTransactionItems(
                        $order,
                        $transaction
                    );

                    $order->update([
                        'payment_status' => 'completed',
                        'status' => 'diproses',
                    ]);

                    $this->sendNotificationToStaff(
                        'payment_received',
                        'Payment Received - ' . $order->order_number,
                        'Payment received for order ' . $order->order_number
                    );

                    $this->reservationService
                        ->commitReservation($order);

                    $this->recordCashflow(
                        $order,
                        'completed'
                    );
                });
            } elseif ($transactionStatus == 'pending') {

                $order->update([
                    'payment_status' => 'pending'
                ]);
            } elseif ($transactionStatus == 'deny') {

                $order->update([
                    'payment_status' => 'failed'
                ]);

                Payment::where(
                    'order_id',
                    $order->id
                )->update([
                    'status' => 'failed'
                ]);

                $this->recordCashflow(
                    $order,
                    'failed'
                );

                $this->reservationService
                    ->releaseReservation(
                        $order
                    );
            } elseif ($transactionStatus == 'expire') {

                $order->update([
                    'payment_status' => 'expired'
                ]);

                Payment::where(
                    'order_id',
                    $order->id
                )->update([
                    'status' => 'expired'
                ]);

                $this->reservationService
                    ->releaseReservation(
                        $order
                    );
            } elseif ($transactionStatus == 'cancel') {

                $order->update([
                    'payment_status' => 'cancelled'
                ]);

                Payment::where(
                    'order_id',
                    $order->id
                )->update([
                    'status' => 'cancelled'
                ]);

                $this->reservationService
                    ->releaseReservation(
                        $order
                    );
            }

            return response()->json([
                'status' => 'ok'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Helper: Record cashflow when payment completed
     */
    private function recordCashflow($order, $status)
    {
        if ($status === 'completed') {

            $exists = Cashflow::where(
                'reference_type',
                'Order'
            )
                ->where(
                    'reference_id',
                    $order->id
                )
                ->where(
                    'type',
                    'income'
                )
                ->exists();

            if ($exists) {
                return;
            }

            Cashflow::create([
                'transaction_date' => now(),
                'type' => 'income',
                'category' => 'penjualan_online',
                'amount' => $order->total_amount,
                'description' => "Penjualan Order #{$order->order_number}",
                'reference_type' => Order::class,
                'reference_id' => $order->id,
                'notes' => "Payment via {$order->payment_method}",
            ]);
        }
    }

    private function sendNotificationToStaff(
        string $type,
        string $title,
        string $message
    ) {
        $users = User::whereIn(
            'role',
            ['owner', 'staff']
        )->get();

        foreach ($users as $user) {

            Notification::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => $type,
                    'title' => $title,
                ],
                [
                    'message' => $message,
                    'severity' => 'info',
                    'is_read' => false,
                ]
            );
        }
    }

    private function createTransactionItems(
        $order,
        $transaction
    ) {
        foreach ($order->items as $item) {

            \App\Models\TransactionItem::firstOrCreate(

                [
                    'transaction_id' => $transaction->id,
                    'medicine_id' => $item->medicine_id,
                ],

                [
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ]
            );
        }
    }
}
