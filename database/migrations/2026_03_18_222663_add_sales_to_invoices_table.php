<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->unsignedBigInteger('source_sale_id')->nullable()->after('family_name');
            $table->foreign('source_sale_id')->references('order_id')->on('sales_orders')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_items');
    }
};
