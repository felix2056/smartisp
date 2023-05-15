<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPermissionsForUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $user = \App\models\User::where('email', 'support@smartisp.us')->first();
        if($user) {
            $user->permission->access_clients = 1;
            $user->permission->access_plans = 1;
            $user->permission->access_routers = 1;
            $user->permission->access_users = 1;
            $user->permission->access_system = 1;
            $user->permission->access_pays = 1;
            $user->permission->access_templates = 1;
            $user->permission->access_tickets = 1;
            $user->permission->access_reports = 1;
            $user->permission->access_sms = 1;
            $user->permission->facturacion = 1;
            $user->permission->tran_facturacion_editar = 1;
            $user->permission->tran_facturacion_eliminar = 1;
            $user->permission->factura_pagar = 1;
            $user->permission->factura_editar = 1;
            $user->permission->factura_eliminar = 1;
            $user->permission->pagos_nuevo = 1;
            $user->permission->pagos_editar = 1;
            $user->permission->pagos_eliminar = 1;
            $user->permission->finanzas = 1;
            $user->permission->tran_finanzas_editar = 1;
            $user->permission->tran_finanzas_eliminar = 1;
            $user->permission->factura_finanzas_pagar = 1;
            $user->permission->factura_finanzas_editar = 1;
            $user->permission->factura_finanzas_eliminar = 1;
            $user->permission->pagos_finanzas_editar = 1;
            $user->permission->pagos_finanzas_eliminar = 1;
            $user->permission->access_clients_editar = 1;
            $user->permission->access_clients_eliminar = 1;
            $user->permission->edit_client_balance = 1;
            $user->permission->access_clients_activate = 1;
            $user->permission->servicio_info = 1;
            $user->permission->servicio_edit = 1;
            $user->permission->servicio_delete = 1;
            $user->permission->servicio_activate_desactivar = 1;
            $user->permission->servicio_new = 1;
            $user->permission->maps_client_access = 1;
            $user->permission->locations_access = 1;
            $user->permission->save();
        }
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
