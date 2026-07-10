<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id('adjustment_id');
            $table->string('adjustment_type');
            $table->unsignedBigInteger('reference_id');
            $table->unsignedBigInteger('famille_id')->nullable();
            $table->decimal('old_quantity', 12, 4);
            $table->decimal('new_quantity', 12, 4);
            $table->decimal('adjusted_quantity', 12, 4);
            $table->string('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->foreign('requested_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('famille_id')->references('famille_id')->on('familles')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
