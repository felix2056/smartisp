<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWayToPayColumnInCashierDepositHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cashier_deposit_history', function (Blueprint $table) {
            $table->string('way_to_pay')->default('Cash');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cashier_deposit_history', function (Blueprint $table) {
            $table->dropColumn('way_to_pay');
        });
    }
}
