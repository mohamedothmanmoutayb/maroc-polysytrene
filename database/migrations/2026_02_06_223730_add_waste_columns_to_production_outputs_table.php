<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('production_output', function (Blueprint $table) {
            $table->decimal('recyclable_waste_volume', 12, 6)->default(0)->after('waste_volume_m3');
            $table->decimal('pure_waste_volume', 12, 6)->default(0)->after('recyclable_waste_volume');
            $table->boolean('waste_declaration_completed')->default(false)->after('pure_waste_volume');
        });
    }

    public function down()
    {
        Schema::table('production_output', function (Blueprint $table) {
            $table->dropColumn([
                'recyclable_waste_volume',
                'pure_waste_volume',
                'waste_declaration_completed'
            ]);
        });
    }
};
