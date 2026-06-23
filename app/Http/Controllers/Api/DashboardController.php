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
    public function exportSalesReport(Request $request)
{
    // Menggunakan logik yang sama dengan salesReport
    $request->merge(['period' => $request->get('period', 'daily')]);
    
    // Panggil fungsi salesReport untuk mendapatkan datanya
    $reportData = $this->salesReport($request)->getData()->data;
    
    // Gunakan library Excel atau PDF sesuai standar proyek King
    // Jika belum ada, gunakan ini sebagai placeholder sementara
    return response()->json([
        'message' => 'Fungsi export PDF/Excel siap dihubungkan!',
        'data' => $reportData
    ]);
}

    /**
     * Profit Report
     */
public function profitReport(Request $request)
    {
        try {
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            $query = \App\Models\Order::query();
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }

            // Kita buat clone query agar filter tanggal tetap sama untuk grafik
            $chartQuery = clone $query;

            // 1. Hitung Pendapatan (Revenue)
            $revenue = $query->sum('total_amount') ?? 0;
            
            // 2. Hitung Modal (COGS) - CARA SUPER AMAN 🚀
            $orderItems = \App\Models\OrderItem::with('medicine')
                ->whereIn('order_id', $query->pluck('id'))
                ->get();

            $cost = 0;
            foreach ($orderItems as $item) {
                // Sistem Jaring Pengaman:
                // Cek harga modal (purchase_price) dari tabel obat. 
                // Jika kolomnya tidak ada/kosong, otomatis anggap modalnya 80% dari harga jual.
                $modalObat = $item->medicine->purchase_price ?? ($item->unit_price * 0.8);
                $cost += ($item->quantity * $modalObat);
            }

            // 3. Kalkulasi Profit & Margin
            $profit = $revenue - $cost;
            $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

            // 4. Data untuk Grafik (Chart)
            $chartData = $chartQuery->select(
                \Illuminate\Support\Facades\DB::raw('DATE(created_at) as date'),
                \Illuminate\Support\Facades\DB::raw('SUM(total_amount) as daily_revenue')
            )
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_revenue' => (float)$revenue,
                    'total_cost' => (float)$cost,
                    'total_profit' => (float)$profit,
                    'margin' => (float)number_format($margin, 2),
                    'chart_labels' => $chartData->pluck('date'),
                    'chart_values' => $chartData->pluck('daily_revenue')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            // 1. Hitung Pemasukan (PASTI AMAN KARENA TABEL ORDERS ADA)
            $incomeQuery = \App\Models\Order::query();

            if ($startDate && $endDate) {
                $incomeQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }

            $totalIncome = $incomeQuery->sum('total_amount') ?? 0;

            // 2. Hitung Pengeluaran (SAFE MODE: KITA SET 0 SEMENTARA)
            // Jika nanti King sudah punya tabel pengeluaran (misal: purchases/expenses),
            // baris ini baru kita hidupkan lagi.
            $totalExpense = 0; 
            
            $netBalance = $totalIncome - $totalExpense;

            // 3. Data untuk Grafik (Hanya Pemasukan dulu yang digambar)
            $chartData = clone $incomeQuery;
            $incomeData = $chartData->select(
                \Illuminate\Support\Facades\DB::raw('DATE(created_at) as date'),
                \Illuminate\Support\Facades\DB::raw('SUM(total_amount) as daily_income')
            )->groupBy('date')->orderBy('date', 'ASC')->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_income' => (float)$totalIncome,
                    'total_expense' => (float)$totalExpense,
                    'net_balance' => (float)$netBalance,
                    'chart_labels' => $incomeData->pluck('date'),
                    'chart_income' => $incomeData->pluck('daily_income'),
                    'chart_expense' => [] // Kosongkan sementara agar grafik tidak error
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Cashflow Report
     */
public function cashflowReport(Request $request)
    {
        try {
            // SAFE MODE: Kita kembalikan nilai 0 dan tabel kosong 
            // agar Vue tidak error saat pertama kali me-render halaman.
            // Jika King sudah punya tabel cashflow, barulah kita ganti dengan query asli.

            return response()->json([
                'success' => true,
                'data' => [
                    'initial_balance' => 0,
                    'cash_in' => 0,
                    'cash_out' => 0,
                    'final_balance' => 0,
                    'transactions' => [
                        // Contoh dummy data agar King bisa melihat bentuk tabelnya
                        // Hapus saja nanti kalau sudah connect ke database asli
                        [
                            'date' => now()->format('Y-m-d H:i'),
                            'description' => 'Contoh Kas Masuk (Penjualan Obat)',
                            'type' => 'in',
                            'amount' => 150000
                        ],
                        [
                            'date' => now()->format('Y-m-d H:i'),
                            'description' => 'Contoh Kas Keluar (Bayar Listrik)',
                            'type' => 'out',
                            'amount' => 50000
                        ]
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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
/**
     * FUNGSI UNTUK HALAMAN ANALYTICS (Data Lanjutan)
     */
    public function analytics(Request $request)
    {
        try {
            // 1. Hitung Rata-rata Transaksi
            $totalOrders = \App\Models\Order::count();
            $totalRevenue = \App\Models\Order::sum('total_amount');
            $avgTransaction = $totalOrders > 0 ? ($totalRevenue / $totalOrders) : 0;

            // 2. Hitung Total Produk Terjual
            $totalItemsSold = \App\Models\OrderItem::sum('quantity');

            // 3. Hitung Conversion Rate (Pesanan Selesai vs Total Pesanan)
            $completedOrders = \App\Models\Order::where('status', 'completed')->orWhere('status', 'selesai')->count();
            $conversionRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0;

            // 4. Cari 5 Obat Paling Laris
            $topMedicinesData = \App\Models\OrderItem::select('medicine_id', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_sold'))
                ->with('medicine:id,name') // Pastikan mengambil nama obatnya
                ->groupBy('medicine_id')
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get();

            // Hitung persentase untuk animasi Progress Bar di Vue
            $maxSold = $topMedicinesData->max('total_sold') ?: 1; // Mencegah pembagian dengan nol

            $topMedicines = $topMedicinesData->map(function ($item) use ($maxSold) {
                return [
                    'name' => $item->medicine ? $item->medicine->name : 'Obat Dihapus',
                    'total_sold' => (int) $item->total_sold,
                    'percentage' => round(($item->total_sold / $maxSold) * 100) // Nilai 1-100%
                ];
            });

            // 5. Kirim semua data ke Vue
            return response()->json([
                'success' => true,
                'data' => [
                    'avg_transaction' => $avgTransaction,
                    'total_items_sold' => (int) $totalItemsSold,
                    'conversion_rate' => $conversionRate,
                    'top_medicines' => $topMedicines
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data analytics: ' . $e->getMessage()
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
}
