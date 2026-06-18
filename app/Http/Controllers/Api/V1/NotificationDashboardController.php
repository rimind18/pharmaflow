<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;

class NotificationDashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,

            'data' => [

                'total_notifications' =>
                    Notification::count(),

                'unread_notifications' =>
                    Notification::where(
                        'is_read',
                        false
                    )->count(),

                'critical_count' =>
                    Notification::where(
                        'severity',
                        'critical'
                    )->count(),

                'warning_count' =>
                    Notification::where(
                        'severity',
                        'warning'
                    )->count(),

                'info_count' =>
                    Notification::where(
                        'severity',
                        'info'
                    )->count(),

                'out_of_stock_count' =>
                    Notification::where(
                        'type',
                        'out_of_stock'
                    )->count(),

                'expired_count' =>
                    Notification::where(
                        'type',
                        'expired_medicine'
                    )->count(),

                'low_stock_count' =>
                    Notification::where(
                        'type',
                        'low_stock'
                    )->count(),

                'reorder_count' =>
                    Notification::where(
                        'type',
                        'reorder_alert'
                    )->count(),

                'latest_notifications' =>
                    Notification::latest()
                        ->take(10)
                        ->get()
            ]
        ]);
    }
}