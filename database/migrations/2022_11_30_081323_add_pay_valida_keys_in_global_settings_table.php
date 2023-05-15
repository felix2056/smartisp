<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPayValidaKeysInGlobalSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->string('pay_valida_fixed_hash', 200)->nullable();
            $table->string('pay_valida_fixed_hash_notification', 200)->nullable();
            $table->string('pay_valida_merchant_id', 100)->nullable();
            $table->enum('pay_valida_mode', ['sandbox', 'live'])->default('sandbox');
            $table->boolean('pay_valida_status')->default(0);
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
