<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->boolean('waste_declaration_required')->default(false)->after('waste_volume');
            $table->boolean('waste_declaration_completed')->default(false)->after('waste_declaration_required');
        });
    }

    public function down()
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropColumn(['waste_declaration_required', 'waste_declaration_completed']);
        });
    }
};
