<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->onUpdate(null)->onDelete(null);
            $table->decimal('amount', 12, 2);
            $table->decimal('account_balance', 12, 2)->nullable();
            $table->text('category');
            $table->date('date');
            $table->text('quantity');
            $table->text('description')->nullable();
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
        Schema::dropIfExists('transactions');
    }
}
