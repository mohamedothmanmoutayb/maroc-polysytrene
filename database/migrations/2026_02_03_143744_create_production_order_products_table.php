<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('production_order_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_order_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('conversion_rate', 10, 4)->default(1);
            $table->integer('quantity_to_produce')->default(0);
            $table->decimal('source_required', 12, 4)->default(0);
            $table->decimal('volume_per_unit', 12, 4)->default(0);
            $table->decimal('total_volume', 12, 4)->default(0);
            $table->timestamps();

            $table->foreign('production_order_id')
                  ->references('order_id')
                  ->on('production_orders')
                  ->onDelete('cascade');

            $table->foreign('product_id')
                  ->references('product_id')
                  ->on('products')
                  ->onDelete('cascade');

            $table->unique(['production_order_id', 'product_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('production_order_products');
    }
};
