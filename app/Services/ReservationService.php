<?php

namespace App\Services;

use App\Models\StockCostLayer;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Order;
use App\Models\Stock;
use App\Models\StockReservation;
use App\Services\FIFOConsumptionService;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    protected StockMutationService $mutationService;
    protected FIFOConsumptionService $fifoConsumptionService;

    public function __construct(
        StockMutationService $mutationService,
        FIFOConsumptionService $fifoConsumptionService
    ) {
        $this->mutationService =
            $mutationService;

        $this->fifoConsumptionService =
            $fifoConsumptionService;
    }
    public function activeReservedQty(
        int $stockId
    ): int {

        return StockReservation::query()
            ->where('stock_id', $stockId)
            ->where('status', 'active')
            ->sum('quantity');
    }

    public function checkMedicineAvailability(
        int $medicineId,
        int $requestedQty
    ): bool {

        $totalStock = Stock::query()
            ->where('medicine_id', $medicineId)
            ->sum('quantity');

        if ($totalStock < $requestedQty) {

            throw ValidationException::withMessages([
                'stock' => 'Stok tidak mencukupi'
            ]);
        }

        return true;
    }

    public function reserveStock(
        Order $order,
        int $medicineId,
        int $requestedQty
    ): void {

        $remainingQty = $requestedQty;

        $stocks = Stock::query()

            ->where(
                'medicine_id',
                $medicineId
            )

            ->available()

            ->where(function ($query) {

                $query
                    ->whereNull('expired_date')
                    ->orWhereDate(
                        'expired_date',
                        '>',
                        now()
                    );
            })

            ->FIFO()

            ->lockForUpdate()

            ->get();

        foreach ($stocks as $stock) {

            if ($remainingQty <= 0) {
                break;
            }

            $reservedQty = StockReservation::query()

                ->where(
                    'stock_id',
                    $stock->id
                )

                ->where(
                    'status',
                    'active'
                )

                ->sum(
                    'quantity'
                );

            $availableQty =
                $stock->quantity
                - $reservedQty;

            if ($availableQty <= 0) {
                continue;
            }

            $allocateQty = min(
                $availableQty,
                $remainingQty
            );

            StockReservation::create([

                'order_id'   => $order->id,

                'stock_id'   => $stock->id,

                'quantity'   => $allocateQty,

                'status'     => 'active',

                'expires_at' => now()
                    ->addMinutes(30),

            ]);

            $remainingQty -= $allocateQty;
        }

        if ($remainingQty > 0) {

            throw ValidationException::withMessages([
                'stock' =>
                'Stok tidak mencukupi'
            ]);
        }
    }
    public function commitReservation(
        Order $order
    ): void {

        $reservations = StockReservation::query()

            ->where(
                'order_id',
                $order->id
            )

            ->where(
                'status',
                'active'
            )

            ->lockForUpdate()

            ->get();

        foreach ($reservations as $reservation) {

            $stock = Stock::lockForUpdate()
                ->findOrFail(
                    $reservation->stock_id
                );

            if (
                $stock->quantity <
                $reservation->quantity
            ) {
                throw ValidationException::withMessages([
                    'stock' => 'Stock integrity violation'
                ]);
            }

            $beforeQty = $stock->quantity;

            $transaction =
                Transaction::where(
                    'reference_number',
                    'COD-' . $order->order_number
                )
                ->orWhere(
                    'reference_number',
                    'MID-' . $order->order_number
                )
                ->first();

            if ($transaction) {

                $transactionItem =
                    TransactionItem::where(
                        'transaction_id',
                        $transaction->id
                    )
                    ->where(
                        'medicine_id',
                        $stock->medicine_id
                    )
                    ->first();
            }

             if ($transactionItem) {

                $this->fifoConsumptionService
                    ->consume(
                        $transactionItem,
                        $reservation->quantity
                    );
            }
            
            $stock->decrement(
                'quantity',
                $reservation->quantity
            );

            $this->mutationService
                ->record(

                    $stock,

                    'sale',

                    -$reservation->quantity,

                    Order::class,

                    $order->id,

                    'Penjualan Order #' .
                        $order->order_number,

                    $beforeQty

                );

            $reservation->update([
                'status' => 'committed'
            ]);
        }
    }
    public function releaseReservation(
        Order $order
    ): void {

        StockReservation::query()

            ->where(
                'order_id',
                $order->id
            )

            ->where(
                'status',
                'active'
            )

            ->update([
                'status' => 'released'
            ]);
    }
}
