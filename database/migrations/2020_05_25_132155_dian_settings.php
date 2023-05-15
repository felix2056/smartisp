<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DianSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('dian_settings', function (Blueprint $table) {
            $table->collation = 'utf8mb4_bin';
            $table->integer('id');
            $table->string('typeoperation_cod')->default('2');
            $table->string('softwarename')->default('smartisp');
            $table->string('softwareid')->default('');
            $table->string('softwarepin')->default('12345');
            $table->string('tecnicalkey')->default('');
            $table->string('testsetid')->default('');
            $table->date('resolutiondate')->default('2019-01-19');
            $table->date('resolutiondatestar')->default('2019-01-19');
            $table->date('resolutiondateend')->default('2030-01-19');
            $table->string('resolutionnumber')->default('18760000001');
            $table->string('prefix')->default('SETP');
            $table->string('numberstart')->default('990000000');
            $table->string('numberend')->default('995000000');
            $table->string('prefixnc')->default('NC');
            $table->string('numberstartnc')->default('0');
            $table->string('numberendnc')->default('999999999');
            $table->string('prefixnd')->default('ND');
            $table->string('numberstartnd')->default('0');
            $table->string('numberendnd')->default('999999999');
            $table->integer('fes')->default('0');
            $table->integer('ncs')->default('0');
            $table->integer('nds')->default('0');
            $table->integer('zips')->default('0');
            $table->integer('year')->default('2019');
            $table->string('typedoc_cod')->default('31');
            $table->string('identificacion')->default('');
            $table->string('businessname')->default('');
            $table->string('tradename')->default('');
            $table->string('typetaxpayer_cod')->default('1');
            $table->string('accountingregime_cod')->default('0');
            $table->string('typeresponsibility_cod')->default('0');
            $table->string('economicactivity_cod')->default('0');
            $table->string('municipio_cod')->default('0');
            $table->string('direction')->default('');
            $table->timestamps();
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
