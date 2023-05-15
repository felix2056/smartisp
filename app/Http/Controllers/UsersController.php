<?php

namespace App\Http\Controllers;
use App\DataTables\UserCreditDataTable;
use App\DataTables\UserDataTable;
use App\Http\Requests\Admin\User\CreateRequest;
use App\Http\Requests\Admin\User\UpdateRequest;
use App\libraries\Slog;
use App\libraries\Validator;
use App\models\GlobalSetting;
use App\models\PaymentNew;
use App\models\Permission;
use App\models\User;
use App\models\UserCredit;
use App\Service\CommonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class UsersController extends BaseController
{

    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
	    parent::__construct();
	    $this->middleware(function ($request, $next) {
		    $this->username = auth()->user()->username;
		    $this->userId = auth()->user()->id;
		    return $next($request);
	    });
    }

    public function getIndex(UserDataTable $dataTable)
    {
        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
        $access = $perm[0]->access_users;
        //control permissions only access super administrator (sa)
        if ($level == 'ad' || $access == true) {

            $global = GlobalSetting::all()->first();

            $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans,
                "routers" => $perm[0]->access_routers, "users" => $perm[0]->access_users, "system" => $perm[0]->access_system,
                "bill" => $perm[0]->access_pays, "template" => $perm[0]->access_templates,
                "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
                "reports" => $perm[0]->access_reports, "v" => $global->version, "st" => $global->status,
                "lv" => $global->license, "company" => $global->company,
                'permissions' => $perm->first(),
            );

            if (Auth::user()->level == 'ad')
                @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);

            return $dataTable->render('users.index', $permissions);
        } else
        return Redirect::to('admin');
    }

    //metodo registrar usuarios
    public function postCreate(CreateRequest $request)
    {
        $sites = $request->get('user_acc');
       
        if(!$sites) {
        	$sites = [];
        }

        //acceso clientes
        if (in_array("cli", $sites)) $clients = true;
        else $clients = false;

        //acceso clientes editar
        if (in_array("cliente_editar", $sites)) $cliente_editar = true;
        else $cliente_editar = false;

        //acceso clientes eliminar
        if (in_array("cliente_eliminar", $sites)) $cliente_eliminar = true;
        else $cliente_eliminar = false;

        //acceso clientes activar/desactivar
        if (in_array("cliente_activar", $sites)) $cliente_activar = true;
        else $cliente_activar = false;

        //acceso planes
        if (in_array("pla", $sites)) $plans = true;
        else $plans = false;

        //acceso routers
        if (in_array("rou", $sites)) $routers = true;
        else $routers = false;

        //acceso usuarios
        if (in_array("use", $sites)) $users = true;
        else $users = false;
        //acceso systema
        if (in_array("access_system", $sites)) $system = true;
        else $system = false;

        //acceso cliente maps
        if (in_array("maps_client_access", $sites)) $map = true;
        else $map = false;

        //acceso cliente locations
        if (in_array("locations_access", $sites)) $location = true;
        else $location = false;

        //acceso pagos
        if (in_array("pay", $sites)) $pays = true;
        else $pays = false;

        //acceso plantillas
        if (in_array("tem", $sites)) $tem = true;
        else $tem = false;

        //acceso reportes
        if (in_array("report", $sites)) $report = true;
        else $report = false;

        //acceso reportes
        if (in_array("ticket", $sites)) $ticket = true;
        else $ticket = false;

        //acceso sms
        if (in_array("sms", $sites)) $sms = true;
        else $sms = false;

        //habilitado
        if (empty($request->get('status'))) $status = false;
        else $status = true;

        if (in_array("facturacion", $sites)) $facturacion = true;
        else $facturacion = false;

        if (in_array("servicio_info", $sites)) $servicio_info = true;
        else $servicio_info = false;

        if (in_array("servicio_edit", $sites)) $servicio_edit = true;
        else $servicio_edit = false;

        if (in_array("servicio_delete", $sites)) $servicio_delete = true;
        else $servicio_delete = false;

        if (in_array("servicio_activate_desactivar", $sites)) $servicio_activate_desactivar = true;
        else $servicio_activate_desactivar = false;

        if (in_array("servicio_new", $sites)) $servicio_new = true;
        else $servicio_new = false;

        if (in_array("tran_facturacion_editar", $sites)) $tran_facturacion_editar = true;
        else $tran_facturacion_editar = false;

        if (in_array("tran_facturacion_eliminar", $sites)) $tran_facturacion_eliminar = true;
        else $tran_facturacion_eliminar = false;

        if (in_array("factura_pagar", $sites)) $factura_pagar = true;
        else $factura_pagar = false;

        if (in_array("factura_editar", $sites)) $factura_editar = true;
        else $factura_editar = false;

        if (in_array("factura_eliminar", $sites)) $factura_eliminar = true;
        else $factura_eliminar = false;

        if (in_array("pagos_nuevo", $sites)) $pagos_nuevo = true;
        else $pagos_nuevo = false;

        if (in_array("pagos_editar", $sites)) $pagos_editar = true;
        else $pagos_editar = false;

        if (in_array("pagos_eliminar", $sites)) $pagos_eliminar = true;
        else $pagos_eliminar = false;

        if (in_array("finanzas", $sites)) $finanzas = true;
        else $finanzas = false;

        if (in_array("tran_finanzas_editar", $sites)) $tran_finanzas_editar = true;
        else $tran_finanzas_editar = false;

        if (in_array("estado_financier", $sites)) $estado_financier = true;
        else $estado_financier = false;

        if (in_array("tran_finanzas_eliminar", $sites)) $tran_finanzas_eliminar = true;
        else $tran_finanzas_eliminar = false;

        if (in_array("factura_finanzas_pagar", $sites)) $factura_finanzas_pagar = true;
        else $factura_finanzas_pagar = false;

        if (in_array("factura_finanzas_editar", $sites)) $factura_finanzas_editar = true;
        else $factura_finanzas_editar = false;

        if (in_array("factura_finanzas_eliminar", $sites)) $factura_finanzas_eliminar = true;
        else $factura_finanzas_eliminar = false;

        if (in_array("pagos_finanzas_editar", $sites)) $pagos_finanzas_editar = true;
        else $pagos_finanzas_editar = false;

        if (in_array("pagos_finanzas_eliminar", $sites)) $pagos_finanzas_eliminar = true;
        else $pagos_finanzas_eliminar = false;

        if (in_array("billing_setting_update", $sites)) $billing_setting_update = true;
        else $billing_setting_update = false;

        if (in_array("splitter", $sites)) $splitter = true;
        else $splitter = false;

        if (in_array("onu_cpe", $sites)) $onuCpe = true;
        else $onuCpe = false;

        $password = $request->get('password');

        $level = 'us';
	
	    if($request->has('cashdesk') && $request->get('cashdesk') == 'cs') {
		    $level = 'cs';
	    }
        
        //registramos usuarios
        $id = DB::table('users')->insertGetId(
            array('username' => $request->get('username'), 'name' => $request->get('name'),
                'email' => $request->get('email'), 'phone' => $request->get('phone'), 'status' => $status,
                'level' => $level, 'password' => Hash::make($password), 'photo' => 'none', 'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"))
        );
	
	    if($request->has('cashdesk') && $request->get('cashdesk') == 'cs') {
		    $cliente_editar = false;
			$cliente_eliminar = false;
			$cliente_activar = false;
			$plans = false;
			$routers = false;
			$users = false;
			$system = false;
			$location = false;
			$map = false;
			$pays = false;
			$tem = false;
			$report = false;
			$ticket = false;
			$sms = false;
			$facturacion = false;
			$tran_facturacion_editar = false;
			$tran_facturacion_eliminar = false;
			$factura_pagar = false;
			$factura_editar = false;
			$factura_eliminar = false;
			$servicio_info = false;
			$servicio_edit = false;
			$servicio_delete = false;
			$servicio_activate_desactivar = false;
			$servicio_new = false;
			$pagos_nuevo = false;
			$pagos_editar = false;
			$pagos_eliminar = false;
			$finanzas = false;
			$tran_finanzas_editar = false;
			$tran_finanzas_eliminar = false;
			$estado_financier = false;
			$factura_finanzas_pagar = false;
			$factura_finanzas_editar = false;
			$factura_finanzas_eliminar = false;
			$pagos_finanzas_editar = false;
			$pagos_finanzas_eliminar = false;
			$billing_setting_update = false;
			$splitter = false;
			$onuCpe = false;
        }
        //add to data base permissions for user
        $perm = new Permission();
        $perm->access_clients = $clients;
        $perm->access_clients_editar = $cliente_editar;
        $perm->access_clients_eliminar = $cliente_eliminar;
        $perm->access_clients_activate = $cliente_activar;
        $perm->access_plans = $plans;
        $perm->access_routers = $routers;
        $perm->access_users = $users;
        $perm->access_system = $system;
        $perm->locations_access = $location;
        $perm->maps_client_access = $map;
        $perm->access_pays = $pays;
        $perm->access_templates = $tem;
        $perm->access_reports = $report;
        $perm->access_tickets = $ticket;
        $perm->access_sms = $sms;

        $perm->facturacion = $facturacion;
        $perm->tran_facturacion_editar = $tran_facturacion_editar;
        $perm->tran_facturacion_eliminar = $tran_facturacion_eliminar;

        $perm->factura_pagar = $factura_pagar;
        $perm->factura_editar = $factura_editar;
        $perm->factura_eliminar = $factura_eliminar;

        $perm->servicio_info = $servicio_info;
        $perm->servicio_edit = $servicio_edit;
        $perm->servicio_delete = $servicio_delete;
        $perm->servicio_activate_desactivar = $servicio_activate_desactivar;
        $perm->servicio_new = $servicio_new;

        $perm->pagos_nuevo = $pagos_nuevo;
        $perm->pagos_editar = $pagos_editar;
        $perm->pagos_eliminar = $pagos_eliminar;

        $perm->finanzas = $finanzas;
        $perm->tran_finanzas_editar = $tran_finanzas_editar;
        $perm->tran_finanzas_eliminar = $tran_finanzas_eliminar;
        $perm->estado_financier = $estado_financier;

        $perm->factura_finanzas_pagar = $factura_finanzas_pagar;
        $perm->factura_finanzas_editar = $factura_finanzas_editar;
        $perm->factura_finanzas_eliminar = $factura_finanzas_eliminar;

        $perm->pagos_finanzas_editar = $pagos_finanzas_editar;
        $perm->pagos_finanzas_eliminar = $pagos_finanzas_eliminar;

        $perm->billing_setting_update = $billing_setting_update;

        $perm->splitter = $splitter;
        $perm->onu_cpe = $onuCpe;

        $perm->user_id = $id;
        //saved
        $perm->save();
        //save log
	    $name = $request->get('username');
	    CommonService::log("Nueva usuario añadida: $name", $this->username, 'success' , $this->userId);

        return Response::json(array('msg' => 'success'));
    }

    //metodo para eliminar
    public function postDelete(Request $request)
    {
        $user_id = $request->get('id');
        $user = User::find($user_id);

        //coprobamos si no existe debuelve null
        if (is_null($user))
            return Response::json(array('msg' => 'notfound'));

        DB::table('payment_news')->where('received_by', $user_id)->update([
            'received_by' => null
        ]);

        DB::table('logs')->where('user_id', $user_id)->update([
            'user_id' => null
        ]);

        DB::table('wallet_payments')->where('user_id', $user_id)->update([
            'user_id' => null
        ]);

        $perm = User::find($user_id)->permission;

        DB::beginTransaction();

        if(is_null($perm)) {
            $name = $user->username;
            $user->delete();

        } else {
            $name = $perm->user->username;
            $perm->user->delete();
            //eliminamos y redirigimos
            $perm->delete();
        }


        DB::commit();
        //save log
	    CommonService::log("Usuario eliminada: $name", $this->username, 'danger' , $this->userId);
	
	
	    return Response::json(array('msg' => 'success'));
    }

    //metodo para bloquer a usuarios
    public function postBan(Request $request)
    {
        $user_id = $request->get('id');
        $user = User::find($user_id);
        $status = $user->status;

        if ($status == 1)
            $user->status = false;
        else
            $user->status = true;

        $user->save();
		$name = $user->username;
        //save log
	    CommonService::log("Bloqueo de usuario: $name", $this->username, 'danger' , $this->userId);
	    
	    return Response::json(array('msg' => 'success'));
    }

    //metodo para actualizar a usuarios
    public function postUpdate(UpdateRequest $request)
    {
        $user_id = $request->get('user_id');

        $perm = User::find($user_id)->permission;
        if (is_null($perm))
            return Response::json(array('msg' => 'error'));

        //permissions
        $sites = $request->get('user_acc');

        //acceso clientes
        if (in_array("cli", $sites)) $clients = true;
        else $clients = false;

         //acceso clientes editar
        if (in_array("cliente_editar", $sites)) $cliente_editar = true;
        else $cliente_editar = false;

        //acceso clientes eliminar
        if (in_array("cliente_eliminar", $sites)) $cliente_eliminar = true;
        else $cliente_eliminar = false;

        //acceso clientes activar/desactivar
        if (in_array("cliente_activar", $sites)) $cliente_activar = true;
        else $cliente_activar = false;

        //acceso planes
        if (in_array("pla", $sites)) $plans = true;
        else $plans = false;

        //acceso routers
        if (in_array("rou", $sites)) $routers = true;
        else $routers = false;

        //acceso usuarios
        if (in_array("use", $sites)) $users = true;
        else $users = false;

        //acceso systema
        if (in_array("access_system", $sites)) $system = true;
        else $system = false;

        //acceso cliente maps
        if (in_array("maps_client_access", $sites)) $map = true;
        else $map = false;

        //acceso cliente locations
        if (in_array("locations_access", $sites)) $location = true;
        else $location = false;

        //acceso pagos
        if (in_array("pay", $sites)) $pays = true;
        else $pays = false;

        //acceso plantillas
        if (in_array("tem", $sites)) $tem = true;
        else $tem = false;

        //acceso reportes
        if (in_array("reports", $sites)) $rep = true;
        else $rep = false;

        //acceso tickets
        if (in_array("ticket", $sites)) $ticket = true;
        else $ticket = false;

        //acceso sms
        if (in_array("sms", $sites)) $sms = true;
        else $sms = false;

        if (in_array("facturacion", $sites)) $facturacion = true;
        else $facturacion = false;

        if (in_array("servicio_info", $sites)) $servicio_info = true;
        else $servicio_info = false;

        if (in_array("servicio_edit", $sites)) $servicio_edit = true;
        else $servicio_edit = false;

        if (in_array("servicio_delete", $sites)) $servicio_delete = true;
        else $servicio_delete = false;

        if (in_array("servicio_activate_desactivar", $sites)) $servicio_activate_desactivar = true;
        else $servicio_activate_desactivar = false;

        if (in_array("servicio_new", $sites)) $servicio_new = true;
        else $servicio_new = false;

        if (in_array("tran_facturacion_editar", $sites)) $tran_facturacion_editar = true;
        else $tran_facturacion_editar = false;

        if (in_array("tran_facturacion_eliminar", $sites)) $tran_facturacion_eliminar = true;
        else $tran_facturacion_eliminar = false;

        if (in_array("factura_pagar", $sites)) $factura_pagar = true;
        else $factura_pagar = false;

        //acceso clientes eliminar
        if (in_array("edit_client_balance", $sites)) $edit_client_balance = true;
        else $edit_client_balance = false;

        if (in_array("factura_editar", $sites)) $factura_editar = true;
        else $factura_editar = false;

        if (in_array("factura_eliminar", $sites)) $factura_eliminar = true;
        else $factura_eliminar = false;

        if (in_array("pagos_nuevo", $sites)) $pagos_nuevo = true;
        else $pagos_nuevo = false;

        if (in_array("pagos_editar", $sites)) $pagos_editar = true;
        else $pagos_editar = false;

        if (in_array("pagos_eliminar", $sites)) $pagos_eliminar = true;
        else $pagos_eliminar = false;

        if (in_array("finanzas", $sites)) $finanzas = true;
        else $finanzas = false;

        if (in_array("tran_finanzas_editar", $sites)) $tran_finanzas_editar = true;
        else $tran_finanzas_editar = false;

        if (in_array("estado_financier", $sites)) $estado_financier = true;
        else $estado_financier = false;

        if (in_array("tran_finanzas_eliminar", $sites)) $tran_finanzas_eliminar = true;
        else $tran_finanzas_eliminar = false;

        if (in_array("factura_finanzas_pagar", $sites)) $factura_finanzas_pagar = true;
        else $factura_finanzas_pagar = false;

        if (in_array("factura_finanzas_editar", $sites)) $factura_finanzas_editar = true;
        else $factura_finanzas_editar = false;

        if (in_array("factura_finanzas_eliminar", $sites)) $factura_finanzas_eliminar = true;
        else $factura_finanzas_eliminar = false;

        if (in_array("pagos_finanzas_editar", $sites)) $pagos_finanzas_editar = true;
        else $pagos_finanzas_editar = false;

        if (in_array("pagos_finanzas_eliminar", $sites)) $pagos_finanzas_eliminar = true;
        else $pagos_finanzas_eliminar = false;

        //habilitado
        if (empty($request->get('edit_status'))) $status = false;
        else $status = true;

        if (in_array("billing_setting_update", $sites)) $billing_setting_update = true;
        else $billing_setting_update = false;

        if (in_array("splitter", $sites)) $splitter = true;
        else $splitter = false;

        if (in_array("onu_cpe", $sites)) $onuCpe = true;
        else $onuCpe = false;
	
        $level = 'us';
        
	    if($request->has('edit_cashdesk') && $request->get('edit_cashdesk') == 'cs') {
	        $level = 'cs';
		
		    $clients = false;
			$cliente_editar = false;
			$cliente_eliminar = false;
			$cliente_activar = false;
			$plans = false;
			$routers = false;
			$users = false;
			$system = false;
			$location = false;
			$map = false;
			$pays = false;
			$tem = false;
			$rep = false;
			$ticket = false;
			$sms = false;
			$facturacion = false;
			$tran_facturacion_editar = false;
			$tran_facturacion_eliminar = false;
			$estado_financier = false;
			$servicio_info = false;
			$servicio_edit = false;
			$servicio_delete = false;
			$servicio_activate_desactivar = false;
			$servicio_new = false;
			$factura_pagar = false;
			$edit_client_balance = false;
			$factura_editar = false;
			$factura_eliminar = false;
			$pagos_nuevo = false;
			$pagos_editar = false;
			$pagos_eliminar = false;
			$finanzas = false;
			$tran_finanzas_editar = false;
			$tran_finanzas_eliminar = false;
			$factura_finanzas_pagar = false;
			$factura_finanzas_editar = false;
			$factura_finanzas_eliminar = false;
			$pagos_finanzas_editar = false;
			$pagos_finanzas_eliminar = false;
			$billing_setting_update = false;
			$splitter = false;
			$onuCpe = false;
	    }
		
		//update info
        $perm->user->name = $request->get('edit_name');
        $perm->user->email = $request->get('edit_email');
        $perm->user->phone = $request->get('edit_phone');
        $perm->user->status = $status;
        $perm->user->level = $level;
        
        if($request->has('password') && $request->password != '') {
	        $perm->user->password = Hash::make($request->password);
        }

        //add permissions and access
        $perm->access_clients = $clients;
        $perm->access_clients_editar = $cliente_editar;
        $perm->access_clients_eliminar = $cliente_eliminar;
        $perm->access_clients_activate = $cliente_activar;
        $perm->access_plans = $plans;
        $perm->access_routers = $routers;
        $perm->access_users = $users;
        $perm->access_system = $system;
        $perm->locations_access = $location;
        $perm->maps_client_access = $map;
        $perm->access_pays = $pays;
        $perm->access_templates = $tem;
        $perm->access_reports = $rep;
        $perm->access_tickets = $ticket;
        $perm->access_sms = $sms;

        $perm->facturacion = $facturacion;
        $perm->tran_facturacion_editar = $tran_facturacion_editar;
        $perm->tran_facturacion_eliminar = $tran_facturacion_eliminar;
        $perm->estado_financier = $estado_financier;

        $perm->servicio_info = $servicio_info;
        $perm->servicio_edit = $servicio_edit;
        $perm->servicio_delete = $servicio_delete;
        $perm->servicio_activate_desactivar = $servicio_activate_desactivar;
        $perm->servicio_new = $servicio_new;

        $perm->factura_pagar = $factura_pagar;
        $perm->edit_client_balance = $edit_client_balance;
        $perm->factura_editar = $factura_editar;
        $perm->factura_eliminar = $factura_eliminar;

        $perm->pagos_nuevo = $pagos_nuevo;
        $perm->pagos_editar = $pagos_editar;
        $perm->pagos_eliminar = $pagos_eliminar;

        $perm->finanzas = $finanzas;
        $perm->tran_finanzas_editar = $tran_finanzas_editar;
        $perm->tran_finanzas_eliminar = $tran_finanzas_eliminar;

        $perm->factura_finanzas_pagar = $factura_finanzas_pagar;
        $perm->factura_finanzas_editar = $factura_finanzas_editar;
        $perm->factura_finanzas_eliminar = $factura_finanzas_eliminar;

        $perm->pagos_finanzas_editar = $pagos_finanzas_editar;
        $perm->pagos_finanzas_eliminar = $pagos_finanzas_eliminar;

        $perm->billing_setting_update = $billing_setting_update;

        $perm->splitter = $splitter;
        $perm->onu_cpe = $onuCpe;

        $perm->user->save();
        $perm->save();
        //save log
	    $name = $perm->user->username;
	    CommonService::log("#$user_id Actualizo información del usuario: $name", $this->username, 'danger' , $this->userId);
	
	
	    return Response::json(array('msg' => 'success'));
    }
    
    public function getCredits(UserCreditDataTable $dataTable, $id)
    {
        $this->user = User::with('credits')->find($id);
        
        if(!$this->user || $this->user->level != 'cs') {
        	return abort(404);
        }
	
	    $perm = DB::table('permissions')->where('user_id', '=', auth()->user()->id)->first();
	    $access = $perm->access_users;
	    $level = Auth::user()->level;
	    
	    //control permissions only access super administrator (sa)
	    if ($level == 'ad' || $access == true) {
		    $this->clients = $perm->access_clients;
		    $this->plans = $perm->access_plans;
		    $this->routers = $perm->access_routers;
		    $this->users = $perm->access_users;
		    $this->system = $perm->access_system;
		    $this->permissions = $perm;
		    $this->bill = $perm->access_pays;
		    $this->template = $perm->access_templates;
		    $this->ticket = $perm->access_tickets;
		    $this->sms = $perm->access_sms;
		    $this->reports = $perm->access_reports;
		    $global = GlobalSetting::first();
		    $this->v = $global->version;
		    $this->lv = $global->license;
		    $this->st = $global->status;
		    $this->company = $global->company;
		    $this->menu = "users";
		    $this->submenu = 1;
		
		    if (Auth::user()->level == 'ad')
			    @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);
		
		    return $dataTable->render('users/credits/index', $this->data);
	    } else
			return Redirect::to('admin');
	   
    }
    
    public function storeCredits(\App\Http\Requests\Admin\Credit\CreateRequest $request)
    {
        $userCredit = new UserCredit();
        
        $userCredit->credit = $request->credit;
        $userCredit->comment = $request->comment;
        $userCredit->user_id = $request->user_id;
        $userCredit->save();
        
        
        $user = User::find($request->user_id);
        $user->balance = (float)$user->balance + (float) $request->credit;
        $user->save();
	
        $name = $user->name;
	    CommonService::log("Créditos agregados para @$name ", $this->username, 'success' , $this->userId);
	
	
	    return \response()->json(['status' => 'success', 'message' => 'Successfully added']);
    }
	
	
	//metodo para eliminar
	public function deleteCredits(Request $request)
	{
		$creditId = $request->get('id');
		
		$credit = UserCredit::with('user')->find($creditId);
		
		$credit->user->balance = (float) $credit->user->balance - (float) $credit->credit;
		$credit->user->save();
		
		$credit->delete();
		
		$name = $credit->user->name;
		CommonService::log("Créditos eliminados para @$name ", $this->username, 'success' , $this->userId);
		return Response::json(array('msg' => 'success'));
	}

}
