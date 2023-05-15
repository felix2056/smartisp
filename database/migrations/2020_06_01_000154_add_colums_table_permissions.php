<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\models\Permission;
class AddColumsTablePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->integer('access_clients_activate')->nullable()->default(0);
            $table->integer('servicio_info')->nullable()->default(1);
            $table->integer('servicio_edit')->nullable()->default(0);
            $table->integer('servicio_delete')->nullable()->default(0);
            $table->integer('servicio_activate_desactivar')->nullable()->default(0);
            $table->integer('servicio_new')->nullable()->default(0);
        });

        $superadmin = Permission::find(1);
        $superadmin->access_clients_activate=1;
        $superadmin->servicio_info=1;
        $superadmin->servicio_edit=1;
        $superadmin->servicio_delete=1;
        $superadmin->servicio_activate_desactivar=1;
        $superadmin->servicio_new=1;
        $superadmin->save();

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
