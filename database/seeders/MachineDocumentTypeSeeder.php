<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MachineDocumentType;

class MachineDocumentTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            [
                'type_code' => 'maintenance_contract',
                'type_name' => 'Contrat de Maintenance',
                'description' => 'Contrat de maintenance préventive et curative',
                'reminder_days_before' => 30,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'type_code' => 'inspection',
                'type_name' => 'Inspection Périodique',
                'description' => 'Inspection technique de sécurité',
                'reminder_days_before' => 30,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'type_code' => 'warranty',
                'type_name' => 'Garantie',
                'description' => 'Garantie constructeur',
                'reminder_days_before' => 30,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'type_code' => 'certification',
                'type_name' => 'Certification',
                'description' => 'Certification de conformité',
                'reminder_days_before' => 30,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'type_code' => 'insurance',
                'type_name' => 'Assurance',
                'description' => 'Assurance machine',
                'reminder_days_before' => 30,
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($types as $type) {
            MachineDocumentType::updateOrCreate(
                ['type_code' => $type['type_code']],
                $type
            );
        }
    }
}
