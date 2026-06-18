<?php

namespace App\Services;

use App\Models\StockCostLayer;
use App\Models\SalesBatchUsage;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;

class FIFOConsumptionService
{
    public function consume(
        TransactionItem $transactionItem,
        int $quantityNeeded
    ) {
        DB::beginTransaction();

        try {

            $layers = StockCostLayer::where(
                'medicine_id',
                $transactionItem->medicine_id
            )
                ->where('quantity_remaining', '>', 0)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $remaining = $quantityNeeded;

            foreach ($layers as $layer) {

                if ($remaining <= 0) {
                    break;
                }

                $take = min(
                    $remaining,
                    $layer->quantity_remaining
                );

                SalesBatchUsage::create([
                    'transaction_item_id' => $transactionItem->id,
                    'stock_cost_layer_id' => $layer->id,
                    'quantity' => $take,
                    'unit_cost' => $layer->unit_cost,
                    'total_cost' => $take * $layer->unit_cost,
                ]);
                
                $layer->decrement(
                    'quantity_remaining',
                    $take
                );

                $remaining -= $take;
            }

            if ($remaining > 0) {
                throw new \Exception(
                    'Insufficient FIFO stock layer'
                );
            }

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }
}
