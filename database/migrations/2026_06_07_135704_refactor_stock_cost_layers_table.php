<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_cost_layers', function (Blueprint $table) {

            $table->dropColumn([
                'transaction_item_id',
                'stock_id',
                'quantity',
                'total_cost'
            ]);

            $table->foreignId('medicine_id')
                ->after('id');

            $table->foreignId('purchase_item_id')
                ->nullable()
                ->after('medicine_id');

            $table->integer('quantity_received')
                ->after('purchase_item_id');

            $table->integer('quantity_remaining')
                ->after('quantity_received');
        });
    }

    public function down(): void
    {
        //
    }
};