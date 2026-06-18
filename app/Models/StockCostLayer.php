<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCostLayer extends Model
{
    protected $fillable = [
        'medicine_id',
        'purchase_item_id',
        'quantity_received',
        'quantity_remaining',
        'unit_cost'
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2'
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItem::class);
    }
    public function usages()
    {
        return $this->hasMany(SalesBatchUsage::class);
    }
}
