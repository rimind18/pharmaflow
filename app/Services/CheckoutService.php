<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use App\Services\ReservationService;

class CheckoutService
{
    protected ReservationService $reservationService;

    public function __construct(
        ReservationService $reservationService
    ) {
        $this->reservationService = $reservationService;
    }

    public function createOrder(
        array $checkoutData,
        array $cartItems
    ): Order {

        return DB::transaction(function () use (
            $checkoutData,
            $cartItems
        ) {

            // Validasi stok
            foreach ($cartItems as $item) {

                $this->reservationService
                    ->checkMedicineAvailability(
                        $item['medicine_id'],
                        $item['quantity']
                    );
            }

            // Buat order
            $order = Order::create([

                'order_number'   => $checkoutData['order_number'] ?? uniqid('ORD-'),

                'customer_id'    => $checkoutData['customer_id'] ?? null,

                'customer_name'  => $checkoutData['customer_name'] ?? null,

                'customer_phone' => $checkoutData['customer_phone'] ?? null,

                'subtotal'       => $checkoutData['subtotal'] ?? 0,

                'shipping_cost'  => $checkoutData['shipping_cost'] ?? 0,

                'total_amount'   => $checkoutData['total_amount'] ?? 0,

                'payment_method' => $checkoutData['payment_method'] ?? 'cod',

                'payment_status' => 'pending',

                'status'         => 'pending',

            ]);

            // Buat order items
            foreach ($cartItems as $item) {

                OrderItem::create([

                    'order_id'    => $order->id,

                    'medicine_id' => $item['medicine_id'],

                    'quantity'    => $item['quantity'],

                    'unit_price'  => $item['price'],

                    'subtotal'    => $item['subtotal'],

                ]);
            }

            // Reserve stock
            foreach ($cartItems as $item) {

                $this->reservationService
                    ->reserveStock(
                        $order,
                        $item['medicine_id'],
                        $item['quantity']
                    );
            }

            return $order;
        });
    }
}