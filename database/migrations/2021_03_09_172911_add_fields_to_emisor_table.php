<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToEmisorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('emisor', function (Blueprint $table) {
             $table->integer('regimenMicroempresas')->nullable()->default(null);
             $table->string('agenteRetencion', 8)->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('emisor', function (Blueprint $table) {
            //
        });
    }
}
