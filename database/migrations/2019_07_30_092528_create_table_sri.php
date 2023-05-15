<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSri extends Migration
{
     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sri', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_factura');
            $table->integer('id_error');
            $table->string('mensaje');
            $table->string('informacionAdicional');
            $table->string('tipo');
            $table->string('claveAcceso');
            $table->string('estado');
            $table->timestamp('updated_at')->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'));;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sri');
    }
}
