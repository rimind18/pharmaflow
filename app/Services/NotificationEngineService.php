<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\Notification;
use App\Models\Stock;
use App\Models\User;

class NotificationEngineService
{
    public function generate()
    {
        $notificationUsers = User::whereIn(
            'role',
            [
                'owner',
                'staff'
            ]
        )->get();

        $medicines = Medicine::with(
            'stocks'
        )->get();

        foreach ($medicines as $medicine) {

            $currentStock =
                Stock::where(
                    'medicine_id',
                    $medicine->id
                )
                ->where(function ($q) {
                    $q->whereNull('expired_date')
                        ->orWhereDate(
                            'expired_date',
                            '>=',
                            now()
                        );
                })
                ->sum('quantity');

            /**
             * ====================================
             * PRIORITY #1
             * EXPIRED MEDICINE
             * ====================================
             */
            $expiredStock = Stock::where(
                'medicine_id',
                $medicine->id
            )
                ->whereDate(
                    'expired_date',
                    '<',
                    now()
                )
                ->exists();

            if ($expiredStock) {

                foreach ($notificationUsers as $user) {

                    Notification::firstOrCreate(

                        [
                            'user_id' => $user->id,
                            'type' => 'expired_medicine',
                            'title' =>
                            'Expired Medicine - ' .
                                $medicine->name
                        ],

                        [
                            'message' => $medicine->name . ' has expired',
                            'severity' => 'critical',
                            'is_read' => false
                        ]
                    );
                }
            }

            /**
             * ====================================
             * PRIORITY #2
             * EXPIRING SOON
             * ====================================
             */
            $expiringSoon = Stock::where(
                'medicine_id',
                $medicine->id
            )
                ->whereDate(
                    'expired_date',
                    '>',
                    now()
                )
                ->whereDate(
                    'expired_date',
                    '<=',
                    now()->addDays(30)
                )
                ->exists();

            if ($expiringSoon) {

                foreach ($notificationUsers as $user) {

                    Notification::firstOrCreate(

                        [
                            'user_id' => $user->id,
                            'type' => 'expiring_soon',
                            'title' =>
                            'Expiring Soon - ' .
                                $medicine->name
                        ],

                        [
                            'message' => $medicine->name . ' will expire within 30 days',
                            'severity' => 'warning',
                            'is_read' => false
                        ]
                    );
                }
            }

            /**
             * ====================================
             * PRIORITY #3
             * OUT OF STOCK
             * ====================================
             */
            if ($currentStock <= 0) {

                foreach ($notificationUsers as $user) {

                    Notification::firstOrCreate(

                        [
                            'user_id' => $user->id,
                            'type' => 'out_of_stock',
                            'title' =>
                            'Out Of Stock - ' .
                                $medicine->name
                        ],

                        [
                            'message' => $medicine->name . ' is out of stock',
                            'severity' => 'critical',
                            'is_read' => false
                        ]
                    );
                }

                continue;
            }

            /**
             * ====================================
             * PRIORITY #4
             * LOW STOCK
             * ====================================
             */
            if (
                $currentStock > 0 &&
                $currentStock <= 10 &&
                !$expiredStock
            ) {

                foreach ($notificationUsers as $user) {

                    Notification::firstOrCreate(

                        [
                            'user_id' => $user->id,
                            'type' => 'low_stock',
                            'title' =>
                            'Low Stock - ' .
                                $medicine->name
                        ],

                        [
                            'message' => 'Current stock only ' . $currentStock . ' units',
                            'severity' => 'warning',
                            'is_read' => false
                        ]
                    );
                }

                /**
                 * ====================================
                 * PRIORITY #5
                 * REORDER ALERT
                 * ====================================
                 */
                foreach ($notificationUsers as $user) {

                    Notification::firstOrCreate(

                        [
                           'user_id' => $user->id,
                            'type' => 'reorder_alert',
                            'title' =>
                            'Reorder Alert - ' .
                                $medicine->name
                        ],

                        [
                            'message' => 'Consider creating purchase order for ' . $medicine->name,
                            'severity' => 'warning',
                            'is_read' => false
                        ]
                    );
                }
            }
            User::where(
                'receive_notifications',
                true
            )->get();
        }
    }
}
