<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_stock_movements', function (Blueprint $table) {
            $table->id('movement_id');
            $table->unsignedBigInteger('product_id');
            $table->enum('movement_type', [
                'purchase',
                'sale',
                'production_output',
                'production_output_adjustment',
                'production_output_deletion',
                'stock_adjustment',
                'transfer',
                'return',
                'damage',
                'expiry'
            ]);
            $table->decimal('quantity', 15, 4);
            $table->decimal('previous_stock', 15, 4)->default(0);
            $table->decimal('new_stock', 15, 4)->default(0);
            $table->decimal('unit_cost', 15, 4)->nullable();
            $table->decimal('total_cost', 15, 4)->nullable();
            $table->enum('reference_type', [
                'purchase_order',
                'sale_order',
                'production_order',
                'production_output',
                'stock_adjustment',
                'transfer',
                'other'
            ])->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->dateTime('movement_date');
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['product_id', 'movement_date']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('movement_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_stock_movements');
    }
};
