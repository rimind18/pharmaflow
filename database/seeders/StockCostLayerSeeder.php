<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medicine;
use App\Models\StockCostLayer;

class StockCostLayerSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Medicine::all() as $medicine) {

            StockCostLayer::create([
                'medicine_id' => $medicine->id,

                // karena data seed
                'purchase_item_id' => null,

                'quantity_received' => 100,
                'quantity_remaining' => 100,

                'unit_cost' => $medicine->base_price,
            ]);
        }
    }
}