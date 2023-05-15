<?php

namespace App\Http\Controllers;
use App\models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class GetUserController extends BaseController
{

    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function postData(Request $request)
    {
        $user_id = $request->get('user');
        $perm = User::find($user_id)->permission;


        if (is_null($perm))
            return Response::json(array('success' => false));

        $data = array(
            'success' => true,
            'id' => $perm->user->id,
            'name' => $perm->user->name,
            'phone' => $perm->user->phone,
            'email' => $perm->user->email,
            'username' => $perm->user->username,
            'level' => $perm->user->level,
            'status' => $perm->user->status,
            'acc_clients' => $perm->access_clients,
            'acc_access_clients_editar' => $perm->access_clients_editar,
            'acc_access_clients_eliminar' => $perm->access_clients_eliminar,
            'acc_access_clients_activate' => $perm->access_clients_activate,
            'acc_plans' => $perm->access_plans,
            'acc_routers' => $perm->access_routers,
            'acc_users' => $perm->access_users,
            'acc_system' => $perm->access_system,
            'acc_pays' => $perm->access_pays,
            'acc_tools' => $perm->access_tools,
            'acc_box' => $perm->access_templates,
            'acc_rep' => $perm->access_reports,
            'acc_tic' => $perm->access_tickets,
            'acc_sms' => $perm->access_sms,
            'facturacion' => $perm->facturacion,

            'servicio_info' => $perm->servicio_info,
            'servicio_edit' => $perm->servicio_edit,
            'servicio_delete' => $perm->servicio_delete,
            'servicio_activate_desactivar' => $perm->servicio_activate_desactivar,
            'servicio_new' => $perm->servicio_new,

            'tran_facturacion_editar' => $perm->tran_facturacion_editar,
            'tran_facturacion_eliminar' => $perm->tran_facturacion_eliminar,

            'factura_pagar' => $perm->factura_pagar,
            'factura_editar' => $perm->factura_editar,
            'factura_eliminar' => $perm->factura_eliminar,

            'pagos_nuevo' => $perm->pagos_nuevo,
            'pagos_editar' => $perm->pagos_editar,
            'pagos_eliminar' => $perm->pagos_eliminar,

            'finanzas' => $perm->finanzas,

            'tran_finanzas_editar' => $perm->tran_finanzas_editar,
            'estado_financier' => $perm->estado_financier,
            'tran_finanzas_eliminar' => $perm->tran_finanzas_eliminar,

            'factura_finanzas_pagar' => $perm->factura_finanzas_pagar,
            'factura_finanzas_editar' => $perm->factura_finanzas_editar,
            'factura_finanzas_eliminar' => $perm->factura_finanzas_eliminar,

            'pagos_finanzas_editar' => $perm->pagos_finanzas_editar,
            'pagos_finanzas_eliminar' => $perm->pagos_finanzas_eliminar,
            'edit_client_balance' => $perm->edit_client_balance,
            'access_system' => $perm->access_system,
            'locations_access' => $perm->locations_access,
            'maps_client_access' => $perm->maps_client_access,
            'billing_setting_update' => $perm->billing_setting_update,
            'splitter' => $perm->splitter,
            'onu_cpe' => $perm->onu_cpe,
        );

        return Response::json($data);
    }
}
