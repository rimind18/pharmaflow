<?php

namespace App\Services;

use App\Models\StockCostLayer;

class StockCostLayerService
{
    public function createLayer(
        int $medicineId,
        ?int $purchaseItemId,
        int $qty,
        float $unitCost
    ): StockCostLayer {

        return StockCostLayer::create([
            'medicine_id' => $medicineId,
            'purchase_item_id' => $purchaseItemId,
            'quantity_received' => $qty,
            'quantity_remaining' => $qty,
            'unit_cost' => $unitCost
        ]);
    }
}