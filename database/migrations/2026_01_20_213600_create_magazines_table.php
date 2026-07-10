<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMagazinesTable extends Migration
{
    public function up()
    {
        Schema::create('magazines', function (Blueprint $table) {
            $table->id('magazine_id');
            $table->string('magazine_code')->unique();
            $table->string('magazine_name');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('raw_materials', function (Blueprint $table) {
            $table->unsignedBigInteger('magazine_id')->nullable()->after('location_in_warehouse');
            $table->foreign('magazine_id')->references('magazine_id')->on('magazines');
            $table->dropColumn('location_in_warehouse');
        });
    }

    public function down()
    {
        Schema::table('raw_materials', function (Blueprint $table) {
            $table->dropForeign(['magazine_id']);
            $table->dropColumn('magazine_id');
            $table->string('location_in_warehouse')->nullable()->after('supplier_id');
        });

        Schema::dropIfExists('magazines');
    }
}
