<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_payment_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('check_id')->nullable()->after('document_type');
            $table->unsignedBigInteger('traite_id')->nullable()->after('check_id');

            $table->foreign('check_id')->references('check_id')->on('checks')->nullOnDelete();
            $table->foreign('traite_id')->references('traite_id')->on('traites')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_payment_documents', function (Blueprint $table) {
            $table->dropForeign(['check_id']);
            $table->dropForeign(['traite_id']);
            $table->dropColumn(['check_id', 'traite_id']);
        });
    }
};
