<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('product_stock_movements', function (Blueprint $table) {
            $table->foreignId('famille_id')->nullable()->constrained('familles', 'famille_id')->onDelete('set null');
            $table->string('famille_name')->nullable()->after('famille_id');
            $table->index(['product_id', 'famille_id']);
        });
    }

    public function down()
    {
        Schema::table('product_stock_movements', function (Blueprint $table) {
            $table->dropForeign(['famille_id']);
            $table->dropColumn(['famille_id', 'famille_name']);
        });
    }
};
