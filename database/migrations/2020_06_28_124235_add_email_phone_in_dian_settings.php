<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmailPhoneInDianSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `dian_settings` ADD `email` VARCHAR(255) NOT NULL DEFAULT '' AFTER `direction`, ADD `phone` VARCHAR(255) NOT NULL DEFAULT '' AFTER `email`;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dian_settings', function (Blueprint $table) {
            //
        });
    }
}
