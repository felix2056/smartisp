<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTableRadius extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('radius', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('secret');

            $table->unsignedInteger('router_id');
            $table->foreign('router_id')->references('id')->on('routers');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('radius');
    }
}
