<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('item_id')->after('order_id')->nullable();
            $table->index('item_id');
        });
    }

    public function down()
    {
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->dropColumn('item_id');
        });
    }
};
