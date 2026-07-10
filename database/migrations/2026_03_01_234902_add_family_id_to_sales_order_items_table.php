<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('family_id')->after('order_id')->nullable();
            $table->index('family_id');
        });
    }

    public function down()
    {
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->dropColumn('family_id');
        });
    }
};
