<?php

namespace App\Exports;

use App\Models\TransactionItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProfitReportExport
implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function collection()
    {
        return TransactionItem::query()

            ->join(
                'medicines',
                'transaction_items.medicine_id',
                '=',
                'medicines.id'
            )

            ->selectRaw('
                medicines.name as medicine_name,

                SUM(transaction_items.quantity)
                    as qty_sold,

                SUM(transaction_items.subtotal)
                    as revenue,

                SUM(
                    medicines.base_price *
                    transaction_items.quantity
                ) as cost
            ')

            ->groupBy(
                'medicines.id',
                'medicines.name'
            )

            ->get()

            ->map(function ($item) {

                $profit =
                    $item->revenue -
                    $item->cost;

                $margin =
                    $item->revenue > 0
                    ? round(
                        ($profit / $item->revenue) * 100,
                        2
                    )
                    : 0;

                return [

                    'Medicine' =>
                    $item->medicine_name,

                    'Qty Sold' =>
                    $item->qty_sold,

                    'Revenue' =>
                    $item->revenue,

                    'Cost' =>
                    $item->cost,

                    'Profit' =>
                    $profit,

                    'Margin %' =>
                    $margin
                ];
            });
    }

    public function headings(): array
    {
        return [

            'Medicine',
            'Qty Sold',
            'Revenue',
            'Cost',
            'Profit',
            'Margin %'
        ];
    }
}