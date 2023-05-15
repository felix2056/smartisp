<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterNameAndAddressColumnInClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `clients` CHANGE `name` `name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL;");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `clients` CHANGE `address` `address` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NULL DEFAULT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            //
        });
    }
}
