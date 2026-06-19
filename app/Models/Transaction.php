<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'kasir_id',
        'customer_id',
        'total_amount',
        'discount_amount',
        'final_amount',
        'cash_received',
        'change_amount',
        'payment_method',
        'payment_status',
        'due_date',
        'notes'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | AUTO REFERENCE NUMBER
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {

            if ($transaction->reference_number) {
                return;
            }

            $today = now()->format('Ymd');

            $lastTransaction =
                self::whereDate(
                    'created_at',
                    today()
                )
                ->orderByDesc('id')
                ->first();

            $sequence = 1;

            if (
                $lastTransaction &&
                preg_match(
                    '/(\d+)$/',
                    $lastTransaction->reference_number,
                    $matches
                )
            ) {
                $sequence =
                    intval($matches[1]) + 1;
            }

            $transaction->reference_number =
                'TRX-' .
                $today .
                '-' .
                str_pad(
                    $sequence,
                    4,
                    '0',
                    STR_PAD_LEFT
                );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function kasir()
    {
        return $this->belongsTo(
            User::class,
            'kasir_id'
        );
    }

    public function customer()
    {
        return $this->belongsTo(
            User::class,
            'customer_id'
        );
    }

    public function items()
    {
        return $this->hasMany(
            TransactionItem::class
        );
    }
}
