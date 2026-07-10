<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Magazine;

class MagazineSeeder extends Seeder
{
    public function run()
    {
        $magazines = [
            [
                'magazine_code' => 'MAG-01',
                'magazine_name' => 'Magasin Principal',
                'location' => 'Zone A',
                'description' => 'Magasin principal pour les matières premières',
                'is_active' => true,
            ],
            [
                'magazine_code' => 'MAG-02',
                'magazine_name' => 'Magasin Secondaire',
                'location' => 'Zone B',
                'description' => 'Magasin secondaire pour stockage supplémentaire',
                'is_active' => true,
            ],
            [
                'magazine_code' => 'MAG-03',
                'magazine_name' => 'Magasin Réfrigéré',
                'location' => 'Zone C',
                'description' => 'Pour les matières nécessitant une température contrôlée',
                'is_active' => true,
            ],
        ];

        foreach ($magazines as $magazine) {
            Magazine::create($magazine);
        }
    }
}
