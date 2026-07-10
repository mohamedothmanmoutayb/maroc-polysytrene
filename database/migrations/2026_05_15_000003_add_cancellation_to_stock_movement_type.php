<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE raw_material_stock_movements MODIFY COLUMN movement_type ENUM('purchase','production_consumption','adjustment','return','transfer','waste_recovery','sale','cancellation')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE raw_material_stock_movements MODIFY COLUMN movement_type ENUM('purchase','production_consumption','adjustment','return','transfer','waste_recovery','sale')");
    }
};
