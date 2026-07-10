<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'full_name' => 'Mohamed Othman',
                'company_name' => 'Textile Maroc SA',
                'representative_name' => 'Ahmed Benali',
                'email' => 'contact@textilemaroc.ma',
                'phone' => '+212 522 123456',
                'address' => 'Zone Industrielle, Casablanca',
                'payment_terms' => '30 jours',
                'is_active' => true
            ],
            [
                'full_name' => 'Mouad Abraim',
                'company_name' => 'Mousse Pro',
                'representative_name' => 'Fatima Zahra',
                'email' => 'info@moussepro.ma',
                'phone' => '+212 530 987654',
                'address' => 'Sidi Maarouf, Casablanca',
                'payment_terms' => '45 jours',
                'is_active' => true
            ],
            [
                'full_name' => 'Hanane Abir',
                'company_name' => 'Springs International',
                'representative_name' => 'Karim El Fassi',
                'email' => 'sales@springs.ma',
                'phone' => '+212 524 456789',
                'address' => 'Route de Rabat, km 12, Casablanca',
                'payment_terms' => '15 jours',
                'is_active' => true
            ],
            [
                'full_name' => 'Sana Jamil',
                'company_name' => 'Bois du Maroc',
                'representative_name' => 'Mohamed Cherkaoui',
                'email' => 'bois@marocwood.ma',
                'phone' => '+212 523 789123',
                'address' => 'Aïn Sebaâ, Casablanca',
                'payment_terms' => '60 jours',
                'is_active' => true
            ],
            [
                'full_name' => 'Mounir ahmed',
                'company_name' => 'Chemical Supplies',
                'representative_name' => 'Samira Alaoui',
                'email' => 'samira@chemical.ma',
                'phone' => '+212 525 321987',
                'address' => 'Mohammedia',
                'payment_terms' => '30 jours',
                'is_active' => true
            ],
        ];

        foreach ($suppliers as $supplier) {
            DB::table('suppliers')->insert([
                'full_name' => $supplier['full_name'],
                'company_name' => $supplier['company_name'],
                'representative_name' => $supplier['representative_name'],
                'email' => $supplier['email'],
                'phone' => $supplier['phone'],
                'address' => $supplier['address'],
                'payment_terms' => $supplier['payment_terms'],
                'is_active' => $supplier['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ 5 fournisseurs créés.');
    }
}
