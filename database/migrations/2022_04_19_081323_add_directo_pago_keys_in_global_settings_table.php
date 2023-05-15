<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDirectoPagoKeysInGlobalSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->string('directo_pago_api_key', 60)->nullable();
            $table->string('directo_pago_secret_key', 60)->nullable();
            $table->enum('directo_pago_mode', ['sandbox', 'live'])->default('sandbox');
            $table->boolean('directo_pago_status')->default(0);

            $table->boolean('payu_status')->default(0);
            $table->boolean('stripe_status')->default(0);
            $table->boolean('paypal_status')->default(0);

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
