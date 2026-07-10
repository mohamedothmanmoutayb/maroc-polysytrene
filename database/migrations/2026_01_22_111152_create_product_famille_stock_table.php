<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_famille_stock', function (Blueprint $table) {
            $table->id('famille_stock_id');
            $table->foreignId('product_id')->constrained('products', 'product_id')->onDelete('cascade');
            $table->foreignId('famille_id')->constrained('familles', 'famille_id')->onDelete('cascade');
            $table->string('famille_name');
            $table->decimal('current_quantity', 12, 4)->default(0);
            $table->decimal('reserved_quantity', 12, 4)->default(0);
            $table->decimal('available_quantity', 12, 4)->default(0);
            $table->string('location')->default('Entrepôt Principal');
            $table->dateTime('last_updated')->nullable();
            $table->dateTime('last_restocked')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'famille_id']);
            $table->index(['product_id', 'is_active']);
            $table->index(['famille_name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_famille_stock');
    }
};
