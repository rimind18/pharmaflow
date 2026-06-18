<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'po_number',
        'supplier_id',
        'subtotal',
        'tax_amount',
        'shipping_cost',
        'total_amount',
        'purchase_date',
        'expected_delivery_date',
        'status',
        'notes',

        'items_total',
        'items_received',

        'received_at',
        'received_by',

        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'expected_delivery_date' => 'date',

        'received_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function approvedByUser()
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function cashflow(): BelongsTo
    {
        return $this->belongsTo(Cashflow::class);
    }
}
