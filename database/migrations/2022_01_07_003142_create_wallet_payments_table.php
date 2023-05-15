<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_payments', function (Blueprint $table) {
            $table->id();
	        $table->integer('client_id');
	        $table->foreign('client_id')->references('id')->on('clients');
	        $table->integer('num_bill');
	        $table->double('amount', 10, 2);
	        $table->unsignedInteger('user_id')->nullable();
	        $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallet_payments');
    }
}
