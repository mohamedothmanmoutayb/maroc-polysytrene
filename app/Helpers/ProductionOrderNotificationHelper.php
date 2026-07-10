<?php

namespace App\Helpers;

use App\Models\ProductionOrder;
use App\Models\ProductionOrderNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductionOrderNotificationHelper
{
    /**
     * Send notification for new production order
     */
    public static function notifyOrderCreated(ProductionOrder $order)
    {
        try {
            $users = self::getUsersToNotify();

            $title = 'Nouvel ordre de production';
            $message = "L'ordre de production {$order->order_number} pour {$order->product->product_name} a été créé et est en attente d'approbation.";

            foreach ($users as $user) {
                ProductionOrderNotification::create([
                    'production_order_id' => $order->order_id,
                    'user_id' => $user->id,
                    'notification_type' => 'created',
                    'title' => $title,
                    'message' => $message,
                    'data' => json_encode([
                        'order_number' => $order->order_number,
                        'product_name' => $order->product->product_name,
                        'quantity' => $order->quantity_to_produce,
                        'priority' => $order->priority,
                        'created_by' => auth()->id()
                    ]),
                    'is_sent' => true,
                    'sent_at' => now(),
                    'is_read' => false
                ]);
            }

            Log::info('Production order created notification sent', [
                'order_id' => $order->order_id,
                'order_number' => $order->order_number,
                'notified_users' => $users->count()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send order created notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification for order approval
     */
    public static function notifyOrderApproved(ProductionOrder $order)
    {
        try {
            $users = self::getUsersToNotify();

            $title = 'Ordre de production approuvé';
            $message = "L'ordre de production {$order->order_number} a été approuvé et est prêt à démarrer.";

            foreach ($users as $user) {
                ProductionOrderNotification::create([
                    'production_order_id' => $order->order_id,
                    'user_id' => $user->id,
                    'notification_type' => 'approved',
                    'title' => $title,
                    'message' => $message,
                    'data' => json_encode([
                        'order_number' => $order->order_number,
                        'product_name' => $order->product->product_name,
                        'approved_by' => auth()->id(),
                        'approved_at' => now()
                    ]),
                    'is_sent' => true,
                    'sent_at' => now(),
                    'is_read' => false
                ]);
            }

            Log::info('Production order approved notification sent', [
                'order_id' => $order->order_id,
                'order_number' => $order->order_number
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send order approved notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification for order started
     */
    public static function notifyOrderStarted(ProductionOrder $order)
    {
        try {
            $users = self::getUsersToNotify();

            $title = 'Production démarrée';
            $message = "La production pour l'ordre {$order->order_number} a démarré.";

            foreach ($users as $user) {
                ProductionOrderNotification::create([
                    'production_order_id' => $order->order_id,
                    'user_id' => $user->id,
                    'notification_type' => 'started',
                    'title' => $title,
                    'message' => $message,
                    'data' => json_encode([
                        'order_number' => $order->order_number,
                        'product_name' => $order->product->product_name,
                        'started_by' => auth()->id(),
                        'started_at' => now()
                    ]),
                    'is_sent' => true,
                    'sent_at' => now(),
                    'is_read' => false
                ]);
            }

            Log::info('Production order started notification sent', [
                'order_id' => $order->order_id,
                'order_number' => $order->order_number
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send order started notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification for order completed
     */
    public static function notifyOrderCompleted(ProductionOrder $order)
    {
        try {
            $users = self::getUsersToNotify();

            $title = 'Production terminée';
            $message = "La production pour l'ordre {$order->order_number} est terminée. N'oubliez pas de déclarer les chutes.";

            foreach ($users as $user) {
                ProductionOrderNotification::create([
                    'production_order_id' => $order->order_id,
                    'user_id' => $user->id,
                    'notification_type' => 'completed',
                    'title' => $title,
                    'message' => $message,
                    'data' => json_encode([
                        'order_number' => $order->order_number,
                        'product_name' => $order->product->product_name,
                        'completed_by' => auth()->id(),
                        'completed_at' => now()
                    ]),
                    'is_sent' => true,
                    'sent_at' => now(),
                    'is_read' => false
                ]);
            }

            Log::info('Production order completed notification sent', [
                'order_id' => $order->order_id,
                'order_number' => $order->order_number
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send order completed notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification for waste declaration needed
     */
    public static function notifyWasteNeeded(ProductionOrder $order)
    {
        try {
            $users = self::getUsersToNotify();

            $title = 'Déclaration de chutes requise';
            $message = "La production pour l'ordre {$order->order_number} est terminée. Veuillez déclarer les chutes.";

            foreach ($users as $user) {
                ProductionOrderNotification::create([
                    'production_order_id' => $order->order_id,
                    'user_id' => $user->id,
                    'notification_type' => 'waste_needed',
                    'title' => $title,
                    'message' => $message,
                    'data' => json_encode([
                        'order_number' => $order->order_number,
                        'product_name' => $order->product->product_name
                    ]),
                    'is_sent' => true,
                    'sent_at' => now(),
                    'is_read' => false
                ]);
            }

            Log::info('Waste declaration needed notification sent', [
                'order_id' => $order->order_id,
                'order_number' => $order->order_number
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send waste needed notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification for overdue order
     */
    public static function notifyOrderOverdue(ProductionOrder $order)
    {
        try {
            $users = self::getUsersToNotify();
            $daysOverdue = Carbon::parse($order->expected_completion_date)->diffInDays(Carbon::now());

            $title = 'Ordre de production en retard';
            $message = "L'ordre {$order->order_number} est en retard de {$daysOverdue} jour(s).";

            foreach ($users as $user) {
                // Check if we already sent an overdue notification recently
                $existingNotification = ProductionOrderNotification::where('production_order_id', $order->order_id)
                    ->where('user_id', $user->id)
                    ->where('notification_type', 'overdue')
                    ->where('created_at', '>', Carbon::now()->subDay())
                    ->first();

                if (!$existingNotification) {
                    ProductionOrderNotification::create([
                        'production_order_id' => $order->order_id,
                        'user_id' => $user->id,
                        'notification_type' => 'overdue',
                        'title' => $title,
                        'message' => $message,
                        'data' => json_encode([
                            'order_number' => $order->order_number,
                            'product_name' => $order->product->product_name,
                            'days_overdue' => $daysOverdue,
                            'expected_date' => $order->expected_completion_date
                        ]),
                        'is_sent' => true,
                        'sent_at' => now(),
                        'is_read' => false
                    ]);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send overdue notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all notifications for a user
     */
    public static function getUserNotifications($userId = null, $limit = 20)
    {
        $userId = $userId ?? auth()->id();

        return ProductionOrderNotification::where('user_id', $userId)
            ->with('productionOrder.product')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread notification count
     */
    public static function getUnreadCount($userId = null)
    {
        $userId = $userId ?? auth()->id();

        return ProductionOrderNotification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Mark notification as read
     */
    public static function markAsRead($notificationId)
    {
        $notification = ProductionOrderNotification::find($notificationId);

        if ($notification && $notification->user_id == auth()->id()) {
            $notification->update([
                'is_read' => true,
                'read_at' => now()
            ]);
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read
     */
    public static function markAllAsRead($userId = null)
    {
        $userId = $userId ?? auth()->id();

        return ProductionOrderNotification::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
    }

    /**
     * Get notification statistics
     */
    public static function getNotificationStats()
    {
        $stats = [
            'total' => 0,
            'unread' => 0,
            'by_type' => [
                'created' => 0,
                'approved' => 0,
                'started' => 0,
                'completed' => 0,
                'waste_needed' => 0,
                'overdue' => 0
            ]
        ];

        if (auth()->check()) {
            $stats['unread'] = self::getUnreadCount();

            $notifications = ProductionOrderNotification::where('user_id', auth()->id())
                ->select('notification_type', DB::raw('count(*) as count'))
                ->groupBy('notification_type')
                ->get();

            foreach ($notifications as $notification) {
                $stats['by_type'][$notification->notification_type] = $notification->count;
                $stats['total'] += $notification->count;
            }
        }

        return $stats;
    }

    /**
     * Check for overdue orders and send notifications
     */
    public static function checkOverdueOrders()
    {
        $overdueOrders = ProductionOrder::whereIn('status', ['approved', 'in_progress'])
            ->where('expected_completion_date', '<', Carbon::now())
            ->get();

        foreach ($overdueOrders as $order) {
            self::notifyOrderOverdue($order);
        }

        return $overdueOrders->count();
    }

    /**
     * Check for orders needing waste declaration
     */
    public static function checkWasteNeededOrders()
    {
        $orders = ProductionOrder::where('status', 'in_progress')
            ->with(['outputs', 'wastes'])
            ->get()
            ->filter(function($order) {
                $totalProduced = $order->outputs->sum('quantity_produced');
                $isProductionComplete = $totalProduced >= $order->quantity_to_produce;
                $hasNoWaste = $order->wastes->count() == 0;

                return $isProductionComplete && $hasNoWaste;
            });

        foreach ($orders as $order) {
            // Check if we already sent notification
            $existingNotification = ProductionOrderNotification::where('production_order_id', $order->order_id)
                ->where('notification_type', 'waste_needed')
                ->where('created_at', '>', Carbon::now()->subDay())
                ->exists();

            if (!$existingNotification) {
                self::notifyWasteNeeded($order);
            }
        }

        return $orders->count();
    }

    /**
     * Get users to notify (users with admin or production manager roles)
     */
    private static function getUsersToNotify()
    {
        if (class_exists('\App\Models\User') && \Schema::hasTable('model_has_roles')) {
            return User::whereHas('roles', function($query) {
                $query->whereIn('name', ['admin', 'production_manager', 'supervisor']);
            })->get();
        }

        return User::where('is_admin', true)->orWhere('user_type', 'admin')->get();
    }
}
