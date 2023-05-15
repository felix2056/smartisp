<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBillCustomersItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `bill_customer_item` DROP FOREIGN KEY `bill_customer_item_plan_id_foreign`;");
//        \Illuminate\Support\Facades\DB::statement(" ALTER TABLE `bill_customer_item` ADD CONSTRAINT `bill_customer_item_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
