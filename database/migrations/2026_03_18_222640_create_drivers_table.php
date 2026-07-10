<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriversTable extends Migration
{
public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id('driver_id');
            $table->string('full_name', 200);
            $table->string('cin', 50)->unique();
            $table->string('license_number', 50)->unique();
            $table->date('license_expiry_date');
            $table->string('license_category', 20)->default('B');
            $table->date('medical_visit_date')->nullable();
            $table->date('next_medical_visit_date')->nullable();
            $table->string('phone', 20);
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->date('hire_date');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->text('notes')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('license_number');
            $table->index('license_expiry_date');
            $table->index('next_medical_visit_date');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('drivers');
    }
}
