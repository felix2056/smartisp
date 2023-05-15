<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWalletBalanceColumnInClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	\Illuminate\Support\Facades\DB::statement("ALTER TABLE `clients` CHANGE `balance` `balance` DECIMAL(15,2) NULL DEFAULT '0.00' COMMENT 'pending invoice balance';");
    	
        Schema::table('clients', function (Blueprint $table) {
            $table->double('wallet_balance', 10, 2)->default(0.00);
            $table->dropColumn('date_in');
            $table->dropColumn('plan_id');
            $table->dropColumn('router_id');
            $table->dropColumn('status');
            $table->dropColumn('onmikrotik');
            $table->dropColumn('online');
            $table->dropColumn('user_hot');
            $table->dropColumn('pass_hot');
            $table->dropColumn('typeauth');
            $table->dropColumn('billing_type');
            $table->dropColumn('ip');
            $table->dropColumn('mac');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            //
        });
    }
}
