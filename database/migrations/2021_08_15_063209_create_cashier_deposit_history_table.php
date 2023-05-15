<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashierDepositHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cashier_deposit_history', function (Blueprint $table) {
            $table->id();
	        $table->integer('client_id');
	        $table->foreign('client_id')->references('id')->on('clients')->onUpdate(null)->onDelete(null);
	        $table->unsignedInteger('user_id');
	        $table->foreign('user_id')->references('id')->on('users');
	        $table->unsignedDouble('amount', 10, 2);
			$table->text('comment')->nullable();
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
        Schema::dropIfExists('cashier_deposit_history');
    }
}
