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
        Schema::table('familles', function (Blueprint $table) {
            $table->decimal('prix_client', 15, 2)->default(0)->after('sort_order');
            $table->decimal('prix_grossiste', 15, 2)->default(0)->after('prix_client');
            $table->decimal('prix_commercial', 15, 2)->default(0)->after('prix_grossiste');
            $table->decimal('prix_special', 15, 2)->default(0)->after('prix_commercial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('familles', function (Blueprint $table) {
            $table->dropColumn([
                'prix_client',
                'prix_grossiste',
                'prix_commercial',
                'prix_special'
            ]);
        });
    }
};
