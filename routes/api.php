<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MedicineController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\ShelfController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\CashflowController;
use App\Http\Controllers\Api\StockMonitoringController;
use App\Http\Controllers\Api\StockMutationReportController;
use App\Http\Controllers\Api\StockOpnameController;
use App\Http\Controllers\Api\AuditLogReportController;
use App\Http\Controllers\Api\PurchaseReportController;
use App\Http\Controllers\Api\ProfitAnalysisController;
use App\Http\Controllers\Api\ProfitLossReportController;
use App\Http\Controllers\Api\BalanceSheetController;
use App\Http\Controllers\Api\CashFlowStatementController;
use App\Http\Controllers\Api\OwnerFinancialDashboardController;
use App\Http\Controllers\Api\OwnerAnalyticsController;
use App\Http\Controllers\Api\InventoryIntelligenceController;
use App\Http\Controllers\Api\AdvancedOwnerAnalyticsController;
use App\Http\Controllers\Api\AdvancedOwnerAnalyticsV2Controller;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\V1\NotificationDashboardController;


Route::prefix('v1')->group(function () {

    // ================================================
    // PUBLIC ROUTES (No Auth Required)
    // ================================================

    // Authentication
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');


    // Public Product Routes (E-commerce)
    Route::get('medicines', [MedicineController::class, 'index']);
    Route::get('medicines/{id}', [MedicineController::class, 'show']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('promotions', [PromotionController::class, 'index']);

    // ================================================
    // GUEST CHECKOUT - PUBLIC
    // ================================================

    Route::post(
        'orders',
        [OrderController::class, 'store']
    );

    Route::post(
        'orders/track',
        [OrderController::class, 'trackByPhone']
    );

    // PUBLIC ORDER DETAIL
    Route::get(
        'orders/{order_number}',
        [OrderController::class, 'show']
    );

    Route::post(
        '/payments/snap-token',
        [PaymentController::class, 'createSnapToken']
    );

    Route::post(
        'vouchers/validate',
        [VoucherController::class, 'validate']
    );

    // Midtrans Webhook
    Route::post(
        'webhook/midtrans',
        [PaymentController::class, 'notification']
    );

    // ================================================
    // PROTECTED ROUTES (JWT Required)
    // ================================================

    Route::middleware('jwt')->group(function () {

        Route::get(
            'stocks/check-expired',
            [
                StockMonitoringController::class,
                'checkExpiringMedicine'
            ]
        );

        Route::get(
            'payments/{id}/status',
            [PaymentController::class, 'status']
        );
        // ============================================
        // AUTH ROUTES
        // ============================================

        Route::post(
            'auth/logout',
            [AuthController::class, 'logout']
        );

        Route::post(
            'auth/refresh',
            [AuthController::class, 'refresh']
        );

        Route::get(
            'auth/me',
            [AuthController::class, 'me']
        );

        Route::post('auth/validate', [AuthController::class, 'validateToken']);

        Route::put(
            'profile',
            [
                AuthController::class,
                'updateProfile'
            ]
        );

        Route::put(
            'change-password',
            [
                AuthController::class,
                'changePassword'
            ]
        );

        // ============================================
        // NOTIFICATIONS (ALL ROLES)
        // ============================================

        Route::get(
            'notifications',
            [NotificationController::class, 'index']
        );

        Route::get(
            'notifications/stats',
            [NotificationController::class, 'stats']
        );

        Route::get(
            'notifications/{id}',
            [NotificationController::class, 'show']
        );

        Route::post(
            'notifications/{id}/read',
            [NotificationController::class, 'markAsRead']
        );

        Route::post(
            'notifications/mark-all-read',
            [NotificationController::class, 'markAllAsRead']
        );

        // ============================================
        // CUSTOMER ROUTES
        // ============================================

        Route::middleware('role:customer')->group(function () {

            Route::get('/my-orders', [OrderController::class, 'myOrders']);

            Route::put('orders/{id}', [
                OrderController::class,
                'update'
            ]);

            // Promotions
            Route::get('promotions', [
                PromotionController::class,
                'index'
            ]);

            Route::post(
                'orders/{id}/cancel',
                [OrderController::class, 'cancel']
            );


            Route::get('promotions/{id}', [
                PromotionController::class,
                'show'
            ]);
        });

        // ============================================
        // STAFF ROUTES
        // ============================================

        Route::middleware('role:staff')->group(function () {

            Route::post(
                'payments/cod/confirm',
                [PaymentController::class, 'confirmCOD']
            );

            // TRANSACTIONS
            Route::post(
                'transactions/calculate-change',
                [
                    TransactionController::class,
                    'calculateChange'
                ]
            );

            Route::apiResource(
                'transactions',
                TransactionController::class
            );

            Route::post(
                'transactions/{id}/complete',
                [TransactionController::class, 'complete']
            );

            Route::get(
                'reports/daily',
                [TransactionController::class, 'dailyReport']
            );

            // POS SEARCH
            Route::get(
                'medicines/search/{code}',
                [MedicineController::class, 'searchByCode']
            );

            // ATTENDANCE
            Route::post(
                'attendance/check-in',
                [AttendanceController::class, 'checkIn']
            );

            Route::post(
                'attendance/check-out',
                [AttendanceController::class, 'checkOut']
            );

            Route::apiResource(
                'attendance',
                AttendanceController::class
            );

            Route::get(
                'attendance/today',
                [AttendanceController::class, 'today']
            );

            Route::get(
                'attendance/monthly-report',
                [AttendanceController::class, 'monthlyReport']
            );

            // MASTER DATA
            Route::apiResource(
                'medicines',
                MedicineController::class
            )->except(['index', 'show']);

            Route::apiResource(
                'categories',
                CategoryController::class
            )->except(['index', 'show']);

            Route::apiResource(
                'suppliers',
                SupplierController::class
            );

            Route::apiResource(
                'warehouses',
                WarehouseController::class
            );

            Route::apiResource(
                'shelves',
                ShelfController::class
            );
            // STOCK
            Route::get(
                'stocks',
                [StockController::class, 'index']
            );

            Route::get(
                'stocks/{id}',
                [StockController::class, 'show']
            );

            Route::get(
                'stocks/low-stock',
                [StockController::class, 'lowStock']
            );

            Route::get(
                'stocks/expired',
                [StockController::class, 'expired']
            );

            Route::get(
                'stocks/expiring-soon',
                [StockController::class, 'expiringSoon']
            );

            Route::post(
                'stocks/adjustment',
                [StockController::class, 'adjustment']
            );

            Route::post(
                'stocks/opname',
                [StockController::class, 'opname']
            );

            // PURCHASES
            Route::apiResource(
                'purchases',
                PurchaseController::class
            );

            Route::post(
                'purchases/{id}/receive',
                [PurchaseController::class, 'receive']
            );

            // ORDERS
            Route::get(
                'staff/orders',
                [OrderController::class, 'index']
            );

            Route::put(
                'orders/{id}',
                [OrderController::class, 'update']
            );

            // EMPLOYEES
            Route::apiResource(
                'employees',
                EmployeeController::class
            );

            Route::get(
                'employees/{id}/attendance-report',
                [EmployeeController::class, 'attendanceReport']
            );

            // PROMOTIONS & VOUCHERS
            Route::apiResource(
                'promotions',
                PromotionController::class
            );

            Route::apiResource(
                'vouchers',
                VoucherController::class
            );

            // DASHBOARD
            Route::get(
                'dashboard/summary',
                [DashboardController::class, 'summary']
            );

            Route::get(
                'reports/sales',
                [DashboardController::class, 'salesReport']
            );

            Route::get(
                'reports/profit',
                [DashboardController::class, 'profitReport']
            );

            Route::get(
                'reports/inventory',
                [DashboardController::class, 'inventoryReport']
            );

            // CASHFLOW
            Route::get(
                'cashflow',
                [CashflowController::class, 'index']
            );

            Route::get(
                'cashflow/summary',
                [CashflowController::class, 'summary']
            );

            Route::get(
                'cashflow/by-category',
                [CashflowController::class, 'byCategory']
            );

            Route::get(
                'cashflow/daily-trend',
                [CashflowController::class, 'dailyTrend']
            );

            Route::get(
                'cashflow/top-categories',
                [CashflowController::class, 'topCategories']
            );

            Route::get(
                'cashflow/export',
                [CashflowController::class, 'export']
            );

            Route::post(
                'cashflow',
                [CashflowController::class, 'store']
            );

            Route::put(
                'cashflow/{id}',
                [CashflowController::class, 'update']
            );

            Route::delete(
                'cashflow/{id}',
                [CashflowController::class, 'destroy']
            );

            Route::prefix('stock-opname')->group(function () {

                Route::post('/', [StockOpnameController::class, 'store']);

                Route::get('/', [StockOpnameController::class, 'index']);

                Route::get('/{id}', [StockOpnameController::class, 'show']);
            });
        });

        // ============================================
        // OWNER ROUTES
        // ============================================

        Route::middleware('role:owner')->group(function () {
            // DASHBOARD
            Route::get(
                'owner/orders',
                [OrderController::class, 'index']
            );

            Route::get(
                'dashboard/summary',
                [DashboardController::class, 'summary']
            );

            Route::post(
                'payments/cod/confirm',
                [PaymentController::class, 'confirmCOD']
            );

            Route::get(
                'dashboard/analytics',
                [DashboardController::class, 'analytics']
            );

            /**
             * PART 4.4 — OWNER DASHBOARD PRO
             */
            Route::get(
                'dashboard/kpi',
                [DashboardController::class, 'kpi']
            );

            Route::get(
                'dashboard/charts',
                [DashboardController::class, 'charts']
            );

            Route::get(
                'dashboard/top-medicines',
                [DashboardController::class, 'topMedicines']
            );

            Route::get(
                'dashboard/warnings',
                [DashboardController::class, 'warnings']
            );

            Route::get(
                '/dashboard/reorder-recommendation',
                [
                    DashboardController::class,
                    'reorderRecommendation'
                ]
            );

            Route::get(
                'dashboard/analytics-owner',
                [DashboardController::class, 'analyticsOwner']
            );

            Route::post(
                '/purchases/generate-from-recommendation',
                [
                    PurchaseController::class,
                    'generateFromRecommendation'
                ]
            );

            Route::post(
                '/purchases/{id}/approve',
                [PurchaseController::class, 'approve']
            );

            Route::post(
                '/purchases/{id}/ordered',
                [PurchaseController::class, 'markOrdered']
            );


            // REPORTS
            Route::get(
                'reports/financial',
                [DashboardController::class, 'financialReport']
            );

            Route::get(
                'reports/cashflow',
                [DashboardController::class, 'cashflowReport']
            );

            Route::get(
                'reports/product-performance',
                [DashboardController::class, 'productPerformance']
            );

            Route::get(
                'reports/audit-logs',
                [AuditLogReportController::class, 'index']
            );

            // CASHFLOW
            Route::get(
                'cashflow',
                [CashflowController::class, 'index']
            );

            Route::get(
                'cashflow/summary',
                [CashflowController::class, 'summary']
            );

            Route::get(
                'cashflow/by-category',
                [CashflowController::class, 'byCategory']
            );

            Route::get(
                'cashflow/daily-trend',
                [CashflowController::class, 'dailyTrend']
            );

            Route::get(
                'cashflow/top-categories',
                [CashflowController::class, 'topCategories']
            );

            Route::get(
                'cashflow/export',
                [CashflowController::class, 'export']
            );

            Route::post(
                'cashflow',
                [CashflowController::class, 'store']
            );

            Route::put(
                'cashflow/{id}',
                [CashflowController::class, 'update']
            );

            Route::delete(
                'cashflow/{id}',
                [CashflowController::class, 'destroy']
            );
            Route::get(
                'reports/stock-mutations',
                [StockMutationReportController::class, 'index']
            );
            Route::get(
                '/reports/purchases',
                [PurchaseReportController::class, 'index']
            );
            Route::get(
                'reports/sales',
                [DashboardController::class, 'salesReport']
            );

            Route::get(
                'reports/profit',
                [DashboardController::class, 'profitReport']
            );

            Route::get(
                'reports/inventory',
                [DashboardController::class, 'inventoryReport']
            );
            Route::get(
                'reports/profit-products',
                [ProfitAnalysisController::class, 'index']
            );
            Route::get(
                'reports/purchases/export',
                [PurchaseReportController::class, 'export']
            );
            Route::get(
                'reports/profit-products/export',
                [ProfitAnalysisController::class, 'export']
            );
            Route::get(
                'dashboard/executive',
                [DashboardController::class, 'executive']
            );
            Route::get(
                'reports/inventory-valuation',
                [DashboardController::class, 'inventoryValuation']
            );
            Route::get(
                'reports/inventory-valuation/export',
                [DashboardController::class, 'exportInventoryValuation']
            );
            Route::get(
                'reports/financial/export',
                [DashboardController::class, 'exportFinancialReport']
            );
            Route::get(
                'reports/profit-loss',
                [ProfitLossReportController::class, 'index']
            );
            Route::get(
                '/reports/balance-sheet',
                [BalanceSheetController::class, 'index']
            );
            Route::get(
                'reports/cash-flow-statement',
                [CashFlowStatementController::class, 'index']
            );
            Route::get(
                'dashboard/owner-financial',
                [OwnerFinancialDashboardController::class, 'index']
            );
            Route::get(
                'dashboard/owner-analytics',
                [OwnerAnalyticsController::class, 'index']
            );
            Route::get(
                'dashboard/inventory-intelligence',
                [InventoryIntelligenceController::class, 'index']
            );

            Route::get(
                '/dashboard/owner-analytics-v2',
                [AdvancedOwnerAnalyticsV2Controller::class, 'index']
            );

            Route::post(
                '/notifications/generate',
                [
                    NotificationController::class,
                    'generate'
                ]
            );

            Route::get(
                '/notifications/dashboard',
                [NotificationDashboardController::class, 'index']
            );

            Route::prefix('stock-opname')->group(function () {

                Route::post(
                    '/{id}/approve',
                    [StockOpnameController::class, 'approve']
                );

                Route::post(
                    '/{id}/reject',
                    [StockOpnameController::class, 'reject']
                );
            });
        });
    });
});
