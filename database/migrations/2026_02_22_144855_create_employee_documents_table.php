<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id('document_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('document_name');
            $table->string('document_type'); // PDF, DOC, IMAGE, etc.
            $table->string('file_path');
            $table->string('category')->nullable(); // CNSS, CIN, CONTRACT, DIPLOMA, etc.
            $table->text('description')->nullable();
            $table->string('mime_type');
            $table->integer('file_size'); // in KB
            $table->boolean('is_confidentiel')->default(false);
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();

            $table->foreign('employee_id')
                  ->references('employee_id')
                  ->on('employees')
                  ->onDelete('cascade');

            $table->index(['employee_id', 'category']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_documents');
    }
};
