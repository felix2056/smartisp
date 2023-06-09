<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRecurringInvoiceInBillCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bill_customers', function (Blueprint $table) {
            $table->enum('recurring_invoice', ['yes', 'no'])->default('no');
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
            $table->dropColumn('recurring_invoice');
        });
    }
}
