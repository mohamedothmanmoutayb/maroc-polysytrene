<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add magazine_id to raw_material_purchases
        // Schema::table('raw_material_purchases', function (Blueprint $table) {
        //     $table->foreignId('magazine_id')->nullable()->after('supplier_id')
        //         ->constrained('magazines')->nullOnDelete();

        //     // Add percentage fields
        //     $table->decimal('tax_percentage', 5, 2)->default(20)->after('total_amount');
        //     $table->decimal('discount_percentage', 5, 2)->default(0)->after('tax_amount');
        // });

        // Create checks table
        // Schema::create('checks', function (Blueprint $table) {
        //     $table->id('check_id');
        //     $table->string('check_number')->unique();
        //     $table->enum('check_type', ['client', 'personal'])->default('client');
        //     $table->decimal('amount', 15, 2);
        //     $table->string('bank_name')->nullable();
        //     $table->string('account_holder');
        //     $table->date('issue_date');
        //     $table->date('deposit_date')->nullable();
        //     $table->date('clearing_date')->nullable();
        //     $table->enum('status', ['pending', 'deposited', 'cleared', 'bounced', 'cancelled'])->default('pending');
        //     $table->text('notes')->nullable();
        //     $table->boolean('is_active')->default(true);
        //     $table->foreignId('created_by')->nullable()->constrained('users');
        //     $table->timestamps();
        // });

        // Create check_allocations table for linking checks to purchases
        Schema::create('check_allocations', function (Blueprint $table) {
            $table->id('allocation_id');
            $table->foreignId('check_id')->constrained('checks')->onDelete('cascade');
            $table->foreignId('purchase_id')->constrained('raw_material_purchases')->onDelete('cascade');
            $table->decimal('allocated_amount', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['check_id', 'purchase_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('check_allocations');
        Schema::dropIfExists('checks');

        Schema::table('raw_material_purchases', function (Blueprint $table) {
            $table->dropForeign(['magazine_id']);
            $table->dropColumn(['magazine_id', 'tax_percentage', 'discount_percentage']);
        });

        Schema::table('raw_materials', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('magazine_id')->nullable()->constrained('magazines')->nullOnDelete();
        });
    }
};
