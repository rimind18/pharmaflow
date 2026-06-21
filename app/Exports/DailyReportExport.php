<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class DailyReportExport implements
    FromArray,
    WithStyles,
    ShouldAutoSize
{
    protected $report;

    public function __construct($report)
    {
        $this->report = $report;
    }
 

    public function styles(Worksheet $sheet)
    {
        return [

            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                ],
            ],

            8 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
            ],

            9 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
        
    }

    public function array(): array
    {
            return [

            ['LAPORAN HARIAN PHARMAFLOW'],
            [],

            ['Total Transaksi', $this->report['summary']['transaction_count']],
            ['Total Penjualan', $this->report['summary']['total_sales']],
            ['Total Profit', $this->report['summary']['total_profit']],
            ['Margin (%)', $this->report['summary']['profit_margin']],
            ['Item Terjual', $this->report['summary']['total_items_sold']],
            ['Rata-rata Transaksi', $this->report['summary']['average_transaction']],
            [
            'Total Penjualan',
            'Rp' . number_format(
                $this->report['summary']['total_sales'],
                0,
                ',',
                '.'
            )
        ],
            [],
            ['TOP PRODUK'],

            ['Produk', 'Qty', 'Revenue'],

            ...collect($this->report['top_products'])
                ->map(fn($item) => [

                    $item['medicine']['name'] ?? '-',

                    $item['qty_sold'] ?? 0,

                    $item['revenue'] ?? 0,

                ])
                ->toArray(),
        ];
        
    }
}
