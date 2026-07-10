<?php

namespace App\Helpers;

use App\Models\Vehicle;
use App\Models\Driver;
use Carbon\Carbon;

class NotificationHelper
{
    /**
     * Get all notifications from vehicles and drivers
     *
     * @return array
     */
    public static function getAllNotifications()
    {
        $notifications = [];

        // Vehicle notifications
        $vehicles = Vehicle::with('documents')->get();
        foreach ($vehicles as $vehicle) {
            $notifications = array_merge($notifications, self::getVehicleNotifications($vehicle));
        }

        // Driver notifications
        $drivers = Driver::with('employee')->where('status', 'active')->get();
        foreach ($drivers as $driver) {
            $notifications = array_merge($notifications, self::getDriverNotifications($driver));
        }

        // Sort by date (soonest first)
        usort($notifications, function($a, $b) {
            if (!isset($a['date']) || !isset($b['date'])) {
                return 0;
            }
            return strtotime($a['date']) - strtotime($b['date']);
        });

        return $notifications;
    }

    /**
     * Get notifications for a vehicle
     *
     * @param Vehicle $vehicle
     * @return array
     */
    private static function getVehicleNotifications($vehicle)
    {
        $notifications = [];

        // Check each document for the vehicle
        foreach ($vehicle->documents as $document) {
            if (!$document->end_date || !$document->is_current) {
                continue;
            }

            $daysLeft = Carbon::now()->diffInDays($document->end_date, false);
            $documentType = $document->documentType;

            // Document expired
            if ($daysLeft < 0) {
                $notifications[] = [
                    'type' => 'vehicle_document_expired',
                    'title' => 'Document expiré - ' . $documentType->type_name,
                    'message' => "Le document {$documentType->type_name} du véhicule {$vehicle->registration_number} est expiré depuis " . abs($daysLeft) . " jours.",
                    'date' => $document->end_date,
                    'color' => 'danger',
                    'icon' => 'fas fa-file-alt',
                    'link' => route('vehicles.show', $vehicle->vehicle_id)
                ];
            }
            // Document expires within reminder period
            elseif ($daysLeft <= ($documentType->reminder_days_before ?? 30) && $daysLeft >= 0) {
                $notifications[] = [
                    'type' => 'vehicle_document_expiring',
                    'title' => 'Document expirant bientôt - ' . $documentType->type_name,
                    'message' => "Le document {$documentType->type_name} du véhicule {$vehicle->registration_number} expire dans {$daysLeft} jours.",
                    'date' => $document->end_date,
                    'color' => $daysLeft <= 10 ? 'danger' : 'warning',
                    'icon' => 'fas fa-clock',
                    'link' => route('vehicles.show', $vehicle->vehicle_id)
                ];
            }
        }

        return $notifications;
    }

    /**
     * Get notifications for a driver
     *
     * @param Driver $driver
     * @return array
     */
    private static function getDriverNotifications($driver)
    {
        $notifications = [];

        // Check license expiration
        if ($driver->license_expiry_date) {
            $daysLeft = Carbon::now()->diffInDays($driver->license_expiry_date, false);

            if ($daysLeft < 0) {
                $notifications[] = [
                    'type' => 'driver_license_expired',
                    'title' => 'Permis de conduire expiré',
                    'message' => "Le permis de conduire de {$driver->full_name} est expiré depuis " . abs($daysLeft) . " jours.",
                    'date' => $driver->license_expiry_date,
                    'color' => 'danger',
                    'icon' => 'fas fa-id-card',
                    'link' => route('drivers.show', $driver->driver_id)
                ];
            } elseif ($daysLeft <= 30 && $daysLeft >= 0) {
                $color = $daysLeft <= 10 ? 'danger' : ($daysLeft <= 20 ? 'warning' : 'info');
                $notifications[] = [
                    'type' => 'driver_license_expiring',
                    'title' => 'Permis de conduire expirant bientôt',
                    'message' => "Le permis de conduire de {$driver->full_name} expire dans {$daysLeft} jours.",
                    'date' => $driver->license_expiry_date,
                    'color' => $color,
                    'icon' => 'fas fa-id-card',
                    'link' => route('drivers.show', $driver->driver_id)
                ];
            }
        }

        // Check medical visit
        if ($driver->next_medical_visit_date) {
            $daysLeft = Carbon::now()->diffInDays($driver->next_medical_visit_date, false);

            if ($daysLeft < 0) {
                $notifications[] = [
                    'type' => 'driver_medical_overdue',
                    'title' => 'Visite médicale en retard',
                    'message' => "La visite médicale de {$driver->full_name} est en retard de " . abs($daysLeft) . " jours.",
                    'date' => $driver->next_medical_visit_date,
                    'color' => 'danger',
                    'icon' => 'fas fa-stethoscope',
                    'link' => route('drivers.show', $driver->driver_id)
                ];
            } elseif ($daysLeft <= 30 && $daysLeft >= 0) {
                $color = $daysLeft <= 10 ? 'warning' : 'info';
                $notifications[] = [
                    'type' => 'driver_medical_upcoming',
                    'title' => 'Visite médicale prévue',
                    'message' => "La visite médicale de {$driver->full_name} est prévue dans {$daysLeft} jours.",
                    'date' => $driver->next_medical_visit_date,
                    'color' => $color,
                    'icon' => 'fas fa-stethoscope',
                    'link' => route('drivers.show', $driver->driver_id)
                ];
            }
        }

        return $notifications;
    }

