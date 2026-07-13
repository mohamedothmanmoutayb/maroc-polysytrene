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
        Schema::create('machine_maintenance_completions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->date('completed_at');
            $table->date('previous_due_at')->nullable();
            $table->date('next_due_at');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->timestamps();

            $table->foreign('schedule_id')
                  ->references('id')
                  ->on('machine_maintenance_schedules')
                  ->onDelete('cascade');

            $table->foreign('completed_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->index('schedule_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_maintenance_completions');
    }
};
