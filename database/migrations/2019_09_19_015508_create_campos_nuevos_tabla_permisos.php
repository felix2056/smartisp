<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\models\Permission;

class CreateCamposNuevosTablaPermisos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
         $table->integer('access_clients_editar')->nullable();
         $table->integer('access_clients_eliminar')->nullable();
       });


        $superadmin = Permission::find(1);
        $superadmin->access_clients_editar=1;
        $superadmin->access_clients_eliminar=1;
        $superadmin->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
     Schema::table('permissions', function (Blueprint $table) {

     });
 }
}
