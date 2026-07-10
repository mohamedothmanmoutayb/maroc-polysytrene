<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\RolePermissionController;

class SeedPermissionsCommand extends Command
{
    protected $signature = 'permissions:seed';
    protected $description = 'Seed default permissions and roles';

    public function handle()
    {
        $this->info('Seeding permissions and roles...');
        RolePermissionController::seedPermissions();
        $this->info('Permissions and roles seeded successfully!');
    }
}
