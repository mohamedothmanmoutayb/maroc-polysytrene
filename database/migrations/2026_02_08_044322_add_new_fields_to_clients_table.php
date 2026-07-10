<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            // Remove old fields
            $table->dropColumn(['client_group', 'tax_number', 'representative_name']);

            // Add new fields for both types
            $table->enum('client_type', ['client', 'commerciale', 'grossiste'])->change();
            $table->enum('person_type', ['physique', 'morale'])->default('physique')->after('client_type');
            $table->string('cin', 20)->nullable()->after('full_name');
            $table->string('ice', 20)->nullable()->after('cin');
            $table->string('rc', 20)->nullable()->after('ice');
            $table->string('patente', 20)->nullable()->after('rc');
            $table->decimal('credit_limit', 15, 2)->default(0)->after('notes');

            // Rename fields for clarity
            $table->renameColumn('company_name', 'entreprise_name');
            $table->renameColumn('full_name', 'name');
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['person_type', 'cin', 'ice', 'rc', 'patente', 'credit_limit']);
            $table->string('client_group')->nullable();
            $table->string('tax_number', 30)->nullable();
            $table->string('representative_name', 100)->nullable();
            $table->renameColumn('entreprise_name', 'company_name');
            $table->renameColumn('name', 'full_name');
        });
    }
};
