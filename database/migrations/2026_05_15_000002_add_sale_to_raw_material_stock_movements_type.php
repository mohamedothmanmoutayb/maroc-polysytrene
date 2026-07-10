<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE raw_material_stock_movements MODIFY COLUMN movement_type ENUM('purchase','production_consumption','adjustment','return','transfer','waste_recovery','sale')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE raw_material_stock_movements MODIFY COLUMN movement_type ENUM('purchase','production_consumption','adjustment','return','transfer','waste_recovery')");
    }
};
