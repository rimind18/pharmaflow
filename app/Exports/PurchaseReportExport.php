<?php

namespace App\Exports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PurchaseReportExport
implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function collection()
    {
        return Purchase::with('supplier')
            ->get()
            ->map(function ($purchase) {

                return [

                    'PO Number' =>
                    $purchase->po_number,

                    'Supplier' =>
                    $purchase->supplier->name ?? '-',

                    'Status' =>
                    $purchase->status,

                    'Total Amount' =>
                    $purchase->total_amount,

                    'Purchase Date' =>
                    $purchase->purchase_date,

                    'Received At' =>
                    $purchase->received_at,

                    'Items Total' =>
                    $purchase->items_total,
                ];
            });
    }

    public function headings(): array
    {
        return [

            'PO Number',
            'Supplier',
            'Status',
            'Total Amount',
            'Purchase Date',
            'Received At',
            'Items Total'
        ];
    }
}