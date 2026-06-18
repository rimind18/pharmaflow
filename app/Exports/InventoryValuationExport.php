<?php

namespace App\Exports;

use App\Models\Medicine;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InventoryValuationExport implements
    FromCollection, WithHeadings, ShouldAutoSize
{
    public function collection()
    {
        return Medicine::with('stocks')
            ->get()
            ->map(function ($medicine) {

                $stock =
                    $medicine->stocks
                    ->sum('quantity');

                return [

                    $medicine->name,

                    $medicine->code,

                    $stock,

                    $medicine->base_price,

                    $stock *
                    $medicine->base_price,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Medicine',
            'Code',
            'Stock',
            'Base Price',
            'Inventory Value'
        ];
    }
    
}             