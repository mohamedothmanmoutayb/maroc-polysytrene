<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

new class extends Migration
{
    public function up()
    {
        Schema::create('vehicle_assignments', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('driver_id');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('reason')->nullable();
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();

            $table->foreign('vehicle_id')->references('vehicle_id')->on('vehicles')->onDelete('cascade');
            $table->foreign('driver_id')->references('driver_id')->on('drivers')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_assignments');
    }
};
