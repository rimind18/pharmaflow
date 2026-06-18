<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * LIST NOTIFICATION
     */
    public function index(Request $request)
    {
        $query = Notification::where(
            'user_id',
            Auth::id()
        );

        if ($request->filled('type')) {

            $query->where(
                'type',
                $request->type
            );
        }

        if ($request->boolean('unread')) {

            $query->where(
                'is_read',
                false
            );
        }

        $notifications =
            $query
            ->latest()
            ->paginate(
                $request->per_page ?? 15
            );

        return response()->json([

            'success' => true,

            'data' => $notifications
        ]);
    }

    /**
     * DETAIL NOTIFICATION
     */
    public function show($id)
    {
        $notification =
            Notification::where(
                'user_id',
                Auth::id()
            )
            ->findOrFail($id);

        return response()->json([

            'success' => true,

            'data' => $notification
        ]);
    }

    /**
     * MARK AS READ
     */
    public function markAsRead($id)
    {
        $notification =
            Notification::where(
                'user_id',
                Auth::id()
            )
            ->findOrFail($id);

        $notification->update([

            'is_read' => true
        ]);

        return response()->json([

            'success' => true,

            'message' =>
                'Notification marked as read'
        ]);
    }

    /**
     * MARK ALL READ
     */
    public function markAllAsRead()
    {
        Notification::where(
            'user_id',
            Auth::id()
        )
        ->where(
            'is_read',
            false
        )
        ->update([

            'is_read' => true
        ]);

        return response()->json([

            'success' => true,

            'message' =>
                'All notifications marked as read'
        ]);
    }

    /**
     * STATS DASHBOARD
     */
    public function stats()
    {
        $userId = Auth::id();

        $total =
            Notification::where(
                'user_id',
                $userId
            )->count();

        $unread =
            Notification::where(
                'user_id',
                $userId
            )
            ->where(
                'is_read',
                false
            )
            ->count();

        $transactionNotif =
            Notification::where(
                'user_id',
                $userId
            )
            ->where(
                'type',
                'transaction'
            )
            ->count();

        $stockNotif =
            Notification::where(
                'user_id',
                $userId
            )
            ->whereIn(
                'type',
                [
                    'low_stock',
                    'out_of_stock',
                    'expired_medicine',
                    'expiring_soon',
                    'reorder_alert'
                ]
            )
            ->count();

        return response()->json([

            'success' => true,

            'data' => [

                'total' =>
                    $total,

                'unread' =>
                    $unread,

                'transaction' =>
                    $transactionNotif,

                'stock' =>
                    $stockNotif
            ]
        ]);
    }

    /**
     * GENERATE SYSTEM NOTIFICATIONS
     */
    public function generate(
        NotificationEngineService $service
    )
    {
        $service->generate();

        return response()->json([

            'success' => true,

            'message' =>
                'Notifications generated'
        ]);
    }
}