<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->json('additional_data')->nullable()->after('notes');
        });
    }

    public function down()
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropColumn('additional_data');
        });
    }
};
