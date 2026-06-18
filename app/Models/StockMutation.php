<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMutation extends Model
{
    protected $fillable = [

        'stock_id',
        'type',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'reference_type',
        'reference_id',
        'notes',

    ];

    public function stock()
    {
        return $this->belongsTo(
            Stock::class
        );
    }
}