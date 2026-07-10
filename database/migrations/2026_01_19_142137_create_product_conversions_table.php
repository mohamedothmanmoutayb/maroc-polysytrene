<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_conversions', function (Blueprint $table) {
            $table->id('conversion_id');
            $table->unsignedBigInteger('parent_product_id');
            $table->unsignedBigInteger('child_product_id');
            $table->decimal('conversion_rate', 10, 4);
            $table->decimal('waste_percentage', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('parent_product_id')->references('product_id')->on('products')->onDelete('cascade');
            $table->foreign('child_product_id')->references('product_id')->on('products')->onDelete('cascade');

            // Unique constraint
            $table->unique(['parent_product_id', 'child_product_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_conversions');
    }
};
