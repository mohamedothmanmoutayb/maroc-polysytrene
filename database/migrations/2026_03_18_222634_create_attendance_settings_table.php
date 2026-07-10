<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
    {
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id('setting_id');
            $table->time('check_in_time')->default('09:00:00');
            $table->time('check_out_time')->default('18:00:00');
            $table->time('late_threshold')->default('09:15:00');
            $table->decimal('work_hours_per_day', 5, 2)->default(8.00);
            $table->json('working_days');
            $table->json('weekend_days');
            $table->boolean('auto_mark_absent')->default(true);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('updated_by')->references('id')->on('users');
        });

        // Insert default settings
        DB::table('attendance_settings')->insert([
            'check_in_time' => '09:00:00',
            'check_out_time' => '18:00:00',
            'late_threshold' => '09:15:00',
            'work_hours_per_day' => 8.00,
            'working_days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
            'weekend_days' => json_encode(['saturday', 'sunday']),
            'auto_mark_absent' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('attendance_settings');
    }
};
