<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('familles', function (Blueprint $table) {
            $table->id('famille_id');
            $table->string('famille_code')->unique();
            $table->string('famille_name');
            $table->text('description')->nullable();
            $table->foreignId('product_id')->constrained('products', 'product_id')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('familles');
    }
};
