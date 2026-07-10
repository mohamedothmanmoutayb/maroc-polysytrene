<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable()->after('driver_id');
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('set null');

            // Drop old fields that are now in employees table
            $table->dropColumn(['full_name', 'cin', 'phone', 'email', 'address', 'hire_date']);
        });
    }

    public function down()
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
            $table->string('full_name', 200)->nullable();
            $table->string('cin', 50)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->date('hire_date')->nullable();
        });
    }
};
