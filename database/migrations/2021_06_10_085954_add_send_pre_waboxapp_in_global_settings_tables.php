<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSendPreWaboxappInGlobalSettingsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
             \Illuminate\Support\Facades\DB::statement("ALTER TABLE `global_settings` ADD `send_prewaboxapp` tinyint(4) NOT NULL DEFAULT '0' AFTER `send_prewhatsapp`;");
        });
		;
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
