<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('products', function (Blueprint $table) {
            $table->id('product_id');
            $table->string('product_code', 50)->unique();
            $table->string('product_name', 255);
            $table->unsignedInteger('category_id');

            $table->foreign('category_id')
                ->references('category_id')
                ->on('product_categories')
                ->onDelete('restrict');


            // Product type: production, decoupage, finale
            $table->enum('product_type', ['production', 'decoupage', 'finale'])->default('production');

            // Unit of measure
            $table->string('unit_of_measure', 50);

            // Prices
            $table->decimal('price_client', 12, 2)->default(0);
            $table->decimal('price_revendeur', 12, 2)->nullable();
            $table->decimal('price_commercial', 12, 2)->nullable();
            $table->decimal('cost_price', 12, 2)->default(0);

            // Dimensions for volume calculation (in millimeters)
            $table->decimal('height_mm', 10, 2)->nullable();
            $table->decimal('width_mm', 10, 2)->nullable();
            $table->decimal('depth_mm', 10, 2)->nullable();
            $table->decimal('volume_m3', 10, 4)->nullable();
            $table->decimal('weight_kg', 10, 2)->nullable();

            // Stock levels
            $table->decimal('min_stock_level', 12, 2)->default(0);
            $table->decimal('max_stock_level', 12, 2)->nullable();

            // Production info
            $table->integer('production_time_days')->nullable();
            $table->string('material_type', 100)->nullable();
            $table->string('color', 50)->nullable();
            $table->text('description')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('product_code');
            $table->index('product_name');
            $table->index('product_type');
            $table->index('is_active');
            $table->index('category_id');
        });
        Schema::enableForeignKeyConstraints();

        // Create pivot table for product-famille many-to-many relationship
        // Schema::create('product_famille', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
        //     $table->foreignId('famille_id')->constrained('familles')->onDelete('cascade');
        //     $table->decimal('quantity_per_unit', 15, 2)->default(1);
        //     $table->integer('sort_order')->default(0);
        //     $table->timestamps();

        //     $table->unique(['product_id', 'famille_id']);
        // });
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('product_famille');
        Schema::dropIfExists('products');
    }
};
