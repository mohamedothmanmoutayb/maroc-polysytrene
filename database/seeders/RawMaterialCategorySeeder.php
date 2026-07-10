<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RawMaterialCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['category_name' => 'Tissus', 'description' => 'Tissus pour matelas', 'is_active' => true],
            ['category_name' => 'Mousses', 'description' => 'Mousses polyuréthane', 'is_active' => true],
            ['category_name' => 'Ressorts', 'description' => 'Ressorts hélicoïdaux', 'is_active' => true],
            ['category_name' => 'Bois', 'description' => 'Bois pour cadre', 'is_active' => true],
            ['category_name' => 'Rembourrage', 'description' => 'Matériaux de rembourrage', 'is_active' => true],
            ['category_name' => 'Accessoires', 'description' => 'Fermetures éclair, rubans', 'is_active' => true],
            ['category_name' => 'Chimiques', 'description' => 'Colle, produits chimiques', 'is_active' => true],
            ['category_name' => 'Emballage', 'description' => 'Carton, plastique', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            DB::table('raw_material_categories')->insert([
                'category_name' => $category['category_name'],
                'description' => $category['description'],
                'is_active' => $category['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ 8 catégories de matières premières créées.');
    }
}
