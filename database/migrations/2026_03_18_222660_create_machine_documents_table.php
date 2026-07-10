<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('machine_documents', function (Blueprint $table) {
            $table->id('document_id');
            $table->unsignedBigInteger('machine_id');
            $table->unsignedBigInteger('document_type_id');
            $table->string('document_number')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('issuing_authority')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_current')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('machine_id')
                  ->references('machine_id')
                  ->on('machines')
                  ->onDelete('cascade');

            $table->foreign('document_type_id')
                  ->references('document_type_id')
                  ->on('machine_document_types')
                  ->onDelete('cascade');

            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->index(['machine_id', 'document_type_id', 'is_current']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('machine_documents');
    }
};
