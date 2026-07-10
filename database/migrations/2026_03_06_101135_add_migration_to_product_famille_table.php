<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('product_famille', function (Blueprint $table) {
            $table->decimal('prix_client_m3', 10, 2)->nullable()->after('prix_client');
            $table->decimal('prix_grossiste_m3', 10, 2)->nullable()->after('prix_grossiste');
            $table->decimal('prix_commercial_m3', 10, 2)->nullable()->after('prix_commercial');
            $table->decimal('prix_special_m3', 10, 2)->nullable()->after('prix_special');
            $table->decimal('volume_applied', 10, 4)->nullable()->after('prix_special_m3');
        });
    }

    public function down()
    {
        Schema::table('product_famille', function (Blueprint $table) {
            $table->dropColumn([
                'prix_client_m3',
                'prix_grossiste_m3',
                'prix_commercial_m3',
                'prix_special_m3',
                'volume_applied'
            ]);
        });
    }
};
