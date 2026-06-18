<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Stock;
use App\Models\Medicine;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Medicine::all() as $medicine) {

            Stock::create([
                'medicine_id'  => $medicine->id,
                'warehouse_id' => 1,
                'shelf_id'     => 1,
                'quantity'     => 100,
                'batch_number' => 'BATCH-'.$medicine->id,
                'expired_date' => now()->addYear(),
            ]);
        }
    }
}