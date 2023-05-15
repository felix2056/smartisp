<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\models\campos_view_client;
class CreateEditCamposViewClient extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campos_view_client', function (Blueprint $table) {
            $table->integer('zone')->nullable();
            $table->integer('odb_id')->nullable();
            $table->integer('onu_id')->nullable();
            $table->integer('onusn')->nullable();
        });

        $superadmin = new campos_view_client;
        $superadmin->zone=0;
        $superadmin->odb_id=0;
        $superadmin->onu_id=0;
        $superadmin->onusn=0;
        $superadmin->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campos_view_client', function (Blueprint $table) {
        });
    }
}
