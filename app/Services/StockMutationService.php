<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockMutation;

class StockMutationService
{
    public function record(
        Stock $stock,
        string $type,
        int $change,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null,
        ?int $beforeQty = null
    ): void {

        $before =
            $beforeQty
            ?? $stock->quantity;

        $after =
            $before + $change;

        StockMutation::create([

            'stock_id' => $stock->id,

            'type' => $type,

            'quantity_before' =>
            $before,

            'quantity_change' =>
            $change,

            'quantity_after' =>
            $after,

            'reference_type' =>
            $referenceType,

            'reference_id' =>
            $referenceId,

            'notes' =>
            $notes,

        ]);
    }
}
