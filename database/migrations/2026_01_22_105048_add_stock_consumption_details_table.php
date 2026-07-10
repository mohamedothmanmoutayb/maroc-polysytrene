<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stock_consumption_details', function (Blueprint $table) {
            $table->id('consumption_detail_id');
            $table->unsignedBigInteger('stock_movement_id');
            $table->unsignedBigInteger('stock_detail_id');
            $table->decimal('quantity_consumed', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_cost', 12, 2);
            $table->timestamps();

            $table->foreign('stock_movement_id')
                  ->references('movement_id')
                  ->on('raw_material_stock_movements')
                  ->onDelete('cascade');

            $table->foreign('stock_detail_id')
                  ->references('stock_detail_id')
                  ->on('stock_movement_details')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_consumption_details');
    }
};
