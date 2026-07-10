<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('production_orders', 'material_source')) {
                $table->enum('material_source', ['bom_only', 'chutes_only', 'both'])
                      ->nullable()
                      ->after('waste_percentage');
            }

            if (!Schema::hasColumn('production_orders', 'bom_percentage')) {
                $table->decimal('bom_percentage', 5, 2)
                      ->default(100)
                      ->after('material_source');
            }

            if (!Schema::hasColumn('production_orders', 'chutes_volume')) {
                $table->decimal('chutes_volume', 10, 4)
                      ->default(0)
                      ->after('bom_percentage');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropColumn(['material_source', 'bom_percentage', 'chutes_volume']);
        });
    }
};
