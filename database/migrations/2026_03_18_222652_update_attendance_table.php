<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->json('time_entries')->nullable()->after('hours_worked');
            $table->json('break_entries')->nullable()->after('time_entries');
            $table->dropColumn(['check_in', 'check_out', 'hourly_blocks', 'break_hours']);
        });
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->json('hourly_blocks')->nullable();
            $table->json('break_hours')->nullable();
            $table->dropColumn(['time_entries', 'break_entries']);
        });
    }
};
