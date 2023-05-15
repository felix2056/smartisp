<?php

namespace App\Http\Controllers;

use App\Classes\Reply;
use App\DataTables\ClientDataTable;
use App\DataTables\ClientDayFreeDataTable;
use App\DataTables\ClientLockedDataTable;
use App\DataTables\PlanClientDataTable;
use App\DataTables\RouterClientDataTable;
use App\Events\UpdateClientEvent;
use App\Export\DataTableExport;
use App\Http\Requests\Admin\Client\CortadoChangeRequest;
use App\Http\Requests\Admin\Client\CreateRequest;
use App\Http\Requests\Admin\Client\UpdateRequest;
use App\Http\Requests\Client\UpdateBalanceRequest;
use App\libraries\AddClient;
use App\libraries\Burst;
use App\libraries\Chkerr;
use App\libraries\CountClient;
use App\libraries\GetDate;
use App\libraries\GetPlan;
use App\libraries\Helpers;
use App\libraries\Intersep;
use App\libraries\Mikrotik;
use App\libraries\Mkerror;
use App\libraries\MkMigrate;
use App\libraries\Pencrypt;
use App\libraries\PermitidosList;
use App\libraries\RegPay;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\libraries\Sclient;
use App\libraries\Slog;
use App\libraries\StatusIp;
use App\libraries\UpdateClient;
use App\libraries\Validator;
use App\models\AddressRouter;
use App\models\BillCustomer;
use App\models\BillingSettings;
use App\models\CashierDepositHistory;
use App\models\Client;
use App\models\ClientService;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\Logg;
use App\models\Network;
use App\models\PaymentNew;
use App\models\Plan;
use App\models\radius\Nas;
use App\models\radius\Radcheck;
use App\models\radius\Radius;
use App\models\radius\Radreply;
use App\models\Router;
use App\models\SuspendClient;
use App\models\Ticket;
use App\models\Establecimientos;
use App\models\PuntoEmision;
use App\models\Transaction;
use App\models\WalletPayment;
use App\Service\CommonService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;
use App\models\campos_view_client;
use Yajra\DataTables\Facades\DataTables;
use App\models\OdbSplitter;
use App\models\OnuType;
use App\models\Zone;
use Session;
use App\models\Typedoc;
use App\models\Typetaxpayer;
use App\models\Accountingregime;
use App\models\Typeresponsibility;
use App\models\Economicactivity;
use App\models\Factel;
use App\libraries\Radius as RadiusLibrary;

class ClientsController extends BaseController
{
    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
        $this->global = GlobalSetting::first();


	    $this->middleware(function ($request, $next) {
		    $this->username = auth()->user()->username;
		    $this->userId = auth()->user()->id;
		    return $next($request);
	    });
        // $hashed_random_password = Hash::make('SmartISP777ac');
        // dd($hashed_random_password);
    }

    public function postIdClient()
    {
        $cliente = Client::orderby('id', 'DESC')->take(1)->get();
        $id = $cliente[0]->id;
        return Response::json(array(array('id' => $id)));
    }

    //metodo para ingresar a planes
    public function getIndex(ClientDataTable $dataTable)
    {
        $id = Auth::user()->id;
        $campos_v = campos_view_client::find(1);
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
        $access = $perm[0]->access_clients;
        //consulta para llenar los select del form cliente
        $municipio = DB::table('municipio')
            ->Join('departamento', 'departamento.cod', '=', 'municipio.departamento_cod')
            ->select('municipio.Description AS Municipio', 'departamento.Description AS Departamento', 'municipio.cod')->get();
        $punto_emision = PuntoEmision::all();
        $typedoc = Typedoc::all();
        $typetaxpayer = Typetaxpayer::all();
        $accountingregime = Accountingregime::all();
        $typeresponsibility = Typeresponsibility::all();
        $economicactivity = Economicactivity::all();
        $falctelStatus = Factel::all()->first()->status;
        //control permissions only access super administrator (sa)
        if ($level == 'ad' || $access == true) {
            $global = GlobalSetting::all()->first();

            $GoogleMaps = Helpers::get_api_options('googlemaps');

            if (count($GoogleMaps) > 0) {
                $key = $GoogleMaps['k'];
            } else {
                $key = 0;
            }
            $OdbSplitter = OdbSplitter::all();
            $OnuType = OnuType::all();
            $Zone = Zone::all();

            $allRouters = Router::all();
            $allPlans = Plan::all();

            $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
                "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
                "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
                "reports" => $perm[0]->access_reports,
                "v" => $global->version, "st" => $global->status, "map" => $key,
                "lv" => $global->license, "company" => $global->company,
                "global" => $global,
                'permissions' => $perm->first(),
                'allRouters' => $allRouters,
                'allPlans' => $allPlans,
                // menu options
                "campos_v" => $campos_v, "OdbSplitter" => $OdbSplitter,
                "OnuType" => $OnuType, "Zone" => $Zone, "falctelStatus" => $falctelStatus, "cmbtypedoc" => (!empty($typedoc)) ? $typedoc : '', "cmbmunicipio" => (!empty($municipio)) ? $municipio : '',
                "cmbtypetaxpayer" => (!empty($typetaxpayer)) ? $typetaxpayer : '', "cmbtypetaxpayer" => (!empty($typetaxpayer)) ? $typetaxpayer : '',
                "punto_emision" => $punto_emision,
                "cmbtyperesponsibility" => (!empty($typeresponsibility)) ? $typeresponsibility : '', "cmbeconomicactivity" => (!empty($economicactivity)) ? $economicactivity : '',
            );

            if (Auth::user()->level == 'ad') {
                @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);
            }

            return $dataTable->render('clients.index', $permissions);

