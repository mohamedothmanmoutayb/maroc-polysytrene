<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('client_balance_history', function (Blueprint $table) {
            $table->id('history_id');
            $table->unsignedBigInteger('client_id');
            $table->decimal('previous_balance', 15, 2);
            $table->decimal('new_balance', 15, 2);
            $table->decimal('amount', 15, 2);
            $table->string('type'); // 'order_created', 'order_updated', 'payment_added', 'payment_updated', 'payment_deleted'
            $table->string('reference_type'); // 'sales_order', 'sales_order_payment'
            $table->unsignedBigInteger('reference_id');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('client_id')->on('clients')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['client_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('client_balance_history');
    }
};
