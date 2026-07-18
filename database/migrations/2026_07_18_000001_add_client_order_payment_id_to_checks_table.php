<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checks', function (Blueprint $table) {
            $table->integer('client_id')->nullable()->after('check_type');
            $table->integer('order_id')->nullable()->after('client_id');
            $table->unsignedBigInteger('payment_id')->nullable()->after('order_id');

            $table->foreign('client_id')->references('client_id')->on('clients')->onDelete('set null');
            $table->foreign('order_id')->references('order_id')->on('sales_orders')->onDelete('set null');
            $table->foreign('payment_id')->references('payment_id')->on('sales_order_payments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('checks', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['order_id']);
            $table->dropForeign(['payment_id']);
            $table->dropColumn(['client_id', 'order_id', 'payment_id']);
        });
    }
};
