<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE production_orders MODIFY quantity_to_produce DECIMAL(12,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE production_orders MODIFY required_quantity DECIMAL(12,2) NULL');
        DB::statement('ALTER TABLE production_orders MODIFY total_good_quantity DECIMAL(12,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE production_orders MODIFY total_defective_quantity DECIMAL(12,2) NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE production_order_products MODIFY quantity_to_produce DECIMAL(12,2) NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE production_output MODIFY quantity_produced DECIMAL(12,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE production_output MODIFY quantity_consumed DECIMAL(12,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE production_output MODIFY quantity_defective DECIMAL(12,2) NULL DEFAULT 0');
    }

    public function down()
    {
        DB::statement('ALTER TABLE production_orders MODIFY quantity_to_produce INT(11) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE production_orders MODIFY required_quantity INT(11) NULL');
        DB::statement('ALTER TABLE production_orders MODIFY total_good_quantity INT(11) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE production_orders MODIFY total_defective_quantity INT(11) NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE production_order_products MODIFY quantity_to_produce INT(11) NOT NULL DEFAULT 0');

        DB::statement('ALTER TABLE production_output MODIFY quantity_produced INT(11) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE production_output MODIFY quantity_consumed INT(11) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE production_output MODIFY quantity_defective INT(11) NULL DEFAULT 0');
    }
};
