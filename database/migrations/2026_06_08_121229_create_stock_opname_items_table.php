<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('stock_opname_items', function (Blueprint $table) {

        $table->id();

        $table->foreignId('stock_opname_id')
            ->constrained('stock_opname')
            ->cascadeOnDelete();

        $table->foreignId('medicine_id')
            ->constrained();

        $table->integer('system_quantity');

        $table->integer('physical_quantity');

        $table->integer('difference');

        $table->text('notes')->nullable();

        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('stock_opname_items');
}
};
