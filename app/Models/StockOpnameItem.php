<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpnameItem extends Model
{
    protected $fillable = [

        'stock_opname_id',

        'medicine_id',

        'system_quantity',

        'physical_quantity',

        'difference',

        'notes'
    ];

    public function stockOpname()
    {
        return $this->belongsTo(
            StockOpname::class
        );
    }

    public function medicine()
    {
        return $this->belongsTo(
            Medicine::class
        );
    }
}