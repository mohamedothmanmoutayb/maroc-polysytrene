<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('raw_material_purchases', function (Blueprint $table) {
            $table->json('payment_documents')->nullable()->after('payment_method');
        });

        Schema::create('purchase_payment_documents', function (Blueprint $table) {
            $table->id('document_id');
            $table->unsignedBigInteger('purchase_id');
            $table->string('document_number')->unique();
            $table->string('document_type')->nullable(); // receipt, transfer_proof, check_copy, etc.
            $table->string('file_path');
            $table->string('original_filename');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable(); // cash, bank_transfer, check
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamps();

            $table->foreign('purchase_id')
                  ->references('purchase_id')
                  ->on('raw_material_purchases')
                  ->onDelete('cascade');
            $table->foreign('uploaded_by')
                  ->references('id')
                  ->on('users');
        });
    }

    public function down()
    {
        Schema::table('raw_material_purchases', function (Blueprint $table) {
            $table->dropColumn('payment_documents');
        });
        Schema::dropIfExists('purchase_payment_documents');
    }
};
