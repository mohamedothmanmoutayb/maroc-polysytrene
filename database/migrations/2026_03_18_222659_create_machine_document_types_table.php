<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('machine_document_types', function (Blueprint $table) {
            $table->id('document_type_id');
            $table->string('type_code')->unique();
            $table->string('type_name');
            $table->text('description')->nullable();
            $table->integer('reminder_days_before')->default(30);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('machine_document_types');
    }
};
