<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPayuColumnsToGlobalSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
	        $table->string('payu_merchant_id')->nullable();
            $table->string('payu_account_id')->nullable();
	        $table->string('payu_api_key')->nullable();
	        $table->enum('payu_mode', ['sandbox', 'live'])->nullable();
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
            $table->dropColumn("payu_merchant_id");
            $table->dropColumn("payu_account_id");
            $table->dropColumn("payu_api_key");
            $table->dropColumn("payu_mode");
        });
    }
}
