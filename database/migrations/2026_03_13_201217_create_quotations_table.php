<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuotationsTable extends Migration
{
    public function up()
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id('quote_id');
            $table->string('quote_number')->unique();
            $table->foreignId('client_id')->constrained('clients', 'client_id');
            $table->date('quote_date');
            $table->date('valid_until')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('final_amount', 15, 2)->default(0);
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->foreignId('created_by')->constrained('users', 'id');
            $table->timestamps();

            $table->index('quote_number');
            $table->index('client_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('quotations');
    }
}
