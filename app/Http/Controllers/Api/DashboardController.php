<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\Medicine;
use App\Models\Stock;
use App\Models\Cashflow;
use App\Models\StockCostLayer;
use App\Models\SalesBatchUsage;
use App\Models\Supplier;
use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\InventoryValuationExport;
use App\Exports\FinancialReportExport;
use Maatwebsite\Excel\Facades\Excel;




class DashboardController extends Controller
{
    /**
     * Dashboard Summary (Overview)
     */
    public function summary(Request $request)
    {
        try {
            $startDate = Carbon::parse(
                $request->get(
                    'start_date',
                    now()->startOfMonth()
                )
            )->startOfDay();

            $endDate = Carbon::parse(
                $request->get(
                    'end_date',
                    now()
                )
            )->endOfDay();

            // Sales Data
            $totalSales = Transaction::whereBetween('created_at', [$startDate, $endDate])
                ->sum('final_amount');

            $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', '!=', 'dibatalkan')
                ->count();

            $totalRevenue =
                Cashflow::where(
                    'type',
                    'income'
                )
                ->whereBetween(
                    'transaction_date',
                    [$startDate, $endDate]
                )
                ->sum('amount');

            // Inventory Data
            $totalMedicines = Medicine::where('status', 'aktif')->count();
            $totalStock = Stock::sum('quantity');
            $lowStockCount = Medicine::whereRaw('(SELECT COALESCE(SUM(quantity), 0) FROM stocks WHERE medicine_id = medicines.id) <= stock_minimum')
                ->count();
            $expiredCount = Stock::whereDate('expired_date', '<=', now())
                ->where('quantity', '>', 0)
                ->count();

            // Expenses Data
            $totalExpenses =
                Cashflow::where(
                    'type',
                    'expense'
                )
                ->whereBetween(
                    'transaction_date',
                    [$startDate, $endDate]
                )
                ->sum('amount');

            $profit = $totalRevenue - $totalExpenses;
            $profitMargin = $totalRevenue > 0 ? ($profit / $totalRevenue) * 100 : 0;

            // Transaction Summary
            $cashTransactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
                ->where('payment_status', 'lunas')
                ->sum('final_amount');

            $creditTransactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
                ->where('payment_status', 'kredit')
                ->sum('final_amount');

            // Pending Orders
            $pendingOrders = Order::where('status', 'pending')->count();
            $inProcessOrders = Order::where('status', 'diproses')->count();
            $inTransitOrders = Order::where('status', 'dikirim')->count();

            return response()->json([
                'message' => 'Dashboard summary berhasil diambil',
                'data' => [
                    'period' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ],
                    'sales' => [
                        'total_sales' => (float) $totalSales,
                        'total_orders' => $totalOrders,
                        'total_revenue' => (float) $totalRevenue,
                        'cash_transactions' => (float) $cashTransactions,
                        'credit_transactions' => (float) $creditTransactions,
                    ],
                    'inventory' => [
                        'total_medicines' => $totalMedicines,
                        'total_stock' => $totalStock,
                        'low_stock_count' => $lowStockCount,
                        'expired_count' => $expiredCount,
                    ],
                    'financial' => [
                        'total_expenses' => (float) $totalExpenses,
                        'profit' => (float) $profit,
                        'profit_margin' => round($profitMargin, 2) . '%',
                    ],
                    'orders' => [
                        'pending' => $pendingOrders,
                        'in_process' => $inProcessOrders,
                        'in_transit' => $inTransitOrders,
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sales Report
     */
    public function salesReport(Request $request)
    {
        try {
            $period = $request->get('period', 'daily'); // daily, weekly, monthly
            $startDate = Carbon::parse(
                $request->get(
                    'start_date',
                    now()->startOfMonth()
                )
            )->startOfDay();

            $endDate = Carbon::parse(
                $request->get(
                    'end_date',
                    now()
                )
            )->endOfDay();

            $query = Transaction::whereBetween('created_at', [$startDate, $endDate]);

            if ($period === 'daily') {
                $data = $query->selectRaw('DATE(created_at) as date, COUNT(*) as transaction_count, SUM(final_amount) as total_sales, SUM(discount_amount) as total_discount')
                    ->groupBy('date')
                    ->orderBy('date', 'asc')
                    ->get();
            } elseif ($period === 'weekly') {
                $data = $query->selectRaw('WEEK(created_at) as week, YEAR(created_at) as year, COUNT(*) as transaction_count, SUM(final_amount) as total_sales, SUM(discount_amount) as total_discount')
                    ->groupBy('week', 'year')
                    ->orderBy('year', 'asc')
                    ->orderBy('week', 'asc')
                    ->get();
            } else {
                $data = $query->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as transaction_count, SUM(final_amount) as total_sales, SUM(discount_amount) as total_discount')
                    ->groupBy('month', 'year')
                    ->orderBy('year', 'asc')
                    ->orderBy('month', 'asc')
                    ->get();
            }

            // Top products by sales
            $topProducts = \App\Models\TransactionItem::whereIn(
                'transaction_id',
                Transaction::whereBetween('created_at', [$startDate, $endDate])
                    ->pluck('id')
            )
                ->with('medicine')
                ->get()
                ->groupBy('medicine_id')
                ->map(function ($items) {
                    return [
                        'medicine' => $items[0]->medicine,
                        'total_quantity' => $items->sum('quantity'),
                        'total_revenue' => $items->sum('subtotal')
                    ];
                })
                ->sortByDesc('total_revenue')
                ->values()
                ->take(10);

            // Sales by payment status
            $byPaymentStatus = Transaction::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('payment_status, COUNT(*) as count, SUM(final_amount) as total')
                ->groupBy('payment_status')
                ->get();

            // Sales by staff
            $byStaff = Transaction::whereBetween(
                'created_at',
                [$startDate, $endDate]
            )
                ->with('kasir')
                ->get()
                ->groupBy('kasir_id')
                ->map(function ($transactions) {

                    return [
                        'staff' =>
                        $transactions[0]->kasir,

                        'transaction_count' =>
                        $transactions->count(),

                        'total_sales' =>
                        $transactions->sum(
                            'final_amount'
                        ),

                        'total_discount' =>
                        $transactions->sum(
                            'discount_amount'
                        ),
                    ];
                })
                ->sortByDesc('total_sales')
                ->values();

            return response()->json([
                'message' => 'Sales report berhasil diambil',
                'data' => [
                    'period' => $period,
                    'range' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ],
                    'chart_data' => $data,
                    'top_products' => $topProducts,
                    'by_payment_status' => $byPaymentStatus,
                    'by_staff' => $byStaff,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Profit Report
     */
    public function profitReport(Request $request)
    {
        try {
            $period = $request->get('period', 'daily');
            $startDate = Carbon::parse(
                $request->get(
                    'start_date',
                    now()->startOfMonth()
                )
            )->startOfDay();

            $endDate = Carbon::parse(
                $request->get(
                    'end_date',
                    now()
                )
            )->endOfDay();

            // Get revenue
            $revenueBase = Transaction::whereBetween(
                'created_at',
                [$startDate, $endDate]
            );
            $ordersQuery = Order::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', '!=', 'dibatalkan');

            // Get expenses
            $expenseQuery =
                Cashflow::where(
                    'type',
                    'expense'
                )
                ->whereBetween(
                    'transaction_date',
                    [$startDate, $endDate]
                );

            if ($period === 'daily') {
                $revenue = (clone $revenueBase)
                    ->selectRaw('DATE(created_at) as date,
                 SUM(final_amount) as total')
                    ->groupBy(DB::raw('DATE(created_at)'))
                    ->pluck('total', 'date');

                $ordersRevenue = (clone $ordersQuery)
                    ->selectRaw(
                        'DATE(created_at) as date,
                          SUM(total_amount) as total'
                    )
                    ->groupBy(DB::raw('DATE(created_at)'))
                    ->pluck('total', 'date');

                $expenses = (clone $expenseQuery)
                    ->selectRaw(
                        'DATE(transaction_date) as date,
                         SUM(amount) as total'
                    )
                    ->groupBy(DB::raw('DATE(transaction_date)'))
                    ->pluck('total', 'date');
            } elseif ($period === 'weekly') {
                $revenue = (clone $revenueBase)
                    ->selectRaw('WEEK(created_at) as week,
                 YEAR(created_at) as year,
                 SUM(final_amount) as total')
                    ->groupBy('week', 'year')
                    ->get();

                $ordersRevenue = (clone $ordersQuery)
                    ->selectRaw(
                        'WEEK(created_at) as week,
                        YEAR(created_at) as year,
                        SUM(total_amount) as total'
                    )
                    ->groupBy('week', 'year')
                    ->get();

                $expenses = (clone $expenseQuery)
                    ->selectRaw(
                        'WEEK(transaction_date) as week,
                        YEAR(transaction_date) as year,
                        SUM(amount) as total'
                    )
                    ->groupBy('week', 'year')
                    ->get();
            } else {
                $revenue = (clone $revenueBase)
                    ->selectRaw('MONTH(created_at) as month,
                 YEAR(created_at) as year,
                 SUM(final_amount) as total')
                    ->groupBy('month', 'year')
                    ->get();

                $ordersRevenue = (clone $ordersQuery)
                    ->selectRaw(
                        'MONTH(created_at) as month,
                        YEAR(created_at) as year,
                        SUM(total_amount) as total'
                    )
                    ->groupBy('month', 'year')
                    ->get();

                $expenses = (clone $expenseQuery)
                    ->selectRaw(
                        'MONTH(transaction_date) as month,
                        YEAR(transaction_date) as year,
                        SUM(amount) as total'
                    )
                    ->groupBy('month', 'year')
                    ->get();
            }

            $totalRevenue =
                Cashflow::where(
                    'type',
                    'income'
                )
                ->whereBetween(
                    'transaction_date',
                    [$startDate, $endDate]
                )
                ->sum('amount');
            $totalExpenses = (clone $expenseQuery)->sum('amount');
            $totalProfit = $totalRevenue - $totalExpenses;
            $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

            // Profit by category
            $profitByCategory = \App\Models\TransactionItem::whereIn(
                'transaction_id',
                Transaction::whereBetween('created_at', [$startDate, $endDate])
                    ->pluck('id')
            )
                ->with('medicine.category')
                ->get()
                ->groupBy('medicine.category_id')
                ->map(function ($items) {
                    return [
                        'category' => $items[0]->medicine->category->name,
                        'total_revenue' => $items->sum('subtotal'),
                        'quantity_sold' => $items->sum('quantity')
                    ];
                })
                ->sortByDesc('total_revenue')
                ->values();

            return response()->json([
                'message' => 'Profit report berhasil diambil',
                'data' => [
                    'period' => $period,
                    'range' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ],
                    'summary' => [
                        'total_revenue' => (float) $totalRevenue,
                        'total_expenses' => (float) $totalExpenses,
                        'total_profit' => (float) $totalProfit,
                        'profit_margin' => round($profitMargin, 2) . '%',
                    ],
                    'by_category' => $profitByCategory,
                    'chart_data' => [
                        'revenue' => $revenue,
                        'expenses' => $expenses,
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Inventory Report
     */
    public function inventoryReport(Request $request)
    {
        try {
            $warehouseId = $request->get('warehouse_id');

            $query = Stock::with('medicine', 'warehouse');

            if ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            }

            $stocks = $query->get();

            // Low stock items
            $lowStockItems = $stocks->filter(function ($stock) {
                return $stock->quantity <= $stock->medicine->stock_minimum;
            })->sortBy('quantity');

            // Expired items
            $expiredItems = $stocks->filter(function ($stock) {
                return $stock->expired_date && $stock->expired_date->isPast() && $stock->quantity > 0;
            });

            // Stock by category
            $stockByCategory = $stocks->groupBy('medicine.category_id')
                ->map(function ($categoryStocks) {
                    return [
                        'category' => $categoryStocks[0]->medicine->category->name,
                        'total_quantity' => $categoryStocks->sum('quantity'),
                        'total_value' => $categoryStocks->sum(function ($stock) {
                            return $stock->quantity * $stock->medicine->base_price;
                        })
                    ];
                })
                ->sortByDesc('total_value')
                ->values();

            // Stock value
            $totalStockValue = $stocks->sum(function ($stock) {
                return $stock->quantity * $stock->medicine->base_price;
            });

            return response()->json([
                'message' => 'Inventory report berhasil diambil',
                'data' => [
                    'summary' => [
                        'total_items' => $stocks->count(),
                        'total_quantity' => $stocks->sum('quantity'),
                        'total_value' => (float) $totalStockValue,
                        'low_stock_count' => $lowStockItems->count(),
                        'expired_count' => $expiredItems->count(),
                    ],
                    'low_stock_items' => $lowStockItems->values(),
                    'expired_items' => $expiredItems->values(),
                    'by_category' => $stockByCategory,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Financial Report (Cashflow)
     */
    public function financialReport(Request $request)
    {
        try {
            $startDate = Carbon::parse(
                $request->get(
                    'start_date',
                    now()->startOfMonth()
                )
            )->startOfDay();

            $endDate = Carbon::parse(
                $request->get(
                    'end_date',
                    now()
                )
            )->endOfDay();

            // Income
            $income = Cashflow::where('type', 'income')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->selectRaw('category, SUM(amount) as total')
                ->groupBy('category')
                ->get();

            // Expenses
            $expenses = Cashflow::where('type', 'expense')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->selectRaw('category, SUM(amount) as total')
                ->groupBy('category')
                ->get();

            $totalIncome = $income->sum('total');
            $totalExpenses = $expenses->sum('total');
            $netIncome = $totalIncome - $totalExpenses;

            // Daily cashflow
            $dailyCashflow = Cashflow::whereBetween('transaction_date', [$startDate, $endDate])
                ->selectRaw('DATE(transaction_date) as date, type, SUM(amount) as total')
                ->groupBy(DB::raw('DATE(transaction_date)'), 'type')
                ->orderBy('date', 'asc')
                ->get();

            return response()->json([
                'message' => 'Financial report berhasil diambil',
                'data' => [
                    'range' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ],
                    'summary' => [
                        'total_income' => (float) $totalIncome,
                        'total_expenses' => (float) $totalExpenses,
                        'net_income' => (float) $netIncome,
                    ],
                    'income_by_category' => $income,
                    'expenses_by_category' => $expenses,
                    'daily_cashflow' => $dailyCashflow,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cashflow Report
     */
    public function cashflowReport(Request $request)
    {
        try {

            $startDate = Carbon::parse(
                $request->get(
                    'start_date',
                    now()->startOfMonth()
                )
            )->startOfDay();

            $endDate = Carbon::parse(
                $request->get(
                    'end_date',
                    now()
                )
            )->endOfDay();

            $cashflows = Cashflow::whereBetween('transaction_date', [$startDate, $endDate])
                ->orderBy('transaction_date', 'asc')
                ->get();

            // Calculate running balance
            $openingBalance =
                Cashflow::whereDate(
                    'transaction_date',
                    '<',
                    $startDate
                )
                ->where('type', 'income')
                ->sum('amount')

                -

                Cashflow::whereDate(
                    'transaction_date',
                    '<',
                    $startDate
                )
                ->where('type', 'expense')
                ->sum('amount');

            $balance = $openingBalance;
            $cashflows = $cashflows->map(function ($cf) use (&$balance) {
                if ($cf->type === 'income') {
                    $balance += $cf->amount;
                } else {
                    $balance -= $cf->amount;
                }
                $cf->balance = $balance;
                return $cf;
            });

            return response()->json([
                'message' => 'Cashflow report berhasil diambil',
                'data' => [
                    'range' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ],
                    'opening_balance' => (float) $openingBalance,
                    'closing_balance' => (float) $balance,
                    'cashflows' => $cashflows,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Product Performance Report
     */
    public function productPerformance(Request $request)
    {
        try {
            $startDate = Carbon::parse(
                $request->get(
                    'start_date',
                    now()->startOfMonth()
                )
            )->startOfDay();

            $endDate = Carbon::parse(
                $request->get(
                    'end_date',
                    now()
                )
            )->endOfDay();
            $limit = $request->get('limit', 10);

            // Top selling products
            $topProducts = \App\Models\TransactionItem::whereIn(
                'transaction_id',
                Transaction::whereBetween('created_at', [$startDate, $endDate])
                    ->pluck('id')
            )
                ->with('medicine')
                ->get()
                ->groupBy('medicine_id')
                ->map(function ($items) {
                    return [
                        'medicine' => $items[0]->medicine,
                        'total_quantity' => $items->sum('quantity'),
                        'total_revenue' => $items->sum('subtotal'),
                        'transaction_count' => $items->count(),
                    ];
                })
                ->sortByDesc('total_revenue')
                ->values()
                ->take($limit);

            // Slowest moving products
            $slowProducts = Medicine::where('status', 'aktif')
                ->with([
                    'transactionItems' => function ($q) use ($startDate, $endDate) {
                        $q->whereHas('transaction', function ($trx) use ($startDate, $endDate) {
                            $trx->whereBetween('created_at', [$startDate, $endDate]);
                        });
                    }
                ])
                ->get()
                ->filter(function ($medicine) {
                    return $medicine->transactionItems->count() < 5;
                })
                ->map(function ($medicine) {
                    return [
                        'medicine' => $medicine,
                        'sold_quantity' => $medicine->transactionItems->sum('quantity'),
                        'transaction_count' => $medicine->transactionItems->count(),
                    ];
                })
                ->sortBy('transaction_count')
                ->values()
                ->take($limit);

            return response()->json([
                'message' => 'Product performance report berhasil diambil',
                'data' => [
                    'range' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ],
                    'top_products' => $topProducts,
                    'slow_products' => $slowProducts,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analytics (Advanced metrics)
     */
    public function analytics(Request $request)
    {
        try {
            $startDate = Carbon::parse(
                $request->get(
                    'start_date',
                    now()->startOfMonth()
                )
            )->startOfDay();

            $endDate = Carbon::parse(
                $request->get(
                    'end_date',
                    now()
                )
            )->endOfDay();

            // Customer metrics
            $newCustomers = \App\Models\User::where('role', 'customer')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $repeatCustomers = Order::whereBetween('created_at', [$startDate, $endDate])
                ->select('customer_id')
                ->groupBy('customer_id')
                ->havingRaw('COUNT(*) > 1')
                ->get()
                ->count();

            $customerLifetimeValue = Order::with('customer')
                ->where('status', '!=', 'dibatalkan')
                ->get()
                ->groupBy('customer_id')
                ->map(function ($orders) {
                    return [
                        'customer' => $orders[0]->customer,
                        'total_spent' => $orders->sum('total_amount'),
                        'order_count' => $orders->count(),
                    ];
                })
                ->sortByDesc('total_spent')
                ->values()
                ->take(10);

            // Product turnover
            $productTurnover = Medicine::where('status', 'aktif')
                ->with('transactionItems', 'stocks')
                ->get()
                ->map(function ($medicine) {
                    $totalSold = $medicine->transactionItems->sum('quantity');
                    $currentStock = $medicine->stocks->sum('quantity');
                    $turnoverRate = $totalSold > 0 ? ($currentStock / ($totalSold / 30)) : 0; // days
                    return [
                        'medicine' => $medicine,
                        'total_sold' => $totalSold,
                        'current_stock' => $currentStock,
                        'turnover_days' => round($turnoverRate, 2),
                    ];
                })
                ->sortBy('turnover_days')
                ->values()
                ->take(10);

            return response()->json([
                'message' => 'Analytics berhasil diambil',
                'data' => [
                    'range' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ],
                    'customer_metrics' => [
                        'new_customers' => $newCustomers,
                        'repeat_customers' => $repeatCustomers,
                    ],
                    'customer_lifetime_value' => $customerLifetimeValue,
                    'product_turnover' => $productTurnover,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * OWNER KPI
     */
    public function kpi(Request $request)
    {
        try {

            $startDate = Carbon::parse(
                $request->get(
                    'start_date',
                    now()->startOfMonth()
                )
            )->startOfDay();

            $endDate = Carbon::parse(
                $request->get(
                    'end_date',
                    now()
                )
            )->endOfDay();

            $totalRevenue = Cashflow::where('type', 'income')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('amount');

            $totalExpenses = Cashflow::where('type', 'expense')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('amount');

            $profit = $totalRevenue - $totalExpenses;

            $transactionCount =
                Transaction::whereBetween(
                    'created_at',
                    [$startDate, $endDate]
                )->count()

                +

                Order::whereBetween(
                    'created_at',
                    [$startDate, $endDate]
                )
                ->where(
                    'status',
                    '!=',
                    'dibatalkan'
                )
                ->count();

            $lowStock =
                Stock::lowStock()
                ->count();

            $expiredMedicine =
                Stock::expired()
                ->count();

            $pendingOrder =
                Order::where(
                    'status',
                    'pending'
                )->count();

            return response()->json([

                'success' => true,

                'data' => [

                    'revenue' =>
                    $totalRevenue,

                    'expense' =>
                    $totalExpenses,

                    'profit' =>
                    $profit,

                    'transaction_count' =>
                    $transactionCount,

                    'low_stock' =>
                    $lowStock,

                    'expired_medicine' =>
                    $expiredMedicine,

                    'pending_order' =>
                    $pendingOrder
                ]
            ]);
        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                $e->getMessage()

            ], 500);
        }
    }

    /**
     * DASHBOARD CHARTS
     */
    public function charts(Request $request)
    {
        try {

            $days =
                $request->get(
                    'days',
                    7
                );

            $data = Cashflow::selectRaw(
                'DATE(transaction_date) as date,
                 SUM(amount) as revenue'
            )
                ->where('type', 'income')
                ->whereDate(
                    'transaction_date',
                    '>=',
                    now()->subDays($days)
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return response()->json([

                'success' => true,

                'data' =>
                $data

            ]);
        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                $e->getMessage()

            ], 500);
        }
    }

    /**
     * TOP MEDICINES
     */
    public function topMedicines()
    {
        try {

            $data =
                \App\Models\TransactionItem::select(
                    'medicine_id',
                    DB::raw(
                        'SUM(quantity)
                    as total_sold'
                    ),
                    DB::raw(
                        'SUM(subtotal)
                    as revenue'
                    )
                )
                ->with('medicine')
                ->groupBy(
                    'medicine_id'
                )
                ->orderByDesc(
                    'total_sold'
                )
                ->take(10)
                ->get();

            return response()->json([

                'success' => true,

                'data' =>
                $data

            ]);
        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                $e->getMessage()

            ], 500);
        }
    }

    /**
     * WARNING SYSTEM
     */
    public function warnings()
    {
        try {

            $lowStock = Stock::with('medicine')
                ->lowStock()
                ->get();

            $expired = Stock::with('medicine')
                ->expired()
                ->get();

            $expiring30 = Stock::with('medicine')
                ->expiringInDays(30)
                ->get();

            $expiring60 = Stock::with('medicine')
                ->expiringInDays(60)
                ->get();

            $expiring90 = Stock::with('medicine')
                ->expiringInDays(90)
                ->get();

            return response()->json([

                'success' => true,

                'data' => [

                    'summary' => [

                        'low_stock_count' =>
                        $lowStock->count(),

                        'expired_count' =>
                        $expired->count(),

                        'expiring_30_days_count' =>
                        $expiring30->count(),

                        'expiring_60_days_count' =>
                        $expiring60->count(),

                        'expiring_90_days_count' =>
                        $expiring90->count(),
                    ],

                    'low_stock' =>
                    $lowStock,

                    'expired' =>
                    $expired,

                    'expiring_30_days' =>
                    $expiring30,

                    'expiring_60_days' =>
                    $expiring60,

                    'expiring_90_days' =>
                    $expiring90,
                ]
            ]);
        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                $e->getMessage()

            ], 500);
        }
    }
    /**
     * OWNER ANALYTICS
     */
    public function analyticsOwner()
    {
        try {

            $bestStaff =
                Transaction::select(
                    'kasir_id',
                    DB::raw(
                        'COUNT(*) as total_transaction'
                    ),
                    DB::raw(
                        'SUM(final_amount)
                    as revenue'
                    )
                )
                ->with('kasir')
                ->groupBy(
                    'kasir_id'
                )
                ->orderByDesc(
                    'revenue'
                )
                ->take(5)
                ->get();

            $averageTransaction =
                Transaction::avg(
                    'final_amount'
                );

            $busyHours =
                Transaction::selectRaw(
                    'HOUR(created_at)
                as hour,
                COUNT(*) as total'
                )
                ->groupBy('hour')
                ->orderByDesc(
                    'total'
                )
                ->take(5)
                ->get();

            return response()->json([

                'success' => true,

                'data' => [

                    'best_staff' =>
                    $bestStaff,

                    'average_transaction' =>
                    $averageTransaction,

                    'busy_hours' =>
                    $busyHours
                ]
            ]);
        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                $e->getMessage()

            ], 500);
        }
    }
    /**
     * AUTO REORDER RECOMMENDATION
     */
    public function reorderRecommendation(Request $request)
    {
        try {

            $days =
                $request->get(
                    'days',
                    30
                );

            $targetDays =
                $request->get(
                    'target_days',
                    30
                );

            $medicines =
                Medicine::with([
                    'supplier',
                    'stocks',
                    'transactionItems'
                ])
                ->where('status', 'aktif')
                ->get();

            $recommendations =
                $medicines->map(function ($medicine)
                use (
                    $days,
                    $targetDays
                ) {

                    $currentStock =
                        $medicine->stocks
                        ->sum('quantity');

                    $soldQty =
                        TransactionItem::where(
                            'medicine_id',
                            $medicine->id
                        )
                        ->whereHas(
                            'transaction',
                            function ($q) use ($days) {

                                $q->where(
                                    'created_at',
                                    '>=',
                                    now()->subDays(
                                        $days
                                    )
                                );
                            }
                        )
                        ->sum('quantity');

                    $averageDailySales =
                        $days > 0
                        ? round(
                            $soldQty / $days,
                            2
                        )
                        : 0;

                    $daysRemaining =
                        $averageDailySales > 0
                        ? round(
                            $currentStock /
                                $averageDailySales,
                            2
                        )
                        : 999;

                    $recommendedOrder = max(
                        0,
                        ceil(
                            (
                                $averageDailySales *
                                $targetDays
                            )
                                -
                                $currentStock
                        )
                    );

                    if (
                        $currentStock <=
                        $medicine->stock_minimum
                    ) {

                        $recommendedOrder =
                            max(
                                $recommendedOrder,
                                (
                                    $medicine->stock_minimum
                                    * 2
                                )
                                    -
                                    $currentStock
                            );
                    }

                    if ($currentStock <= 0) {

                        $priority = 'HIGH';
                    } elseif (
                        $currentStock <=
                        $medicine->stock_minimum
                    ) {

                        $priority = 'MEDIUM';
                    } elseif ($daysRemaining <= 7) {

                        $priority = 'HIGH';
                    } elseif ($daysRemaining <= 14) {

                        $priority = 'MEDIUM';
                    } else {

                        $priority = 'LOW';
                    }

                    return [

                        'medicine_id' =>
                        $medicine->id,

                        'medicine_code' =>
                        $medicine->code,

                        'medicine_name' =>
                        $medicine->name,

                        'current_stock' =>
                        $currentStock,

                        'stock_minimum' =>
                        $medicine->stock_minimum,

                        'average_daily_sales' =>
                        $averageDailySales,

                        'days_remaining' =>
                        $daysRemaining,

                        'recommended_order' =>
                        $recommendedOrder,

                        'supplier_id' =>
                        $medicine->supplier_id,

                        'supplier_name' =>
                        $medicine->supplier?->name,


                        'estimated_cost' =>
                        $recommendedOrder *
                            $medicine->base_price,

                        'priority' =>
                        $priority,
                    ];
                })

                ->filter(function ($item) {

                    return
                        $item['recommended_order'] > 0
                        ||
                        $item['current_stock']
                        <=
                        $item['stock_minimum'];
                })

                ->sortByDesc(function ($item) {

                    return match ($item['priority']) {
                        'HIGH' => 3,
                        'MEDIUM' => 2,
                        default => 1,
                    };
                })
                ->values();

            return response()->json([

                'success' => true,

                'data' =>
                $recommendations

            ]);
        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                $e->getMessage()

            ], 500);
        }
    }
    public function executive()
    {
        try {

            /*
        |--------------------------------------------------------------------------
        | TOTAL SALES
        |--------------------------------------------------------------------------
        */

            $totalSales =
                Transaction::sum(
                    'final_amount'
                );

            /*
        |--------------------------------------------------------------------------
        | TOTAL PURCHASE
        |--------------------------------------------------------------------------
        */

            $totalPurchases =
                Purchase::sum(
                    'total_amount'
                );

            /*
        |--------------------------------------------------------------------------
        | INVENTORY VALUE
        |--------------------------------------------------------------------------
        */

            $inventoryValue =
                StockCostLayer::selectRaw(
                    '
        SUM(
            quantity_remaining *
            unit_cost
        ) as inventory_value
        '
                )
                ->value('inventory_value') ?? 0;
            /*
        |--------------------------------------------------------------------------
        | CASH BALANCE
        |--------------------------------------------------------------------------
        */

            $cashIn =
                Cashflow::where(
                    'type',
                    'income'
                )->sum('amount');

            $cashOut =
                Cashflow::where(
                    'type',
                    'expense'
                )->sum('amount');

            $cashBalance =
                $cashIn - $cashOut;

            /*
        |--------------------------------------------------------------------------
        | NET PROFIT
        |--------------------------------------------------------------------------
        */

            $cost =
                SalesBatchUsage::sum(
                    'total_cost'
                );
            $netProfit =
                $totalSales -
                ($cost ?? 0);

            /*
        |--------------------------------------------------------------------------
        | TOP PRODUCT
        |--------------------------------------------------------------------------
        */

            $topProduct =
                TransactionItem::selectRaw(
                    '
                medicine_id,
                SUM(quantity) as qty
                '
                )
                ->with('medicine')
                ->groupBy(
                    'medicine_id'
                )
                ->orderByDesc('qty')
                ->first();

            /*
        |--------------------------------------------------------------------------
        | TOP SUPPLIER
        |--------------------------------------------------------------------------
        */

            $topSupplier =
                Purchase::selectRaw(
                    '
                supplier_id,
                SUM(total_amount)
                as total
                '
                )
                ->with('supplier')
                ->groupBy(
                    'supplier_id'
                )
                ->orderByDesc('total')
                ->first();

            /*
        /*
|--------------------------------------------------------------------------
| LOW STOCK
|--------------------------------------------------------------------------
*/

            $lowStockCount =
                Medicine::lowStock()
                ->count();

            return response()->json([

                'success' => true,

                'data' => [

                    'net_profit' =>
                    round($netProfit, 2),

                    'total_sales' =>
                    round($totalSales, 2),

                    'total_purchases' =>
                    round($totalPurchases, 2),

                    'inventory_value' =>
                    round($inventoryValue, 2),

                    'cash_balance' =>
                    round($cashBalance, 2),

                    'top_product' =>
                    $topProduct?->medicine?->name,

                    'top_supplier' =>
                    $topSupplier?->supplier?->name,

                    'low_stock_items' =>
                    $lowStockCount,
                ]
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function inventoryValuation()
    {
        try {

            $data = Medicine::all()
                ->map(function ($medicine) {

                    $layers =
                        StockCostLayer::where(
                            'medicine_id',
                            $medicine->id
                        )->get();

                    $currentStock =
                        $layers->sum(
                            'quantity_remaining'
                        );

                    $inventoryValue =
                        $layers->sum(function ($layer) {

                            return
                                $layer->quantity_remaining *
                                $layer->unit_cost;
                        });

                    return [

                        'medicine_id' =>
                        $medicine->id,

                        'medicine_code' =>
                        $medicine->code,

                        'medicine_name' =>
                        $medicine->name,

                        'current_stock' =>
                        $currentStock,

                        'inventory_value' =>
                        round(
                            $inventoryValue,
                            2
                        ),
                    ];
                });

            return response()->json([

                'success' => true,

                'summary' => [

                    'total_inventory_value' =>
                    round(
                        $data->sum(
                            'inventory_value'
                        ),
                        2
                    ),

                    'total_items' =>
                    $data->count(),
                ],

                'data' => $data
            ]);
        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                $e->getMessage()

            ], 500);
        }
    }
    public function exportInventoryValuation()
    {
        return Excel::download(
            new InventoryValuationExport(),
            'inventory_valuation_report.xlsx'
        );
    }

    public function exportFinancialReport()
    {
        return Excel::download(
            new FinancialReportExport(),
            'financial_report.xlsx'
        );
    }
    public function posSummary()
    {
        return response()->json([
            'transactions_today' =>
            Transaction::whereDate(
                'created_at',
                today()
            )->count(),

            'revenue_today' =>
            Transaction::whereDate(
                'created_at',
                today()
            )->sum('final_amount'),
        ]);
    }
}
