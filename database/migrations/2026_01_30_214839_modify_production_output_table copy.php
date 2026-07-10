<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('production_output', function (Blueprint $table) {
            $table->decimal('total_volume_m3', 10, 4)->nullable()->after('quantity_defective');
            $table->decimal('waste_volume_m3', 10, 4)->nullable()->after('total_volume_m3');
            $table->decimal('unit_volume_m3', 10, 4)->nullable()->after('waste_volume_m3');
        });

        Schema::create('production_wastes', function (Blueprint $table) {
            $table->id('waste_id');
            $table->unsignedBigInteger('production_output_id');
            $table->unsignedBigInteger('material_id')->nullable();
            $table->string('waste_type', 50); // planned, unplanned, recyclable, reusable
            $table->decimal('quantity', 12, 4);
            $table->decimal('volume_m3', 10, 4)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_recovered')->default(false);
            $table->timestamp('recovery_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('production_output_id')->references('output_id')->on('production_output')->onDelete('cascade');
            $table->foreign('material_id')->references('material_id')->on('raw_materials')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('production_wastes');

        Schema::table('production_output', function (Blueprint $table) {
            $table->dropColumn(['total_volume_m3', 'waste_volume_m3', 'unit_volume_m3']);
        });
    }
};
