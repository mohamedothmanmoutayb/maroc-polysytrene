<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_item_sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_item_id');
            $table->unsignedBigInteger('sales_order_id');
            $table->decimal('quantity', 15, 4)->nullable();
            $table->timestamps();

            $table->foreign('invoice_item_id')->references('invoice_item_id')->on('invoice_items')->onDelete('cascade');
            $table->foreign('sales_order_id')->references('order_id')->on('sales_orders')->onDelete('cascade');
            $table->unique(['invoice_item_id', 'sales_order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_item_sales');
    }
};
