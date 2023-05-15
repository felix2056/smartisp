<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\models\User;
use App\models\Permission;
class UserAdministrator extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $superadmin = new User;
        $superadmin->username='SmartISPv3';
        $superadmin->name='SmartISPv3';
        $superadmin->email='support@smartisp.us';
        $superadmin->phone=0;
        $superadmin->level='ad';
        $superadmin->status=1;
        $superadmin->photo='none';
        $superadmin->remember_token='';
        $superadmin->password='$2y$10$nvpOoFwP31qVywTbrrrKD.kHEu/N8YtIguZ/joJhpT5JSvJ2K6zJy';
        $superadmin->save();
     
        $user_idp=$superadmin->id;
        $superadmin = new Permission;
        $superadmin->user_id=$user_idp;

        $superadmin->access_clients=1;
        $superadmin->access_plans=1;
        $superadmin->access_routers=1;
        $superadmin->access_users=1;
        $superadmin->access_system=1;
        $superadmin->access_pays=1;
        $superadmin->access_templates=1;
        $superadmin->access_tickets=1;
        $superadmin->access_reports=1;
        $superadmin->access_sms=1;

        $superadmin->facturacion=1;
        $superadmin->tran_facturacion_editar=1;
        $superadmin->tran_facturacion_eliminar=1;
        $superadmin->factura_pagar=1;
        $superadmin->factura_editar=1;
        $superadmin->factura_eliminar=1;
        $superadmin->pagos_nuevo=1;
        $superadmin->pagos_editar=1;
        $superadmin->pagos_eliminar=1;
        $superadmin->finanzas=1;
        $superadmin->tran_finanzas_editar=1;
        $superadmin->tran_finanzas_eliminar=1;
        
        $superadmin->factura_finanzas_pagar=1;
        $superadmin->factura_finanzas_editar=1;
        $superadmin->factura_finanzas_eliminar=1;
        $superadmin->pagos_finanzas_editar=1;
        $superadmin->pagos_finanzas_eliminar=1;
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
