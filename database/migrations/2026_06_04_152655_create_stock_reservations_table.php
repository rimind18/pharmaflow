<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_reservations', function (
            Blueprint $table
        ) {

            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('stock_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('quantity');

            $table->enum('status', [
                'active',
                'released',
                'committed'
            ])->default('active');

            $table->timestamp('expires_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'stock_id',
                'status'
            ]);

            $table->index([
                'order_id',
                'status'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'stock_reservations'
        );
    }
};