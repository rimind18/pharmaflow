<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockReservation extends Model
{
    protected $fillable = [

        'order_id',
        'stock_id',
        'quantity',
        'status',
        'expires_at',

    ];

    protected $casts = [

        'expires_at' => 'datetime',

    ];

    public function order()
    {
        return $this->belongsTo(
            Order::class
        );
    }

    public function stock()
    {
        return $this->belongsTo(
            Stock::class
        );
    }
}