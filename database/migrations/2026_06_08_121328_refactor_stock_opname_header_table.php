<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('stock_opname', function (Blueprint $table) {

            $table->dropForeign(['medicine_id']);

            $table->dropColumn([
                'medicine_id',
                'system_quantity',
                'physical_quantity',
                'difference'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_opname', function (Blueprint $table) {

            $table->foreignId('medicine_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('system_quantity')
                ->default(0);

            $table->integer('physical_quantity')
                ->default(0);

            $table->integer('difference')
                ->default(0);
        });
    }
};
