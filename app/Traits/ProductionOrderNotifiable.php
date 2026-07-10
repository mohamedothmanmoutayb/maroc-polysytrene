<?php

namespace App\Traits;

use App\Models\Notification;
use App\Models\ProductionOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait ProductionOrderNotifiable
{
    /**
     * Check and send notifications for production order status changes
     */
    protected function notifyStatusChange(ProductionOrder $order, $oldStatus, $newStatus)
    {
        $notification = null;

        switch ($newStatus) {
            case 'pending':
                $notification = [
                    'title' => 'Ordre de production créé',
                    'message' => "L'ordre {$order->order_number} a été créé et attend votre approbation.",
                    'type' => 'info'
                ];
                break;

            case 'approved':
                $notification = [
                    'title' => 'Ordre de production approuvé',
                    'message' => "L'ordre {$order->order_number} a été approuvé et peut démarrer.",
                    'type' => 'success'
                ];
                break;

            case 'in_progress':
                $notification = [
                    'title' => 'Production démarrée',
                    'message' => "La production pour l'ordre {$order->order_number} a démarré.",
                    'type' => 'info'
                ];
                break;

            case 'completed':
                $notification = [
                    'title' => 'Production terminée',
                    'message' => "L'ordre {$order->order_number} est terminé. N'oubliez pas de déclarer les chutes.",
                    'type' => 'success'
                ];
                break;

            case 'cancelled':
                $notification = [
                    'title' => 'Ordre de production annulé',
                    'message' => "L'ordre {$order->order_number} a été annulé.",
                    'type' => 'warning'
                ];
                break;
        }

        if ($notification) {
            $this->createNotification($order, $notification);
        }
    }

    /**
     * Check for overdue orders and send notifications
     */
    protected function checkOverdueOrders()
    {
        $overdueOrders = ProductionOrder::where('status', 'in_progress')
            ->where('expected_completion_date', '<', Carbon::now())
            ->whereDoesntHave('notifications', function($query) {
                $query->where('type', 'overdue')
                      ->where('created_at', '>', Carbon::now()->subDay());
            })
            ->get();

        foreach ($overdueOrders as $order) {
            $daysOverdue = Carbon::parse($order->expected_completion_date)->diffInDays(Carbon::now());

            $this->createNotification($order, [
                'title' => 'Production en retard',
                'message' => "L'ordre {$order->order_number} est en retard de {$daysOverdue} jour(s).",
                'type' => 'danger'
            ]);
        }
    }

    /**
     * Create a notification record
     */
    protected function createNotification(ProductionOrder $order, array $data)
    {
        try {
            if (class_exists('\App\Models\Notification')) {
                // Get users to notify
                $users = $this->getUsersToNotify($order);

                foreach ($users as $user) {
                    Notification::create([
                        'user_id' => $user->id,
                        'type' => 'production_order_' . $order->status,
                        'title' => $data['title'],
                        'message' => $data['message'],
                        'link' => route('production-orders.show', $order->order_id),
                        'is_read' => false,
                        'created_at' => now(),
                    ]);
                }
            }

            Log::info('Production order notification created', [
                'order_id' => $order->order_id,
                'title' => $data['title']
            ]);

        } catch (\Exception $e) {
            Log::warning('Failed to create notification: ' . $e->getMessage());
        }
    }

    /**
     * Get users who should receive notifications
     */
    protected function getUsersToNotify(ProductionOrder $order)
    {
        // Return users with relevant roles
        return \App\Models\User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'production_manager', 'supervisor']);
        })->get();
    }
}
