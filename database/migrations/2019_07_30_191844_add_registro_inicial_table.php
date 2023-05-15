<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
class AddRegistroInicialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('factel', function (Blueprint $table) {
            $table->timestamp('updatedAt')->nullable();
        });
        \DB::table('factel')->insert(array(
            'certificado_digital' => 'NA',
            'pass_certificado' => 'NA',
            'status' => 1
        ));

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('factel', function (Blueprint $table) {
            //
        });
    }
}
