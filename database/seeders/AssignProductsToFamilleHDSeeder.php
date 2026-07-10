<?php

namespace Database\Seeders;

use App\Models\Famille;
use App\Models\Product;
use Illuminate\Database\Seeder;

class AssignProductsToFamilleHDSeeder extends Seeder
{
    public function run(): void
    {
        $famille = Famille::firstOrCreate(
            ['famille_code' => 'HD'],
            [
                'famille_name' => 'HD',
                'is_active' => true,
                'sort_order' => 0,
                'prix_client' => 0,
                'prix_grossiste' => 0,
                'prix_commercial' => 0,
                'prix_special' => 0,
            ]
        );

        $prixClientM3     = (float) $famille->prix_client;
        $prixGrossisteM3  = (float) $famille->prix_grossiste;
        $prixCommercialM3 = (float) $famille->prix_commercial;
        $prixSpecialM3    = (float) $famille->prix_special;

        $products = Product::all();

        $syncData = [];

        foreach ($products as $product) {
            $volume = $product->total_volume; // uses volume_m3 or height_m*width_m*depth_m

            $syncData[$product->product_id] = [
                'quantity_per_unit'   => 1,
                'sort_order'          => 0,
                'prix_client'         => round($prixClientM3     * $volume, 2),
                'prix_client_m3'      => $prixClientM3,
                'prix_grossiste'      => round($prixGrossisteM3  * $volume, 2),
                'prix_grossiste_m3'   => $prixGrossisteM3,
                'prix_commercial'     => round($prixCommercialM3 * $volume, 2),
                'prix_commercial_m3'  => $prixCommercialM3,
                'prix_special'        => round($prixSpecialM3    * $volume, 2),
                'prix_special_m3'     => $prixSpecialM3,
                'volume_applied'      => $volume,
            ];
        }

        $famille->products()->syncWithoutDetaching($syncData);

        $this->command->info("Famille HD (ID: {$famille->famille_id}) assigned to {$products->count()} products.");
    }
}
