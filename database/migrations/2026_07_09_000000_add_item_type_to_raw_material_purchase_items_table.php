<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('raw_material_purchase_items', function (Blueprint $table) {
            $table->enum('item_type', ['raw_material', 'charge_diverse'])
                ->default('raw_material')
                ->after('purchase_id');
            $table->string('description', 255)->nullable()->after('material_id');
        });

        DB::statement('ALTER TABLE raw_material_purchase_items MODIFY material_id INT(11) NULL');
        DB::statement('ALTER TABLE raw_material_purchase_items MODIFY quantity DECIMAL(10,2) NULL');
        DB::statement('ALTER TABLE raw_material_purchase_items MODIFY unit_price DECIMAL(10,2) NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE raw_material_purchase_items MODIFY material_id INT(11) NOT NULL');
        DB::statement('ALTER TABLE raw_material_purchase_items MODIFY quantity DECIMAL(10,2) NOT NULL');
        DB::statement('ALTER TABLE raw_material_purchase_items MODIFY unit_price DECIMAL(10,2) NOT NULL');

        Schema::table('raw_material_purchase_items', function (Blueprint $table) {
            $table->dropColumn(['item_type', 'description']);
        });
    }
};
