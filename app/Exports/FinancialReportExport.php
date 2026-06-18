<?php

namespace App\Exports;

use App\Models\Cashflow;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FinancialReportExport implements
    FromCollection, ShouldAutoSize
{
    public function collection()
    {
        $revenue =
            Cashflow::where(
                'type',
                'income'
            )->sum('amount');

        $expense =
            Cashflow::where(
                'type',
                'expense'
            )->sum('amount');

        $profit =
            $revenue - $expense;

        return collect([

            [
                'Metric' => 'Total Revenue',
                'Value' => $revenue
            ],

            [
                'Metric' => 'Total Expense',
                'Value' => $expense
            ],

            [
                'Metric' => 'Net Profit',
                'Value' => $profit
            ],

            [
                'Metric' => 'Cash Balance',
                'Value' => $profit
            ]
        ]);
    }
}