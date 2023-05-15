<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtraColumnsInBillCustomers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bill_customers', function (Blueprint $table) {
            $table->string('note')->nullable();
            $table->string('memo')->nullable();
            $table->string('xero_id')->nullable();
            $table->date('paid_on')->nullable();
            $table->date('start_date')->nullable();
            $table->enum('use_transactions', ['0', '1'])->default('0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bill_customers', function (Blueprint $table) {
            if (Schema::hasColumn('bill_customers', 'note')) {
                $table->removeColumn('note')
                    ->removeColumn('memo')
                    ->removeColumn('xero_id')
                    ->removeColumn('paid_on')
                    ->removeColumn('start_date')
                    ->removeColumn('use_transactions');
            }
        });
    }
}
