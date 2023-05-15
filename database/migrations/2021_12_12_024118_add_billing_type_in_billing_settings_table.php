<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBillingTypeInBillingSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('billing_settings', function (Blueprint $table) {
            $table->enum('billing_invoice_pay_type', ['prepay', 'postpay'])->default('prepay');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('billing_settings', function (Blueprint $table) {
            $table->dropColumn('billing_invoice_pay_type');
        });
    }
}