    /**
     * Get urgent notifications (expired or expiring within 10 days)
     *
     * @return array
     */
    public static function getUrgentNotifications()
    {
        $allNotifications = self::getAllNotifications();

        return array_filter($allNotifications, function($notification) {
            return in_array($notification['color'], ['danger', 'warning']);
        });
    }

    /**
     * Get upcoming notifications (expiring within 30 days)
     *
     * @return array
     */
    public static function getUpcomingNotifications()
    {
        $allNotifications = self::getAllNotifications();

        return array_filter($allNotifications, function($notification) {
            return $notification['color'] == 'info';
        });
    }

    /**
     * Get unread count (based on session or database)
     *
     * @return int
     */
    public static function getUnreadCount()
    {
        // Store last viewed timestamp in session
        $lastViewed = session('notifications_last_viewed', Carbon::now()->subDays(7));

        $allNotifications = self::getAllNotifications();
        $unreadCount = 0;

        foreach ($allNotifications as $notification) {
            if (isset($notification['date']) && Carbon::parse($notification['date']) > $lastViewed) {
                $unreadCount++;
            }
        }

        return $unreadCount;
    }

    /**
     * Mark notifications as read
     *
     * @return void
     */
    public static function markAsRead()
    {
        session(['notifications_last_viewed' => Carbon::now()]);
    }

    /**
     * Get grouped notifications by category
     *
     * @return array
     */
    public static function getGroupedNotifications()
    {
        $notifications = self::getAllNotifications();

        $grouped = [
            'expired' => [],
            'expiring_soon' => [],
            'upcoming' => [],
            'other' => []
        ];

        foreach ($notifications as $notification) {
            if ($notification['color'] == 'danger') {
                $grouped['expired'][] = $notification;
            } elseif ($notification['color'] == 'warning') {
                $grouped['expiring_soon'][] = $notification;
            } elseif ($notification['color'] == 'info') {
                $grouped['upcoming'][] = $notification;
            } else {
                $grouped['other'][] = $notification;
            }
        }

        return $grouped;
    }

    /**
     * Get notifications count by type
     *
     * @return array
     */
    public static function getNotificationsCount()
    {
        $notifications = self::getAllNotifications();

        $counts = [
            'total' => count($notifications),
            'expired' => 0,
            'expiring_soon' => 0,
            'upcoming' => 0,
            'vehicles' => 0,
            'drivers' => 0
        ];

        foreach ($notifications as $notification) {
            if ($notification['color'] == 'danger') {
                $counts['expired']++;
            } elseif ($notification['color'] == 'warning') {
                $counts['expiring_soon']++;
            } elseif ($notification['color'] == 'info') {
                $counts['upcoming']++;
            }

            if (strpos($notification['type'], 'vehicle') !== false) {
                $counts['vehicles']++;
            } elseif (strpos($notification['type'], 'driver') !== false) {
                $counts['drivers']++;
            }
        }

        return $counts;
    }

    /**
     * Send email notifications (to be implemented)
     *
     * @return void
     */
    public static function sendEmailNotifications()
    {
        // This method can be called by a cron job to send email notifications
        $urgentNotifications = self::getUrgentNotifications();

        foreach ($urgentNotifications as $notification) {
            // Logic to send email
            // You can implement email sending here
        }
    }
}
