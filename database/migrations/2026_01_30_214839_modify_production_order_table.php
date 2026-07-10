<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->decimal('source_volume', 12, 4)->nullable()->after('waste_percentage');
            $table->decimal('final_volume', 12, 4)->nullable()->after('source_volume');
            $table->decimal('total_volume_produced', 12, 4)->nullable()->after('final_volume');
            $table->decimal('waste_volume', 12, 4)->nullable()->after('total_volume_produced');
        });
    }

    public function down()
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropColumn(['source_volume', 'final_volume', 'total_volume_produced', 'waste_volume']);
        });
    }
};
