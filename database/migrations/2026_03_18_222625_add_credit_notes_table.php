<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Schema::create('credit_notes', function (Blueprint $table) {
        //     $table->id('credit_note_id');
        //     $table->string('credit_note_number', 50)->unique();
        //     $table->unsignedBigInteger('client_id');
        //     $table->unsignedBigInteger('sales_order_id')->nullable();
        //     $table->date('credit_note_date');
        //     $table->decimal('total_amount', 15, 2)->default(0);
        //     $table->string('status', 20)->default('draft');
        //     $table->text('reason')->nullable();
        //     $table->text('notes')->nullable();
        //     $table->unsignedBigInteger('created_by')->nullable();
        //     $table->unsignedBigInteger('approved_by')->nullable();
        //     $table->timestamp('approved_at')->nullable();
        //     $table->timestamps();
        //     $table->softDeletes();

        //     $table->foreign('client_id')->references('client_id')->on('clients');
        //     $table->foreign('sales_order_id')->references('order_id')->on('sales_orders');
        //     $table->foreign('created_by')->references('id')->on('users');
        //     $table->foreign('approved_by')->references('id')->on('users');

        //     $table->index('credit_note_number');
        //     $table->index('client_id');
        //     $table->index('status');
        // });

        Schema::create('credit_note_items', function (Blueprint $table) {
            $table->id('credit_note_item_id');
            $table->unsignedBigInteger('credit_note_id');
            $table->unsignedBigInteger('order_item_id')->nullable();
            $table->string('item_type', 50);
            $table->unsignedBigInteger('item_id');
            $table->string('item_name', 255);
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->unsignedBigInteger('family_id')->nullable();
            $table->string('family_name', 255)->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->foreign('credit_note_id')->references('credit_note_id')->on('credit_notes')->onDelete('cascade');
            $table->foreign('order_item_id')->references('order_item_id')->on('sales_order_items');
        });
    }

    public function down()
    {
        Schema::dropIfExists('credit_note_items');
        Schema::dropIfExists('credit_notes');
    }
};
