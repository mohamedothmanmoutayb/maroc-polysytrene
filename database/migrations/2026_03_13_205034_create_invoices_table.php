<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id('invoice_id');
            $table->string('invoice_number')->unique();
            $table->foreignId('client_id')->constrained('clients', 'client_id');
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('final_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('payment_status')->default('pending'); // pending, partial, paid
            $table->string('status')->default('draft'); // draft, sent, paid, cancelled
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->foreignId('created_by')->constrained('users', 'id');
            $table->timestamps();

            $table->index('invoice_number');
            $table->index('client_id');
            $table->index('status');
            $table->index('payment_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
