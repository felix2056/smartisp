<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypedocToClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `clients` ADD `typedoc_cod` VARCHAR(2) NOT NULL DEFAULT '0' AFTER `zona_id`, ADD `economicactivity_cod` VARCHAR(10) NOT NULL DEFAULT '0' AFTER `typedoc_cod`, ADD `municipio_cod` VARCHAR(10) NOT NULL DEFAULT '0' AFTER `economicactivity_cod`, ADD `typeresponsibility_cod` VARCHAR(10) NOT NULL DEFAULT 'ZZ' AFTER `municipio_cod`, ADD `typetaxpayer_cod` VARCHAR(2) NOT NULL DEFAULT '0' AFTER `typeresponsibility_cod`");
        });
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
