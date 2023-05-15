<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentColumnsToGlobalSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->string('paypal_client_id')->nullable();
            $table->string('paypal_secret')->nullable();
            $table->enum('paypal_mode', ['sandbox', 'live'])->nullable();
            $table->string('stripe_key')->nullable();
            $table->string('stripe_secret')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            //
        });
    }
}