//            $contents = View::make('clients.index', $permissions);
//            $response = Response::make($contents, 200);
//            $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
//            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
//            $response->header('Pragma', 'no-cache');
//            return $response;
        } else {
            return Redirect::to('admin');
        }

    }

    public function getIndex2(ClientLockedDataTable $dataTable)
    {
        $id = Auth::user()->id;
        $campos_v = campos_view_client::find(1);
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
        $access = $perm[0]->access_clients;
        //control permissions only access super administrator (sa)
        if ($level == 'ad' || $access == true) {
            $global = GlobalSetting::all()->first();

            $GoogleMaps = Helpers::get_api_options('googlemaps');

            if (count($GoogleMaps) > 0) {
                $key = $GoogleMaps['k'];
            } else {
                $key = 0;
            }
            $punto_emision = PuntoEmision::all();
            $OdbSplitter = OdbSplitter::all();
            $OnuType = OnuType::all();
            $Zone = Zone::all();
            $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
                "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
                "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
                "reports" => $perm[0]->access_reports,
                "v" => $global->version, "st" => $global->status, "map" => $key,
                "lv" => $global->license, "company" => $global->company,
                // menu options
	            "campos_v" => $campos_v, "OdbSplitter" => $OdbSplitter,
                "OnuType" => $OnuType, "Zone" => $Zone,
                "punto_emision" => $punto_emision,
                'permissions' => $perm->first(),
            );

            if (Auth::user()->level == 'ad') {
                @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);
            }

            return $dataTable->render('clients.index_b', $permissions);

        } else {
            return Redirect::to('admin');
        }

    }

    public function getIndex3(ClientDayFreeDataTable $dataTable)
    {
        $id = Auth::user()->id;
        $campos_v = campos_view_client::find(1);
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
        $access = $perm[0]->access_clients;
        //control permissions only access super administrator (sa)
        if ($level == 'ad' || $access == true) {
            $global = GlobalSetting::all()->first();

            $GoogleMaps = Helpers::get_api_options('googlemaps');

            if (count($GoogleMaps) > 0) {
                $key = $GoogleMaps['k'];
            } else {
                $key = 0;
            }
            $OdbSplitter = OdbSplitter::all();
            $punto_emision = PuntoEmision::all();
            $OnuType = OnuType::all();
            $Zone = Zone::all();
            $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
                "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
                "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
                "reports" => $perm[0]->access_reports,
                "v" => $global->version, "st" => $global->status, "map" => $key,
                "lv" => $global->license, "company" => $global->company,
                // menu options
                "campos_v" => $campos_v, "OdbSplitter" => $OdbSplitter,
                "OnuType" => $OnuType, "Zone" => $Zone,
                "punto_emision" => $punto_emision,
                'permissions' => $perm->first(),
            );

            if (Auth::user()->level == 'ad') {
                @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);
            }

            return $dataTable->render('clients.index_day', $permissions);
        } else {
            return Redirect::to('admin');
        }

    }

    //metodo para recuperar info general del cliente
    public function postInfo_data($id)
    {

        //recuperamos info del cliente
        $client = DB::table('clients')
            ->join('plans', 'plans.id', '=', 'clients.plan_id')
            ->join('routers', 'routers.id', '=', 'clients.router_id')
            ->join('suspend_clients', 'suspend_clients.client_id', '=', 'clients.id')
            ->join('control_routers', 'control_routers.router_id', '=', 'clients.router_id')
            ->select('clients.name', 'clients.status As stclient', 'clients.ip As ipclient', 'clients.mac',
                'plans.name As plan_name', 'suspend_clients.expiration', 'routers.name As routername',
                'control_routers.type_control', 'control_routers.arpmac', 'control_routers.dhcp',
                'control_routers.adv')->where('clients.id', $id)->get();

        $global = GlobalSetting::all()->first();

        $before = $global->before_days;

        $tolerance = $global->tolerance;
        if (count($client) > 0) {
            $paydate = date_create($client[0]->expiration);

            if ($tolerance != 0) {
                # Significa que hay tolerancia en dias aumentamos
                $newexpiries = strtotime('+' . $tolerance . ' day', strtotime($client[0]->expiration));
                $cutdate = date('d/m/Y', $newexpiries);

            } else {
                $cutdate = date_format($paydate, "d/m/Y");
            }

            if ($global->send_presms == '1') {

                $newExpiries = strtotime('-' . $before . ' day', strtotime($client[0]->expiration));
                $send_sms = date('d/m/Y', $newExpiries);
                $send_sms = $send_sms . ' ' . $global->send_hrs;

            } else {
                $send_sms = 'Desactivado';
            }

            if ($global->send_prebill == '1') {

                $newExpiries = strtotime('-' . $before . ' day', strtotime($client[0]->expiration));
                $send_email = date('d/m/Y', $newExpiries);
                $send_email = $send_email . ' ' . $global->send_hrs;

            } else {
                $send_email = 'Desactivado';
            }


            $data = array(

                'name' => $client[0]->name,
                'paydate' => date_format($paydate, "d/m/Y"),
                'plan' => $client[0]->plan_name,
                'sms' => $send_sms,
                'email' => $send_email,
                'cut' => $cutdate . ' 00:00:00',
                'router' => $client[0]->routername,
                'ip' => $client[0]->ipclient,
                'mac' => $client[0]->mac,
                'control' => $client[0]->type_control,
                'portal' => $client[0]->adv,
                'status' => $client[0]->stclient
            );


        } else {
            $data = array(

                'name' => '-',
                'paydate' => '-',
                'plan' => '-',
                'sms' => '-',
                'email' => '-',
                'cut' => '-',
                'router' => '-',
                'ip' => '-',
                'mac' => '-',
                'control' => '-',
                'portal' => '-',
                'status' => '-'
            );
        }
        return $data;

    }

    //metodo registrar usuarios
    public function postCreateCamposView(Request $request)
    {

        $friendly_names = array(
            'campos_acc' => __('app.Userpermits')
        );

        $rules = array(
            'campos_acc' => 'required'
        );

        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);

        if ($validation->fails())
            return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

        $sites = $request->get('campos_acc');

        //acceso clientes
        if (in_array("name", $sites)) $name = true;
        else $name = false;

        if (in_array("ip", $sites)) $ip = true;
        else $ip = false;

        if (in_array("router", $sites)) $router = true;
        else $router = false;

        if (in_array("estado", $sites)) $estado = true;
        else $estado = false;

        if (in_array("control", $sites)) $control = true;
        else $control = false;

        if (in_array("plan", $sites)) $plan = true;
        else $plan = false;

        if (in_array("servicio", $sites)) $servicio = true;
        else $servicio = false;

        if (in_array("balance", $sites)) $balance = true;
        else $balance = false;

        if (in_array("day_payment", $sites)) $day_payment = true;
        else $day_payment = false;

        if (in_array("cut", $sites)) $cut = true;
        else $cut = false;

        if (in_array("mac", $sites)) $mac = true;
        else $mac = false;


        if (in_array("zone", $sites)) $zone = true;
        else $zone = false;

        if (in_array("odb_id", $sites)) $odb_id = true;
        else $odb_id = false;

        if (in_array("onu_id", $sites)) $onu_id = true;
        else $onu_id = false;

        if (in_array("onusn", $sites)) $onusn = true;
        else $onusn = false;


        //add to data base permissions for user
        $perm = campos_view_client::find(1);
        $perm->name = $name;
        $perm->ip = $ip;
        $perm->router = $router;
        $perm->estado = $estado;
        $perm->control = $control;
        $perm->plan = $plan;
        $perm->servicio = $servicio;
        $perm->balance = $balance;
        $perm->day_payment = $day_payment;
        $perm->cut = $cut;
        $perm->mac = $mac;
        $perm->zone = $zone;
        $perm->odb_id = $odb_id;
        $perm->onu_id = $onu_id;
        $perm->onusn = $onusn;
        //saved
        $perm->save();

        return Response::json(array('msg' => 'success'));
    }

    private function like_match($pattern, $subject)
    {
        $pattern = str_replace('%', '.*', preg_quote($pattern, '/'));
        return (bool)preg_match("/^{$pattern}$/i", $subject);
    }

    public function isRealDate($date)
    {
        if (strtotime($date)) {
            return true;
        }
        return false;
    }

    //metodo para listar clientes
    public function postList(Request $request)
    {

//        $users = Client::leftJoin('client_services', 'client_services.client_id', '=', 'clients.id')
//            ->leftJoin('odb_splitter', 'odb_splitter.id', '=', 'clients.odb_id')
//            ->leftJoin('zone', 'zone.id', '=', 'clients.zona_id')
//            ->leftJoin('onu_type', 'onu_type.id', '=', 'clients.onu_id')
//            ->leftJoin('billing_settings', 'billing_settings.client_id', '=', 'clients.id')
//            ->leftJoin('routers', 'routers.id', '=', 'client_services.router_id')
//            ->leftJoin('control_routers', 'routers.id', '=', 'control_routers.router_id')
//            ->leftJoin('suspend_clients as suspend', 'suspend.client_id', '=', 'client_services.client_id')
//            ->leftJoin('plans', 'plans.id', '=', 'client_services.plan_id');
//
//        if ($request->control != 'all') {
//            $users = $users->where('control_routers.type_control', $request->control);
//        }
//
//        if ($request->client_status == 'active') {
//            $users = $users->whereHas('service');
//        }
//
//        if ($request->client_status == 'inactive') {
//            $users = $users->doesntHave('service');
//        }
//
//        if ($request->client_name != '') {
//            $users = $users->whereRaw(
//                "REPLACE(clients.name,' ','') like ?", ['%' . str_replace(' ', '', $request->client_name) . '%']
//            );
//        }
//
//        if ($request->ip_filter != '') {
//            $users = $users->where('client_services.ip', 'like', "%$request->ip_filter%");
//        }
//
//        if ($request->router != 'all') {
//            $users = $users->where('routers.id', $request->router);
//        }
//
//        if ($request->plan != 'all') {
//            $users = $users->where('plans.id', $request->plan);
//        }
//
//        if ($request->online != 'all') {
//            $users = $users->where('client_services.online', $request->online);
//        }
//
//        if ($request->status != 'all') {
//            $users = $users->where('client_services.status', $request->status);
//        }
//
//        if ($request->expiration) {
//            $users = $users->whereDate('suspend.expiration', '=', Carbon::parse($request->expiration));
//        }
//
//        if ($request->cut) {
//            $cutDate = Carbon::parse($request->cut)->format('Y-m-d');
//            $users = $users->whereDate('suspend.expiration', '=', DB::raw('DATE_SUB("' . $cutDate . '", INTERVAL (billing_settings.billing_grace_period + ' . $this->global->tolerance . ') DAY)'));
//        }
//
//        $users = $users->select('clients.id', 'clients.name',
//            'clients.balance', 'billing_settings.billing_grace_period', 'zone.name as zone', 'odb_splitter.name as odb_id', 'onu_type.onutype as onu_id', 'clients.onusn')
//            ->with('service', 'service.router', 'service.router.control_router', 'service.plan')->groupBy('clients.id');
//
//        return DataTables::of($users)
//            ->addColumn('action',
//                function ($row) {
//                    $actions = '';
//
//                    $eliminar = '';
//                    $editar = '';
//
//                    $styleb = '<div class="action-buttons">';
//                    if (PermissionsController::hasAnyRole('access_clients_editar'))
//                        $editar .= '<a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="' . $row->id . '" title="' . __('app.edit') . '"><i class="ace-icon fa fa-pencil bigger-130"></i></a>';
//
//                    if (PermissionsController::hasAnyRole('access_clients_eliminar'))
//                        $eliminar = '<a class="red del" href="#" id="' . $row->id . '" title="' . __('app.remove') . '"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>';
//
//                    if ($row->service->count() > 0) {
//                        foreach ($row->service as $service) {
//                            if ($service->status == 'ac') {
//                                $active_s = '';
//                                if (PermissionsController::hasAnyRole('access_clients_activate'))
//                                    $active_s = '<a class="blue ban-service" href="#" id="' . $service->id . '" title="' . __('app.serviceCut') . '" xmlns="http://www.w3.org/1999/html"><i class="ace-icon fa fa-adjust bigger-130"></i></a>';
//
//                                $actions .= $styleb . $active_s . $editar . $eliminar . '<a class="grey tool" title="' . __('app.tools') . '" href="#" id="' . $service->id . '"><i class="ace-icon fa fa-wrench bigger-130"></i></a><a class="blue infos" title="' . __('app.information') . '" href="#" id="' . $service->id . '"><i class="ace-icon fa fa-info-circle bigger-130"></i></a></div></br>';
//                            }
//                            if ($service->status == 'de') {
//                                $active_s = '';
//                                if (PermissionsController::hasAnyRole('access_clients_activate'))
//                                    $active_s = '<a class="blue ban-service" href="#" id="' . $service->id . '" title="' . __('app.activate') . ' ' . __('app.service') . '"><i class="ace-icon fa fa-adjust bigger-130"></i></a>';
//
//                                $actions .= $styleb . $active_s . $eliminar . '<a class="blue infos" title="' . __('app.information') . '" href="#" id="' . $service->id . '"><i class="ace-icon fa fa-info-circle bigger-130"></i></a></div>';
//                            }
//                        }
//                    } else {
//                        $actions .= $styleb . $editar . $eliminar;
//                    }
//                    return $actions;
//
//                })
//            ->editColumn('name', function ($row) {
//                return '<a href="' . route('billing', $row->id) . '">' . $row->name . '</a>';
//            })
//            ->editColumn('online', function ($row) {
//
//                $tp = '';
//                foreach ($row->service as $service) {
//                    if ($service->online == 'on') {
//                        $tp .= '<p><span class="label label-success">' . __('app.Online') . '</span></p>';
//                    }
//                    if ($service->online == 'off') {
//                        $tp .= '<p><span class="label label-danger">' . __('app.disconnected') . '</span></p>';
//                    }
//                    if ($service->online == 'ver') {
//                        $tp .= '<p><span class="label label-warning">' . __('app.verifying') . '</span></p>';
//                    }
//                }
//
//                if ($tp == '') {
//                    $tp = '---';
//                }
//                return $tp;
//            })
//            ->editColumn('tp', function ($row) {
//                $tp = '';
//                foreach ($row->service as $service) {
//                    if ($service->router->control_router->type_control == 'ho') {
//                        $tp .= '<p><span class="label label-purple">Hotspot</span></p>';
//                    }
//
//                    if ($service->router->control_router->type_control == 'ha') {
//                        $tp .= '<p><span class="label label-purple">Hotspot - PCQ</span></p>';
//                    }
//
//                    if ($service->router->control_router->type_control == 'sq') {
//                        $tp .= '<p><span class="label label-success">Simple Queues</span></p>';
//                    }
//                    if ($service->router->control_router->type_control == 'pp') {
//                        $tp .= '<p><span class="label label-yellow">PPPoE</span></p>';
//                    }
//                    if ($service->router->control_router->type_control == 'pa') {
//                        $tp .= '<p><span class="label label-yellow">PPPoE - PCQ</span></p>';
//                    }
//                    if ($service->router->control_router->type_control == 'nc') {
//                        $tp .= '<p><span class="label label-grey">Sin conexión</span></p>';
//                    }
//                    if ($service->router->control_router->type_control == 'pc') {
//                        $tp .= '<p><span class="label label-warning">PCQ</span></p>';
//                    }
//                    if ($service->router->control_router->type_control == 'st') {
//                        $tp .= '<p><span class="label label-success">Simple Queues (with Tree)</span></p>';
//                    }
//                    if ($service->router->control_router->type_control == 'pt') {
//                        $tp .= '<p><span class="label label-yellow">PPPoE - Simple Queues (with Tree)</span></p>';
//                    }
//                    if ($service->router->control_router->type_control == 'dl') {
//                        $tp .= '<p><span class="label label-default">DHCP Leases</span></p>';
//                    }
//                    if ($service->router->control_router->type_control == 'ps') {
//                        $tp .= '<p><span class="label label-yellow">PPPoE - Simple Queues</span></p>';
//                    }
//                    if ($service->router->control_router->type_control == 'no') {
//                        $tp .= '<p><span class="label label-default">' . __('app.none') . '</span></p>';
//                    }
//                }
//
//                if ($tp == '') {
//                    $tp = '---';
//                }
//                return $tp;
//
//            })
//            ->editColumn('plan_name', function ($row) {
//                $planName = '';
//                foreach ($row->service as $service) {
//                    if ($service->plan->name == 'Importados')
//                        $planName .= '<p><span class="text-danger">' . $service->plan->name . '</span></p>';
//                    else
//                        $planName .= '<p>' . $service->plan->name . '</p>';
//                }
//
//                if ($planName == '') {
//                    $planName = '---';
//                }
//                return $planName;
//            })
//            ->editColumn('status', function ($row) {
//                $status = '';
//                foreach ($row->service as $service) {
//                    if ($service->status == 'ac')
//                        $status .= '<p><span class="label label-success arrowed">' . __('app.active') . '</span></p>';
//                    else
//                        $status .= '<p><span class="label label-danger">' . __('app.blocked') . '</span></p>';
//                }
//
//                if ($status == '') {
//                    $status = '---';
//                }
//
//                return $status;
//            })
//            ->editColumn('expiration', function ($row) {
//                $expiration = '';
//                foreach ($row->service as $service) {
//                    if ($service->suspend_client) {
//                        $expiration .= '<p>' . $service->suspend_client->expiration->format("Y-m-d") . '</p>';
//                    } else {
//                        $expiration .= '<p>--</p>';
//                    }
//
//                }
//
//                if ($expiration == '') {
//                    $expiration = '---';
//                }
//
//                return $expiration;
//            })
//            ->editColumn('ip', function ($row) {
//                $ip = '';
//                foreach ($row->service as $service) {
//                    $ip .= '<p>' . $service->ip . '</p>';
//                }
//
//                if ($ip == '') {
//                    $ip = '---';
//                }
//                return $ip;
//            })
//            ->editColumn('mac', function ($row) {
//                $ip = '';
//                foreach ($row->service as $service) {
//                    $ip .= '<p>' . $service->mac . '</p>';
//                }
//
//                if ($ip == '') {
//                    $ip = '---';
//                }
//                return $ip;
//            })
//            ->editColumn('router', function ($row) {
//                $router = '';
//                foreach ($row->service as $service) {
//                    $router .= '<p>' . $service->router->name . '</p>';
//                }
//
//                if ($router == '') {
//                    $router = '---';
//                }
//
//                return $router;
//            })
//            ->addColumn('cut', function ($row) {
//                $expiration = '';
//                foreach ($row->service as $service) {
//
//                    if (!$service->suspend_client) {
//                        $expiration = '';
//                        continue;
//                    }
//
//                    if (($row->billing_grace_period != 0) || ($this->global->tolerance != 0)) {
//                        $t_diass = $row->billing_grace_period + $this->global->tolerance;
//                        $expiration .= '<p>' . Carbon::parse($service->suspend_client->expiration)->addDays($t_diass)->format('d/m/Y H:i:s') . '</p>';
//                    } else {
//                        $expiration .= '<p>' . Carbon::parse($service->suspend_client->expiration)->format('d/m/Y H:i:s') . '</p>';
//                    }
//                }
//
//                if ($expiration == '') {
//                    $expiration = '---';
//                }
//
//                return $expiration;
//            })
//            ->rawColumns(['action', 'name', 'online', 'tp', 'plan_name', 'status', 'zone', 'odb_id', 'onu_id', 'onusn', 'cut', 'expiration', 'router', 'ip', 'mac'])
//            ->make(true);

        /*$clients = DB::table('clients')
        ->join('plans', 'plans.id', '=', 'clients.plan_id')
        ->join('routers', 'routers.id', '=', 'clients.router_id')
        ->join('control_routers', 'routers.id', '=', 'control_routers.router_id')
        ->select('clients.id', 'clients.name', 'clients.ip', 'clients.mac', 'routers.name As router',
            'plans.name As plan_name', 'plans.cost', 'clients.status', 'control_routers.type_control As tp',
            'clients.plan_id', 'clients.router_id', 'clients.online','clients.balance')->orderBy('clients.name')->get();

        foreach ($clients as $client) {
            $info_add = $this->postInfo_data($client->id);
            $client->paydate=$info_add['paydate'];
            $client->cut=$info_add['cut'];
        }

        return Response::json($clients);*/
    }

    //metodo para listar clientes
    public function postList3()
    {

        $users = Client::with('service', 'service.router', 'service.router.control_router', 'service.plan')
            ->leftjoin('odb_splitter', 'odb_splitter.id', '=', 'clients.odb_id')
            ->leftjoin('zone', 'zone.id', '=', 'clients.zona_id')
            ->leftjoin('onu_type', 'onu_type.id', '=', 'clients.onu_id')
            ->leftjoin('billing_settings', 'billing_settings.client_id', '=', 'clients.id')
            ->select('clients.id', 'clients.name',
                'clients.online', 'clients.balance', 'billing_settings.billing_grace_period', 'zone.name as zone', 'odb_splitter.name as odb_id', 'onu_type.onutype as onu_id', 'clients.onusn')
            ->where('billing_settings.billing_grace_period', '>', "0")
            ->groupBy('clients.id');

        return DataTables::of($users)
//             ->filterColumn('ip', function ($query, $keyword) {
//                $sql = "service.ip  like ?";
//                $query->whereRaw($sql, ["%{$keyword}%"]);
//            })
//            ->filterColumn('status', function ($query, $keyword) {
//                $sql = "clients.status  like ?";
//                if ($this->like_match('%' . $keyword . '%', 'cortado')) {
//                    $keyword = 'de';
//                } else if ($this->like_match('%' . $keyword . '%', 'activo')) {
//                    $keyword = 'ac';
//                }
//
//                $query->whereRaw($sql, ["%{$keyword}%"]);
//            })
//            ->filterColumn('service.router.control_router.type_control', function ($query, $keyword) {
//                $sql = "service.router.control_router.type_control  like ?";
//                if ($keyword == 'PCQ' || $keyword == 'pcq') {
//                    $query->whereRaw($sql, ["%ho%"])->orWhereRaw($sql, ["%ha%"])->orWhereRaw($sql, ["%pc%"]);
//                }
//                else if ($this->like_match('%' . $keyword . '%', 'hotspot')) {
//                    $keyword = 'ho';
//                    $query->whereRaw($sql, ["%{$keyword}%"]);
//                } else if ($this->like_match('%' . $keyword . '%', 'Hotspot - PCQ')) {
//                    $keyword = 'ha';
//                    $query->whereRaw($sql, ["%{$keyword}%"]);
//                } else if ($this->like_match('%' . $keyword . '%', 'Simple Queues')) {
//                    $keyword = 'sq';
//                    $query->whereRaw($sql, ["%{$keyword}%"])->orWhereRaw($sql, ["%ps%"]);
//                } else if ($this->like_match('%' . $keyword . '%', 'PPPoE')) {
//                    $query->whereRaw($sql, ["%pp%"])->orWhereRaw($sql, ["%pa%"])->orWhereRaw($sql, ["%ps%"]);
//                } else if ($this->like_match('%' . $keyword . '%', 'Sin conexión')) {
//                    $keyword = 'nc';
//                    $query->whereRaw($sql, ["%{$keyword}%"]);
//                } else if ($this->like_match('%' . $keyword . '%', 'DHCP Leases')) {
//                    $keyword = 'dl';
//                    $query->whereRaw($sql, ["%{$keyword}%"]);
//                } else if ($this->like_match('%' . $keyword . '%', 'none')) {
//                    $keyword = 'no';
//                    $query->whereRaw($sql, ["%{$keyword}%"]);
//                }
//            })
//            ->filterColumn('router', function ($query, $keyword) {
//                $sql = "routers.name  like ?";
//                $query->whereRaw($sql, ["%{$keyword}%"]);
//            })
//            ->filterColumn('plan_name', function ($query, $keyword) {
//                $sql = "plans.name  like ?";
//                $query->whereRaw($sql, ["%{$keyword}%"]);
//            })
//            ->filterColumn('cut', function ($query, $keyword) {
//                if($this->isRealDate($keyword)) {
//
//                    // if ($row->billing_grace_period != 0) {
//                    //     $keyword = Carbon::parse($row->expiration)->addDays($row->billing_grace_period)->format('d/m/Y H:i:s');
//                    // }
//                    // else {
//                    $keyword = Carbon::parse($keyword)->format('Y-m-d');
//                    // }
//                    $sql = "suspend_clients.expiration  like ?";
//                    $query->whereRaw($sql, ["%{$keyword}%"]);
//                }
//
//            })
            ->addColumn('action', function ($row) {
                $actions = '';

                $eliminar = '';
                $editar = '';

                $styleb = '<div class="hidden-sm hidden-xs action-buttons">';
                $stylem = '<div class="hidden-md hidden-lg"><div class="inline position-relative"><button class="btn btn-minier btn-yellow dropdown-toggle" data-toggle="dropdown" data-position="auto"><i class="ace-icon fa fa-caret-down icon-only bigger-120"></i></button><ul class="dropdown-menu dropdown-only-icon dropdown-yellow dropdown-menu-right dropdown-caret dropdown-close"><li>';
                $stylee = '</li></ul></div></div>';
                if (PermissionsController::hasAnyRole('access_clients_editar'))
                    $editar .= '<a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="' . $row->id . '" title="' . __('app.edit') . '"><i class="ace-icon fa fa-pencil bigger-130"></i></a>';

                if (PermissionsController::hasAnyRole('access_clients_eliminar'))
                    $eliminar = '<a class="red del" href="#" id="' . $row->id . '" title="' . __('app.remove') . '"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>';

                if ($row->service->count() > 0) {
                    foreach ($row->service as $service) {
                        if ($service->status == 'ac') {
                            $actions .= $styleb . '<a class="blue ban-service" href="#" id="' . $service->id . '" title="' . __('app.serviceCut') . '" xmlns="http://www.w3.org/1999/html"><i class="ace-icon fa fa-adjust bigger-130"></i></a>' . $editar . $eliminar . '<a class="grey tool" title="' . __('app.tools') . '" href="#" id="' . $service->id . '"><i class="ace-icon fa fa-wrench bigger-130"></i></a><a class="blue infos" title="' . __('app.information') . '" href="#" id="' . $service->id . '"><i class="ace-icon fa fa-info-circle bigger-130"></i></a></div>' . $stylem . '<a href="#" class="ban-service" id="' . $service->id . '" title="' . __('app.cut') . '"><span class="blue"><i class="ace-icon fa fa-adjust bigger-120"></i></span></a></li><li><a href="#" class="blue infos" id="' . $service->id . '" title="información"><span class="blue"><i class="ace-icon fa fa-info-circle bigger-120"></i></span></a><li><a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="' . $row->id . '" title="' . __('app.edit') . '"><span class="success"><i class="ace-icon fa fa-pencil bigger-120"></i></span></a></li><li><a href="#" class="grey tool" id="' . $row->id . '" title="' . __('app.tools') . '"><span class="default"><i class="ace-icon fa fa-wrench bigger-120"></i></span></a></li><li><a class="red del" href="#" id="' . $row->id . '" title="' . __('app.remove') . '"><span class="red"><i class="ace-icon fa fa-trash-o bigger-120"></i></span></a>' . $stylee . '</br>';
                        }
                        if ($service->status == 'de') {
                            $actions .= $styleb . '<a class="blue ban-service" href="#" id="' . $service->id . '" title="' . __('app.activate') . ' ' . __('app.service') . '"><i class="ace-icon fa fa-adjust bigger-130"></i></a>' . $eliminar . '<a class="blue infos" title="' . __('app.information') . '" href="#" id="' . $service->id . '"><i class="ace-icon fa fa-info-circle bigger-130"></i></a></div>' . $stylem . '<a href="#" class="ban-service" id="' . $service->id . '" title="' . __('app.activate') . ' ' . __('app.service') . '"><span class="blue"><i class="ace-icon fa fa-adjust bigger-120"></i></span></a></li><li><a href="#" class="blue infos" id="' . $service->id . '" title="información"><span class="blue"><i class="ace-icon fa fa-info-circle bigger-120"></i></span><li><a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="' . $row->id . '" title="' . __('app.edit') . '"><span class="success"><i class="ace-icon fa fa-pencil bigger-120"></i></span></a></li><li><a class="red del" href="#" id="' . $row->id . '" title="' . __('app.remove') . '"><span class="red"><i class="ace-icon fa fa-trash-o bigger-120"></i></span></a>' . $stylee . '</br>';
                        }
                    }
                } else {
                    $actions .= $styleb . $editar . $eliminar . '</div>' . $stylem . '<li><a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="' . $row->id . '" title="' . __('app.edit') . '"><span class="success"><i class="ace-icon fa fa-pencil bigger-120"></i></span></a></li><li><a class="red del" href="#" id="' . $row->id . '" title="' . __('app.remove') . '"><span class="red"><i class="ace-icon fa fa-trash-o bigger-120"></i></span></a>' . $stylee;
                }
                return $actions;

            })
            ->editColumn('name', function ($row) {
                return '<a href="' . route('billing', $row->id) . '">' . $row->name . '</a>';
            })
            ->editColumn('online', function ($row) {
                if ($row->online == 'on')
                    return '<span class="label label-success">' . __('app.Online') . '</span>';
                if ($row->online == 'off')
                    return '<span class="label label-danger">' . __('app.disconnected') . '</span>';
                if ($row->online == 'ver')
                    return '<span class="label label-warning">' . __('app.verifying') . '</span>';

            })
            ->editColumn('tp', function ($row) {
                $tp = '';
                foreach ($row->service as $service) {
                    if ($service->router->control_router->type_control == 'ho') {
                        $tp .= '<p><span class="label label-purple">Hotspot</span></p>';
                    }

                    if ($service->router->control_router->type_control == 'ha') {
                        $tp .= '<p><span class="label label-purple">Hotspot - PCQ</span></p>';
                    }

                    if ($service->router->control_router->type_control == 'sq') {
                        $tp .= '<p><span class="label label-success">Simple Queues</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'pp') {
                        $tp .= '<p><span class="label label-yellow">PPPoE</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'pa') {
                        $tp .= '<p><span class="label label-yellow">PPPoE - PCQ</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'nc') {
                        $tp .= '<p><span class="label label-grey">Sin conexión</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'pc') {
                        $tp .= '<p><span class="label label-warning">PCQ</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'st') {
                        $tp .= '<p><span class="label label-success">Simple Queues (with Tree)</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'pt') {
                        $tp .= '<p><span class="label label-yellow">PPPoE - Simple Queues (with Tree)</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'dl') {
                        $tp .= '<p><span class="label label-default">DHCP Leases</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'ps') {
                        $tp .= '<p><span class="label label-yellow">PPPoE - Simple Queues</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'ra') {
                        $tp .= '<p><span class="label label-yellow">PPPoE - Simple Queues</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'rp') {
                        $tp .= '<p><span class="label label-yellow">PPPoE - Secrets - PCQ Address List with Radius</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'rr') {
                        $tp .= '<p><span class="label label-yellow">PPPoE - Simple Queues with Radius</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'no') {
                        $tp .= '<p><span class="label label-default">' . __('app.none') . '</span></p>';
                    }
                }

                if ($tp == '') {
                    $tp = '---';
                }
                return $tp;

            })
            ->editColumn('plan_name', function ($row) {
                $planName = '';
                foreach ($row->service as $service) {
                    if ($service->plan->plan_name == 'Importados')
                        $planName .= '<p><span class="text-danger">' . $service->plan->name . '</span></p>';
                    else
                        $planName .= '<p>' . $service->plan->name . '</p>';
                }

                if ($planName == '') {
                    $planName = '---';
                }
                return $planName;
            })
            ->editColumn('status', function ($row) {
                $status = '';
                foreach ($row->service as $service) {
                    if ($service->status == 'ac')
                        $status .= '<p><span class="label label-success arrowed">' . __('app.active') . '</span></p>';
                    else
                        $status .= '<p><span class="label label-danger">' . __('app.blocked') . '</span></p>';
                }

                if ($status == '') {
                    $status = '---';
                }

                return $status;
            })
            ->editColumn('expiration', function ($row) {
                $expiration = '';
                foreach ($row->service as $service) {
                    $expiration .= '<p>' . $service->suspend_client->expiration->format("Y-m-d") . '</p>';
                }


                if ($expiration == '') {
                    $expiration = '---';
                }

                return $expiration;
            })
            ->editColumn('ip', function ($row) {
                $ip = '';
                foreach ($row->service as $service) {
                    $ip .= '<p>' . $service->ip . '</p>';
                }

                if ($ip == '') {
                    $ip = '---';
                }
                return $ip;
            })
            ->editColumn('mac', function ($row) {
                $ip = '';
                foreach ($row->service as $service) {
                    $ip .= '<p>' . $service->mac . '</p>';
                }

                if ($ip == '') {
                    $ip = '---';
                }
                return $ip;
            })
            ->editColumn('router', function ($row) {
                $router = '';
                foreach ($row->service as $service) {
                    $router .= '<p>' . $service->router->name . '</p>';
                }

                if ($router == '') {
                    $router = '---';
                }

                return $router;
            })
            ->addColumn('cut', function ($row) {
                $expiration = '';
                foreach ($row->service as $service) {
                    if (($row->billing_grace_period != 0) || ($this->global->tolerance != 0)) {
                        $t_diass = $row->billing_grace_period + $this->global->tolerance;
                        $expiration .= '<p>' . Carbon::parse($service->suspend_client->expiration)->addDays($t_diass)->format('d/m/Y H:i:s') . '</p>';
                    } else {
                        $expiration .= '<p>' . Carbon::parse($service->suspend_client->expiration)->format('d/m/Y H:i:s') . '</p>';
                    }
                }

                if ($expiration == '') {
                    $expiration = '---';
                }

                return $expiration;
            })
            ->rawColumns(['action', 'name', 'online', 'tp', 'plan_name', 'status', 'zone', 'odb_id', 'onu_id', 'onusn', 'cut', 'expiration', 'router', 'mac', 'ip'])
            ->make(true);

        /*$clients = DB::table('clients')
        ->join('plans', 'plans.id', '=', 'clients.plan_id')
        ->join('routers', 'routers.id', '=', 'clients.router_id')
        ->join('control_routers', 'routers.id', '=', 'control_routers.router_id')
        ->select('clients.id', 'clients.name', 'clients.ip', 'clients.mac', 'routers.name As router',
            'plans.name As plan_name', 'plans.cost', 'clients.status', 'control_routers.type_control As tp',
            'clients.plan_id', 'clients.router_id', 'clients.online','clients.balance')->orderBy('clients.name')->get();

        foreach ($clients as $client) {
            $info_add = $this->postInfo_data($client->id);
            $client->paydate=$info_add['paydate'];
            $client->cut=$info_add['cut'];
        }

        return Response::json($clients);*/
    }

    //metodo para listar clientes
    public function postList2(Request $request)
    {
        $users = Client::join('client_services', 'client_services.client_id', '=', 'clients.id')
            ->leftJoin('odb_splitter', 'odb_splitter.id', '=', 'clients.odb_id')
            ->leftJoin('zone', 'zone.id', '=', 'clients.zona_id')
            ->leftJoin('onu_type', 'onu_type.id', '=', 'clients.onu_id')
            ->join('billing_settings', 'billing_settings.client_id', '=', 'clients.id')
            ->whereHas('service', function (Builder $query) {
                $query->where('status', 'de');
            })->with(['service' => function ($query) {
                return $query->where('status', 'de');
            }, 'service.router', 'service.router.control_router', 'service.plan']);

        if ($request->control != 'all') {
            $users = $users->join('routers', 'routers.id', '=', 'clients.router_id')
                ->join('control_routers', 'routers.id', '=', 'control_routers.router_id')
                ->where('control_routers.type_control', $request->control);
        }

        if ($request->online != 'all') {
            $users = $users->where('client_services.online', $request->online);
        }

        $users = $users->select('clients.id', 'clients.name',
            'client_services.online', 'clients.balance', 'billing_settings.billing_grace_period', 'zone.name as zone', 'odb_splitter.name as odb_id', 'onu_type.onutype as onu_id', 'clients.onusn')
            ->groupBy('clients.id');


        return DataTables::of($users)
            ->addColumn('action', function ($row) {
                $actions = '';

                $eliminar = '';
                $editar = '';

                $styleb = '<div class="hidden-sm hidden-xs action-buttons">';
                $stylem = '<div class="hidden-md hidden-lg"><div class="inline position-relative"><button class="btn btn-minier btn-yellow dropdown-toggle" data-toggle="dropdown" data-position="auto"><i class="ace-icon fa fa-caret-down icon-only bigger-120"></i></button><ul class="dropdown-menu dropdown-only-icon dropdown-yellow dropdown-menu-right dropdown-caret dropdown-close"><li>';
                $stylee = '</li></ul></div></div>';
                if (PermissionsController::hasAnyRole('access_clients_editar'))
                    $editar .= '<a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="' . $row->id . '" title="' . __('app.edit') . '"><i class="ace-icon fa fa-pencil bigger-130"></i></a>';

                if (PermissionsController::hasAnyRole('access_clients_eliminar'))
                    $eliminar = '<a class="red del" href="#" id="' . $row->id . '" title="' . __('app.remove') . '"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>';

                if ($row->service->count() > 0) {
                    foreach ($row->service as $service) {
                        if ($service->status == 'ac') {
                            $actions .= $styleb . '<a class="blue ban-service" href="#" id="' . $service->id . '" title="' . __('app.serviceCut') . '" xmlns="http://www.w3.org/1999/html"><i class="ace-icon fa fa-adjust bigger-130"></i></a>' . $editar . $eliminar . '<a class="grey tool" title="' . __('app.tools') . '" href="#" id="' . $service->id . '"><i class="ace-icon fa fa-wrench bigger-130"></i></a><a class="blue infos" title="' . __('app.information') . '" href="#" id="' . $service->id . '"><i class="ace-icon fa fa-info-circle bigger-130"></i></a></div>' . $stylem . '<a href="#" class="ban-service" id="' . $service->id . '" title="' . __('app.cut') . '"><span class="blue"><i class="ace-icon fa fa-adjust bigger-120"></i></span></a></li><li><a href="#" class="blue infos" id="' . $service->id . '" title="información"><span class="blue"><i class="ace-icon fa fa-info-circle bigger-120"></i></span></a><li><a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="' . $row->id . '" title="' . __('app.edit') . '"><span class="success"><i class="ace-icon fa fa-pencil bigger-120"></i></span></a></li><li><a href="#" class="grey tool" id="' . $row->id . '" title="' . __('app.tools') . '"><span class="default"><i class="ace-icon fa fa-wrench bigger-120"></i></span></a></li><li><a class="red del" href="#" id="' . $row->id . '" title="' . __('app.remove') . '"><span class="red"><i class="ace-icon fa fa-trash-o bigger-120"></i></span></a>' . $stylee . '</br>';
                        }
                        if ($service->status == 'de') {
                            $actions .= $styleb . '<a class="blue ban-service" href="#" id="' . $service->id . '" title="' . __('app.activate') . ' ' . __('app.service') . '"><i class="ace-icon fa fa-adjust bigger-130"></i></a>' . $eliminar . '<a class="blue infos" title="' . __('app.information') . '" href="#" id="' . $service->id . '"><i class="ace-icon fa fa-info-circle bigger-130"></i></a></div>' . $stylem . '<a href="#" class="ban-service" id="' . $service->id . '" title="' . __('app.activate') . ' ' . __('app.service') . '"><span class="blue"><i class="ace-icon fa fa-adjust bigger-120"></i></span></a></li><li><a href="#" class="blue infos" id="' . $service->id . '" title="información"><span class="blue"><i class="ace-icon fa fa-info-circle bigger-120"></i></span><li><a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="' . $row->id . '" title="' . __('app.edit') . '"><span class="success"><i class="ace-icon fa fa-pencil bigger-120"></i></span></a></li><li><a class="red del" href="#" id="' . $row->id . '" title="' . __('app.remove') . '"><span class="red"><i class="ace-icon fa fa-trash-o bigger-120"></i></span></a>' . $stylee . '</br>';
                        }
                    }
                } else {
                    $actions .= $styleb . $editar . $eliminar . '</div>' . $stylem . '<li><a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="' . $row->id . '" title="' . __('app.edit') . '"><span class="success"><i class="ace-icon fa fa-pencil bigger-120"></i></span></a></li><li><a class="red del" href="#" id="' . $row->id . '" title="' . __('app.remove') . '"><span class="red"><i class="ace-icon fa fa-trash-o bigger-120"></i></span></a>' . $stylee;
                }
                return $actions;

            })
            ->editColumn('name', function ($row) {
                return '<a href="' . route('billing', $row->id) . '">' . $row->name . '</a>';
            })
            ->editColumn('online', function ($row) {

                $tp = '';
                foreach ($row->service as $service) {
                    if ($service->online == 'on') {
                        $tp .= '<p><span class="label label-success">' . __('app.Online') . '</span></p>';
                    }
                    if ($service->online == 'off') {
                        $tp .= '<p><span class="label label-danger">' . __('app.disconnected') . '</span></p>';
                    }
                    if ($service->online == 'ver') {
                        $tp .= '<p><span class="label label-warning">' . __('app.verifying') . '</span></p>';
                    }
                }

                if ($tp == '') {
                    $tp = '---';
                }
                return $tp;
            })
            ->editColumn('tp', function ($row) {
                $tp = '';
                foreach ($row->service as $service) {
                    if ($service->router->control_router->type_control == 'ho') {
                        $tp .= '<p><span class="label label-purple">Hotspot</span></p>';
                    }

                    if ($service->router->control_router->type_control == 'ha') {
                        $tp .= '<p><span class="label label-purple">Hotspot - PCQ</span></p>';
                    }

                    if ($service->router->control_router->type_control == 'sq') {
                        $tp .= '<p><span class="label label-success">Simple Queues</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'pp') {
                        $tp .= '<p><span class="label label-yellow">PPPoE</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'pa') {
                        $tp .= '<p><span class="label label-yellow">PPPoE - PCQ</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'nc') {
                        $tp .= '<p><span class="label label-grey">Sin conexión</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'pc') {
                        $tp .= '<p><span class="label label-warning">PCQ</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'st') {
                        $tp .= '<p><span class="label label-success">Simple Queues (with Tree)</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'pt') {
                        $tp .= '<p><span class="label label-yellow">PPPoE - Simple Queues (with Tree)</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'dl') {
                        $tp .= '<p><span class="label label-default">DHCP Leases</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'ps') {
                        $tp .= '<p><span class="label label-yellow">PPPoE - Simple Queues</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'no') {
                        $tp .= '<p><span class="label label-default">' . __('app.none') . '</span></p>';
                    }
                }

                if ($tp == '') {
                    $tp = '---';
                }
                return $tp;

            })
            ->editColumn('plan_name', function ($row) {
                $planName = '';
                foreach ($row->service as $service) {
                    if ($service->plan->name == 'Importados')
                        $planName .= '<p><span class="text-danger">' . $service->plan->name . '</span></p>';
                    else
                        $planName .= '<p>' . $service->plan->name . '</p>';
                }

                if ($planName == '') {
                    $planName = '---';
                }
                return $planName;
            })
            ->editColumn('status', function ($row) {
                $status = '';
                foreach ($row->service as $service) {
                    if ($service->status == 'ac')
                        $status .= '<p><span class="label label-success arrowed">' . __('app.active') . '</span></p>';
                    else
                        $status .= '<p><span class="label label-danger">' . __('app.blocked') . '</span></p>';
                }

                if ($status == '') {
                    $status = '---';
                }

                return $status;
            })
            ->editColumn('expiration', function ($row) {
                $expiration = '';
                foreach ($row->service as $service) {
                    $expiration .= '<p>' . $service->suspend_client->expiration->format("Y-m-d") . '</p>';
                }


                if ($expiration == '') {
                    $expiration = '---';
                }

                return $expiration;
            })
            ->editColumn('ip', function ($row) {
                $ip = '';
                foreach ($row->service as $service) {
                    $ip .= '<p>' . $service->ip . '</p>';
                }

                if ($ip == '') {
                    $ip = '---';
                }
                return $ip;
            })
            ->editColumn('mac', function ($row) {
                $ip = '';
                foreach ($row->service as $service) {
                    $ip .= '<p>' . $service->mac . '</p>';
                }

                if ($ip == '') {
                    $ip = '---';
                }
                return $ip;
            })
            ->editColumn('router', function ($row) {
                $router = '';
                foreach ($row->service as $service) {
                    $router .= '<p>' . $service->router->name . '</p>';
                }

                if ($router == '') {
                    $router = '---';
                }

                return $router;
            })
            ->addColumn('cut', function ($row) {
                $expiration = '';
                foreach ($row->service as $service) {
                    if (($row->billing_grace_period != 0) || ($this->global->tolerance != 0)) {
                        $t_diass = $row->billing_grace_period + $this->global->tolerance;
                        $expiration .= '<p>' . Carbon::parse($service->suspend_client->expiration)->addDays($t_diass)->format('d/m/Y H:i:s') . '</p>';
                    } else {
                        $expiration .= '<p>' . Carbon::parse($service->suspend_client->expiration)->format('d/m/Y H:i:s') . '</p>';
                    }
                }

                if ($expiration == '') {
                    $expiration = '---';
                }

                return $expiration;
            })
            ->rawColumns(['action', 'name', 'online', 'tp', 'plan_name', 'status', 'zone', 'odb_id', 'onu_id', 'onusn', 'cut', 'expiration', 'router', 'ip', 'mac'])
            ->make(true);
    }

    //metodo para agregar clientes
    public function postCreate(Request $request)
    {

        $process = new Chkerr();
        $friendly_names = array(
            'name' => __('app.name'),
            'phone' => __('app.telephone'),
            'dni' => 'DNI/CI',
            'date_pay' => __('app.paymentDate'),
            'ip' => 'IP ' . __('app.client'),
            'plan' => __('plan'),
            'mac' => 'MAC',
            'user_hot' => __('app.username') . ' hotspot/pppoe',
            'pass_hot' => __('app.password') . ' hotspot/pppoe',
            'pass' => __('app.password') . ' portal',
        );
        //validamos reglas inputs
        $rules = array(
            'name' => 'required|max:100|string',
            'phone' => 'unique:clients',
            'email' => 'email|unique:clients',
            'dni' => 'required|unique:clients',
            'date_pay' => 'required|date',
            'ip' => 'required|ip|unique:clients,ip',
            'plan' => 'required',
            'user_hot' => 'unique:clients',
            'pass_hot' => '',
            'pass' => 'min:3',
        );

        ///////////////// Control de datos /////////////////////////////////////////////////////////////////////////
        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);

        if ($validation->fails()) {
            return Response::json(array(array('msg' => 'error', 'errors' => $validation->getMessageBag()->toArray())));
        }


        $global = GlobalSetting::all()->first();
        if ($global->license_id == '0') {
            return Response::json(array(array('msg' => 'licenciaactivate')));
        } else {
            $data_licencia = SecurityController::status_licencia($global->license_id);
            if ($data_licencia['status'] == 200) {
                if ($data_licencia['license'] == 'valid') {

                    if (!$data_licencia['status_reg_cli']) {
                        return Response::json(array(array('msg' => 'limitelicenciasup')));
                    }

                } else {
                    return Response::json(array(array('msg' => 'licenciaexpirada')));
                }
            } else {
                return Response::json(array(array('msg' => 'errorlicencia')));
            }
        }


        $router_id = $request->get('router');
        //verificamos si esta seleccionado el router
        if ($router_id == 'none') {
            return Response::json(array('msg' => 'errorslcrouter'));
        }

        //obtenemos la configuracion del router typo de autenticacion
        $config = ControlRouter::where('router_id', '=', $router_id)->get();
        $typeconf = $config[0]->type_control;
        $advs = $config[0]->adv;
        $dhcp = $config[0]->dhcp;
        $arp = $config[0]->arpmac;

        $macClient = $request->get('mac');
        $user = '';
        $pass = '';

        if ($typeconf == 'sq' || $typeconf == 'st' || $typeconf == 'no' || $typeconf == 'pc') {
            if ($arp == 1 || $dhcp == 1) {
                $macClient = $request->get('mac');
                if (empty($macClient)) {
                    return $process->show('reqmac');
                }
            } else {
                $macClient = empty($request->get('mac')) ? '00:00:00:00:00:00' : $request->get('mac');
            }

            $user = '';
            $pass = '';
        }

        if ($typeconf == 'ho' || $typeconf == 'pp' || $typeconf == 'pa' || $typeconf == 'ha' || $typeconf == 'ps' || $typeconf == 'pt') {
            if ($arp == 1 || $dhcp == 1) {
                $macClient = $request->get('mac');
                if (empty($macClient)) {
                    return $process->show('reqmac');
                }
            } else {
                $macClient = empty($request->get('mac')) ? '00:00:00:00:00:00' : $request->get('mac');
            }

            if (empty($request->get('auth'))) {

                $user = '';
                $pass = '';
            } elseif ($request->get('auth') == 'binding') {
                $user = $request->get('user_hot');
                $pass = '';
            } elseif ($request->get('auth') == 'userpass') {
                $user = $request->get('user_hot');
                $pass = $request->get('pass_hot');
            } else {
                $user = '';
                $pass = '';
            }

        }

        //si es dhcp leases
        if ($typeconf == 'dl') {
            $macClient = $request->get('mac');
            if (empty($macClient)) {
                return $process->show('reqmac');
            }
            $user = '';
            $pass = '';
        }

        /// Fin de control de datos ////////////////////////////////////////////////////////////////

        //Recuperamos y preparamos todos los datos ////////////////////////////////////////////////

        $billing_date = $request->get('billing_date');
        \Session::put('billing_date', $billing_date);

        //recuperamos información del cliente
        $regpay = $request->get('regpay', 0);
        $nameClient = $request->get('name');

        $typedoc_cod = $request->get('typedoc_cod', '0');
        $economicactivity_cod = $request->get('economicactivity_cod', '0');
        $municipio_cod = $request->get('municipio_cod', '0');
        $typeresponsibility_cod = $request->get('typeresponsibility_cod', '0');
        $typetaxpayer_cod = $request->get('typetaxpayer_cod', 'ZZ');

        $pay_date = $request->get('date_pay');
        $pay_date = date("Y-m-d", strtotime($pay_date));

        $date_in = $request->get('date_in');
        $date_in = date("Y-m-d", strtotime($date_in));
        //recuperamos informacion del plan
        $plan_id = $request->get('plan');
        $pl = new GetPlan();
        $plan = $pl->get($plan_id);
        $planCost = $plan['cost'];
        $iva = $plan['iva'];
        $namePlan = $plan['name'];
        $maxlimit = $plan['maxlimit'];
        $comment = 'SmartISP - ' . $namePlan;
        //opcion avanzada burst del plan
        $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

        $num_cli = Client::where('plan_id', '=', $plan_id)->where('status', 'ac')->where('router_id', $router_id)->count(); //for pcq queue tree

        $router = new RouterConnect();
        $con = $router->get_connect($router_id);
        $authUsername = auth()->user()->username;
        $authUserId = auth()->user()->id;
        //preparando datos adicionales
        $data = array(
            //general data
            'name' => $nameClient,
            'typedoc_cod' => $typedoc_cod,
            'economicactivity_cod' => $economicactivity_cod,
            'municipio_cod' => $municipio_cod,
            'typeresponsibility_cod' => $typeresponsibility_cod,
            'typetaxpayer_cod' => $typetaxpayer_cod,
            'address' => $request->get('ip'),
            'mac' => $macClient,
            'arp' => $arp,
            'adv' => $advs,
            'dhcp' => $dhcp,
            'drop' => 0,
            'lan' => $con['lan'],
            'maxlimit' => $maxlimit,
            'comment' => $comment,
            'billing_type' => $request->get('billing_type'),
            //advanced
            'bl' => $burst['blu'] . '/' . $burst['bld'],
            'bth' => $burst['btu'] . '/' . $burst['btd'],
            'bt' => $plan['burst_time'] . '/' . $plan['burst_time'],
            'priority' => $plan['priority'] . '/' . $plan['priority'],
            'priority_a' => $plan['priority'],
            'limit_at' => $burst['lim_at_up'] . '/' . $burst['lim_at_down'],
            //advanced for pcq
            'num_cl' => $num_cli,
            'rate_down' => $plan['download'] . 'k',
            'rate_up' => $plan['upload'] . 'k',
            'burst_rate_down' => $burst['bld'],
            'burst_rate_up' => $burst['blu'],
            'burst_threshold_down' => $burst['btd'],
            'burst_threshold_up' => $burst['btu'],
            'limit_at_down' => $burst['lim_at_down'],
            'limit_at_up' => $burst['lim_at_up'],
            'burst_time' => $plan['burst_time'],
            //data for simple queue tree
            'download' => $plan['download'],
            'upload' => $plan['upload'],
            'aggregation' => $plan['aggregation'],
            'limitat' => $plan['limitat'],
            'burst_limit' => $plan['burst_limit'],
            'burst_threshold' => $plan['burst_threshold'],
            //other data
            'date_in' => $date_in,
            'plan_id' => $plan_id,
            'namePlan' => $namePlan,
            '`router_id`' => $router_id,
            'email' => $request->get('email'),
            'phone' => $request->get('phone'),
            'dir' => $request->get('dir'),
            'loc' => $request->get('location', '0'),
            'dni' => $request->get('dni'),
            'typeauth' => $request->get('auth'),
            'user' => $user,
            'pass' => $pass,
            'odb_id' => $request->get('odb_id'),
            'onu_id' => $request->get('onu_id'),
            'port' => $request->get('port'),
            'onusn' => $request->get('onusn'),
            'zona_id' => $request->get('zona_id'),
            'passportal' => $request->get('pass'),
            'profile' => $request->get('profile', false),
	        'no_rules' => $plan['no_rules'],
        );

        $Nclient = new AddClient();
        $counter = new CountClient();
        $usedip = new StatusIp();
        $expclient = new Sclient();
        $log = new Slog();

        $global = GlobalSetting::all()->first();
        $debug = $global->debug;

        //verificamos el modo de conexion del router

        if ($con['connect'] == 1) {
            # Sin api mikrotik
            // agregamos a la BD
            $id = $Nclient->add($data);
            //verificamos si esta activo la opcion de agregar pago
            if ($regpay) {
                $regp = new RegPay();
                $dp = new GetDate();
                $df = $dp->get_date(1, 0);
                $regp->add($id, $pay_date, $planCost, $iva, $plan_id, $router_id, 1, $df);
                //save log
	            CommonService::log("Se ha registrado un pago del cliente:", $nameClient, 'info' , $authUserId);
            }
            //marcamos como ocupada la ip
            $usedip->is_used_ip($request->get('ip'), $id, true);
            // aumentamos el contador de numero de clientes del router
            $counter->step_up_router($router_id);
            // aumentamos el contador del plan
            $counter->step_up_plan($plan_id);

//            $expclient->exp($id, $router_id, $pay_date);
            //save log
	        CommonService::log("Se ha registrado un cliente:$nameClient", $this->username, 'info' , $authUserId);
            //mostramos mensaje de confirmación
            return $process->show('success');
        } else {

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            //inicializacion de clases principales
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];
            //inicializamos el nucleo del sistema
            $rocket = new RocketCore();

            if ($typeconf == 'no') {
                # Sin control de trafico solo dhcp, arp o adv
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $error = new Mkerror();

                    $SB = $rocket->set_basic_config($API, $error, $data, $request->get('ip'), null, 'add', $debug);

                    if ($debug == 1) {
                        if ($SB != false) {
                            return $SB;
                        }

                    }

                    // agregamos a la BD
                    $id = $Nclient->add($data);
                    //verificamos si esta activo la opcion de agregar pago
                    if ($regpay) {
                        $regp = new RegPay();
                        $dp = new GetDate();
                        $df = $dp->get_date(1, 0);
                        $regp->add($id, $pay_date, $planCost, $iva, $plan_id, $router_id, 1, $df);
                        //save log
	                    CommonService::log("Se ha registrado un pago del cliente:", $nameClient, 'success' , $authUserId);

                    }
                    //marcamos como ocupada la ip
                    $usedip->is_used_ip($request->get('ip'), $id, true);
                    // aumentamos el contador de numero de clientes del router
                    $counter->step_up_router($router_id);
                    // aumentamos el contador del plan
                    $counter->step_up_plan($plan_id);
//                    $expclient->exp($id, $router_id, $pay_date);
                    //Desconectamos la API MIKROTIK
                    $API->disconnect();
                    //save log
	                CommonService::log("Se ha registrado un cliente: $nameClient", $this->username, 'success' , $authUserId);
                    //mostramos mensaje de confirmación
                    return $process->show('success');

                } else {
                    return $process->show('errorConnect');
                }

            }

            if ($typeconf == 'sq') { //añadimos a simple queues

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $SQUEUES = $rocket->add_simple_queues($API, $data, $request->get('ip'), $debug);

                    if ($debug == 1) {
                        //control de y procesamiento de errores
                        if ($process->check($SQUEUES)) {
                            return $process->check($SQUEUES);
                        }

                        // agregamos a la BD
                        $id = $Nclient->add($data);
                        //verificamos si esta activo la opcion de agregar pago
                        if ($regpay) {
                            $regp = new RegPay();
                            $dp = new GetDate();
                            $df = $dp->get_date(1, 0);
                            $regp->add($id, $pay_date, $planCost, $iva, $plan_id, $router_id, 1, $df);
                            //save log
	                        CommonService::log("Se ha registrado un pago del cliente:", $nameClient, 'success' , $authUserId);
                        }
                        //marcamos como ocupada la ip
                        $usedip->is_used_ip($request->get('ip'), $id, true);
                        // aumentamos el contador de numero de clientes del router
                        $counter->step_up_router($router_id);
                        // aumentamos el contador del plan
                        $counter->step_up_plan($plan_id);
                        $expclient->exp($id, $router_id, $pay_date);
                        //Desconectamos la API MIKROTIK
                        $API->disconnect();
                        //save log
	                    CommonService::log("Se ha registrado un cliente: $nameClient", $this->username, 'success' , $authUserId);
                        //mostramos mensaje de confirmación
                        return $process->show('success');
                    } else {
                        return $process->show('errorConnect');
                    }

                }// fin de simple queues
            }
            if ($typeconf == 'st') { //añadimos a simple queues (with Tree)

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    // agregamos a la BD
                    $id = $Nclient->add($data);
                    //verificamos si esta activo la opcion de agregar pago
                    if ($regpay) {
                        $regp = new RegPay();
                        $dp = new GetDate();
                        $df = $dp->get_date(1, 0);
                        $regp->add($id, $pay_date, $planCost, $iva, $plan_id, $router_id, 1, $df);
                        //save log
	                    CommonService::log("Se ha registrado un pago del cliente:", $nameClient, 'success' , $authUserId);
                    }
                    //marcamos como ocupada la ip
                    $usedip->is_used_ip($request->get('ip'), $id, true);
                    // aumentamos el contador de numero de clientes del router
                    $counter->step_up_router($router_id);
                    // aumentamos el contador del plan
                    $counter->step_up_plan($plan_id);
//					$expclient->exp($id,$router_id,$pay_date);

                    $SQUEUES = $rocket->add_simple_queue_with_tree($API, $data, $request->get('ip'), 'add', $debug);

                    if ($debug == 1) {
                        //control de y procesamiento de errores
                        if ($process->check($SQUEUES))
                            return $process->check($SQUEUES);
                    }

                    //Desconectamos la API MIKROTIK
                    $API->disconnect();

                    //save log
	                CommonService::log("Se ha registrado un cliente: $nameClient", $this->username, 'success' , $authUserId);
                    //mostramos mensaje de confirmación
                    return $process->show('success');
                } else {
                    return $process->show('errorConnect');
                }

            }// fin de simple queues (with Tree)


            if ($typeconf == 'ho') { //hotspot users profiles

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $HOTSPOT = $rocket->add_user_hotspot($API, $data, $request->get('ip'), $debug);

                    if ($debug == 1) {
                        //control y procesamiento de errores
                        if ($process->check($HOTSPOT)) {
                            return $process->check($HOTSPOT);
                        }
                    }

                    // agregamos a la BD
                    $id = $Nclient->add($data);
                    //verificamos si esta activo la opcion de agregar pago
                    if ($regpay) {
                        $regp = new RegPay();
                        $dp = new GetDate();
                        $df = $dp->get_date(1, 0);
                        $regp->add($id, $pay_date, $planCost, $iva, $plan_id, $router_id, 1, $df);

	                    CommonService::log("Se ha registrado un pago del cliente:", $nameClient, 'success' , $authUserId);
                    }
                    //marcamos como ocupada la ip
                    $usedip->is_used_ip($request->get('ip'), $id, true);
                    // aumentamos el contador de numero de clientes del router
                    $counter->step_up_router($router_id);
                    $counter->step_up_plan($plan_id);
                    // añadimos a la suspención de clientes
//                    $expclient->exp($id, $router_id, $pay_date);
                    //Desconectamos la API MIKROTIK
                    $API->disconnect();
                    //save log
	                CommonService::log("Se ha registrado un cliente: $nameClient", $this->username, 'success' , $authUserId);
                    //mostramos mensaje de confirmación
                    return $process->show('success');
                } else {
                    return $process->show('errorConnect');
                }

            } //fin de hotspot users profiles

            if ($typeconf == 'ha') { //hotspot pcq

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $HOTSPOT = $rocket->add_user_hotspot_pcq($API, $data, $request->get('ip'), $debug);

                    if ($debug == 1) {
                        if ($process->check($HOTSPOT)) {
                            return $process->check($HOTSPOT);
                        }
                    }

                    // agregamos a la BD
                    $id = $Nclient->add($data);
                    //verificamos si esta activo la opcion de agregar pago
                    if ($regpay) {
                        $regp = new RegPay();
                        $dp = new GetDate();
                        $df = $dp->get_date(1, 0);
                        $regp->add($id, $pay_date, $planCost, $iva, $plan_id, $router_id, 1, $df);

	                    CommonService::log("Se ha registrado un pago del cliente:", $nameClient, 'success' , $authUserId);
                    }
                    //marcamos como ocupada la ip
                    $usedip->is_used_ip($request->get('ip'), $id, true);
                    // aumentamos el contador de numero de clientes del router
                    $counter->step_up_router($router_id);
                    $counter->step_up_plan($plan_id);
                    // añadimos a la suspención de clientes
//                    $expclient->exp($id, $router_id, $pay_date);
                    //Desconectamos la API MIKROTIK
                    $API->disconnect();
                    //save log
	                CommonService::log("Se ha registrado un cliente: $nameClient", $this->username, 'success' , $authUserId);
                    //mostramos mensaje de confirmación
                    return $process->show('success');

                } else {
                    return $process->show('errorConnect');
                }

            }

            if ($typeconf == 'dl') { //DHCP Leases

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $DHCP = $rocket->add_dhcp_leases($API, $data, $request->get('ip'), $debug);

                    if ($debug == 1) {
                        //control y procesamiento de errores
                        if ($process->check($DHCP)) {
                            return $process->check($DHCP);
                        }
                    }

                    // agregamos a la BD
                    $id = $Nclient->add($data);
                    //verificamos si esta activo la opcion de agregar pago
                    if ($regpay) {
                        $regp = new RegPay();
                        $dp = new GetDate();
                        $df = $dp->get_date(1, 0);
                        $regp->add($id, $pay_date, $planCost, $iva, $plan_id, $router_id, 1, $df);

	                    CommonService::log("Se ha registrado un pago del cliente:", $nameClient, 'success' , $authUserId);
                    }
                    //marcamos como ocupada la ip
                    $usedip->is_used_ip($request->get('ip'), $id, true);
                    // aumentamos el contador de numero de clientes del router
                    $counter->step_up_router($router_id);
                    $counter->step_up_plan($plan_id);
                    // añadimos a la suspención de clientes
//                    $expclient->exp($id, $router_id, $pay_date);
                    //Desconectamos la API MIKROTIK
                    $API->disconnect();
                    //save log
	                CommonService::log("Se ha registrado un cliente: $nameClient", $this->username, 'success' , $authUserId);
                    //mostramos mensaje de confirmación
                    return $process->show('success');
                } else {
                    return $process->show('errorConnect');
                }

            } //fin de DHCP Leases


            if ($typeconf == 'ps') { //PPP secrets Simple Queues

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    //get gateway for addres
                    $network = Network::where('ip', $request->get('ip'))->get();
                    $gat = AddressRouter::find($network[0]->address_id);

                    $PPP = $rocket->add_ppp_simple($API, $data, $request->get('ip'), $gat->gateway, $debug);

                    if ($debug == 1) {
                        //control y procesamiento de errores
                        if ($process->check($PPP)) {
                            return $process->check($PPP);
                        }
                    }

                    // agregamos a la BD
                    $id = $Nclient->add($data);
                    //verificamos si esta activo la opcion de agregar pago
                    if ($regpay) {
                        $regp = new RegPay();
                        $dp = new GetDate();
                        $df = $dp->get_date(1, 0);
                        $regp->add($id, $pay_date, $planCost, $iva, $plan_id, $router_id, 1, $df);

	                    CommonService::log("Se ha registrado un pago del cliente:", $nameClient, 'success' , $authUserId);
                    }
                    //marcamos como ocupada la ip
                    $usedip->is_used_ip($request->get('ip'), $id, true);
                    // aumentamos el contador de numero de clientes del router
                    $counter->step_up_router($router_id);
                    $counter->step_up_plan($plan_id);
                    // añadimos a la suspención de clientes
                    $expclient->exp($id, $router_id, $pay_date);
                    //Desconectamos la API MIKROTIK
                    $API->disconnect();
                    //save log
	                CommonService::log("Se ha registrado un cliente: $nameClient", $this->username, 'success' , $authUserId);
                    //mostramos mensaje de confirmación
                    return $process->show('success');
                } else {
                    return $process->show('errorConnect');
                }

            }//fin de PPP secrets SimpleQueues

            if ($typeconf == 'pt') { //PPP secrets Simple Queues with tree

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    //get gateway for addres
                    $network = Network::where('ip', $request->get('ip'))->get();
                    $gat = AddressRouter::find($network[0]->address_id);

                    // agregamos a la BD
                    $id = $Nclient->add($data);
                    //verificamos si esta activo la opcion de agregar pago
                    if ($regpay) {
                        $regp = new RegPay();
                        $dp = new GetDate();
                        $df = $dp->get_date(1, 0);
                        $regp->add($id, $pay_date, $planCost, $iva, $plan_id, $router_id, 1, $df);

	                    CommonService::log("Se ha registrado un pago del cliente:", $nameClient, 'success' , $authUserId);
                    }
                    //marcamos como ocupada la ip
                    $usedip->is_used_ip($request->get('ip'), $id, true);
                    // aumentamos el contador de numero de clientes del router
                    $counter->step_up_router($router_id);
                    $counter->step_up_plan($plan_id);
                    // añadimos a la suspención de clientes
                    $expclient->exp($id, $router_id, $pay_date);

                    $PPP = $rocket->add_ppp_simple_queue_with_tree($API, $data, $request->get('ip'), $gat->gateway, 'add', $debug);

                    if ($debug == 1) {
                        //control y procesamiento de errores
                        if ($process->check($PPP)) {
                            return $process->check($PPP);
                        }
                    }


                    //Desconectamos la API MIKROTIK
                    $API->disconnect();
                    //save log
	                CommonService::log("Se ha registrado un cliente: $nameClient", $this->username, 'success' , $authUserId);
                    //mostramos mensaje de confirmación
                    return $process->show('success');
                } else {
                    return $process->show('errorConnect');
                }

            }//fin de PPP secrets SimpleQueues with tree


            if ($typeconf == 'pp') { //PPP secrets

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    //get gateway for addres
                    $network = Network::where('ip', $request->get('ip'))->get();
                    $gat = AddressRouter::find($network[0]->address_id);
                    $PPP = $rocket->add_ppp_secrets($API, $data, $request->get('ip'), $gat->gateway, $debug);

                    if ($debug == 1) {
                        //control y procesamiento de errores
                        if ($process->check($PPP)) {
                            return $process->check($PPP);
                        }
                    }

                    // agregamos a la BD
                    $id = $Nclient->add($data);
                    //verificamos si esta activo la opcion de agregar pago
                    if ($regpay) {
                        $regp = new RegPay();
                        $dp = new GetDate();
                        $df = $dp->get_date(1, 0);
                        $regp->add($id, $pay_date, $planCost, $iva, $plan_id, $router_id, 1, $df);

	                    CommonService::log("Se ha registrado un pago del cliente:", $nameClient, 'success' , $authUserId);
                    }
                    //marcamos como ocupada la ip
                    $usedip->is_used_ip($request->get('ip'), $id, true);
                    // aumentamos el contador de numero de clientes del router
                    $counter->step_up_router($router_id);
                    $counter->step_up_plan($plan_id);
                    // añadimos a la suspención de clientes
//                    $expclient->exp($id, $router_id, $pay_date);
                    //Desconectamos la API MIKROTIK
                    $API->disconnect();
                    //save log
	                CommonService::log("Se ha registrado un cliente: $nameClient", $this->username, 'success' , $authUserId);
                    //mostramos mensaje de confirmación
                    return $process->show('success');
                } else {
                    return $process->show('errorConnect');
                }

            } //fin de PPP secrets

            if ($typeconf == 'pa') { //PPP secrets + PCQ-Address List

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    //get gateway for addres
                    $network = Network::where('ip', $request->get('ip'))->get();
                    $gat = AddressRouter::find($network[0]->address_id);

                    $PPP = $rocket->add_ppp_secrets_pcq($API, $data, $request->get('ip'), $gat->gateway, $debug);

                    if ($debug == 1) {
                        if ($process->check($PPP)) {
                            return $process->check($PPP);
                        }
                    }

                    // agregamos a la BD
                    $id = $Nclient->add($data);
                    //verificamos si esta activo la opcion de agregar pago
                    if ($regpay) {
                        $regp = new RegPay();
                        $dp = new GetDate();
                        $df = $dp->get_date(1, 0);
                        $regp->add($id, $pay_date, $planCost, $iva, $plan_id, $router_id, 1, $df);

	                    CommonService::log("Se ha registrado un pago del cliente:", $nameClient, 'success' , $authUserId);
                    }

                    //marcamos como ocupada la ip
                    $usedip->is_used_ip($request->get('ip'), $id, true);
                    // aumentamos el contador de numero de clientes del router
                    $counter->step_up_router($router_id);
                    $counter->step_up_plan($plan_id);
                    // añadimos a la suspención de clientes
//                    $expclient->exp($id, $router_id, $pay_date);
                    //Desconectamos la API MIKROTIK
                    $API->disconnect();
                    //save log
	                CommonService::log("Se ha registrado un cliente: $nameClient", $this->username, 'success' , $authUserId);
                    //mostramos mensaje de confirmación
                    return $process->show('success');

                } else {
                    return $process->show('errorConnect');
                }

            }

            if ($typeconf == 'pc') { // PCQ-ADDRESS LIST

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    ///////////////////////////////////////////////////////////////
                    $PCQ_ADDRESS = $rocket->add_pcq_list($API, $data, $request->get('ip'), $debug);

                    if ($debug == 1) {
                        if ($process->check($PCQ_ADDRESS)) {
                            return $process->check($PCQ_ADDRESS);
                        }
                    }

                    // agregamos a la BD
                    $id = $Nclient->add($data);
                    //verificamos si esta activo la opcion de agregar pago
                    if ($regpay) {
                        $regp = new RegPay();
                        $dp = new GetDate();
                        $df = $dp->get_date(1, 0);
                        $regp->add($id, $pay_date, $planCost, $iva, $plan_id, $router_id, 1, $df);

	                    CommonService::log("Se ha registrado un pago del cliente:", $nameClient, 'success' , $authUserId);
                    }
                    //marcamos como ocupada la ip
                    $usedip->is_used_ip($request->get('ip'), $id, true);
                    // aumentamos el contador de numero de clientes del router
                    $counter->step_up_router($router_id);
                    $counter->step_up_plan($plan_id);
                    // añadimos a la suspención de clientes
//                    $expclient->exp($id, $router_id, $pay_date);
                    //Desconectamos la API MIKROTIK
                    $API->disconnect();
                    //save log
	                CommonService::log("Se ha registrado un cliente: $nameClient", $this->username, 'success' , $authUserId);
                    //mostramos mensaje de confirmación
                    return $process->show('success');
                    //////////////////////////////////////////////////////////////
                } else {
                    return $process->show('errorConnect');
                }

            } //fin de PCQ-ADDRESS LIST

        } //fin if onmikrotik

    }

    public function postCreateClient(CreateRequest $request)
    {
		$username = auth()->user()->username;
		$userId = auth()->user()->id;
        $en = new Pencrypt();
        $global = GlobalSetting::all()->first();

        if ($global->license_id == '0') {
            return Reply::error(__('messages.PleaseActivateALicense'));
        } else {
            $data_licencia = SecurityController::status_licencia($global->license_id);
            if ($data_licencia['status'] == 200) {
                if ($data_licencia['license'] == 'valid') {

                    if (!$data_licencia['status_reg_cli']) {
                        return Reply::error(__('messages.allowedByYourLicense'));
                    }

                } else {
                    return Reply::error(__('messages.Expiredlicense'));
                }
            } else {
                return Reply::error(__('messages.LicenseError'));
            }
        }

        $client = new Client();
        $client->name = $request->name;
        $client->phone = $request->phone;
        $client->email = $request->email;
//        $client->online = 'ver';
        $client->id_punto_emision = $request->punto_emision;

        if ($request->has('dni') && $request->dni != '') {
            $client->dni = $request->dni;
        }

        if ($request->has('dir') && $request->dir != '') {
            $client->address = $request->dir;
        }

        if ($request->has('odb_id') && $request->odb_id != '') {
            $client->odb_id = $request->odb_id;
        }

        if ($request->has('port') && $request->port != '') {
            $client->port = $request->port;
        }

        if ($request->has('onu_id') && $request->onu_id != '') {
            $client->onu_id = $request->onu_id;
        }

        if ($request->has('zona_id') && $request->zona_id != '') {
            $client->zona_id = $request->zona_id;
        }

        if ($request->has('pass') && $request->pass != '') {
            $client->password = $en->encode($request->pass);
        }

        if ($request->has('location') && $request->location != '') {
            $client->coordinates = $request->location;
        }

        if ($request->has('typedoc_cod') && $request->typedoc_cod != '') {
            $client->typedoc_cod = $request->typedoc_cod;
        }
        if ($request->has('economicactivity_cod') && $request->economicactivity_cod != '') {
            $client->economicactivity_cod = $request->economicactivity_cod;
        }
        if ($request->has('municipio_cod') && $request->municipio_cod != '') {
            $client->municipio_cod = $request->municipio_cod;
        }
        if ($request->has('typeresponsibility_cod') && $request->typeresponsibility_cod != '') {
            $client->typeresponsibility_cod = $request->typeresponsibility_cod;
        }
        if ($request->has('typetaxpayer_cod') && $request->typetaxpayer_cod != '') {
            $client->typetaxpayer_cod = $request->typetaxpayer_cod;
        }

        $client->save();
		$nameClient = $client->name;
	    CommonService::log("Se ha registrado un cliente: $nameClient", $username, 'success' , $userId, $client->id);

        (new BillingSettings())->create([
            'client_id' => $client->id,
            'billing_date' => 1,
            'billing_due_date' => $request->billing_due_date,
        ]);

        return Reply::redirect(route('billing', $client->id), 'Client successfully added');


    }

    public function postUpdateClient(UpdateRequest $request)
    {
        $en = new Pencrypt();
        $global = GlobalSetting::all()->first();

        $username = auth()->user()->username;
        $userId = auth()->user()->id;

        /*if ($global->license_id == '0') {
            return Reply::error(__('messages.PleaseActivateALicense'));
        } else {
            $data_licencia = SecurityController::status_licencia($global->license_id);
            if ($data_licencia['status'] == 200) {
                if ($data_licencia['license'] == 'valid') {

                    if (!$data_licencia['status_reg_cli']) {
                        return Reply::error(__('messages.allowedByYourLicense'));
                    }

                } else {
                    return Reply::error(__('messages.Expiredlicense'));
                }
            } else {
                return Reply::error(__('messages.LicenseError'));
            }
        }*/

        $client_edit = Client::find($request->client_id);
        if ($request->edit_odb_id == '') {
            $request->edit_port = 0;
        }
        $client_edit->odb_id = $request->edit_odb_id;
        $client_edit->port = $request->edit_port;
        $client_edit->save();


        $client = Client::with('service')->find($request->client_id);

        $newName = $request->edit_name;

        $nameChange = false;

        if ($newName != $client->name) {
            $nameChange = true;
        }

        $oldName = $client->name;

        $this->saveClient($client, $request);

        if ($nameChange) {

            $flag = false;

            foreach ($client->service as $service) {
                $router = new RouterConnect();
                $con = $router->get_connect($service->router_id);
                $conf = Helpers::get_api_options('mikrotik');
                // creamos conexion con el router
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                } else {
                    $flag = true;
                }
                $API->disconnect();
            }

            if ($flag) {
                return Response::json(array('msg' => 'errorConnect'));
            }

            event(new UpdateClientEvent($client->id, $oldName));
        }

	    CommonService::log("Una cliente ha actualizado:", $username, 'success' , $userId, $client->id);

        return Reply::success('Client successfully updated');


    }

    //metodo para elimiar clientes
    public function postDelete(Request $request)
    {

        $usedip = new StatusIp();
        $client = Client::with('service')->find($request->id);

        foreach ($client->service as $service) {
            $usedip->is_used_ip($service->ip, 0, false);

            $process = new Chkerr();

            $nameClient = $client->name;
            $target = $service->ip;
            $router_id = $service->router_id;

            $plan = Plan::find($service->plan_id);
            $burst = Burst::get_all_burst($plan->upload, $plan->download, $plan->burst_limit, $plan->burst_threshold, $plan->limitat);

            //recuperamos el tipo de autenticacion del router
            $authType = ControlRouter::where('router_id', '=', $service->router_id)->get();

            $typeconf = $authType[0]->type_control;

            if ($authType[0]->adv == 1) {
                $drop = 0;
            } else {
                $drop = 1;
            }

            //get connection data for login ruter
            $router = new RouterConnect();
            $con = $router->get_connect($router_id);

            $usedip = new StatusIp();
            $counter = new CountClient();
            $log = new Slog();

            //preparando datos
            $data = array(
                'arp' => $authType[0]->arpmac,
                'adv' => $authType[0]->adv,
                'dhcp' => $authType[0]->dhcp,
                'drop' => $drop,
                'user' => $service->user_hot,
                'ip' => $service->ip,
                'name' => $client->name . '_' . $service->id,
                'mac' => $service->mac,
                'typeauth' => $service->typeauth,
                'comment' => 'SmartISP - ' . $plan['name'],
                /////////data for simple queues with tree
                'namePlan' => $plan['name'],
                'plan_id' => $service->plan_id,
                'router_id' => $service->router_id,
                'address' => $service->ip,
                'download' => $plan['download'],
                'upload' => $plan['upload'],
                'maxlimit' => $plan['upload'] . 'k/' . $plan['download'] . 'k',
                'aggregation' => $plan['aggregation'],
                'limitat' => $plan['limitat'],
                'bl' => $burst['blu'] . '/' . $burst['bld'],
                'bth' => $burst['btu'] . '/' . $burst['btd'],
                'bt' => $plan['burst_time'] . '/' . $plan['burst_time'],
                'burst_limit' => $plan['burst_limit'],
                'burst_threshold' => $plan['burst_threshold'],
                'burst_time' => $plan['burst_time'],
                'priority' => $plan['priority'] . '/' . $plan['priority'],
                'tree_priority' => $service->tree_priority,
	            'no_rules' => $plan['no_rules']
            );

            //verificamos si quiere eliminar el cliente de la tabla pero no del router
            if ($plan->name == 'Importados') {
                $counter->step_down_router($router_id);
                $counter->step_down_plan($service->plan_id);
                $service->delete();
//                return $process->show('success');
            }

            if ($typeconf == 'nc') {
                //significa que que no esta activo la conexión con el router
                //eliminamos el usuario solo del sistema
                $service->delete();
                //marcamos como libre la ip
                $usedip->is_used_ip($target, 0, false);
                //descontamos el numero de clientes del router
                $counter->step_down_router($router_id);
                //descontamos el numero de clientes del plan
                $counter->step_down_plan($client->plan_id);
                //eliminamos de la tabla suspend
                SuspendClient::where('service_id', $service->id)->delete();
                //eliminamos de la tabla bill customers

                $log->save("Se ha eliminado un cliente:", "danger", $nameClient);
//                return $process->show('success');

            } else { //conexión con router eliminanos mediante la API

                $global = GlobalSetting::all()->first();
                $debug = $global->debug;

                $rocket = new RocketCore();

                $num_cli = ClientService::where('plan_id', '=', $service->plan_id)->where('router_id', $router_id)->count(); //for pcq queue tree
                $error = new Mkerror();
                if ($typeconf == 'no') {

                    //eliminamos de adv, arp, dhcp o drop
                    //GET all data for API
                    $conf = Helpers::get_api_options('mikrotik');
                    $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                    $API->debug = $conf['d'];
                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                        $DELETE = $rocket->set_basic_config($API, $error, $data, $target, null, 'delete', $debug);
                        $list = PermitidosList::remove($API, $data, $debug, $error);
                        if ($debug == 1) {
                            if ($DELETE != false) {
//                                return $DELETE;
                            }
                        }

                        //eliminamos en la base de datos
                        $service->delete();
                        //marcamos como libre la ip
                        $usedip->is_used_ip($target, 0, false);
                        //descontamos el numero de clientes del router
                        $counter->step_down_router($router_id);
                        //descontamos el numero de clientes del plan
                        $counter->step_down_plan($client->plan_id);
                        //eliminamos de la tabla suspend
                        SuspendClient::where('service_id', '=', $service->id)->delete();

                        //save log
                        $log->save("Se ha eliminado un cliente:", "danger", $nameClient);
                        //Desconectamos de la API
                        $API->disconnect();

//                        return $process->show('success');

                    } else {
                        return Response::json(array('msg' => 'errorConnect'));
                    }

                }

                if ($typeconf == 'sq') {

                    //GET all data for API
                    $conf = Helpers::get_api_options('mikrotik');
                    $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                    $API->debug = $conf['d'];

                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                        $DELETE = $rocket->delete_simple_queues($API, $data, $target, $debug);
                        $list = PermitidosList::remove($API, $data, $debug, $error);
                        if ($debug == 1) {
                            //control y procesamiento de errores
                            if ($process->check($DELETE)) {
                                $API->disconnect();
//                                return $process->check($DELETE);
                            }
                        }

                        //eliminamos en la base de datos
                        $service->delete();
                        //marcamos como libre la ip
                        $usedip->is_used_ip($target, 0, false);
                        //descontamos el numero de clientes del router
                        $counter->step_down_router($router_id);
                        //descontamos el numero de clientes del plan
                        $counter->step_down_plan($client->plan_id);
                        //eliminamos de la tabla suspend
                        SuspendClient::where('service_id', '=', $service->id)->delete();
                        //save log
                        $log->save("Se ha eliminado un cliente:", "danger", $nameClient);
                        //Desconectamos de la API
                        $API->disconnect();

//                        return $process->show('success');
                    } else {
                        return Response::json(array('msg' => 'errorConnect'));
                    }
                }

                if ($typeconf == 'st') { //Simple queue with tree

                    //GET all data for API
                    $conf = Helpers::get_api_options('mikrotik');
                    $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                    $API->debug = $conf['d'];

                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                        $service->delete();

                        $DELETE = $rocket->delete_simple_queue_with_tree($API, $data, $target, 'delete', $debug);
                        $list = PermitidosList::remove($API, $data, $debug, $error);
                        //return $DELETE;

                        if ($debug == 1) {
                            //control y procesamiento de errores
                            if ($process->check($DELETE)) {
                                $API->disconnect();
//                                return $process->check($DELETE);
                            }
                        }

                        //eliminamos en la base de datos

                        //marcamos como libre la ip
                        $usedip->is_used_ip($target, 0, false);
                        //descontamos el numero de clientes del router
                        $counter->step_down_router($router_id);
                        //descontamos el numero de clientes del plan
                        $counter->step_down_plan($client->plan_id);
                        //eliminamos de la tabla suspend
                        SuspendClient::where('service_id', '=', $service->d)->delete();

                        //save log
                        $log->save("Se ha eliminado un cliente:", "danger", $nameClient);
                        //Desconectamos de la API
                        $API->disconnect();

//                        return $process->show('success');
                    } else {
                        return Response::json(array('msg' => 'errorConnect'));
                    }
                }

                if ($typeconf == 'ho') { //hotspot users profiles

                    //GET all data for API
                    $conf = Helpers::get_api_options('mikrotik');
                    $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                    $API->debug = $conf['d'];

                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                        $DELETE = $rocket->delete_hotspot_user($API, $data, $target, $client->typeauth, $debug);
                        $list = PermitidosList::remove($API, $data, $debug, $error);
                        if ($debug == 1) {
                            //control y procesamiento de errores
                            if ($process->check($DELETE)) {
                                $API->disconnect();
//                                return $process->check($DELETE);
                            }
                        }

                        //eliminamos en la base de datos
                        $service->delete();
                        //marcamos como libre la ip
                        $usedip->is_used_ip($target, 0, false);
                        //descontamos el numero de clientes del router
                        $counter->step_down_router($router_id);
                        //descontamos el numero de clientes del plan
                        $counter->step_down_plan($client->plan_id);
                        //eliminamos de la tabla suspend
                        SuspendClient::where('service_id', '=', $service->id)->delete();

                        //save log
                        $log->save("Se ha eliminado un cliente:", "danger", $nameClient);
                        //Desconectamos de la API
                        $API->disconnect();

//                        return $process->show('success');
                    } else {
                        return Response::json(array('msg' => 'errorConnect'));
                    }

                }

                if ($typeconf == 'ha') { //hotspot pcq

                    //GET all data for API
                    $conf = Helpers::get_api_options('mikrotik');
                    $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                    $API->debug = $conf['d'];

                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                        //opcion avanzada burst del plan
                        $burst = Burst::get_all_burst($plan->upload, $plan->download, $plan->burst_limit, $plan->burst_threshold, $plan->limitat);

                        $advanced_data = array(
                            'user' => $client->user_hot,
                            'name' => $nameClient . '_' . $service->id,
                            'mac' => $client->mac,
                            'arp' => $data['arp'],
                            'adv' => $data['adv'],
                            'dhcp' => $data['dhcp'],
                            'drop' => $drop,
                            'typeauth' => $client->typeauth,
                            'namePlan' => $plan->name,
                            'num_cl' => $num_cli,
                            'speed_down' => $plan->download,
                            'speed_up' => $plan->upload,
                            //advanced for pcq
                            'burst_rate_down' => $burst['bld'],
                            'burst_rate_up' => $burst['blu'],
                            'burst_threshold_down' => $burst['btd'],
                            'burst_threshold_up' => $burst['btu'],
                            'limit_at_down' => $burst['lim_at_down'],
                            'limit_at_up' => $burst['lim_at_up'],
                            'burst_time' => $plan->burst_time,
                            'priority_a' => $plan->priority,
                        );

                        $DELETE = $rocket->delete_hotspot_user_pcq($API, $advanced_data, $target, $client->typeauth, $debug);
                        $list = PermitidosList::remove($API, $data, $debug, $error);
                        if ($debug == 1) {
                            //control y procesamiento de errores
                            if ($process->check($DELETE)) {
                                $API->disconnect();
                                return $process->check($DELETE);
                            }
                        }

                        //eliminamos en la base de datos
                        $service->delete();
                        //marcamos como libre la ip
                        $usedip->is_used_ip($target, 0, false);
                        //descontamos el numero de clientes del router
                        $counter->step_down_router($router_id);
                        //descontamos el numero de clientes del plan
                        $counter->step_down_plan($client->plan_id);
                        //eliminamos de la tabla suspend
                        SuspendClient::where('service_id', '=', $service->id)->delete();

                        //save log
                        $log->save("Se ha eliminado un cliente:", "danger", $nameClient);
                        //Desconectamos de la API
                        $API->disconnect();

//                        return $process->show('success');

                    } else {
                        return Response::json(array('msg' => 'errorConnect'));
                    }

                }

                if ($typeconf == 'dl') { //DHCP leases

                    //GET all data for API
                    $conf = Helpers::get_api_options('mikrotik');
                    $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                    $API->debug = $conf['d'];

                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                        $DELETE = $rocket->delete_dhcp_leases($API, $data, $target, $debug);
                        $list = PermitidosList::remove($API, $data, $debug, $error);
                        if ($debug == 1) {
                            //control y procesamiento de errores
                            if ($process->check($DELETE)) {
                                $API->disconnect();
//                                return $process->check($DELETE);
                            }
                        }

                        //eliminamos en la base de datos
                        $service->delete();
                        //marcamos como libre la ip
                        $usedip->is_used_ip($target, 0, false);
                        //descontamos el numero de clientes del router
                        $counter->step_down_router($router_id);
                        //descontamos el numero de clientes del plan
                        $counter->step_down_plan($client->plan_id);
                        //eliminamos de la tabla suspend
                        SuspendClient::where('service_id', '=', $service->id)->delete();

                        //save log
                        $log->save("Se ha eliminado un cliente:", "danger", $nameClient);
                        //Desconectamos de la API
                        $API->disconnect();

//                        return $process->show('success');

                    } else {
                        return Response::json(array('msg' => 'errorConnect'));
                    }
                }

                if ($typeconf == 'ps') { //PPP secrets Simple Queues

                    //GET all data for API
                    $conf = Helpers::get_api_options('mikrotik');
                    $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                    $API->debug = $conf['d'];

                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {
                        $DELETE = $rocket->delete_ppp_simple($API, $data, $target, $debug);
                        $list = PermitidosList::remove($API, $data, $debug, $error);
                        if ($debug == 1) {
                            //control y procesamiento de errores
                            if ($process->check($DELETE)) {
                                $API->disconnect();
//                                return $process->check($DELETE);
                            }
                        }


                        //eliminamos en la base de datos
                        $service->delete();
                        //marcamos como libre la ip
                        $usedip->is_used_ip($target, 0, false);
                        //descontamos el numero de clientes del router
                        $counter->step_down_router($router_id);
                        //descontamos el numero de clientes del plan
                        $counter->step_down_plan($client->plan_id);
                        //eliminamos de la tabla suspend
                        SuspendClient::where('service_id', '=', $service->id)->delete();

                        //save log
                        $log->save("Se ha eliminado un cliente:", "danger", $nameClient);
                        //Desconectamos de la API
                        $API->disconnect();

//                        return $process->show('success');
                    } else {
                        return Response::json(array('msg' => 'errorConnect'));
                    }
                }

                if ($typeconf == 'pt') { //PPP secrets Simple Queues With Tree

                    //GET all data for API
                    $conf = Helpers::get_api_options('mikrotik');
                    $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                    $API->debug = $conf['d'];

                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                        //eliminamos en la base de datos
                        $service->delete();
                        //marcamos como libre la ip
                        $usedip->is_used_ip($target, 0, false);
                        //descontamos el numero de clientes del router
                        $counter->step_down_router($router_id);
                        //descontamos el numero de clientes del plan
                        $counter->step_down_plan($client->plan_id);
                        //eliminamos de la tabla suspend
                        SuspendClient::where('service_id', '=', $service->id)->delete();

                        $DELETE = $rocket->delete_ppp_simple_queue_with_tree($API, $data, $target, 'delete', $debug);
                        $list = PermitidosList::remove($API, $data, $debug, $error);
                        if ($debug == 1) {
                            //control y procesamiento de errores
                            if ($process->check($DELETE)) {
                                $API->disconnect();
//                                return $process->check($DELETE);
                            }
                        }

                        //save log
                        $log->save("Se ha eliminado un cliente:", "danger", $nameClient);
                        //Desconectamos de la API
                        $API->disconnect();

//                        return $process->show('success');
                    } else {
                        return Response::json(array('msg' => 'errorConnect'));
                    }

                }

                if ($typeconf == 'pp') { //PPP secrets

                    //GET all data for API
                    $conf = Helpers::get_api_options('mikrotik');
                    $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                    $API->debug = $conf['d'];

                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {
                        $DELETE = $rocket->delete_ppp_user($API, $data, $target, $debug);
                        $list = PermitidosList::remove($API, $data, $debug, $error);
                        if ($debug == 1) {
                            //control y procesamiento de errores
                            if ($process->check($DELETE)) {
                                $API->disconnect();
//                                return $process->check($DELETE);
                            }
                        }

                        //eliminamos en la base de datos
                        $service->delete();
                        //marcamos como libre la ip
                        $usedip->is_used_ip($target, 0, false);
                        //descontamos el numero de clientes del router
                        $counter->step_down_router($router_id);
                        //descontamos el numero de clientes del plan
                        $counter->step_down_plan($client->plan_id);
                        //eliminamos de la tabla suspend
                        SuspendClient::where('service_id', '=', $service->id)->delete();

                        //save log
                        $log->save("Se ha eliminado un cliente:", "danger", $nameClient);
                        //Desconectamos de la API
                        $API->disconnect();

//                        return $process->show('success');
                    } else {
                        return Response::json(array('msg' => 'errorConnect'));
                    }
                }

                if ($typeconf == 'pa') { //PPP secrets pcq

                    //GET all data for API
                    $conf = Helpers::get_api_options('mikrotik');
                    $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                    $API->debug = $conf['d'];

                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                        //opcion avanzada burst del plan
                        $burst = Burst::get_all_burst($plan->upload, $plan->download, $plan->burst_limit, $plan->burst_threshold, $plan->limitat);

                        $advanced_data = array(
                            'user' => $service->user_hot,
                            'name' => $nameClient . '_' . $service->id,
                            'mac' => $service->mac,
                            'arp' => $data['arp'],
                            'adv' => $data['adv'],
                            'dhcp' => $data['dhcp'],
                            'drop' => $drop,
                            'namePlan' => $plan->name,
                            'num_cl' => $num_cli,
                            'speed_down' => $plan->download,
                            'speed_up' => $plan->upload,
                            //advanced for pcq
                            'burst_rate_down' => $burst['bld'],
                            'burst_rate_up' => $burst['blu'],
                            'burst_threshold_down' => $burst['btd'],
                            'burst_threshold_up' => $burst['btu'],
                            'limit_at_down' => $burst['lim_at_down'],
                            'limit_at_up' => $burst['lim_at_up'],
                            'burst_time' => $plan->burst_time,
                            'priority_a' => $plan->priority,
                        );

                        $DELETE = $rocket->delete_ppp_secrets_pcq($API, $advanced_data, $target, $debug);
                        $list = PermitidosList::remove($API, $data, $debug, $error);
                        if ($debug == 1) {
                            //control y procesamiento de errores
                            if ($process->check($DELETE)) {
                                //Desconectamos la API
                                $API->disconnect();
//                                return $process->check($DELETE);
                            }
                        }

                        //eliminamos en la base de datos
                        $service->delete();
                        //marcamos como libre la ip
                        $usedip->is_used_ip($target, 0, false);
                        //descontamos el numero de clientes del router
                        $counter->step_down_router($router_id);
                        //descontamos el numero de clientes del plan
                        $counter->step_down_plan($client->plan_id);
                        //eliminamos de la tabla suspend
                        SuspendClient::where('service_id', '=', $service->id)->delete();

                        //save log
                        $log->save("Se ha eliminado un cliente:", "danger", $nameClient);
                        //Desconectamos de la API
                        $API->disconnect();

//                        return $process->show('success');

                    } else {
                        return Response::json(array('msg' => 'errorConnect'));
                    }

                }

                if ($typeconf == 'pc') {

                    //GET all data for API
                    $conf = Helpers::get_api_options('mikrotik');
                    $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                    $API->debug = $conf['d'];

                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                        //opcion avanzada burst del plan
                        $burst = Burst::get_all_burst($plan->upload, $plan->download, $plan->burst_limit, $plan->burst_threshold, $plan->limitat);

                        $advanced_data = array(
                            'name' => $nameClient . '_' . $service->id,
                            'mac' => $service->mac,
                            'arp' => $data['arp'],
                            'adv' => $data['adv'],
                            'dhcp' => $data['dhcp'],
                            'drop' => $drop,
                            'namePlan' => $plan->name,
                            'num_cl' => $num_cli,
                            'speed_down' => $plan->download,
                            'speed_up' => $plan->upload,
                            //advanced for pcq
                            'burst_rate_down' => $burst['bld'],
                            'burst_rate_up' => $burst['blu'],
                            'burst_threshold_down' => $burst['btd'],
                            'burst_threshold_up' => $burst['btu'],
                            'limit_at_down' => $burst['lim_at_down'],
                            'limit_at_up' => $burst['lim_at_up'],
                            'burst_time' => $plan->burst_time,
                            'priority_a' => $plan->priority,
	                        'no_rules' => $plan->no_rules
                        );

                        $DELETE = $rocket->delete_pcq_list($API, $advanced_data, $target, 'delete', $debug);
                        $list = PermitidosList::remove($API, $data, $debug, $error);
                        if ($debug == 1) {
                            //control y procesamiento de errores
                            if ($process->check($DELETE)) {
                                //Desconectamos la API
                                $API->disconnect();
//                                return $process->check($DELETE);
                            }
                        }

                        //eliminamos en la base de datos
                        $service->delete();
                        //marcamos como libre la ip
                        $usedip->is_used_ip($target, 0, false);
                        //descontamos el numero de clientes del router
                        $counter->step_down_router($router_id);
                        //descontamos el numero de clientes del plan
                        $counter->step_down_plan($client->plan_id);
                        //eliminamos de la tabla suspend
                        SuspendClient::where('service_id', '=', $service->id)->delete();

                        //save log
                        $log->save("Se ha eliminado un cliente:", "danger", $nameClient);
                        //Desconectamos de la API
                        $API->disconnect();

//                        return $process->show('success');

                    } else {
                        return Response::json(array('msg' => 'errorConnect'));
                    }
                }

	            if($typeconf == 'rr'){
		            /**eliminamos nas sobre bd de radius**/
		            try {

			            //marcamos como libre la ip
			            $usedip->is_used_ip($target, 0, false);
			            //descontamos el numero de clientes del router
			            $counter->step_down_router($router_id);
			            //descontamos el numero de clientes del plan
			            $counter->step_down_plan($client->plan_id);
			            //eliminamos de la tabla suspend
			            SuspendClient::where('service_id', '=', $service->d)->delete();

			            /**eliminaos en BD Radius**/
                        Radreply::where('username',$service->user_hot)->delete();
                        Radcheck::where('username',$service->user_hot)->delete();
                        $router_buscado = Router::find($router_id);
                        $ejecucion = shell_exec('echo User-Name="'.$service->user_hot.'" | /usr/bin/radclient -c 1 -n 3 -r 3 -t 3 -x '.$router_buscado->ip.':3799 disconnect '.$router_buscado->radius->secret.' 2>&1');

                        $service->delete();

			            //save log
			            $log->save("Se ha eliminado un cliente:", "danger", $nameClient);

		            } catch(\Exception $exception) {
			            return Response::json(array('msg' => 'errorConnect'));
		            }

	            }
            }
			$ip = $service->ip;
	        CommonService::log("Se elimina la IP $ip del cliente:", $this->username, 'success' , $this->userId);

        }

        //eliminamos de la tabla suspend
        SuspendClient::where('client_id', '=', $client->id)->delete();

        //eliminamos de la tabla pagos payments
        PaymentNew::where('client_id', $client->id)->delete();

        //eliminamos de la tabla bill customers
        BillCustomer::where('client_id', $client->id)->delete();

        // delete transactions of this client
	    Transaction::where('client_id', $client->id)->delete();

        //eliminamos todos los tickets
        Ticket::where('client_id', $client->id)->delete();

        // delete cashier deposit history
        CashierDepositHistory::where('client_id', $client->id)->delete();

        // remove logs for this client
        Logg::where('client_id', $client->id)->delete();

        // Wallet payment entry remove before client remove
        WalletPayment::where('client_id', $client->id)->delete();

        $client->delete();
	    CommonService::log("El cliente es eliminado:", $this->username, 'success' , $this->userId);
        return Response::json(array(array('msg' => 'success')));
    }

    //metodo para actualizar clientes
    public function postUpdate(Request $request)
    {
        $process = new Chkerr();

        $friendly_names = array(
            'edit_name' => __('app.name'),
            'edit_phone' => __('app.telephone'),
            'edit_dni' => 'DNI/CI',
            'edit_date_pay' => __('app.paymentDate'),
            'edit_date_in' => __('app.dateOfAddmission'),
            'edit_ip' => 'IP ' . __('app.client'),
            'edit_mac' => 'MAC',
            'edit_user' => __('app.username'),
            'edit_pass' => __('app.new') . ' Password',
            'edit_pass2' => __('app.new') . ' ' . __('app.password'),
            'edit_odb_id' => 'odb_id',
            'edit_onu_id' => 'onu_id',
            'edit_port' => 'port',
            'edit_onusn' => 'onusn',
            'edit_zona_id' => 'onusn',
        );

        if ($request->get('edit_mac') == '00:00:00:00:00:00' || $request->get('edit_mac') == '') {
            $rules = array(
                'edit_name' => 'required|max:100|string',
                'edit_phone' => 'string',
                'edit_email' => 'email|unique:clients,email,' . $request->get('client_id'),
                'edit_dni' => 'unique:clients,dni,' . $request->get('client_id'),
                'edit_date_pay' => 'required|date',
                'edit_date_in' => 'required|date',
                'edit_ip' => 'required|ip',
            );
        } else {
            $rules = array(
                'edit_name' => 'required|max:100|string',
                'edit_phone' => 'string',
                'edit_email' => 'email|unique:clients,email,' . $request->get('client_id'),
                'edit_dni' => 'unique:clients,dni,' . $request->get('client_id'),
                'edit_date_pay' => 'required|date',
                'edit_date_in' => 'required|date',
                'edit_ip' => 'required|ip',
                'edit_mac' => 'min:17|unique:clients,mac,' . $request->get('client_id'),
            );
        }

        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);
        if ($validation->fails()) {
            return Response::json(array(array('msg' => 'error', 'errors' => $validation->getMessageBag()->toArray())));
        }

        //find and get client
        $client = Client::find($request->get('client_id'));
        //get type control router
        $type = ControlRouter::where('router_id', '=', $request->get('router'))->get();

        //control de dhcp
        if ($type[0]->dhcp == '1') {

            if ($request->get('edit_mac') == '00:00:00:00:00:00' || $request->get('edit_mac') == '') {
                return $process->show('reqmac');
            }
        }

        //significa que esta cambiando de router
        if ($client->router_id == $request->get('router')) {
            $changeRouter = false;
        } else {
            $changeRouter = true;
        }

        //verificamos si esta cambiando de plan
        if ($client->plan_id == $request->get('edit_plan')) {
            $changePlan = false;
        } else {
            $changePlan = true;
        }

        //verificamos sis esta cambiando de ip
        if ($client->ip == $request->get('edit_ip')) {
            $changeIP = false;
        } else {
            $changeIP = true;
        }

        $pl = new GetPlan();
        $plan = $pl->get($request->get('edit_plan'));
        $planCost = $plan['cost'];

        $num_cli = Client::where('plan_id', '=', $request->get('edit_plan'))->where('status', 'ac')->where('router_id', $request->get('router'))->count();

        //get  burst profiles
        $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

        $bt = $plan['burst_time'] . '/' . $plan['burst_time'];
        $bl = $burst['blu'] . '/' . $burst['bld'];
        $bth = $burst['btu'] . '/' . $burst['btd'];
        $limit_at = $burst['lim_at_up'] . '/' . $burst['lim_at_down'];
        $priority = $plan['priority'] . '/' . $plan['priority'];
        $en = new Pencrypt();

        //get connection data for login ruter
        $router = new RouterConnect();
        $con = $router->get_connect($request->get('router'));

        //datos generales
        $data = array(
            'name' => $request->get('edit_name'),
            'mac' => $request->get('edit_mac', '00:00:00:00:00:00'),
            'arp' => $type[0]->arpmac,
            'adv' => $type[0]->adv,
            'dhcp' => $type[0]->dhcp,
            'speed_down' => $plan['download'],
            'lan' => $con['lan'],
            'maxlimit' => $plan['maxlimit'],
            'bl' => $bl,
            'bth' => $bth,
            'speed_up' => $plan['upload'],
            'bt' => $bt,
            'limit_at' => $limit_at,
            'priority_a' => $plan['priority'],
            'drop' => 0,
            //for simple queue with tree
            'download' => $plan['download'],
            'upload' => $plan['upload'],
            'aggregation' => $plan['aggregation'],
            'limitat' => $plan['limitat'],
            'burst_limit' => $plan['burst_limit'],
            'burst_threshold' => $plan['burst_threshold'],
            //advanced for pcq
            'num_cl' => $num_cli,
            'rate_down' => $plan['download'], //
            'rate_up' => $plan['upload'], //
            'burst_rate_down' => $burst['bld'],
            'burst_rate_up' => $burst['blu'],
            'burst_threshold_down' => $burst['btd'],
            'burst_threshold_up' => $burst['btu'],
            'limit_at_down' => $burst['lim_at_down'],
            'limit_at_up' => $burst['lim_at_up'],
            'burst_time' => $plan['burst_time'],
            'billing_type' => $request->get('billing_type'),
            'odb_id' => $request->get('edit_odb_id'),
            'onu_id' => $request->get('edit_onu_id'),
            'port' => $request->get('edit_port'),
            'onusn' => $request->get('edit_onusn'),
            'zona_id' => $request->get('edit_zona_id'),

            //end pcq
            'priority' => $priority,
            'comment' => 'SmartISP - ' . $plan['name'],
            'ip' => $request->get('edit_ip'),
            'pay_date' => date("Y-m-d", strtotime($request->get('edit_date_pay'))),
            'date_in' => date("Y-m-d", strtotime($request->get('edit_date_in'))),
            'plan_id' => $request->get('edit_plan'),
            'namePlan' => $plan['name'],
            'router_id' => $request->get('router'),
            'email' => $request->get('edit_email'),
            'phone' => $request->get('edit_phone', ''),
            'dir' => $request->get('edit_dir'),
            'loc' => $request->get('location_edit', '0'),
            'dni' => $request->get('edit_dni'),
            'user' => Intersep::replace($request->get('edit_user'), ''),
            'pass' => Intersep::replace($request->get('edit_pass'), $en->decode($client->pass_hot)),
            'old_user' => $client->user_hot,
            'pass2' => $request->get('edit_pass2'),
            'changePlan' => $changePlan,
            'changeRouter' => $changeRouter,
            'typeauth' => $request->get('edit_auth'),
            'newtarget' => $request->get('edit_ip'),
            'client_id' => $request->get('client_id'),
            'old_name' => $client->name,
            'profile' => $request->get('editprofile'),
            'oldplan' => $client->plan_id,
            'old_router' => $client->router_id,
	        'no_rules' => $plan['no_rules'],
        );
        //comprobamos si esta cambiando de router recuperamos datos anteriores

        $upadeclient = new UpdateClient();
        $client->billing_settings()->update(['billing_due_date' => Carbon::parse(request()->edit_date_pay)->day]);
        $usedip = new StatusIp();
        $global = GlobalSetting::all()->first();
        $debug = $global->debug;

        //prepare old data//
        $oldtarget = $client->ip;
        $oldplan = $client->plan_id;
        $old_router = $client->router_id;
        $oldtype = ControlRouter::where('router_id', '=', $old_router)->get();

        //verificamos si esta cambiando de router
        if ($data['changeRouter']) {

            if ($oldtype[0]['adv'] == 1) {
                $odrop = 1;
            } else {
                $odrop = 0;
            }

            $pl = new GetPlan();
            $plan = $pl->get($oldplan);
            //get  burst profiles
            $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

            $num_cli = Client::where('plan_id', '=', $oldplan)->where('status', 'ac')->where('router_id', $old_router)->count();

            $bt = $plan['burst_time'] . '/' . $plan['burst_time'];
            $bl = $burst['blu'] . '/' . $burst['bld'];
            $bth = $burst['btu'] . '/' . $burst['btd'];

            $oldData = array(
                'name' => $client->name,
                'user' => $client->user_hot,
                'mac' => $client->mac,
                'arp' => $oldtype[0]['arpmac'],
                'adv' => $oldtype[0]['adv'],
                'drop' => $odrop,
                'dhcp' => $oldtype[0]['dhcp'],
                'namePlan' => $plan['name'],
                'typeauth' => $client->typeauth,

                //advanced for pcq
                'num_cl' => $num_cli,
                'speed_down' => $plan['download'],
                'speed_up' => $plan['upload'],
                'rate_down' => $plan['download'] . 'k',
                'rate_up' => $plan['upload'] . 'k',
                'burst_rate_down' => $burst['bld'],
                'burst_rate_up' => $burst['blu'],
                'burst_threshold_down' => $burst['btd'],
                'burst_threshold_up' => $burst['btu'],
                'limit_at_down' => $burst['lim_at_down'],
                'limit_at_up' => $burst['lim_at_up'],
                'burst_time' => $plan['burst_time'],
                'priority_a' => $plan['priority'],
                //for simple queue with tree
                'plan_id' => $oldplan,
                'download' => $plan['download'],
                'upload' => $plan['upload'],
                'aggregation' => $plan['aggregation'],
                'limitat' => $plan['limitat'],
                'burst_limit' => $plan['burst_limit'],
                'burst_threshold' => $plan['burst_threshold'],
                'priority' => $plan['priority'] . '/' . $plan['priority'],
                'maxlimit' => $plan['maxlimit'],
                'bl' => $bl,
                'bth' => $bth,
                'bt' => $bt,
                'comment' => 'SmartISP - ' . $plan['name'],
	            'no_rules' => $plan['no_rules'],

            );

            //iniciamos el migrador
            $migrate = new MkMigrate();
            $process = new Chkerr();
            $rocket = new RocketCore();
            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];


            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $data['rate_down'] = $data['rate_down'] . 'k';
                $data['rate_up'] = $data['rate_up'] . 'k';

                $MK = $migrate->migrate_up($API, $rocket, $data, $data['newtarget'], $type[0]['type_control'], $debug);

                if ($debug == 1) {
                    if ($process->check($MK)) {
                        return $process->check($MK);
                    }
                }

                //desactivamos la ip si esta cambiando
                if ($changeIP) { //esta cambiando de ip
                    //activamos la nueva IP
                    $usedip->is_used_ip($data['newtarget'], $data['client_id'], true);
                }

            } else {
                return Response::json([['msg' => 'errorConnect']]);
            }

            //abrimos otra conexión nueva
            $router = new RouterConnect();
            $con = $router->get_connect($old_router);
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                // Eliminanos de la anterior configuracion
                $MK = $migrate->remove_previous($API, $rocket, $oldData, $oldtarget, $oldtype[0]['type_control'], $debug);

                if ($debug == 1) {
                    //control de y procesamiento de errores
                    if ($process->check($MK)) {
                        return $process->check($MK);
                    }
                }

                if ($changeIP) { //esta cambiando de ip
                    //desactivamos la IP anterior
                    $usedip->is_used_ip($oldtarget, $data['client_id'], false);
                }

            } else {
                return Response::json([['msg' => 'errorConnect']]);
            }

            //actualizamos en la base de datos
            $upadeclient->update($data);

            return $process->show('success');

        } //fin del if change
        else { // no esta cambiando de router

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            //Verificamos el tipo de control
            switch ($type[0]['type_control']) {

                case 'no': //no shaping control only arp, adv or drop
                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {
                        $rocket = new RocketCore();
                        $error = new Mkerror();

                        $UPDATE = $rocket->set_basic_config($API, $error, $data, $oldtarget, $data['newtarget'], 'update', $debug);

                        if ($debug == 1) {
                            if ($UPDATE != false) {
                                return $UPDATE;
                            }
                        }

                        //actualizamos el estado del ip en IP/Redes
                        if ($changeIP) { //esta cambiando de IP
                            //desactivamos la IP anterior
                            $usedip->is_used_ip($oldtarget, $data['client_id'], false);
                            //activamos la nueva IP
                            $usedip->is_used_ip($data['newtarget'], $data['client_id'], true);
                        } else { //no esta cambiando la ip pero actualizamos el estado.
                            $usedip->refresh_ip($oldtarget, $data['client_id']);
                        }

                    } else {
                        return Response::json([['msg' => 'errorConnect']]);
                    }

                    //actualizamos en la base de datos
                    $upadeclient->update($data);
                    return $process->show('success');

                    break;

                case 'sq': //Control simple Queues
                    # verificamos si el cliente sera actualizado en el router

                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                        $rocket = new RocketCore();
                        $UPDATE = $rocket->update_simple_queues($API, $data, $oldtarget, $data['newtarget'], $debug);

                        if ($debug == 1) {
                            if ($process->check($UPDATE)) {
                                $API->disconnect();
                                return $process->check($UPDATE);
                            }
                        }

                        //actualizamos el estado del ip en IP/Redes
                        if ($changeIP) { //esta cambiando de IP
                            //desactivamos la IP anterior
                            $usedip->is_used_ip($oldtarget, $data['client_id'], false);
                            //activamos la nueva IP
                            $usedip->is_used_ip($data['newtarget'], $data['client_id'], true);
                        } else { //no esta cambiando la ip pero actualizamos el estado.
                            $usedip->refresh_ip($oldtarget, $data['client_id']);
                        }

                    } else {
                        return Response::json([['msg' => 'errorConnect']]);
                    }

                    //actualizamos en la base de datos
                    $upadeclient->update($data);
                    return $process->show('success');

                    break;

                case 'st': //Simple queues with tree

                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                        //actualizamos el estado del ip en IP/Redes
                        if ($changeIP) { //esta cambiando de IP
                            //desactivamos la IP anterior
                            $usedip->is_used_ip($oldtarget, $data['client_id'], false);
                            //activamos la nueva IP
                            $usedip->is_used_ip($data['newtarget'], $data['client_id'], true);
                        } else { //no esta cambiando la ip pero actualizamos el estado.
                            $usedip->refresh_ip($oldtarget, $data['client_id']);
                        }

                    } else {
                        return Response::json([['msg' => 'errorConnect']]);
                    }

                    //actualizamos en la base de datos
                    $upadeclient->update($data);

                    $rocket = new RocketCore();

                    $UPDATE = $rocket->update_simple_queue_with_tree($API, $data, $oldtarget, $data['newtarget'], $debug);

                    //return $UPDATE; //comentar

                    if ($debug == 1) {
                        if ($process->check($UPDATE)) {
                            $API->disconnect();
                            return $process->check($UPDATE);
                        }
                    }

                    return $process->show('success');


                    break;

                case 'ho': //Control Hostspot user profiles


                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                        $rocket = new RocketCore();

                        $UPDATE = $rocket->update_hotspot_user($API, $data, $oldtarget, $data['newtarget'], $debug);

                        if ($debug == 1) {
                            if ($process->check($UPDATE)) {
                                $API->disconnect();
                                return $process->check($UPDATE);
                            }
                        }

                        //actualizamos el estado del ip en IP/Redes
                        if ($changeIP) { //esta cambiando de IP
                            //desactivamos la IP anterior
                            $usedip->is_used_ip($oldtarget, $data['client_id'], false);
                            //activamos la nueva IP
                            $usedip->is_used_ip($data['newtarget'], $data['client_id'], true);
                        } else { //no esta cambiando la ip pero actualizamos el estado.
                            $usedip->refresh_ip($oldtarget, $data['client_id']);
                        }
                    } else {
                        return Response::json([['msg' => 'errorConnect']]);
                    }

                    //actualizamos en la base de datos
                    $upadeclient->update($data);
                    return $process->show('success');

                    break;

                case 'ha': //Control Hotspot pcq

                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                        $rocket = new RocketCore();

                        $UPDATE = $rocket->update_hotspot_user_pcq($API, $data, $oldtarget, $data['newtarget'], $data['typeauth'], $debug);

                        if ($debug == 1) {
                            if ($process->check($UPDATE)) {
                                $API->disconnect();
                                return $process->check($UPDATE);
                            }
                        }

                        //actualizamos el estado del ip en IP/Redes
                        if ($changeIP) { //esta cambiando de IP
                            //desactivamos la IP anterior
                            $usedip->is_used_ip($oldtarget, $data['client_id'], false);
                            //activamos la nueva IP
                            $usedip->is_used_ip($data['newtarget'], $data['client_id'], true);
                        } else { //no esta cambiando la ip pero actualizamos el estado.
                            $usedip->refresh_ip($oldtarget, $data['client_id']);
                        }

                    } else {
                        return Response::json([['msg' => 'errorConnect']]);
                    }

                    //actualizamos en la base de datos
                    $upadeclient->update($data);
                    return $process->show('success');

                    break;

                case 'dl': //control DHCP leases

                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                        $rocket = new RocketCore();
                        $UPDATE = $rocket->update_dhcp_leases_user($API, $data, $oldtarget, $data['newtarget'], $debug);

                        if ($debug == 1) {
                            if ($process->check($UPDATE)) {
                                $API->disconnect();
                                return $process->check($UPDATE);
                            }
                        }

                        //actualizamos el estado del ip en IP/Redes
                        if ($changeIP) { //esta cambiando de IP
                            //desactivamos la IP anterior
                            $usedip->is_used_ip($oldtarget, $data['client_id'], false);
                            //activamos la nueva IP
                            $usedip->is_used_ip($data['newtarget'], $data['client_id'], true);
                        } else { //no esta cambiando la ip pero actualizamos el estado.
                            $usedip->refresh_ip($oldtarget, $data['client_id']);
                        }
                    } else {
                        return Response::json([['msg' => 'errorConnect']]);
                    }

                    $upadeclient->update($data);
                    return $process->show('success');

                    break;

                case 'pt': //control PPP Simple Queue with tree

                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                        //get gateway for addres
                        $network = Network::where('ip', $data['newtarget'])->get();

                        if (count($network) == 0) {
                            return $process->show('error_no_address');
                        }

                        $gat = AddressRouter::find($network[0]->address_id);


                        $upadeclient->update($data);

                        //actualizamos el estado del ip en IP/Redes
                        if ($changeIP) { //esta cambiando de IP
                            //desactivamos la IP anterior
                            $usedip->is_used_ip($oldtarget, $data['client_id'], false);
                            //activamos la nueva IP
                            $usedip->is_used_ip($data['newtarget'], $data['client_id'], true);
                        } else { //no esta cambiando la ip pero actualizamos el estado.
                            $usedip->refresh_ip($oldtarget, $data['client_id']);
                        }

                        $rocket = new RocketCore();

                        $UPDATE = $rocket->update_ppp_simple_queue_with_tree($API, $data, $oldtarget, $data['newtarget'], $gat->gateway, $debug);

                        if ($debug == 1) {
                            if ($process->check($UPDATE)) {
                                $API->disconnect();
                                return $process->check($UPDATE);
                            }
                        }

                    } else {
                        return Response::json([['msg' => 'errorConnect']]);
                    }


                    return $process->show('success');


                    break;

                case 'ps': //control PPP Simple Queue

                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                        //get gateway for addres
                        $network = Network::where('ip', $data['newtarget'])->get();

                        if (count($network) == 0) {
                            return $process->show('error_no_address');
                        }

                        $gat = AddressRouter::find($network[0]->address_id);

                        $rocket = new RocketCore();

                        $UPDATE = $rocket->update_ppp_simple($API, $data, $oldtarget, $data['newtarget'], $gat->gateway, $debug);

                        if ($debug == 1) {
                            if ($process->check($UPDATE)) {
                                $API->disconnect();
                                return $process->check($UPDATE);
                            }
                        }

                        //actualizamos el estado del ip en IP/Redes
                        if ($changeIP) { //esta cambiando de IP
                            //desactivamos la IP anterior
                            $usedip->is_used_ip($oldtarget, $data['client_id'], false);
                            //activamos la nueva IP
                            $usedip->is_used_ip($data['newtarget'], $data['client_id'], true);
                        } else { //no esta cambiando la ip pero actualizamos el estado.
                            $usedip->refresh_ip($oldtarget, $data['client_id']);
                        }

                    } else {
                        return Response::json([['msg' => 'errorConnect']]);
                    }

                    $upadeclient->update($data);

                    return $process->show('success');

                    break;

                case 'pp': //control PPP

                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                        //get gateway for addres
                        $network = Network::where('ip', $data['newtarget'])->get();

                        if (count($network) == 0) {
                            return $process->show('error_no_address');
                        }

                        $gat = AddressRouter::find($network[0]->address_id);

                        $rocket = new RocketCore();

                        $UPDATE = $rocket->update_ppp_user($API, $data, $oldtarget, $data['newtarget'], $gat->gateway, $debug);

                        if ($debug == 1) {
                            if ($process->check($UPDATE)) {
                                $API->disconnect();
                                return $process->check($UPDATE);
                            }
                        }

                        //actualizamos el estado del ip en IP/Redes
                        if ($changeIP) { //esta cambiando de IP
                            //desactivamos la IP anterior
                            $usedip->is_used_ip($oldtarget, $data['client_id'], false);
                            //activamos la nueva IP
                            $usedip->is_used_ip($data['newtarget'], $data['client_id'], true);
                        } else { //no esta cambiando la ip pero actualizamos el estado.
                            $usedip->refresh_ip($oldtarget, $data['client_id']);
                        }
                    } else {
                        return Response::json([['msg' => 'errorConnect']]);
                    }

                    $upadeclient->update($data);
                    return $process->show('success');

                    break;
                case 'pa': //control ppp pcq

                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                        //get gateway for addres
                        $network = Network::where('ip', $data['newtarget'])->get();

                        if (count($network) == 0) {
                            return $process->show('error_no_address');
                        }

                        $gat = AddressRouter::find($network[0]->address_id);

                        $rocket = new RocketCore();

                        $UPDATE = $rocket->update_ppp_secrets_pcq($API, $data, $oldtarget, $data['newtarget'], $gat->gateway, $debug);

                        if ($debug == 1) {
                            if ($process->check($UPDATE)) {
                                $API->disconnect();
                                return $process->check($UPDATE);
                            }
                        }

                        //actualizamos el estado del ip en IP/Redes
                        if ($changeIP) { //esta cambiando de IP
                            //desactivamos la IP anterior
                            $usedip->is_used_ip($oldtarget, $data['client_id'], false);
                            //activamos la nueva IP
                            $usedip->is_used_ip($data['newtarget'], $data['client_id'], true);
                        } else { //no esta cambiando la ip pero actualizamos el estado.
                            $usedip->refresh_ip($oldtarget, $data['client_id']);
                        }

                    } else {
                        return Response::json([['msg' => 'errorConnect']]);
                    }

                    $upadeclient->update($data);
                    return $process->show('success');

                    break;
                case 'pc':
                    //Verificamos el tipo de control solo la versión Pro puede utilizar PCQ

                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                        $rocket = new RocketCore();

                        $UPDATE = $rocket->update_pcq_list($API, $data, $oldtarget, $data['newtarget'], $debug);

                        if ($debug == 1) {
                            if ($process->check($UPDATE)) {
                                $API->disconnect();
                                return $process->check($UPDATE);
                            }
                        }

                        //actualizamos el estado del ip en IP/Redes
                        if ($changeIP) { //esta cambiando de IP
                            //desactivamos la IP anterior
                            $usedip->is_used_ip($oldtarget, $data['client_id'], false);
                            //activamos la nueva IP
                            $usedip->is_used_ip($data['newtarget'], $data['client_id'], true);
                        } else { //no esta cambiando la ip pero actualizamos el estado.
                            $usedip->refresh_ip($oldtarget, $data['client_id']);
                        }

                    } else {
                        return Response::json([['msg' => 'errorConnect']]);
                    }

                    $upadeclient->update($data);
                    return $process->show('success');

                    break;

                case 'nc': //Sin control mikrotik

                    $upadeclient->update($data);
                    //actualizamos el estado del ip en IP/Redes
                    if ($changeIP) { //esta cambiando de IP
                        //desactivamos la IP anterior
                        $usedip->is_used_ip($oldtarget, $data['client_id'], false);
                        //activamos la nueva IP
                        $usedip->is_used_ip($data['newtarget'], $data['client_id'], true);
                    } else { //no esta cambiando la ip pero actualizamos el estado.
                        $usedip->refresh_ip($oldtarget, $data['client_id']);
                    }

                    return $process->show('success');

                    break;
            } //end switch

        }//end if change

    }///////// fin del metodo //////////

    public function postBan(Request $request)
    {

        $process = new Chkerr();

        $client_id = $request->get('id');
        $client = Client::find($client_id);

        //obtenemos la ip del cliente
        $nameClient = $client->name;
        $target = $client->ip;
        $mac = $client->mac;
        $statusClient = $client->status;
        $router_id = $client->router_id;
        $userClient = $client->user_hot;

        $pl = new GetPlan();
        $plan = $pl->get($client->plan_id);
        $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

        $namePlan = $plan['name'];
        $maxlimit = $plan['maxlimit'];

        $config = ControlRouter::where('router_id', '=', $router_id)->get();

        $typeconf = $config[0]->type_control;
        $arp = $config[0]->arpmac;
        $advs = $config[0]->adv;
        $dhcp = $config[0]->dhcp;

        if ($advs == 1) {
            $drop = 0;
        } else {
            $drop = 1;
        }


        $router = new RouterConnect();
        $con = $router->get_connect($router_id);
        $log = new Slog();

        $data = array(
            'name' => $nameClient,
            'user' => $userClient,
            'status' => $statusClient,
            'arp' => $arp,
            'adv' => $advs,
            'drop' => $drop,
            'planName' => $namePlan,
            'namePlan' => $plan['name'],
            'mac' => $mac,
            'lan' => $con['lan'],
            //for simple queue with tree
            'plan_id' => $client->plan_id,
            'download' => $plan['download'],
            'upload' => $plan['upload'],
            'maxlimit' => $plan['maxlimit'],
            'aggregation' => $plan['aggregation'],
            'limitat' => $plan['limitat'],
            'bl' => $burst['blu'] . '/' . $burst['bld'],
            'bth' => $burst['btu'] . '/' . $burst['btd'],
            'bt' => $plan['burst_time'] . '/' . $plan['burst_time'],
            'burst_limit' => $plan['burst_limit'],
            'burst_threshold' => $plan['burst_threshold'],
            'burst_time' => $plan['burst_time'],
            'priority' => $plan['priority'] . '/' . $plan['priority'],
            'comment' => 'SmartISP - ' . $plan['name']

        );

        $counter = new CountClient();

        if ($typeconf == 'nc') {

            $STATUS = Client::find($client_id);

            if ($STATUS->status == 'ac') {
                $st = 'de';
                $online = 'off';
                $m = "Se ha cortado el servicio al cliente: ";
                //descontamos el numero de clientes del plan
                $counter->step_down_plan($client->plan_id);
            } else {
                $st = 'ac';
                $online = 'on';
                $m = "Se ha activado el servicio al cliente: ";
                //incrementamos el numero de clientes en el plan
                $counter->step_up_plan($client->plan_id);
            }

            $client->status = $st;
            $client->online = $online;
            $client->save();
            $log->save($m, "change", $nameClient);

            if ($STATUS == 'ac')
                return $process->show('banned');
            else
                return $process->show('unbanned');
        }


        $global = GlobalSetting::all()->first();
        $debug = $global->debug;

        if ($typeconf == 'no') {

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            //Bloqueamos simple
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                $rocket = new RocketCore();
                $error = new Mkerror();

                if ($data['status'] == 'ac') { //esta activo bloqueamos

                    $STATUS = $rocket->set_basic_config($API, $error, $data, $target, null, 'block', $debug);

                    if ($debug == 1) {
                        if ($STATUS != false) {
                            return $STATUS;
                        }
                    }

                    $STATUS = 'true';

                } else {//esta bloqueado activamos

                    $STATUS = $rocket->set_basic_config($API, $error, $data, $target, null, 'unblock', $debug);

                    if ($debug == 1) {
                        if ($STATUS != false) {
                            return $STATUS;
                        }
                    }

                    $STATUS = 'false';
                }

                $API->disconnect();


                if ($STATUS == 'true' || $STATUS == 'ac') {
                    $st = 'de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st = 'ac';
                    $online = 'ver';
                    $m = "Se ha activado el servicio al cliente: ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

                //guardamos en la base de datos
                $client->status = $st;
                $client->online = $online;
                $client->save();

                $log->save($m, "change", $nameClient);

                if ($STATUS == 'true' || $STATUS == 'ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');


            } else
                return $process->show('errorConnect');

        }


        if ($typeconf == 'sq') {

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            //Bloqueamos simple
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $STATUS = $rocket->block_simple_queues($API, $data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();


                if ($STATUS == 'true' || $STATUS == 'ac') {
                    $st = 'de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st = 'ac';
                    $online = 'ver';
                    $m = "Se ha activado el servicio al cliente: ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

                //guardamos en la base de datos
                $client->status = $st;
                $client->online = $online;
                $client->save();
                $log->save($m, "change", $nameClient);

                if ($STATUS == 'true' || $STATUS == 'ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');

            } else
                return $process->show('errorConnect');


        }

        if ($typeconf == 'st') {

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            //Bloqueamos simple
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                if ($statusClient == 'ac') {
                    $st = 'de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st = 'ac';
                    $online = 'ver';
                    $m = "Se ha activado el servicio al cliente: ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

                //guardamos en la base de datos
                $client->status = $st;
                $client->online = $online;
                $client->save();
                $log->save($m, "change", $nameClient);

                $rocket = new RocketCore();

                $STATUS = $rocket->block_simple_queue_with_tree($API, $data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();

                if ($STATUS == 'true' || $STATUS == 'ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');

            } else
                return $process->show('errorConnect');


        }


        if ($typeconf == 'ho') {
            //bloqueamos hotspot

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                $rocket = new RocketCore();

                $STATUS = $rocket->block_hotspot($API, $data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }


                $API->disconnect();


                if ($STATUS == 'true' || $STATUS == 'ac') {
                    $st = 'de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st = 'ac';
                    $online = 'ver';
                    $m = "Se ha activado el servicio al cliente: ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }
                //guardamos en la base de datos
                $client->status = $st;
                $client->online = $online;
                $client->save();
                $log->save($m, "change", $nameClient);

                if ($STATUS == 'true' || $STATUS == 'ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');

            } else
                return $process->show('errorConnect');
        }


        if ($typeconf == 'ha') { //Hotspot PCQ

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $num_cli = Helpers::getnumcl($router_id, $typeconf, $client->plan_id);

                //opcion avanzada burst del plan
                $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

                $advanced_data = array(

                    'name' => $nameClient,
                    'user' => $userClient,
                    'status' => $statusClient,
                    'arp' => $arp,
                    'adv' => $advs,
                    'dhcp' => $dhcp,
                    'drop' => $drop,
                    'mac' => $mac,
                    'lan' => $con['lan'],
                    'namePlan' => $namePlan,
                    'num_cl' => $num_cli,
                    'speed_down' => $plan['download'],
                    'speed_up' => $plan['upload'],
                    //advanced for pcq
                    'priority_a' => $plan['priority'],
                    'rate_down' => $plan['download'] . 'k',
                    'rate_up' => $plan['upload'] . 'k',
                    'burst_rate_down' => $burst['bld'],
                    'burst_rate_up' => $burst['blu'],
                    'burst_threshold_down' => $burst['btd'],
                    'burst_threshold_up' => $burst['btu'],
                    'limit_at_down' => $burst['lim_at_down'],
                    'limit_at_up' => $burst['lim_at_up'],
                    'burst_time' => $plan['burst_time'],
                );

                $STATUS = $rocket->block_hotspot_pcq($API, $advanced_data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();

                if ($STATUS == 'true' || $STATUS == 'ac') {
                    $st = 'de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st = 'ac';
                    $online = 'ver';
                    $m = "Se ha activado el servicio al cliente: ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }
                //guardamos en la base de datos
                $client->status = $st;
                $client->online = $online;
                $client->save();
                $log->save($m, "change", $nameClient);

                if ($STATUS == 'true' || $STATUS == 'ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');

            } else
                return $process->show('errorConnect');

        }

        if ($typeconf == 'dl') {
            //bloqueamos hotspot

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                $rocket = new RocketCore();

                $STATUS = $rocket->block_dhcp_lease($API, $data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }


                $API->disconnect();


                if ($STATUS == 'true' || $STATUS == 'ac') {
                    $st = 'de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st = 'ac';
                    $online = 'ver';
                    $m = "Se ha activado el servicio al cliente: ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }
                //guardamos en la base de datos
                $client->status = $st;
                $client->online = $online;
                $client->save();
                $log->save($m, "change", $nameClient);

                if ($STATUS == 'true' || $STATUS == 'ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');

            } else
                return $process->show('errorConnect');
        }


        if ($typeconf == 'pt') {
            //bloqueamos pppoe simple queue with tree

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                $rocket = new RocketCore();

                $STATUS = $rocket->block_ppp($API, $data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }


                if ($STATUS == 'true' || $STATUS == 'ac') {
                    $st = 'de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st = 'ac';
                    $online = 'ver';
                    $m = "Se ha activado el servicio al cliente: ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }
                //guardamos en la base de datos
                $client->status = $st;
                $client->online = $online;
                $client->save();

                $log->save($m, "change", $nameClient);


                $STATUS = $rocket->block_simple_queue_with_tree($API, $data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();


                if ($STATUS == 'true' || $STATUS == 'ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');

            } else
                return $process->show('errorConnect');
        }


        if ($typeconf == 'pp' || $typeconf == 'ps') {
            //bloqueamos pppoe

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                $rocket = new RocketCore();

                $STATUS = $rocket->block_ppp($API, $data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }


                $API->disconnect();


                if ($STATUS == 'true' || $STATUS == 'ac') {
                    $st = 'de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st = 'ac';
                    $online = 'ver';
                    $m = "Se ha activado el servicio al cliente: ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }
                //guardamos en la base de datos
                $client->status = $st;
                $client->online = $online;
                $client->save();
                $log->save($m, "change", $nameClient);

                if ($STATUS == 'true' || $STATUS == 'ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');

            } else
                return $process->show('errorConnect');
        }


        if ($typeconf == 'pa') {
            //bloqueo PPP-PCQ
            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $num_cli = Helpers::getnumcl($router_id, $typeconf, $client->plan_id);
                //opcion avanzada burst del plan
                $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

                $advanced_data = array(

                    'name' => $nameClient,
                    'user' => $userClient,
                    'status' => $statusClient,
                    'arp' => $arp,
                    'adv' => $advs,
                    'dhcp' => $dhcp,
                    'drop' => $drop,
                    'mac' => $mac,
                    'lan' => $con['lan'],
                    'namePlan' => $namePlan,
                    'num_cl' => $num_cli,
                    'speed_down' => $plan['download'],
                    'speed_up' => $plan['upload'],
                    //advanced for pcq
                    'priority_a' => $plan['priority'],
                    'rate_down' => $plan['download'] . 'k',
                    'rate_up' => $plan['upload'] . 'k',
                    'burst_rate_down' => $burst['bld'],
                    'burst_rate_up' => $burst['blu'],
                    'burst_threshold_down' => $burst['btd'],
                    'burst_threshold_up' => $burst['btu'],
                    'limit_at_down' => $burst['lim_at_down'],
                    'limit_at_up' => $burst['lim_at_up'],
                    'burst_time' => $plan['burst_time'],
                );


                $STATUS = $rocket->block_ppp_secrets_pcq($API, $advanced_data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }


                $API->disconnect();


                if ($STATUS == 'true' || $STATUS == 'ac') {
                    $st = 'de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st = 'ac';
                    $online = 'ver';
                    $m = "Se ha activado el servicio al cliente: ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

                //guardamos en la base de datos
                $client->status = $st;
                $client->online = $online;
                $client->save();
                $log->save($m, "change", $nameClient);

                if ($STATUS == 'true' || $STATUS == 'ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');

            } else
                return $process->show('errorConnect');


        }

        if ($typeconf == 'pc') {
            //bloqueamos PCQ

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $num_cli = Helpers::getnumcl($router_id, $typeconf, $client->plan_id);
                //opcion avanzada burst del plan
                $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

                $advanced_data = array(

                    'name' => $nameClient,
                    'status' => $statusClient,
                    'arp' => $arp,
                    'adv' => $advs,
                    'dhcp' => $dhcp,
                    'drop' => $drop,
                    'mac' => $mac,
                    'lan' => $con['lan'],
                    'namePlan' => $namePlan,
                    'num_cl' => $num_cli,
                    'speed_down' => $plan['download'],
                    'speed_up' => $plan['upload'],
                    //advanced for pcq
                    'priority_a' => $plan['priority'],
                    'rate_down' => $plan['download'] . 'k',
                    'rate_up' => $plan['upload'] . 'k',
                    'burst_rate_down' => $burst['bld'],
                    'burst_rate_up' => $burst['blu'],
                    'burst_threshold_down' => $burst['btd'],
                    'burst_threshold_up' => $burst['btu'],
                    'limit_at_down' => $burst['lim_at_down'],
                    'limit_at_up' => $burst['lim_at_up'],
                    'burst_time' => $plan['burst_time'],

                );

                $STATUS = $rocket->block_pcq($API, $advanced_data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }


                $API->disconnect();


                if ($STATUS == 'true' || $STATUS == 'ac') {
                    $st = 'de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st = 'ac';
                    $online = 'ver';
                    $m = "Se ha activado el servicio al cliente: ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

                //guardamos en la base de datos
                $client->status = $st;
                $client->online = $online;
                $client->save();
                $log->save($m, "change", $nameClient);

                if ($STATUS == 'true' || $STATUS == 'ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');

            } else
                return $process->show('errorConnect');


        }


    }

    //metodo para bloquaer clientes
    public static function postBanNew($id_clientes)
    {

        $process = new Chkerr();

        $client_id = $id_clientes;
        $client = Client::find($client_id);

        //obtenemos la ip del cliente
        $nameClient = $client->name;
        $target = $client->ip;
        $mac = $client->mac;
        $statusClient = $client->status;
        $router_id = $client->router_id;
        $userClient = $client->user_hot;

        $pl = new GetPlan();
        $plan = $pl->get($client->plan_id);
        $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

        $namePlan = $plan['name'];
        $maxlimit = $plan['maxlimit'];

        $config = ControlRouter::where('router_id', '=', $router_id)->get();

        $typeconf = $config[0]->type_control;
        $arp = $config[0]->arpmac;
        $advs = $config[0]->adv;
        $dhcp = $config[0]->dhcp;

        if ($advs == 1) {
            $drop = 0;
        } else {
            $drop = 1;
        }

        $router = new RouterConnect();
        $con = $router->get_connect($router_id);
        $log = new Slog();

        $data = array(
            'name' => $nameClient,
            'user' => $userClient,
            'status' => $statusClient,
            'arp' => $arp,
            'adv' => $advs,
            'drop' => $drop,
            'planName' => $namePlan,
            'namePlan' => $plan['name'],
            'mac' => $mac,
            'lan' => $con['lan'],
            //for simple queue with tree
            'plan_id' => $client->plan_id,
            'download' => $plan['download'],
            'upload' => $plan['upload'],
            'maxlimit' => $plan['maxlimit'],
            'aggregation' => $plan['aggregation'],
            'limitat' => $plan['limitat'],
            'bl' => $burst['blu'] . '/' . $burst['bld'],
            'bth' => $burst['btu'] . '/' . $burst['btd'],
            'bt' => $plan['burst_time'] . '/' . $plan['burst_time'],
            'burst_limit' => $plan['burst_limit'],
            'burst_threshold' => $plan['burst_threshold'],
            'burst_time' => $plan['burst_time'],
            'priority' => $plan['priority'] . '/' . $plan['priority'],
            'comment' => 'SmartISP - ' . $plan['name']

        );

        $counter = new CountClient();

        if ($typeconf == 'nc') {

            $STATUS = Client::find($client_id);

            if ($STATUS->status == 'ac') {
                $st = 'de';
                $online = 'off';
                $m = "Se ha cortado el servicio al cliente: ";
                //descontamos el numero de clientes del plan
                $counter->step_down_plan($client->plan_id);
            } else {
                $st = 'ac';
                $online = 'on';
                $m = "Se ha activado el servicio al cliente: ";
                //incrementamos el numero de clientes en el plan
                $counter->step_up_plan($client->plan_id);
            }

            $client->status = $st;
            $client->online = $online;
            $client->save();
            $log->save($m, "change", $nameClient);

            if ($STATUS == 'ac') {
                return $process->show('banned');
            } else {
                return $process->show('unbanned');
            }

        }

        $global = GlobalSetting::all()->first();
        $debug = $global->debug;

        if ($typeconf == 'no') {

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            //Bloqueamos simple
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();
                $error = new Mkerror();

                if ($data['status'] == 'ac') { //esta activo bloqueamos

                    $STATUS = $rocket->set_basic_config($API, $error, $data, $target, null, 'block', $debug);

                    if ($debug == 1) {
                        if ($STATUS != false) {
                            return $STATUS;
                        }
                    }

                    $STATUS = 'true';

                } else { //esta bloqueado activamos

                    $STATUS = $rocket->set_basic_config($API, $error, $data, $target, null, 'unblock', $debug);

                    if ($debug == 1) {
                        if ($STATUS != false) {
                            return $STATUS;
                        }
                    }

                    $STATUS = 'false';
                }

                $API->disconnect();

                if ($STATUS == 'true' || $STATUS == 'ac') {
                    $st = 'de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st = 'ac';
                    $online = 'ver';
                    $m = "Se ha activado el servicio al cliente: ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

                //guardamos en la base de datos
                $client->status = $st;
                $client->online = $online;
                $client->save();

                $log->save($m, "change", $nameClient);

                if ($STATUS == 'true' || $STATUS == 'ac') {
                    return $process->show('banned');
                } else {
                    return $process->show('unbanned');
                }

            } else {
                return $process->show('errorConnect');
            }

        }

        if ($typeconf == 'sq') {

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            //Bloqueamos simple
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $STATUS = $rocket->block_simple_queues($API, $data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();

                if ($STATUS == 'true' || $STATUS == 'ac') {
                    $st = 'de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st = 'ac';
                    $online = 'ver';
                    $m = "Se ha activado el servicio al cliente: ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

                //guardamos en la base de datos
                $client->status = $st;
                $client->online = $online;
                $client->save();
                $log->save($m, "change", $nameClient);

                if ($STATUS == 'true' || $STATUS == 'ac') {
                    return $process->show('banned');
                } else {
                    return $process->show('unbanned');
                }

            } else {
                return $process->show('errorConnect');
            }

        }

        if ($typeconf == 'st') {

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            //Bloqueamos simple
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                if ($statusClient == 'ac') {
                    $st = 'de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st = 'ac';
                    $online = 'ver';
                    $m = "Se ha activado el servicio al cliente: ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

                //guardamos en la base de datos
                $client->status = $st;
                $client->online = $online;
                $client->save();
                $log->save($m, "change", $nameClient);

                $rocket = new RocketCore();

                $STATUS = $rocket->block_simple_queue_with_tree($API, $data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();

                if ($STATUS == 'true' || $STATUS == 'ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');

            }

        }

        if ($typeconf == 'ho') {
            //bloqueamos hotspot

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                $rocket = new RocketCore();

                $STATUS = $rocket->block_hotspot($API, $data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }


                $API->disconnect();


                if ($STATUS == 'true' || $STATUS == 'ac') {
                    $st = 'de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st = 'ac';
                    $online = 'ver';
                    $m = "Se ha activado el servicio al cliente: ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }
                //guardamos en la base de datos
                $client->status = $st;
                $client->online = $online;
                $client->save();
                $log->save($m, "change", $nameClient);

                if ($STATUS == 'true' || $STATUS == 'ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');

            } else
                return $process->show('errorConnect');
        }

        if ($typeconf == 'ha') { //Hotspot PCQ

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $num_cli = Helpers::getnumcl($router_id, $typeconf, $client->plan_id);

                //opcion avanzada burst del plan
                $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

                $advanced_data = array(

                    'name' => $nameClient,
                    'user' => $userClient,
                    'status' => $statusClient,
                    'arp' => $arp,
                    'adv' => $advs,
                    'dhcp' => $dhcp,
                    'drop' => $drop,
                    'mac' => $mac,
                    'lan' => $con['lan'],
                    'namePlan' => $namePlan,
                    'num_cl' => $num_cli,
                    'speed_down' => $plan['download'],
                    'speed_up' => $plan['upload'],
                    //advanced for pcq
                    'priority_a' => $plan['priority'],
                    'rate_down' => $plan['download'] . 'k',
                    'rate_up' => $plan['upload'] . 'k',
                    'burst_rate_down' => $burst['bld'],
                    'burst_rate_up' => $burst['blu'],
                    'burst_threshold_down' => $burst['btd'],
                    'burst_threshold_up' => $burst['btu'],
                    'limit_at_down' => $burst['lim_at_down'],
                    'limit_at_up' => $burst['lim_at_up'],
                    'burst_time' => $plan['burst_time'],
                );

                $STATUS = $rocket->block_hotspot_pcq($API, $advanced_data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();

                if ($STATUS == 'true' || $STATUS == 'ac') {
                    $st = 'de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st = 'ac';
                    $online = 'ver';
                    $m = "Se ha activado el servicio al cliente: ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }
                //guardamos en la base de datos
                $client->status = $st;
                $client->online = $online;
                $client->save();
                $log->save($m, "change", $nameClient);

                if ($STATUS == 'true' || $STATUS == 'ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');

            } else
                return $process->show('errorConnect');

        }

        if ($typeconf == 'dl') {
            //bloqueamos hotspot

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                $rocket = new RocketCore();

                $STATUS = $rocket->block_dhcp_lease($API, $data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }


                $API->disconnect();


                if ($STATUS == 'true' || $STATUS == 'ac') {
                    $st = 'de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st = 'ac';
                    $online = 'ver';
                    $m = "Se ha activado el servicio al cliente: ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }
                //guardamos en la base de datos
                $client->status = $st;
                $client->online = $online;
                $client->save();
                $log->save($m, "change", $nameClient);

                if ($STATUS == 'true' || $STATUS == 'ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');

            } else
                return $process->show('errorConnect');
        }

        if ($typeconf == 'pt') {
            //bloqueamos pppoe simple queue with tree

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                $rocket = new RocketCore();

                $STATUS = $rocket->block_ppp($API, $data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }


                if ($STATUS == 'true' || $STATUS == 'ac') {
                    $st = 'de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st = 'ac';
                    $online = 'ver';
                    $m = "Se ha activado el servicio al cliente: ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }
                //guardamos en la base de datos
                $client->status = $st;
                $client->online = $online;
                $client->save();

                $log->save($m, "change", $nameClient);


                $STATUS = $rocket->block_simple_queue_with_tree($API, $data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();


                if ($STATUS == 'true' || $STATUS == 'ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');

            } else
                return $process->show('errorConnect');
        }

        if ($typeconf == 'pp' || $typeconf == 'ps') {
            //bloqueamos pppoe

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                $rocket = new RocketCore();

                $STATUS = $rocket->block_ppp($API, $data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }


                $API->disconnect();


                if ($STATUS == 'true' || $STATUS == 'ac') {
                    $st = 'de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st = 'ac';
                    $online = 'ver';
                    $m = "Se ha activado el servicio al cliente: ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }
                //guardamos en la base de datos
                $client->status = $st;
                $client->online = $online;
                $client->save();
                $log->save($m, "change", $nameClient);

                if ($STATUS == 'true' || $STATUS == 'ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');

            } else
                return $process->show('errorConnect');
        }

        if ($typeconf == 'pa') {
            //bloqueo PPP-PCQ
            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            //$API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $num_cli = Helpers::getnumcl($router_id, $typeconf, $client->plan_id);
                //opcion avanzada burst del plan
                $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

                $advanced_data = array(

                    'name' => $nameClient,
                    'user' => $userClient,
                    'status' => $statusClient,
                    'arp' => $arp,
                    'adv' => $advs,
                    'dhcp' => $dhcp,
                    'drop' => $drop,
                    'mac' => $mac,
                    'lan' => $con['lan'],
                    'namePlan' => $namePlan,
                    'num_cl' => $num_cli,
                    'speed_down' => $plan['download'],
                    'speed_up' => $plan['upload'],
                    //advanced for pcq
                    'priority_a' => $plan['priority'],
                    'rate_down' => $plan['download'] . 'k',
                    'rate_up' => $plan['upload'] . 'k',
                    'burst_rate_down' => $burst['bld'],
                    'burst_rate_up' => $burst['blu'],
                    'burst_threshold_down' => $burst['btd'],
                    'burst_threshold_up' => $burst['btu'],
                    'limit_at_down' => $burst['lim_at_down'],
                    'limit_at_up' => $burst['lim_at_up'],
                    'burst_time' => $plan['burst_time'],
                );

                $STATUS = $rocket->block_ppp_secrets_pcq($API, $advanced_data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();

                if ($STATUS == 'true' || $STATUS == 'ac') {
                    $st = 'de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st = 'ac';
                    $online = 'ver';
                    $m = "Se ha activado el servicio al cliente: ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

                //guardamos en la base de datos
                $client->status = $st;
                $client->online = $online;
                $client->save();
                $log->save($m, "change", $nameClient);

                if ($STATUS == 'true' || $STATUS == 'ac') {
                    return $process->show('banned');
                } else {
                    return $process->show('unbanned');
                }

            } else {
                return $process->show('errorConnect');
            }

        }

        if ($typeconf == 'pc') {
            //bloqueamos PCQ

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            // $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $num_cli = Helpers::getnumcl($router_id, $typeconf, $client->plan_id);
                //opcion avanzada burst del plan
                $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

                $advanced_data = array(

                    'name' => $nameClient,
                    'status' => $statusClient,
                    'arp' => $arp,
                    'adv' => $advs,
                    'dhcp' => $dhcp,
                    'drop' => $drop,
                    'mac' => $mac,
                    'lan' => $con['lan'],
                    'namePlan' => $namePlan,
                    'num_cl' => $num_cli,
                    'speed_down' => $plan['download'],
                    'speed_up' => $plan['upload'],
                    //advanced for pcq
                    'priority_a' => $plan['priority'],
                    'rate_down' => $plan['download'] . 'k',
                    'rate_up' => $plan['upload'] . 'k',
                    'burst_rate_down' => $burst['bld'],
                    'burst_rate_up' => $burst['blu'],
                    'burst_threshold_down' => $burst['btd'],
                    'burst_threshold_up' => $burst['btu'],
                    'limit_at_down' => $burst['lim_at_down'],
                    'limit_at_up' => $burst['lim_at_up'],
                    'burst_time' => $plan['burst_time'],

                );

                $STATUS = $rocket->block_pcq($API, $advanced_data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();

                if ($STATUS == 'true' || $STATUS == 'ac') {
                    $st = 'de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st = 'ac';
                    $online = 'ver';
                    $m = "Se ha activado el servicio al cliente: ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

                //guardamos en la base de datos
                $client->status = $st;
                $client->online = $online;
                $client->save();
                $log->save($m, "change", $nameClient);

                if ($STATUS == 'true' || $STATUS == 'ac') {
                    return $process->show('banned');
                } else {
                    return $process->show('unbanned');
                }

            } else {
                return $process->show('errorConnect');
            }

        }

    }

    public function editBalance($id)
    {
        $this->client = Client::find($id);
        return view('clients.update-balance', $this->data);
    }

    public function editCortadoDate($id)
    {
        $this->client = Client::find($id);
        $global = GlobalSetting::first();
        $billing_settings = $this->client->billing_settings;
        $this->serviceCutDate = Carbon::parse(\App\Service\CommonService::getCortadoDateWithTolerence($this->client->id, $billing_settings->billing_grace_period, $global->tolerance));

        return view('clients.change-servicecut-date', $this->data);
    }

    public function updateCortado(CortadoChangeRequest $request, $id)
    {
        $this->client = Client::find($id);
        $this->cortadoDate = \App\Service\CommonService::getServiceCortadoDate($this->client->id);
        $cortadoDate = Carbon::createFromFormat('d-m-Y', $request->cortado_date);

        if(isset($this->cortadoDate['invoiceId'])) {
            $invoice = BillCustomer::find($this->cortadoDate['invoiceId']);

            $newCortadoDate = Carbon::createFromFormat('d-m-Y', $request->cortado_date)->subDays($this->global->tolerance  + $this->client->billing_settings->billing_grace_period);
            $invoice->cortado_date = $newCortadoDate;

            $invoice->save();

            $clientServiceController = new ClientServiceController();

            if(Carbon::now()->startOfDay()->greaterThan($cortadoDate->startOfDay()) && $this->client->balance < 0) {
                foreach($this->client->service as $service) {
                    if($service->status == 'ac') {
                        $request = new Request([
                            'id' => $service->id,
                        ]);
                        $clientServiceController->postBanService($request, $service->id);
                    }
                    $service->status = 'de';
                    $service->save();
                }
            }

            if(Carbon::now()->startOfDay()->lessThanOrEqualTo($cortadoDate->startOfDay()) || $this->client->balance >= 0) {
                foreach($this->client->service as $service) {
                    if($service->status == 'de') {
                        $request = new Request([
                            'id' => $service->id,
                        ]);
                        $clientServiceController->postBanService($request, $service->id);
                    }
                    $service->status = 'ac';
                    $service->save();
                }
            }
        }
        return Reply::success('Successfully updated');
    }

    public function updateBalance(UpdateBalanceRequest $request, $id)
    {
        $this->client = Client::find($id);
        $this->client->wallet_balance = $request->balance;
        $this->client->save();

	    CommonService::log("Se actualiza el saldo del cliente:", $this->username, 'success' , $this->userId, $this->client->id);

        return Reply::success('Balance updated successfully.');
    }

    public function editPendingInvoice($id)
    {
        $this->client = Client::find($id);
        return view('clients.update-pending-invoice', $this->data);
    }

    public function updatePendingInvoice(UpdateBalanceRequest $request, $id)
    {
        $this->client = Client::find($id);
        $this->client->balance = $request->balance;
        $this->client->save();

        return Reply::success('Balance updated successfully.');
    }


    public function filterTotals(Request $request)
    {

	    $users = Client::leftJoin('client_services', 'client_services.client_id', '=', 'clients.id')
		    ->leftJoin('odb_splitter', 'odb_splitter.id', '=', 'clients.odb_id')
		    ->leftJoin('zone', 'zone.id', '=', 'clients.zona_id')
		    ->leftJoin('onu_type', 'onu_type.id', '=', 'clients.onu_id')
		    ->leftJoin('billing_settings', 'billing_settings.client_id', '=', 'clients.id')
		    ->leftJoin('routers', 'routers.id', '=', 'client_services.router_id')
		    ->leftJoin('control_routers', 'routers.id', '=', 'control_routers.router_id')
		    ->leftJoin('plans', 'plans.id', '=', 'client_services.plan_id');

	    if($request->control != 'all' ||
		    $request->client_status == 'active' ||
		    $request->client_status == 'inactive' ||
		    $request->client_name != '' ||
		    $request->ip_filter != '' ||
		    $request->router != 'all' ||
		    $request->plan != 'all' ||
		    $request->online != 'all' ||
		    $request->status != 'all' ||
		    $request->expiration ||
		    $request->cut
	    ) {
		    $users = $users->leftJoin('bill_customers as invoices', 'invoices.client_id', '=', 'clients.id');
	    }

        if ($request->control != 'all') {
            $users = $users->where('control_routers.type_control', $request->control);
        }

        if ($request->client_status == 'active') {
            $users = $users->whereHas('service');
        }

        if ($request->client_status == 'inactive') {
            $users = $users->doesntHave('service');
        }

        if ($request->client_name != '') {
            $users = $users->whereRaw(
                "REPLACE(clients.name,' ','') like ?", ['%' . str_replace(' ', '', $request->client_name) . '%']
            );
        }

        if ($request->ip_filter != '') {
            $users = $users->where('client_services.ip', 'like', "%$request->ip_filter%");
        }

        if ($request->router != 'all') {
            $users = $users->where('routers.id', $request->router);
        }

        if ($request->plan != 'all') {
            $users = $users->where('plans.id', $request->plan);
        }

        if ($request->online != 'all') {
            $users = $users->where('client_services.online', $request->online);
        }

        if ($request->status != 'all') {
            $users = $users->where('client_services.status', $request->status);
        }

        if ($request->expiration) {
            $users = $users->whereDate('suspend.expiration', '=', Carbon::parse($request->expiration));
        }

        if ($request->cut) {
            $cutDate = Carbon::parse($request->cut)->format('Y-m-d');
            $users = $users->whereDate('suspend.expiration', '=', DB::raw('DATE_SUB("' . $cutDate . '", INTERVAL (billing_settings.billing_grace_period + ' . $this->global->tolerance . ') DAY)'));
        }

        $users = $users->select('clients.id', 'clients.name',
            'clients.balance', 'billing_settings.billing_grace_period', 'zone.name as zone', 'odb_splitter.name as odb_id', 'onu_type.onutype as onu_id', 'clients.onusn')
            ->with('service', 'service.router', 'service.router.control_router', 'service.plan')
            ->groupBy('clients.id')->get();


        $blocked = Client::select('clients.id as client_id')
            ->join('client_services', 'clients.id', '=', 'client_services.client_id')
            ->where('client_services.status', '=', 'de')
            ->groupBy('client_id')
            ->get()->count();

	    $active = Client::select('clients.id as client_id')
            ->join('client_services', 'clients.id', '=', 'client_services.client_id')
            ->where('client_services.status', '=', 'ac')
            ->groupBy('client_id')
            ->get()->count();

        $inActive = 0;

        foreach ($users as $user) {
            if ($user->service->count() <= 0) {
                $inActive++;
            }
        }

        return Reply::dataOnly(['data' => [
            'active' => $active,
            'inactive' => $inActive,
            'blocked' => $blocked,
            'all' => $active + $inActive + $blocked
        ]]);
    }

    public function exportToExcel(Request $request)
    {
        $users = Client::leftJoin('client_services', 'client_services.client_id', '=', 'clients.id')
            ->leftJoin('odb_splitter', 'odb_splitter.id', '=', 'clients.odb_id')
            ->leftJoin('zone', 'zone.id', '=', 'clients.zona_id')
            ->leftJoin('onu_type', 'onu_type.id', '=', 'clients.onu_id')
            ->leftJoin('billing_settings', 'billing_settings.client_id', '=', 'clients.id')
            ->leftJoin('routers', 'routers.id', '=', 'client_services.router_id')
            ->leftJoin('control_routers', 'routers.id', '=', 'control_routers.router_id')
            ->leftJoin('suspend_clients as suspend', 'suspend.client_id', '=', 'client_services.client_id')
            ->leftJoin('plans', 'plans.id', '=', 'client_services.plan_id');

        if ($request->control != 'all') {
            $users = $users->where('control_routers.type_control', $request->control);
        }

        if ($request->client_status == 'active') {
            $users = $users->whereHas('service');
        }

        if ($request->client_status == 'inactive') {
            $users = $users->doesntHave('service');
        }

        if ($request->client_name != '') {
            $users = $users->whereRaw(
                "REPLACE(clients.name,' ','') like ?", ['%' . str_replace(' ', '', $request->client_name) . '%']
            );
        }

        if ($request->ip_filter != '') {
            $users = $users->where('client_services.ip', 'like', "%$request->ip_filter%");
        }

        if ($request->router != 'all') {
            $users = $users->where('routers.id', $request->router);
        }

        if ($request->plan != 'all') {
            $users = $users->where('plans.id', $request->plan);
        }

        if ($request->online != 'all') {
            $users = $users->where('client_services.online', $request->online);
        }

        if ($request->status != 'all') {
            $users = $users->where('client_services.status', $request->status);
        }

        if ($request->expiration) {
            $users = $users->whereDate('suspend.expiration', '=', Carbon::parse($request->expiration));
        }

        if ($request->cut) {
            $cutDate = Carbon::parse($request->cut)->format('Y-m-d');
            $users = $users->whereDate('suspend.expiration', '=', DB::raw('DATE_SUB("' . $cutDate . '", INTERVAL (billing_settings.billing_grace_period + ' . $this->global->tolerance . ') DAY)'));
        }

        $users = $users->select('clients.id', 'clients.name',
            'clients.balance', 'billing_settings.billing_grace_period', 'zone.name as zone', 'odb_splitter.name as odb_id', 'onu_type.onutype as onu_id', 'clients.onusn')
            ->with('service', 'service.router', 'service.router.control_router', 'service.plan')->groupBy('clients.id')->get();

        $columnsToExport = [
            'name',
            'service.ip',
            'online',
            'control',
            'tp',
            'expiration',
            'cut',
            'plan_name',
            'status',
            'balance',
            'mac',
            'router',
            'zone',
            'punto_emision',
            'odb_id',
            'onu_id',
            'onusn',
        ];

        return DataTableExport::of($users)
            ->columns($columnsToExport)
            ->export('csv');
    }


	public function getPlanClients(PlanClientDataTable $dataTable, $planId)
	{
		$id = Auth::user()->id;
		$campos_v = campos_view_client::find(1);
		$level = Auth::user()->level;
		$perm = DB::table('permissions')->where('user_id', '=', $id)->get();
		$access = $perm[0]->access_clients;
		//consulta para llenar los select del form cliente
		$municipio = DB::table('municipio')
			->Join('departamento', 'departamento.cod', '=', 'municipio.departamento_cod')
			->select('municipio.Description AS Municipio', 'departamento.Description AS Departamento', 'municipio.cod')->get();
		$punto_emision = PuntoEmision::all();
		$typedoc = Typedoc::all();
		$typetaxpayer = Typetaxpayer::all();
		$accountingregime = Accountingregime::all();
		$typeresponsibility = Typeresponsibility::all();
		$economicactivity = Economicactivity::all();
		$falctelStatus = Factel::all()->first()->status;
		//control permissions only access super administrator (sa)
		if ($level == 'ad' || $access == true) {
			$global = GlobalSetting::all()->first();

			$GoogleMaps = Helpers::get_api_options('googlemaps');

			if (count($GoogleMaps) > 0) {
				$key = $GoogleMaps['k'];
			} else {
				$key = 0;
			}
			$OdbSplitter = OdbSplitter::all();
			$OnuType = OnuType::all();
			$Zone = Zone::all();

			$allRouters = Router::all();
			$allPlans = Plan::all();

			$permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
				"users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
				"template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
				"reports" => $perm[0]->access_reports,
				"v" => $global->version, "st" => $global->status, "map" => $key,
				"lv" => $global->license, "company" => $global->company,
				"global" => $global,
				'permissions' => $perm->first(),
				'allRouters' => $allRouters,
				'allPlans' => $allPlans,
				// menu options
				"campos_v" => $campos_v, "OdbSplitter" => $OdbSplitter,
				"OnuType" => $OnuType, "Zone" => $Zone, "falctelStatus" => $falctelStatus, "cmbtypedoc" => (!empty($typedoc)) ? $typedoc : '', "cmbmunicipio" => (!empty($municipio)) ? $municipio : '',
				"cmbtypetaxpayer" => (!empty($typetaxpayer)) ? $typetaxpayer : '', "cmbtypetaxpayer" => (!empty($typetaxpayer)) ? $typetaxpayer : '',
				"punto_emision" => $punto_emision,
				"cmbtyperesponsibility" => (!empty($typeresponsibility)) ? $typeresponsibility : '', "cmbeconomicactivity" => (!empty($economicactivity)) ? $economicactivity : '',
				"planId" => $planId
			);

			if (Auth::user()->level == 'ad') {
				@setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);
			}

			return $dataTable->render('clients.plan-index', $permissions);
		} else {
			return Redirect::to('admin');
		}

	}


	public function planClientfilterTotals(Request $request, $planId)
	{

		$users = Client::join('client_services', 'client_services.client_id', '=', 'clients.id')
			->join('plans', 'plans.id', '=', 'client_services.plan_id')
			->join('billing_settings', 'billing_settings.client_id', '=', 'clients.id')
			->join('routers', 'routers.id', '=', 'client_services.router_id')
			->join('control_routers', 'routers.id', '=', 'control_routers.router_id')
			->leftJoin('odb_splitter', 'odb_splitter.id', '=', 'clients.odb_id')
			->leftJoin('zone', 'zone.id', '=', 'clients.zona_id')
			->leftJoin('onu_type', 'onu_type.id', '=', 'clients.onu_id');

		if($request->control != 'all' ||
			$request->client_status == 'active' ||
			$request->client_status == 'inactive' ||
			$request->client_name != '' ||
			$request->ip_filter != '' ||
			$request->router != 'all' ||
			$request->plan != 'all' ||
			$request->online != 'all' ||
			$request->status != 'all' ||
			$request->expiration ||
			$request->cut
		) {
			$users = $users->leftJoin('bill_customers as invoices', 'invoices.client_id', '=', 'clients.id');
		}

		$users = $users->where('client_services.plan_id', $planId)->where('client_services.status', 'ac');

		if ($request->control != 'all') {
			$users = $users->where('control_routers.type_control', $request->control);
		}

		if ($request->client_status == 'active') {
			$users = $users->whereHas('service');
		}

		if ($request->client_status == 'inactive') {
			$users = $users->doesntHave('service');
		}

		if ($request->client_name != '') {
			$users = $users->whereRaw(
				"REPLACE(clients.name,' ','') like ?", ['%' . str_replace(' ', '', $request->client_name) . '%']
			);
		}

		if ($request->ip_filter != '') {
			$users = $users->where('client_services.ip', 'like', "%$request->ip_filter%");
		}

		if ($request->router != 'all') {
			$users = $users->where('routers.id', $request->router);
		}

		if ($request->plan != 'all') {
			$users = $users->where('plans.id', $request->plan);
		}

		if ($request->online != 'all') {
			$users = $users->where('client_services.online', $request->online);
		}

		if ($request->status != 'all') {
			$users = $users->where('client_services.status', $request->status);
		}

		if ($request->expiration) {
			$users = $users->whereDate('suspend.expiration', '=', Carbon::parse($request->expiration));
		}

		if ($request->cut) {
			$cutDate = Carbon::parse($request->cut)->format('Y-m-d');
			$users = $users->whereDate('suspend.expiration', '=', DB::raw('DATE_SUB("' . $cutDate . '", INTERVAL (billing_settings.billing_grace_period + ' . $this->global->tolerance . ') DAY)'));
		}

		$users = $users->select('clients.id', 'clients.name',
			'clients.balance', 'billing_settings.billing_grace_period', 'zone.name as zone', 'odb_splitter.name as odb_id', 'onu_type.onutype as onu_id', 'clients.onusn')
			->with('service', 'service.router', 'service.router.control_router', 'service.plan')
			->groupBy('clients.id')->get();


//		$blocked = Client::select('clients.id as client_id')
//			->join('client_services', 'clients.id', '=', 'client_services.client_id')
//			->where('client_services.status', '=', 'de')
//			->where('client_services.plan_id', '=', $planId)
//			->groupBy('client_id')
//			->get()->count();

		$active = Client::select('clients.id as client_id')
			->join('client_services', 'clients.id', '=', 'client_services.client_id')
			->where('client_services.status', '=', 'ac')
			->where('client_services.plan_id', '=', $planId)
			->groupBy('client_id')
			->get()->count();

		$inActive = 0;
		$blocked = 0;
		foreach ($users as $user) {
			if ($user->service->count() <= 0) {
				$inActive++;
			}
		}

		return Reply::dataOnly(['data' => [
			'active' => $active,
			'inactive' => $inActive,
			'blocked' => $blocked,
			'all' => $active + $inActive + $blocked
		]]);
	}


	public function getRouterClients(RouterClientDataTable $dataTable, $routerId)
	{
		$id = Auth::user()->id;
		$campos_v = campos_view_client::find(1);
		$level = Auth::user()->level;
		$perm = DB::table('permissions')->where('user_id', '=', $id)->get();
		$access = $perm[0]->access_clients;
		//consulta para llenar los select del form cliente
		$municipio = DB::table('municipio')
			->Join('departamento', 'departamento.cod', '=', 'municipio.departamento_cod')
			->select('municipio.Description AS Municipio', 'departamento.Description AS Departamento', 'municipio.cod')->get();
		$punto_emision = PuntoEmision::all();
		$typedoc = Typedoc::all();
		$typetaxpayer = Typetaxpayer::all();
		$accountingregime = Accountingregime::all();
		$typeresponsibility = Typeresponsibility::all();
		$economicactivity = Economicactivity::all();
		$falctelStatus = Factel::all()->first()->status;
		//control permissions only access super administrator (sa)
		if ($level == 'ad' || $access == true) {
			$global = GlobalSetting::all()->first();

			$GoogleMaps = Helpers::get_api_options('googlemaps');

			if (count($GoogleMaps) > 0) {
				$key = $GoogleMaps['k'];
			} else {
				$key = 0;
			}
			$OdbSplitter = OdbSplitter::all();
			$OnuType = OnuType::all();
			$Zone = Zone::all();

			$allRouters = Router::all();
			$allPlans = Plan::all();

			$permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
				"users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
				"template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
				"reports" => $perm[0]->access_reports,
				"v" => $global->version, "st" => $global->status, "map" => $key,
				"lv" => $global->license, "company" => $global->company,
				"global" => $global,
				'permissions' => $perm->first(),
				'allRouters' => $allRouters,
				'allPlans' => $allPlans,
				// menu options
				"campos_v" => $campos_v, "OdbSplitter" => $OdbSplitter,
				"OnuType" => $OnuType, "Zone" => $Zone, "falctelStatus" => $falctelStatus, "cmbtypedoc" => (!empty($typedoc)) ? $typedoc : '', "cmbmunicipio" => (!empty($municipio)) ? $municipio : '',
				"cmbtypetaxpayer" => (!empty($typetaxpayer)) ? $typetaxpayer : '', "cmbtypetaxpayer" => (!empty($typetaxpayer)) ? $typetaxpayer : '',
				"punto_emision" => $punto_emision,
				"cmbtyperesponsibility" => (!empty($typeresponsibility)) ? $typeresponsibility : '', "cmbeconomicactivity" => (!empty($economicactivity)) ? $economicactivity : '',
				"routerId" => $routerId
			);

			if (Auth::user()->level == 'ad') {
				@setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);
			}

			return $dataTable->render('clients.router-index', $permissions);
		} else {
			return Redirect::to('admin');
		}

	}


	public function routerClientfilterTotals(Request $request, $routerId)
	{

		$users = Client::join('client_services', 'client_services.client_id', '=', 'clients.id')
			->join('plans', 'plans.id', '=', 'client_services.plan_id')
			->join('billing_settings', 'billing_settings.client_id', '=', 'clients.id')
			->join('routers', 'routers.id', '=', 'client_services.router_id')
			->join('control_routers', 'routers.id', '=', 'control_routers.router_id')
			->leftJoin('odb_splitter', 'odb_splitter.id', '=', 'clients.odb_id')
			->leftJoin('zone', 'zone.id', '=', 'clients.zona_id')
			->leftJoin('onu_type', 'onu_type.id', '=', 'clients.onu_id');

		if($request->control != 'all' ||
			$request->client_status == 'active' ||
			$request->client_status == 'inactive' ||
			$request->client_name != '' ||
			$request->ip_filter != '' ||
			$request->router != 'all' ||
			$request->plan != 'all' ||
			$request->online != 'all' ||
			$request->status != 'all' ||
			$request->expiration ||
			$request->cut
		) {
			$users = $users->leftJoin('bill_customers as invoices', 'invoices.client_id', '=', 'clients.id');
		}

		$users = $users->where('client_services.router_id', $routerId);

		if ($request->control != 'all') {
			$users = $users->where('control_routers.type_control', $request->control);
		}

		if ($request->client_status == 'active') {
			$users = $users->whereHas('service');
		}

		if ($request->client_status == 'inactive') {
			$users = $users->doesntHave('service');
		}

		if ($request->client_name != '') {
			$users = $users->whereRaw(
				"REPLACE(clients.name,' ','') like ?", ['%' . str_replace(' ', '', $request->client_name) . '%']
			);
		}

		if ($request->ip_filter != '') {
			$users = $users->where('client_services.ip', 'like', "%$request->ip_filter%");
		}

		if ($request->plan != 'all') {
			$users = $users->where('plans.id', $request->plan);
		}

		if ($request->online != 'all') {
			$users = $users->where('client_services.online', $request->online);
		}

		if ($request->status != 'all') {
			$users = $users->where('client_services.status', $request->status);
		}

		if ($request->expiration) {
			$users = $users->whereDate('suspend.expiration', '=', Carbon::parse($request->expiration));
		}

		if ($request->cut) {
			$cutDate = Carbon::parse($request->cut)->format('Y-m-d');
			$users = $users->whereDate('suspend.expiration', '=', DB::raw('DATE_SUB("' . $cutDate . '", INTERVAL (billing_settings.billing_grace_period + ' . $this->global->tolerance . ') DAY)'));
		}

		$users = $users->select('clients.id')
			->with('service', 'service.router', 'service.router.control_router', 'service.plan');


		$blocked = clone $users;

		$blocked = $blocked->where('client_services.status', '=', 'de')
			->groupBy('clients.id')
			->get()
			->count();

		$active = clone $users;
		$active = $active->where('client_services.status', '=', 'ac')
			->groupBy('clients.id')
			->get()
			->count();

		$users = $users->groupBy('clients.id')->get();

		$inActive = 0;

		foreach ($users as $user) {
			if ($user->service->count() <= 0) {
				$inActive++;
			}
		}

		return Reply::dataOnly(['data' => [
			'active' => $active,
			'inactive' => $inActive,
			'blocked' => $blocked,
			'all' => $active + $inActive + $blocked
		]]);
	}

    private function saveClient($client, $request)
    {
        $en = new Pencrypt();

        $client->name = $request->edit_name;
        $client->phone = $request->edit_phone;
        $client->email = $request->edit_email;
        $client->id_punto_emision = $request->edit_punto_emision;

        if ($request->has('edit_dni') && $request->edit_dni != '') {
            $client->dni = $request->edit_dni;
        }

        if ($request->has('edit_dir') && $request->edit_dir != '') {
            $client->address = $request->edit_dir;
        }

        if ($request->has('edit_odb_id') && $request->edit_odb_id != '') {
            $client->odb_id = $request->edit_odb_id;
        }

        if ($request->has('edit_onu_id') && $request->edit_onu_id != '') {
            $client->onu_id = $request->edit_onu_id;
        }

        if ($request->has('edit_zona_id') && $request->edit_zona_id != '') {
            $client->zona_id = $request->edit_zona_id;
        }

        if ($request->has('edit_pass2') && $request->edit_pass2 != '') {
            $client->password = $en->encode($request->edit_pass2);
        }

        if ($request->has('location_edit') && $request->location_edit != '') {
            $client->coordinates = $request->location_edit;
        }

        if ($request->has('edit_typedoc_cod') && $request->edit_typedoc_cod != '') {
            $client->typedoc_cod = $request->edit_typedoc_cod;
        }

        if ($request->has('edit_economicactivity_cod') && $request->edit_economicactivity_cod != '') {
            $client->economicactivity_cod = $request->edit_economicactivity_cod;
        }

        if ($request->has('edit_municipio_cod') && $request->edit_municipio_cod != '') {
            $client->municipio_cod = $request->edit_municipio_cod;
        }

        if ($request->has('edit_typeresponsibility_cod') && $request->edit_typeresponsibility_cod != '') {
            $client->typeresponsibility_cod = $request->edit_typeresponsibility_cod;
        }
        if ($request->has('edit_typetaxpayer_cod') && $request->edit_typetaxpayer_cod != '') {
            $client->typetaxpayer_cod = $request->edit_typetaxpayer_cod;
        }

        $client->save();

//	    $log->save("Se ha registrado un cliente:", "success", $nameClient);
    }

    public function updateCoordinates(int $id, Client $client, Request $request)
    {
        $rules = array(
            'coordinates' => 'required|string',
            'id' => 'exists:clients,id'
        );

        $request->merge(['id' => $id]);
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return Response::json(array(array('msg' => 'error', 'errors' => $validation->getMessageBag()->toArray())));
        }
        $client = $client->find($id);
        $client->coordinates = $request->coordinates;
        $client->save();

        Helpers::resetGeoJsonByClient($id);
    }

    public function updateMapMarkerIcon(int $id, Client $client, Request $request)
    {
        $rules = array(
            'map_marker_icon' => 'required|array',
            'id' => 'exists:clients,id'
        );

        $request->merge(['id' => $id]);
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return Response::json(array(array('msg' => 'error', 'errors' => $validation->getMessageBag()->toArray())));
        }
        $client = $client->find($id);
        $client->map_marker_icon = $request->map_marker_icon['type'] == 'image' ? null : $request->map_marker_icon;
        $client->save();
    }

    public function saveOdbGeoJson(int $id, Client $client, Request $request)
    {
        $rules = array(
            'geo_json' => 'required|array',
            'geo_json_styles' => 'nullable|array',
            'id' => 'exists:clients,id'
        );

        $request->merge(['id' => $id]);
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return Response::json(array(array('msg' => 'error', 'errors' => $validation->getMessageBag()->toArray())));
        }
        $client = $client->find($id);
        $client->odb_geo_json = $request->geo_json;
        $client->odb_geo_json_styles = $request->geo_json_styles
        ;
        $client->save();
    }
}

