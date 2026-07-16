<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\ProductionOrderNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // -------------------------------------------------------------------------
    // Fetch all notifications for the authenticated admin
    // -------------------------------------------------------------------------
    public function getNotifications()
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['success' => true, 'data' => [], 'unread_count' => 0]);
        }

        try {
            $notifications = [];

            // 1. Production-order event notifications (persisted by business logic)
            $productionNotifs = ProductionOrderNotification::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->limit(30)
                ->get();

            foreach ($productionNotifs as $notif) {
                $notifications[] = [
                    'id'       => 'po_' . $notif->id,
                    'type'     => $notif->notification_type,
                    'category' => 'production',
                    'title'    => $notif->title,
                    'message'  => $notif->message,
                    'icon'     => $this->iconForType($notif->notification_type),
                    'color'    => $this->colorForType($notif->notification_type),
                    'date'     => $notif->created_at->toISOString(),
                    'is_read'  => (bool) $notif->is_read,
                    'link'     => $notif->production_order_id
                        ? route('production-orders.show', $notif->production_order_id)
                        : null,
                ];
            }

            // 2. System alert notifications created by the daily cron
            $systemNotifs = Notification::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            foreach ($systemNotifs as $notif) {
                $data = $notif->data ?? [];
                $notifications[] = [
                    'id'       => 'sn_' . $notif->id,
                    'type'     => $notif->type,
                    'category' => $data['category'] ?? 'system',
                    'title'    => $notif->title,
                    'message'  => $notif->message,
                    'icon'     => $data['icon'] ?? 'fas fa-bell',
                    'color'    => $data['color'] ?? 'secondary',
                    'date'     => $notif->created_at->toISOString(),
                    'is_read'  => (bool) $notif->is_read,
                    'link'     => $notif->link,
                ];
            }

            usort($notifications, function ($a, $b) {
                return strcmp($b['date'], $a['date']);
            });

            $unreadCount = count(array_filter($notifications, fn($n) => !$n['is_read']));

            return response()->json([
                'success'      => true,
                'data'         => array_values(array_slice($notifications, 0, 50)),
                'unread_count' => $unreadCount,
            ]);

        } catch (\Exception $e) {
            Log::error('getNotifications error: ' . $e->getMessage());
            return response()->json([
                'success'      => false,
                'message'      => $e->getMessage(),
                'data'         => [],
                'unread_count' => 0,
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Mark a single notification as read
    // ID format: "po_{id}" for production-order notifs, "sn_{id}" for system notifs
    // -------------------------------------------------------------------------
    public function markAsRead($id)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['success' => false], 403);
        }

        try {
            if (str_starts_with($id, 'sn_')) {
                $numericId = (int) substr($id, 3);
                Notification::where('id', $numericId)
                    ->where('user_id', Auth::id())
                    ->update(['is_read' => true, 'read_at' => now()]);
            } elseif (str_starts_with($id, 'po_')) {
                $numericId = (int) substr($id, 3);
                ProductionOrderNotification::where('id', $numericId)
                    ->where('user_id', Auth::id())
                    ->update(['is_read' => true, 'read_at' => now()]);
            }

            return response()->json([
                'success'      => true,
                'unread_count' => $this->countUnread(),
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // -------------------------------------------------------------------------
    // Mark all notifications as read
    // -------------------------------------------------------------------------
    public function markAllAsRead()
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['success' => false], 403);
        }

        try {
            $now = now();

            Notification::where('user_id', Auth::id())
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => $now]);

            ProductionOrderNotification::where('user_id', Auth::id())
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => $now]);

            return response()->json(['success' => true, 'unread_count' => 0]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // -------------------------------------------------------------------------
    // Unread count
    // -------------------------------------------------------------------------
    public function getUnreadCount()
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['success' => true, 'count' => 0]);
        }

        try {
            return response()->json(['success' => true, 'count' => $this->countUnread()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'count' => 0, 'message' => $e->getMessage()]);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------
    private function countUnread(): int
    {
        $systemUnread = Notification::where('user_id', Auth::id())->where('is_read', false)->count();
        $prodUnread   = ProductionOrderNotification::where('user_id', Auth::id())->where('is_read', false)->count();

        return $systemUnread + $prodUnread;
    }

    private function iconForType(string $type): string
    {
        return match ($type) {
            'created'      => 'fas fa-plus-circle',
            'approved'     => 'fas fa-check-circle',
            'started'      => 'fas fa-play-circle',
            'completed'    => 'fas fa-flag-checkered',
            'waste_needed' => 'fas fa-trash-alt',
            'overdue'      => 'fas fa-exclamation-triangle',
            default        => 'fas fa-bell',
        };
    }

    private function colorForType(string $type): string
    {
        return match ($type) {
            'created'      => 'info',
            'approved'     => 'success',
            'started'      => 'primary',
            'completed'    => 'success',
            'waste_needed' => 'warning',
            'overdue'      => 'danger',
            default        => 'secondary',
        };
    }
}
