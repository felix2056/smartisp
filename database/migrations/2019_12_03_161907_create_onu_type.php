<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOnuType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('onu_type', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('pontype', ['GPON', 'EPON', 'CPE']);
            $table->string('onutype');
            $table->integer('ethernet_ports');
            $table->integer('wifi_ssids');
            $table->string('detail')->nullable();
            // $table->integer('voip_ports');
            // $table->enum('catv', ['Yes', 'No']);
            // $table->enum('allow_custom_profiles', ['Yes', 'No']);
            // $table->enum('capability', ['Bridging', 'Bridging/Routing']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('onu_type');
    }
}
