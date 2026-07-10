<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('client_documents', function (Blueprint $table) {
            $table->id('document_id');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('document_type', 50);
            $table->string('document_name', 255);
            $table->string('file_path');
            $table->string('file_name');
            $table->integer('file_size');
            $table->string('file_type', 100);
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['client_id', 'document_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('client_documents');
    }
}; 
