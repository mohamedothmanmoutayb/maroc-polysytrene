<?php

namespace App\Console\Commands;

use App\Models\Driver;
use App\Models\Machine;
use App\Models\Notification;
use App\Models\ProductStock;
use App\Models\RawMaterial;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class CheckAndCreateNotifications extends Command
{
    protected $signature   = 'notifications:check';
    protected $description = 'Daily check: create system notifications for alerts (documents, stock, drivers)';

    public function handle(): int
    {
        $admins = User::role('admin')->get();

        if ($admins->isEmpty()) {
            $this->info('No admin users found — skipping.');
            return 0;
        }

        $this->checkVehicleDocuments($admins);
        $this->checkMachineDocuments($admins);
        $this->checkDriverLicenses($admins);
        $this->checkDriverMedicalVisits($admins);
        $this->checkRawMaterialStock($admins);
        $this->checkProductStock($admins);

        $this->info('Notifications check completed.');
        Log::info('notifications:check — completed successfully.');

        return 0;
    }

    // -------------------------------------------------------------------------
    // Vehicle documents
    // -------------------------------------------------------------------------
    private function checkVehicleDocuments(Collection $admins): void
    {
        try {
            $activeRefs = [];

            $vehicles = Vehicle::with(['currentDocuments.documentType'])->get();

            foreach ($vehicles as $vehicle) {
                foreach ($vehicle->currentDocuments as $doc) {
                    if (!$doc->end_date) {
                        continue;
                    }

                    $daysLeft = Carbon::now()->diffInDays($doc->end_date, false);
                    $reminder = optional($doc->documentType)->reminder_days_before ?? 30;

                    if ($daysLeft >= 0 && $daysLeft > $reminder) {
                        continue;
                    }

                    $refKey   = "vehicle_doc_{$doc->id}";
                    $activeRefs[] = $refKey;
                    $expired  = $daysLeft < 0;
                    $typeName = optional($doc->documentType)->type_name ?? 'Document';
                    $color    = $expired ? 'danger' : ($daysLeft <= 10 ? 'danger' : ($daysLeft <= 20 ? 'warning' : 'info'));

                    $payload = [
                        'type'          => $expired ? 'vehicle_document_expired' : 'vehicle_document_expiring',
                        'title'         => $expired
                            ? "Document expiré – {$typeName}"
                            : "Document expire bientôt – {$typeName}",
                        'message'       => $expired
                            ? "Le document {$typeName} du véhicule {$vehicle->registration_number} est expiré depuis " . abs((int) $daysLeft) . " jours."
                            : "Le document {$typeName} du véhicule {$vehicle->registration_number} expire dans {$daysLeft} jours.",
                        'link'          => route('vehicles.show', $vehicle->vehicle_id),
                        'data'          => ['category' => 'vehicle', 'color' => $color, 'icon' => 'fas fa-car'],
                        'expires_at'    => null,
                    ];

                    foreach ($admins as $admin) {
                        Notification::firstOrCreate(
                            ['user_id' => $admin->id, 'reference_key' => $refKey],
                            $payload
                        );
                    }
                }
            }

            // Remove notifications for conditions that are no longer alerting
            $this->pruneResolved($admins, ['vehicle_document_expired', 'vehicle_document_expiring'], $activeRefs);
        } catch (\Exception $e) {
            Log::warning('CheckNotifications::checkVehicleDocuments — ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Machine documents
    // -------------------------------------------------------------------------
    private function checkMachineDocuments(Collection $admins): void
    {
        try {
            $activeRefs = [];

            $machines = Machine::with(['currentDocuments.documentType'])->get();

            foreach ($machines as $machine) {
                foreach ($machine->currentDocuments as $doc) {
                    if (!$doc->end_date) {
                        continue;
                    }

                    $daysLeft = Carbon::now()->diffInDays($doc->end_date, false);
                    $reminder = optional($doc->documentType)->reminder_days_before ?? 30;

                    if ($daysLeft >= 0 && $daysLeft > $reminder) {
                        continue;
                    }

                    $refKey   = "machine_doc_{$doc->id}";
                    $activeRefs[] = $refKey;
                    $expired  = $daysLeft < 0;
                    $typeName = optional($doc->documentType)->type_name ?? 'Document';
                    $color    = $expired ? 'danger' : ($daysLeft <= 10 ? 'danger' : ($daysLeft <= 20 ? 'warning' : 'info'));

                    $payload = [
                        'type'          => $expired ? 'machine_document_expired' : 'machine_document_expiring',
                        'title'         => $expired
                            ? "Document expiré – {$typeName}"
                            : "Document expire bientôt – {$typeName}",
                        'message'       => $expired
                            ? "Le document {$typeName} de la machine {$machine->name} est expiré depuis " . abs((int) $daysLeft) . " jours."
                            : "Le document {$typeName} de la machine {$machine->name} expire dans {$daysLeft} jours.",
                        'link'          => route('machines.show', $machine->machine_id),
                        'data'          => ['category' => 'machine', 'color' => $color, 'icon' => 'fas fa-cog'],
                        'expires_at'    => null,
                    ];

                    foreach ($admins as $admin) {
                        Notification::firstOrCreate(
                            ['user_id' => $admin->id, 'reference_key' => $refKey],
                            $payload
                        );
                    }
                }
            }

            $this->pruneResolved($admins, ['machine_document_expired', 'machine_document_expiring'], $activeRefs);
        } catch (\Exception $e) {
            Log::warning('CheckNotifications::checkMachineDocuments — ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Driver licences
    // -------------------------------------------------------------------------
    private function checkDriverLicenses(Collection $admins): void
    {
        try {
            $activeRefs = [];

            $drivers = Driver::with('employee')->where('status', 'active')->get();

            foreach ($drivers as $driver) {
                if (!$driver->license_expiry_date) {
                    continue;
                }

                $daysLeft = Carbon::now()->diffInDays($driver->license_expiry_date, false);

                if ($daysLeft >= 0 && $daysLeft > 30) {
                    continue;
                }

                $refKey   = "driver_license_{$driver->driver_id}";
                $activeRefs[] = $refKey;
                $expired  = $daysLeft < 0;
                $color    = $expired ? 'danger' : ($daysLeft <= 10 ? 'danger' : ($daysLeft <= 20 ? 'warning' : 'info'));

                $payload = [
                    'type'          => $expired ? 'driver_license_expired' : 'driver_license_expiring',
                    'title'         => $expired ? 'Permis de conduire expiré' : 'Permis expire bientôt',
                    'message'       => $expired
                        ? "Le permis de {$driver->full_name} est expiré depuis " . abs((int) $daysLeft) . " jours."
                        : "Le permis de {$driver->full_name} expire dans {$daysLeft} jours.",
                    'link'          => route('drivers.show', $driver->driver_id),
                    'data'          => ['category' => 'driver', 'color' => $color, 'icon' => 'fas fa-id-card'],
                    'expires_at'    => null,
                ];

                foreach ($admins as $admin) {
                    Notification::firstOrCreate(
                        ['user_id' => $admin->id, 'reference_key' => $refKey],
                        $payload
                    );
                }
            }

            $this->pruneResolved($admins, ['driver_license_expired', 'driver_license_expiring'], $activeRefs);
        } catch (\Exception $e) {
            Log::warning('CheckNotifications::checkDriverLicenses — ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Driver medical visits
    // -------------------------------------------------------------------------
    private function checkDriverMedicalVisits(Collection $admins): void
    {
        try {
            $activeRefs = [];

            $drivers = Driver::with('employee')->where('status', 'active')->get();

            foreach ($drivers as $driver) {
                if (!$driver->next_medical_visit_date) {
                    continue;
                }

                $daysLeft = Carbon::now()->diffInDays($driver->next_medical_visit_date, false);

                if ($daysLeft >= 0 && $daysLeft > 30) {
                    continue;
                }

                $refKey   = "driver_medical_{$driver->driver_id}";
                $activeRefs[] = $refKey;
                $expired  = $daysLeft < 0;
                $color    = $expired ? 'danger' : ($daysLeft <= 10 ? 'warning' : 'info');

                $payload = [
                    'type'          => $expired ? 'driver_medical_overdue' : 'driver_medical_upcoming',
                    'title'         => $expired ? 'Visite médicale en retard' : 'Visite médicale prévue',
                    'message'       => $expired
                        ? "La visite médicale de {$driver->full_name} est en retard de " . abs((int) $daysLeft) . " jours."
                        : "La visite médicale de {$driver->full_name} est prévue dans {$daysLeft} jours.",
                    'link'          => route('drivers.show', $driver->driver_id),
                    'data'          => ['category' => 'driver', 'color' => $color, 'icon' => 'fas fa-stethoscope'],
                    'expires_at'    => null,
                ];

                foreach ($admins as $admin) {
                    Notification::firstOrCreate(
                        ['user_id' => $admin->id, 'reference_key' => $refKey],
                        $payload
                    );
                }
            }

            $this->pruneResolved($admins, ['driver_medical_overdue', 'driver_medical_upcoming'], $activeRefs);
        } catch (\Exception $e) {
            Log::warning('CheckNotifications::checkDriverMedicalVisits — ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Raw material stock
    // -------------------------------------------------------------------------
    private function checkRawMaterialStock(Collection $admins): void
    {
        try {
            $activeRefs = [];

            $lowMaterials = RawMaterial::where('is_active', true)
                ->where('min_stock_level', '>', 0)
                ->whereColumn('current_stock', '<', 'min_stock_level')
                ->get();

            foreach ($lowMaterials as $mat) {
                $refKey = "raw_stock_{$mat->material_id}";
                $activeRefs[] = $refKey;

                $payload = [
                    'type'       => 'stock_alert_raw',
                    'title'      => 'Alerte stock – Matière première',
                    'message'    => "Stock de {$mat->material_name} sous le minimum. Actuel: {$mat->current_stock} {$mat->unit_of_measure}, Min: {$mat->min_stock_level}.",
                    'link'       => route('raw-materials.index'),
                    'data'       => ['category' => 'stock', 'color' => 'warning', 'icon' => 'fas fa-exclamation-triangle'],
                    'expires_at' => null,
                ];

                foreach ($admins as $admin) {
                    Notification::firstOrCreate(
                        ['user_id' => $admin->id, 'reference_key' => $refKey],
                        $payload
                    );
                }
            }

            $this->pruneResolved($admins, ['stock_alert_raw'], $activeRefs);
        } catch (\Exception $e) {
            Log::warning('CheckNotifications::checkRawMaterialStock — ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Finished product stock
    // -------------------------------------------------------------------------
    private function checkProductStock(Collection $admins): void
    {
        try {
            $activeRefs = [];

            $lowStocks = ProductStock::with('product')
                ->whereHas('product', fn($q) => $q->where('is_active', true)->where('min_stock_level', '>', 0))
                ->where('current_quantity', '<', function ($q) {
                    $q->select('min_stock_level')
                        ->from('products')
                        ->whereColumn('products.product_id', 'product_stock.product_id');
                })
                ->get();

            foreach ($lowStocks as $stock) {
                if (!$stock->product) {
                    continue;
                }

                $refKey = "product_stock_{$stock->product_id}";
                $activeRefs[] = $refKey;

                $payload = [
                    'type'       => 'stock_alert_product',
                    'title'      => 'Alerte stock – Produit fini',
                    'message'    => "Stock de {$stock->product->product_name} sous le minimum. Actuel: {$stock->current_quantity}, Min: {$stock->product->min_stock_level}.",
                    'link'       => route('products.index'),
                    'data'       => ['category' => 'stock', 'color' => 'warning', 'icon' => 'fas fa-boxes'],
                    'expires_at' => null,
                ];

                foreach ($admins as $admin) {
                    Notification::firstOrCreate(
                        ['user_id' => $admin->id, 'reference_key' => $refKey],
                        $payload
                    );
                }
            }

            $this->pruneResolved($admins, ['stock_alert_product'], $activeRefs);
        } catch (\Exception $e) {
            Log::warning('CheckNotifications::checkProductStock — ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Delete notifications whose conditions are resolved
    // -------------------------------------------------------------------------
    private function pruneResolved(Collection $admins, array $types, array $activeRefs): void
    {
        Notification::whereIn('user_id', $admins->pluck('id'))
            ->whereIn('type', $types)
            ->when(
                !empty($activeRefs),
                fn($q) => $q->whereNotIn('reference_key', $activeRefs),
                fn($q) => $q // if no active refs, delete all of these types
            )
            ->delete();
    }
}
