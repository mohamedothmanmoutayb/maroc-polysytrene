<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('credit_note_order_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('credit_note_id');
            $table->unsignedBigInteger('order_id');
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->foreign('credit_note_id')->references('credit_note_id')->on('credit_notes')->onDelete('cascade');
            $table->foreign('order_id')->references('order_id')->on('sales_orders')->onDelete('cascade');
        });

        Schema::table('sales_order_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('credit_note_id')->nullable()->after('order_id');
            $table->foreign('credit_note_id')->references('credit_note_id')->on('credit_notes')->onDelete('set null');
        });

        Schema::table('credit_notes', function (Blueprint $table) {
            $table->string('disposition', 20)->default('refund')->after('total_amount');
        });
    }

    public function down()
    {
        Schema::table('sales_order_payments', function (Blueprint $table) {
            $table->dropForeign(['credit_note_id']);
            $table->dropColumn('credit_note_id');
        });

        Schema::table('credit_notes', function (Blueprint $table) {
            $table->dropColumn('disposition');
        });

        Schema::dropIfExists('credit_note_order_applications');
    }
};
