<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockMovementDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('stock_movement_details', function (Blueprint $table) {
            $table->id('stock_detail_id');
            $table->unsignedBigInteger('stock_movement_id');
            $table->unsignedBigInteger('material_id');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->decimal('remaining_quantity', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('stock_movement_id')->references('stock_movement_id')->on('raw_material_stock_movements')->onDelete('cascade');
            $table->foreign('material_id')->references('material_id')->on('raw_materials')->onDelete('cascade');
        });

        Schema::table('raw_materials', function (Blueprint $table) {
            $table->dropColumn(['unit_cost', 'current_stock']);
        });
    }

    public function down()
    {
        Schema::table('raw_materials', function (Blueprint $table) {
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->decimal('current_stock', 10, 2)->default(0);
        });

        Schema::dropIfExists('stock_movement_details');
    }
}
