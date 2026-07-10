<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('raw_material_purchases', function (Blueprint $table) {
            $table->boolean('include_tva')->default(true)->after('total_amount');
        });
    }

    public function down()
    {
        Schema::table('raw_material_purchases', function (Blueprint $table) {
            $table->dropColumn('include_tva');
        });
    }
};
