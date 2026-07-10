<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id('vehicle_id');
            $table->enum('type', ['voiture', 'camion', 'machine'])->default('voiture');
            $table->string('registration_number', 50)->unique();
            $table->date('purchase_date')->nullable();
            // Insurance
            $table->string('insurance_company', 100)->nullable();
            $table->string('insurance_policy_number', 100)->nullable();
            $table->date('insurance_expiry_date')->nullable();
            // Carte grise (Registration)
            $table->string('registration_document_number', 100)->nullable();
            $table->date('registration_expiry_date')->nullable();
            // Technical Control (Visite technique)
            $table->string('technical_control_number', 100)->nullable();
            $table->date('technical_control_expiry_date')->nullable();
            // Maintenance
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->integer('current_mileage')->default(0);
            $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('registration_number');
            $table->index('type');
            $table->index('insurance_expiry_date');
            $table->index('registration_expiry_date');
            $table->index('technical_control_expiry_date');
            $table->index('next_maintenance_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
};
