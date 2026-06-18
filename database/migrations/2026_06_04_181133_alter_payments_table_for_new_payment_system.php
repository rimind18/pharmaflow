<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {

            $table->string('payment_code')
                ->nullable()
                ->after('id');

            $table->unsignedBigInteger('order_id')
                ->nullable()
                ->after('payment_code');

            $table->string('transaction_id_gateway')
                ->nullable()
                ->after('transaction_id');

            $table->text('notes')
                ->nullable()
                ->after('status');

            $table->timestamp('paid_at')
                ->nullable()
                ->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {

            $table->dropColumn([
                'payment_code',
                'order_id',
                'transaction_id_gateway',
                'notes',
                'paid_at'
            ]);
        });
    }
};  