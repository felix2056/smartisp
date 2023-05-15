<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\models\campos_view_client;
class CreateCamposViewClientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campos_view_client', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_id')->nullable();
            $table->string('name')->nullable();
            $table->string('ip')->nullable();
            $table->string('router')->nullable();
            $table->string('estado')->nullable();
            $table->string('control')->nullable();
            $table->string('plan')->nullable();
            $table->string('servicio')->nullable();
            $table->string('balance')->nullable();
            $table->string('day_payment')->nullable();
            $table->string('cut')->nullable();
            $table->string('mac')->nullable();
            $table->timestamps();
        });

        $superadmin = new campos_view_client;
        $superadmin->name=1;
        $superadmin->user_id=1;
        $superadmin->ip=1;
        $superadmin->router=1;
        $superadmin->estado=1;
        $superadmin->control=1;
        $superadmin->plan=1;
        $superadmin->servicio=1;
        $superadmin->balance=0;
        $superadmin->day_payment=0;
        $superadmin->cut=0;
        $superadmin->mac=0;
        $superadmin->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campos_view_client');
    }
}
