<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockReservation;

class StockService
{
    public function availableStock(
        int $stockId
    ): int {

        $stock = Stock::findOrFail(
            $stockId
        );

        $reserved = StockReservation::query()

            ->where(
                'stock_id',
                $stockId
            )

            ->where(
                'status',
                'active'
            )

            ->sum(
                'quantity'
            );

        return max(
            0,
            $stock->quantity - $reserved
        );
    }
}