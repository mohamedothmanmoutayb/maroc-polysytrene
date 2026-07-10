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

        // Schema::create('sales_order_payments', function (Blueprint $table) {
        //     $table->id('payment_id');
        //     $table->unsignedBigInteger('order_id');
        //     $table->enum('payment_method', ['cash', 'check', 'transfer']);
        //     $table->decimal('amount', 15, 2);
        //     $table->date('payment_date');
        //     $table->text('notes')->nullable();
        //     $table->timestamps();

        //     $table->foreign('order_id')->references('order_id')->on('sales_orders')->onDelete('cascade');
        // });

        Schema::create('traites', function (Blueprint $table) {
            $table->id('traite_id');
            $table->string('traite_number')->unique();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('client_id');
            $table->decimal('amount', 15, 2);
            $table->date('issue_date');
            $table->date('due_date');
            $table->string('bank_name')->nullable();
            $table->string('drawee')->nullable();
            $table->text('drawee_address')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'paid', 'overdue', 'bounced'])->default('pending');
            $table->string('document_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('order_id')->on('sales_orders')->onDelete('cascade');
            $table->foreign('payment_id')->references('payment_id')->on('sales_order_payments')->onDelete('set null');
            $table->foreign('client_id')->references('client_id')->on('clients')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traites');
    }
};
