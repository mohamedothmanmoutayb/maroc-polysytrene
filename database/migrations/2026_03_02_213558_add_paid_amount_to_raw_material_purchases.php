<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('raw_material_purchases', function (Blueprint $table) {
            $table->decimal('paid_amount', 15, 2)->default(0)->after('final_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('raw_material_purchases', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });
    }
};
