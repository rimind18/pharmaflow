<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesBatchUsage extends Model
{
    protected $fillable = [

        'transaction_item_id',

        'stock_cost_layer_id',

        'quantity',

        'unit_cost',

        'total_cost'
    ];

    protected $casts = [

        'unit_cost' => 'decimal:2',

        'total_cost' => 'decimal:2'
    ];

    public function transactionItem()
    {
        return $this->belongsTo(
            TransactionItem::class
        );
    }

    public function stockCostLayer()
    {
        return $this->belongsTo(
            StockCostLayer::class
        );
    }
}