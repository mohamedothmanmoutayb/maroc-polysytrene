<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleDocumentType;

class VehicleDocumentTypesSeeder extends Seeder
{
    public function run()
    {
        $documentTypes = [
            [
                'type_code' => 'insurance',
                'type_name' => 'Assurance',
                'description' => 'Assurance du véhicule',
                'sort_order' => 1,
                'default_duration_days' => 365,
                'reminder_days_before' => 30,
            ],
            [
                'type_code' => 'registration',
                'type_name' => 'Carte Grise',
                'description' => 'Certificat d\'immatriculation',
                'sort_order' => 2,
                'default_duration_days' => 365,
                'reminder_days_before' => 30,
            ],
            [
                'type_code' => 'technical_control',
                'type_name' => 'Visite Technique',
                'description' => 'Contrôle technique du véhicule',
                'sort_order' => 3,
                'default_duration_days' => 365,
                'reminder_days_before' => 30,
            ],
            [
                'type_code' => 'maintenance',
                'type_name' => 'Maintenance',
                'description' => 'Maintenance programmée',
                'sort_order' => 4,
                'default_duration_days' => 180,
                'reminder_days_before' => 15,
            ],
        ];

        foreach ($documentTypes as $type) {
            VehicleDocumentType::updateOrCreate(
                ['type_code' => $type['type_code']],
                $type
            );
        }
    }
}
