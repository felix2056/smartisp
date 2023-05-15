<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPlanIdInBillCustomerItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bill_customer_item', function (Blueprint $table) {
            $table->unsignedInteger('plan_id')->after('bill_customer_id')->nullable();
//            $table->foreign('plan_id')->references('id')->on('plans')->onUpdate('SET NULL')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bill_customer_item', function (Blueprint $table) {
            //
        });
    }
}
