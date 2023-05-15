<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->onUpdate(null)->onDelete(null);
            $table->date('date');
            $table->string('bill_number');
            $table->string('total');
            $table->date('payment_date');
            $table->string('note')->nullable();
            $table->string('memo')->nullable();
            $table->string('xero_id')->nullable();
            $table->enum('use_transactions', ['0', '1'])->default('0');
            $table->enum('status', ['unpaid', 'paid(account_balance)', 'paid', 'late', 'removed'])->default('unpaid');
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
        Schema::dropIfExists('invoices');
    }
}
