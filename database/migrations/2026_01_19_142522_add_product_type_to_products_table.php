<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('product_type', ['production', 'sales', 'both'])->default('sales')->after('unit_of_measure');
            $table->string('unit_of_measure_production')->nullable()->after('product_type');
            $table->string('unit_of_measure_sales')->nullable()->after('unit_of_measure_production');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['product_type', 'unit_of_measure_production', 'unit_of_measure_sales']);
        });
    }
};
