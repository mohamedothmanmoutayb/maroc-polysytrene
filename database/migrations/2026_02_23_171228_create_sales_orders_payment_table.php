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
       Schema::table('sales_order_payments', function (Blueprint $table) {
            $table->string('document_path')->nullable()->after('notes');
            $table->string('original_filename')->nullable()->after('document_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_order_payments', function (Blueprint $table) {
            $table->dropColumn(['document_path', 'original_filename']);
        });
    }
};
