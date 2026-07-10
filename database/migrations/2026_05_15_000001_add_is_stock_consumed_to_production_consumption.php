<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_consumption', function (Blueprint $table) {
            $table->boolean('is_stock_consumed')->default(false)->after('notes');
            $table->decimal('stock_consumed_quantity', 12, 4)->nullable()->after('is_stock_consumed');
        });
    }

    public function down(): void
    {
        Schema::table('production_consumption', function (Blueprint $table) {
            $table->dropColumn(['is_stock_consumed', 'stock_consumed_quantity']);
        });
    }
};
