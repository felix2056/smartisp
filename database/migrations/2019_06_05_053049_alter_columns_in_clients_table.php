<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnsInClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

    \Illuminate\Support\Facades\DB::statement("SET sql_mode = '';");
    \Illuminate\Support\Facades\DB::statement('ALTER TABLE `clients` CHANGE `created_at` `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;');
    \Illuminate\Support\Facades\DB::statement('ALTER TABLE `clients` CHANGE `updated_at` `updated_at` TIMESTAMP NULL;');
    \Illuminate\Support\Facades\DB::statement('ALTER TABLE `clients` CHANGE `mac` `mac` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NULL DEFAULT NULL;');

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
