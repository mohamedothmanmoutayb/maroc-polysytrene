<?php

// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;

// class CreateVehicleDocumentTypesTable extends Migration
// {
//     public function up()
//     {
//         Schema::create('vehicle_document_types', function (Blueprint $table) {
//             $table->id('document_type_id');
//             $table->string('type_code', 50)->unique();
//             $table->string('type_name', 100);
//             $table->text('description')->nullable();
//             $table->boolean('is_active')->default(true);
//             $table->integer('sort_order')->default(0);
//             $table->integer('default_duration_days')->nullable();
//             $table->integer('reminder_days_before')->default(10);
//             $table->timestamps();
//             $table->softDeletes();
//         });
//     }

//     public function down()
//     {
//         Schema::dropIfExists('vehicle_document_types');
//     }
// }

// 2025_01_01_000002_create_vehicle_documents_table.php
// <?php

// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;

// return new class extends Migration
// {
//     public function up()
//     {
//         Schema::create('vehicle_documents', function (Blueprint $table) {
//             $table->id('document_id');
//             $table->unsignedBigInteger('vehicle_id');
//             $table->unsignedBigInteger('document_type_id');
//             $table->string('document_number', 100)->nullable();
//             $table->date('start_date')->nullable();
//             $table->date('end_date')->nullable();
//             $table->string('issuing_authority', 100)->nullable();
//             $table->text('notes')->nullable();
//             $table->boolean('is_current')->default(true);
//             $table->unsignedBigInteger('created_by')->nullable();
//             $table->timestamps();
//             $table->softDeletes();

//             $table->foreign('vehicle_id')->references('vehicle_id')->on('vehicles')->onDelete('cascade');
//             $table->foreign('document_type_id')->references('document_type_id')->on('vehicle_document_types');

//             $table->index(['vehicle_id', 'document_type_id', 'is_current']);
//         });
//     }

//     public function down()
//     {
//         Schema::dropIfExists('vehicle_documents');
//     }
// };

// <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'insurance_company',
                'insurance_policy_number',
                'insurance_expiry_date',
                'registration_document_number',
                'registration_expiry_date',
                'technical_control_number',
                'technical_control_expiry_date',
                'last_maintenance_date',
                'next_maintenance_date',
            ]);
        });
    }

    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('insurance_company', 100)->nullable();
            $table->string('insurance_policy_number', 100)->nullable();
            $table->date('insurance_expiry_date')->nullable();
            $table->string('registration_document_number', 100)->nullable();
            $table->date('registration_expiry_date')->nullable();
            $table->string('technical_control_number', 100)->nullable();
            $table->date('technical_control_expiry_date')->nullable();
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
        });
    }
};
