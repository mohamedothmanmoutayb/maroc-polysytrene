<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AssignAdminRoleSeeder extends Seeder
{
    public function run()
    {
        // Get or create admin role if it doesn't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // Assign admin role to specific users
        $adminEmails = [
            'admin@gmail.com',
            'h.sadik@marocpolystyrene.com',
            'l.ghmouch@marocpolystyrene.com',
            'mohamed@maroc-polystyrene.com'
        ];

        foreach ($adminEmails as $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->assignRole('admin');
                $this->command->info("Admin role assigned to: {$email}");
            } else {
                $this->command->warn("User not found: {$email}");
            }
        }
    }
}
