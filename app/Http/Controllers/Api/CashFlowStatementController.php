<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cashflow;

class CashFlowStatementController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | OPERATING ACTIVITIES
        |--------------------------------------------------------------------------
        */

        $operatingCashIn =
            Cashflow::income()
            ->sum('amount');

        $operatingCashOut =
            Cashflow::expense()
            ->sum('amount');

        $netOperatingCash =
            $operatingCashIn
            - $operatingCashOut;

        /*
        |--------------------------------------------------------------------------
        | INVESTING ACTIVITIES
        |--------------------------------------------------------------------------
        */

        $investingCashIn = 0;

        $investingCashOut = 0;

        $netInvestingCash =
            $investingCashIn
            - $investingCashOut;

        /*
        |--------------------------------------------------------------------------
        | FINANCING ACTIVITIES
        |--------------------------------------------------------------------------
        */

        $ownerCapital =
            max(
                0,
                $operatingCashIn
                -
                $operatingCashOut
            );

        $withdrawal = 0;

        $netFinancingCash =
            $ownerCapital
            - $withdrawal;

        /*
        |--------------------------------------------------------------------------
        | TOTAL
        |--------------------------------------------------------------------------
        */

        $netCashChange =
            $netOperatingCash
            +
            $netInvestingCash
            +
            $netFinancingCash;

        return response()->json([

            'success' => true,

            'data' => [

                'operating' => [

                    'cash_in' =>
                        round(
                            $operatingCashIn,
                            2
                        ),

                    'cash_out' =>
                        round(
                            $operatingCashOut,
                            2
                        ),

                    'net_operating_cash' =>
                        round(
                            $netOperatingCash,
                            2
                        ),
                ],

                'investing' => [

                    'cash_in' =>
                        round(
                            $investingCashIn,
                            2
                        ),

                    'cash_out' =>
                        round(
                            $investingCashOut,
                            2
                        ),

                    'net_investing_cash' =>
                        round(
                            $netInvestingCash,
                            2
                        ),
                ],

                'financing' => [

                    'owner_capital' =>
                        round(
                            $ownerCapital,
                            2
                        ),

                    'withdrawal' =>
                        round(
                            $withdrawal,
                            2
                        ),

                    'net_financing_cash' =>
                        round(
                            $netFinancingCash,
                            2
                        ),
                ],

                'net_cash_change' =>
                    round(
                        $netCashChange,
                        2
                    ),

                'ending_cash_balance' =>
                    round(
                        $netOperatingCash,
                        2
                    ),
            ]
        ]);
    }
}