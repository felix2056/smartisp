<?php

namespace App\Http\Controllers;

use App\Classes\Reply;
use App\DataTables\BanHistoryDataTable;
use App\Events\AddServiceClientEvent;
use App\Events\DeleteServiceClientEvent;
use App\Events\UpdateServiceClientEvent;
use App\Http\Requests\Service\StoreRequest;
use App\Http\Requests\Service\UpdateRequest;
use App\libraries\AddClient;
use App\libraries\Burst;
use App\libraries\Chkerr;
use App\libraries\CountClient;
use App\libraries\Firewall;
use App\libraries\GetPlan;
use App\libraries\Helpers;
use App\libraries\Intersep;
use App\libraries\Mikrotik;
use App\libraries\Mkerror;
use App\libraries\Pencrypt;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\libraries\Slog;
use App\libraries\StatusIp;
use App\libraries\UpdateClient;
use App\libraries\Validator;
use App\models\Client;
use App\models\ClientService;
use App\models\ControlRouter;
use App\models\CortadoReason;
use App\models\GlobalSetting;
use App\models\Plan;
use App\models\radius\Radgroupcheck;
use App\models\radius\Radusergroup;
use App\models\Router;
use App\models\SuspendClient;
use App\Service\CommonService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Yajra\DataTables\Facades\DataTables;
use Config;

class ClientServiceController extends BaseController
{
    protected $username = null;
    protected $userId = null;

	public function __construct()
	{

		parent::__construct();
		$this->middleware(function ($request, $next) {
			$this->username = auth()->user()->username;
			$this->userId = auth()->user()->id;
			return $next($request);
		});
	}

    public function index()
    {

    }

    public function create($client)
    {
        $this->client = $client;
        return view('clients/services/add-service', $this->data);
    }

    public function list(Request $request)
    {
        $services = ClientService::with('plan', 'client', 'router')->where('client_id', $request->client_id);
        return DataTables::of($services)
            ->addColumn('action', function ($row) {
                $eliminar = '';
                $editar = '';

                $styleb = '<div class="action-buttons">';
                if (PermissionsController::hasAnyRole('servicio_edit'))
                    $editar .= '<a class="green edit" href="javascript:;" onclick="edit('. $row->id .')" id="' . $row->id . '" title="' . __('app.edit') . '"><i class="ace-icon fa fa-pencil bigger-130"></i></a>';

                    if (PermissionsController::hasAnyRole('servicio_delete'))
                    $eliminar = '<a class="red deletes" href="#" id="' . $row->id . '" title="' . __('app.remove') . '"><i class="ace-icon fa fa-trash bigger-130"></i></a>';

	            $eliminar .= '<a class="blue banHistory" onclick="banHistory('.$row->id.')" title="' . __('app.banHistory') . '" href="#" id="' . $row->id . '"><i class="ace-icon fa fa-ban bigger-130"></i></a>';

	            /**si tiene smartolt**/
                $smartolt =  Helpers::get_api_options('smartolt');

                if(isset($smartolt['c']))
    	            $eliminar .= '<a class="blue banHistory" onclick="onusUnregistered('.$row->id.')" title="' . __('app.autorizar') . '" href="#" id="' . $row->id . '"><i class="ace-icon fa fa-globe bigger-130"></i></a>';


                if ($row->tp != 'nc') {
                    $info='';
                    $info='<a class="blue info" title="' . __('app.information') . '" href="#" id="' . $row->id . '"><i class="ace-icon fa fa-info-circle bigger-130"></i></a>';


                    if ($row->status == 'ac') {
                        $activo_s='';
                        $llave='';
                        $llave='<a class="grey tool" title="' . __('app.tools') . '" href="#" id="' . $row->id . '"><i class="ace-icon fa fa-wrench bigger-130"></i></a>';
                        if (PermissionsController::hasAnyRole('servicio_activate_desactivar')){
                            $activo_s='<a class="blue ban-service" href="#" id="' . $row->id . '" title="' . __('app.serviceCut') . '"><i class="ace-icon fa fa-adjust bigger-130"></i></a>';
                        }
                         return $styleb . $activo_s . $editar.$llave.$info.$eliminar.'</div>';

                    }
                    if ($row->status == 'de') {
                        $desactivo_s='';
                        if (PermissionsController::hasAnyRole('servicio_activate_desactivar'))
                        $desactivo_s='<a class="blue ban-service" href="#" id="' . $row->id . '" title="' . __('app.activate') . ' ' . __('app.service') . '"><i class="ace-icon fa fa-adjust bigger-130"></i></a>';

                        return $styleb . $desactivo_s.$info.$eliminar.'</div>';

                    }
                }

            })
            ->editColumn('plan.name', function ($row) {
                if ($row->plan->plan_name == 'Importados')
                    return '<span class="text-danger">' . $row->name . '</span>';

                return $row->plan->name;
            })
            ->editColumn('cost', function ($row) {
                return $row->plan->cost;
            })
            ->editColumn('name', function ($row) {
                return $row->router->name;
            })
            ->editColumn('date_in', function ($row) {
                return $row->date_in->format('Y-m-d');
            })
            ->editColumn('status', function ($row) {
                if ($row->status == 'ac')
                    return '<span class="label label-success arrowed">' . __('app.active') . '</span>';
                if ($row->status == 'de')
                    return '<span class="label label-danger">' . __('app.blocked') . '</span>';
            })
            ->rawColumns(['action', 'plan_name', 'status'])
            ->make(true);
    }

    public function store(StoreRequest $request, $clientId)
    {
        
        $client = Client::find($clientId);

        $requestData = $request->all();

        $process = new Chkerr();
        $router_id = $request->get('router');

        //obtenemos la configuracion del router typo de autenticacion
        $config = ControlRouter::where('router_id', '=', $router_id)->first();
        $typeconf = $config->type_control;
        $advs = $config->adv;
        $dhcp = $config->dhcp;
        $arp = $config->arpmac;

        $macClient = $request->get('mac', '00:00:00:00:00:00');

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

        if ( $typeconf == 'pp' || $typeconf == 'pa' || $typeconf == 'ps' || $typeconf == 'pt' || $typeconf == 'ra' || $typeconf == 'rp' || $typeconf == 'rr') {
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
        if ($typeconf == 'dl' || $arp == 1) {
            $macClient = $request->get('mac');
            if (empty($macClient) || $macClient == '00:00:00:00:00:00') {
                return $process->show('reqmac');
            }
            $user = '';
            $pass = '';
        }

        /// Fin de control de datos ////////////////////////////////////////////////////////////////

        //Recuperamos y preparamos todos los datos ////////////////////////////////////////////////

        //recuperamos información del cliente

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

        $num_cli = ClientService::where('plan_id', '=', $plan_id)->where('status', 'ac')->where('router_id', $router_id)->count(); //for pcq queue tree

        $router = new RouterConnect();
        $con = $router->get_connect($router_id);
        //preparando datos adicionales
        $data = array(
            //general data
            'client_id' => $client->id,
            'name' => $client->name,
            'address' => $request->get('ip'),
            'newtarget' => $request->get('ip'),
            'oldtarget' => $request->get('ip'),
            'mac' => $macClient,
            'arp' => $arp,
            'send_invoice' => $request->get('send_invoice'),
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
            'router_id' => $router_id,
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
        $global = GlobalSetting::first();

        //GET all data for API
        $conf = Helpers::get_api_options('mikrotik');
        //inicializacion de clases principales
        $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
        $API->debug = $conf['d'];
        //inicializamos el nucleo del sistema
        $process = new Chkerr();
//        if ($API->connect($con['ip'], $con['login'], $con['password'])) {
            $id = $Nclient->addService($data);
            $ip = $data['address'];
	    CommonService::log("Servicio al cliente en ip $ip agregado:", $this->username, 'success' , $this->userId, $data['client_id']);

	    $service = ClientService::with('client')->find($id);
            $data['tree_priority'] = $service->tree_priority;
            $data['ip'] = $service->ip;
            event(new AddServiceClientEvent($router_id, $service->id, $requestData, $data, auth()->user()->username));
            //mostramos mensaje de confirmación
            return $process->show('success');
//        } else {
//            return $process->show('errorConnect');
//        }


    }

    public function edit($id)
    {
        $this->service = ClientService::with('client', 'client.billing_settings')->find($id);

        $cortadoDate = CommonService::getCortadoDateWithTolerence($this->service->client->id, $this->service->client->billing_settings->billing_grace_period, $this->global->tolerance);

        $this->cortadoDate = Carbon::parse($cortadoDate)->format('d-m-Y');
        $this->type = ControlRouter::where('router_id', '=', $this->service->router_id)->first();

        return view('clients/services/edit-service', $this->data);
    }

    public function update(UpdateRequest $request, $id)
    {
        $client = ClientService::find($id);
        $process = new Chkerr();

        //find and get client
        $clientDetail = Client::find($client->client_id);

        //get type control router
        $type = ControlRouter::where('router_id', '=', $request->get('router'))->get();
        $arp = $type[0]->arpmac;
        $address_list = $type[0]->address_list;
        $macClient = $request->get('mac', '00:00:00:00:00:00');

        //control de dhcp
        if ($type[0]->dhcp == '1' || $arp == 1) {
            if ($macClient == '00:00:00:00:00:00' || $macClient == '') {
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
        if ($client->plan_id == $request->get('plan')) {
            $changePlan = false;
        } else {
            $changePlan = true;
        }

        //verificamos sis esta cambiando de ip
        if ($client->ip == $request->get('ip')) {
            $changeIP = false;
        } else {
            $changeIP = true;
        }

        $pl = new GetPlan();
        $plan = $pl->get($request->get('plan'));
        $planCost = $plan['cost'];

        $num_cli = ClientService::where('plan_id', '=', $request->get('plan'))->where('status', 'ac')->where('router_id', $request->get('router'))->count(); //for pcq queue tree

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
            'service_id' => $client->id,
            'name' => $clientDetail->name.'_'.$client->id,
            'mac' => $macClient,
            'arp' => $type[0]->arpmac,
            'send_invoice' => $request->get('send_invoice'),
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
            //end pcq
            'priority' => $priority,
            'comment' => 'SmartISP - '.$plan['name'],
            'ip' => $request->get('ip'),
            'pay_date' => date("Y-m-d", strtotime($request->get('edit_date_pay'))),
            'date_in' => date("Y-m-d", strtotime($request->get('date_in'))),
            'plan_id' => $request->get('plan'),
            'namePlan' => $plan['name'],
            'router_id' => $request->get('router'),
            'user' => Intersep::replace($request->get('edit_user'),$client->user_hot),
            'pass' => Intersep::replace($request->get('edit_pass'), $en->decode($client->pass_hot)),
            'old_user' => $client->user_hot,
            'pass2' => $request->get('edit_pass2'),
            'changePlan' => $changePlan,
            'changeRouter' => $changeRouter,
            'typeauth' => $request->get('auth'),
            'newtarget' => $request->get('ip'),
            'oldtarget' => $client->ip,
            'client_id' => $clientDetail->id,
            //'old_name' => $clientDetail->name,
            'old_name' => $clientDetail->name.'_'.$client->id,/**fix 01/06/2021**/
            'oldplan' => $client->plan_id,
            'old_router' => $client->router_id,
            'changeIP' => $changeIP,
            'profile' => $request->get('editprofile', false),
            'tree_priority' => $client->tree_priority,
	        'no_rules' => $plan['no_rules'],
        );
        //comprobamos si esta cambiando de router recuperamos datos anteriores

        $upadeclient = new UpdateClient();
        $oldServiceDetails = $client->toArray();
        
        $upadeclient->updateService($data);
		$ip = $data['ip'];
	    CommonService::log("Servicio al cliente en ip $ip actualizado:", $this->username, 'success' , $this->userId, $client->client_id);

        event(new UpdateServiceClientEvent($data, $oldServiceDetails));

        return $process->show('success');
    }

    public function postBanService(Request $request, $id)
    {
        if(auth()->user()) {
            $this->username = auth()->user()->username;
            $this->userId = auth()->user()->id;
        }

        $process = new Chkerr();

        $client_id = $request->get('id');
        $service = ClientService::find($id);
        $client = Client::find($service->client_id);

        //obtenemos la ip del cliente
        $nameClient = $client->name;
        $target = $service->ip;
        $mac = $service->mac;
        $statusClient = $service->status;
        $router_id = $service->router_id;
        $userClient = $service->user_hot;


        $pl = new GetPlan();
        $plan = $pl->get($service->plan_id);
        $burst = Burst::get_all_burst($plan['upload'],$plan['download'],$plan['burst_limit'],$plan['burst_threshold'],$plan['limitat']);

        $namePlan = $plan['name'];
        $maxlimit = $plan['maxlimit'];

        $config = ControlRouter::where('router_id','=',$router_id)->get();

        $typeconf = $config[0]->type_control;

        $arp = $config[0]->arpmac;
        $advs = $config[0]->adv;
        $dhcp = $config[0]->dhcp;

        if ($advs==1) {
            $drop=0;
        }else{
            $drop=1;
        }


        $router = new RouterConnect();
        $con = $router->get_connect($router_id);
        $log = new Slog();

        $data = array(
            'name' => $nameClient.'_'.$service->id,
            'user' => $userClient,
            'ip' => $service->ip,
            'status' => $statusClient,
            'arp' => $arp,
            'adv' => $advs,
            'drop' => $drop,
            'planName' => $namePlan,
            'namePlan' => $plan['name'],
            'mac' => $mac,
            'lan' => $con['lan'],
            //for simple queue with tree
            'plan_id' => $service->plan_id,
            'router_id' => $service->router_id,
            'download' => $plan['download'],
            'upload' => $plan['upload'],
            'maxlimit' => $plan['maxlimit'],
            'aggregation' => $plan['aggregation'],
            'limitat' => $plan['limitat'],
            'bl' => $burst['blu'].'/'.$burst['bld'],
            'bth' => $burst['btu'].'/'.$burst['btd'],
            'bt' => $plan['burst_time'].'/'.$plan['burst_time'],
            'burst_limit' => $plan['burst_limit'],
            'burst_threshold' => $plan['burst_threshold'],
            'burst_time' => $plan['burst_time'],
            'priority' => $plan['priority'].'/'.$plan['priority'],
            'comment' => 'SmartISP - '.$plan['name'],
            'tree_priority' => $service->tree_priority,
	        'no_rules' => $plan['no_rules'],

        );

	    $service->save();

        $counter = new CountClient();

        if($typeconf=='nc'){

            $STATUS = ClientService::find($client_id);
	        $ip = $service->ip;
            if($STATUS->status == 'ac') {
                $st='de';
                $online = 'off';
                $m = "Se ha cortado el servicio de atención al cliente para ip $ip ";
                //descontamos el numero de clientes del plan
                $counter->step_down_plan($client->plan_id);
            }
            else {
                $st='ac';
                $online = 'on';

                $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                //incrementamos el numero de clientes en el plan
                $counter->step_up_plan($client->plan_id);
            }

	        // Save history for client ban or active
	        $this->manageCortadoHistory($service, $request);

            $service->status = $st;
            $service->online = $online;



            $service->save();
	        CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);


            if($STATUS=='ac')
                return $process->show('banned');
            else
                return $process->show('unbanned');
        }

        $global = GlobalSetting::all()->first();

        $debug = $global->debug;

        if ($typeconf=='no') {

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];

            //Bloqueamos simple
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                $rocket = new RocketCore();
                $error = new Mkerror();

                if ($data['status']=='ac') { //esta activo bloqueamos

                    $STATUS = $rocket->set_basic_config($API,$error,$data,$target,null,'block',$debug);

                    if ($debug==1) {
                        if ($STATUS!=false) {
                            return $STATUS;
                        }
                    }

                    $STATUS='true';

                } else {//esta bloqueado activamos

                    $STATUS = $rocket->set_basic_config($API,$error,$data,$target,null,'unblock',$debug);

                    if ($debug==1) {
                        if ($STATUS!=false) {
                            return $STATUS;
                        }
                    }

                    $STATUS='false';
                }

                $API->disconnect();

	            $ip = $service->ip;

                if($STATUS=='true' || $STATUS=='ac') {
                    $st='de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                }
                else {
                    $st='ac';
                    $online = 'on';
                    $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

	            // Save history for client ban or active
	            $this->manageCortadoHistory($service, $request);

                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();

//                $log->save($m,"change",$nameClient);
	            CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);

	            if($STATUS=='true' || $STATUS=='ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');
            }
            else
                return $process->show('errorConnect');

        }

        if($typeconf=='sq'){

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];

            //Bloqueamos simple
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $STATUS = $rocket->block_simple_queues($API,$data,$target,$debug);

                if ($debug==1) {
                    if($process->check($STATUS)){
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();

	            $ip = $service->ip;

                if($STATUS=='true' || $STATUS=='ac'){
                    $st='de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio de atención al cliente para ip $ip ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st='ac';
                    $online = 'on';
                    $m = "Se ha activado el servicio de atención al cliente para ip $ip ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

	            // Save history for client ban or active
	            $this->manageCortadoHistory($service, $request);

                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();
	            CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);

                if($STATUS=='true' || $STATUS=='ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');
            }
            else
                return $process->show('errorConnect');
        }

        if($typeconf=='st'){

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];

            //Bloqueamos simple
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {
	            $ip = $service->ip;
                if($statusClient=='ac'){
                    $st='de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                }
                else{
                    $st='ac';
                    $online = 'on';
                    $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

	            // Save history for client ban or active
	            $this->manageCortadoHistory($service, $request);

                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();
	            CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);

                $rocket = new RocketCore();

                $STATUS = $rocket->block_simple_queue_with_tree($API,$data,$target,$debug);

                if ($debug==1) {
                    if($process->check($STATUS)){
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();

                if($STATUS=='true' || $STATUS=='ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');
            }
            else
                return $process->show('errorConnect');
        }

        if ($typeconf=='dl') {
            //bloqueamos hotspot

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $STATUS = $rocket->block_dhcp_lease($API,$data,$target,$debug);

                if ($debug==1) {
                    if($process->check($STATUS)){
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();

	            $ip = $service->ip;

                if($STATUS=='true' || $STATUS=='ac'){
                    $st='de';
                    $online='off';
                    $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st='ac';
                    $online='on';
                    $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

	            // Save history for client service ban or active
	            $this->manageCortadoHistory($service, $request);


                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();
	            CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);

                if($STATUS=='true' || $STATUS=='ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');

            }
            else
                return $process->show('errorConnect');
        }

        if ($typeconf=='pt') {
            //bloqueamos pppoe simple queue with tree

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                $rocket = new RocketCore();

                $STATUS = $rocket->block_ppp($API,$data,$target,$debug);

                if ($debug==1) {
                    if($process->check($STATUS)){
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }
	            $ip = $service->ip;
                if($STATUS=='true' || $STATUS=='ac'){
                    $st='de';
                    $online='off';
                    $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                }
                else{
                    $st='ac';
                    $online='on';
                    $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

	            // Save history for client service ban or active
	            $this->manageCortadoHistory($service, $request);

                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();

	            CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);


                $STATUS = $rocket->block_simple_queue_with_tree($API,$data,$target,$debug);

                if ($debug==1) {
                    if($process->check($STATUS)){
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();


                if($STATUS=='true' || $STATUS=='ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');

            }
            else
                return $process->show('errorConnect');
        }

        if ($typeconf=='pp' || $typeconf=='ps') {
            //bloqueamos pppoe

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                $rocket = new RocketCore();

                $STATUS = $rocket->block_ppp($API,$data,$target,$debug);

                if ($debug==1) {
                    if($process->check($STATUS)){
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();
	            $ip = $service->ip;
                if($STATUS=='true' || $STATUS=='ac'){
                    $st='de';
                    $online='off';
                    $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                }
                else{
                    $st='ac';
                    $online='on';
                    $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

	            // Save history for client service ban or active
	            $this->manageCortadoHistory($service, $request);

                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();
	            CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);

                if($STATUS=='true' || $STATUS=='ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');

            }
            else
                return $process->show('errorConnect');
        }

        if ($typeconf=='pa') {
            //bloqueo PPP-PCQ
            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                //$num_cli = Helpers::getnumcl($router_id,$typeconf,$client->plan_id);
                $num_cli = Helpers::getnumcl($router_id,$typeconf,$service->plan_id); /**fix 19/06**/
                //opcion avanzada burst del plan
                $burst = Burst::get_all_burst($plan['upload'],$plan['download'],$plan['burst_limit'],$plan['burst_threshold'],$plan['limitat']);

                $advanced_data = array(
                    'name' => $nameClient.'_'.$service->id,
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
                    'rate_down' => $plan['download'].'k',
                    'rate_up' => $plan['upload'].'k',
                    'burst_rate_down' => $burst['bld'],
                    'burst_rate_up' => $burst['blu'],
                    'burst_threshold_down' => $burst['btd'],
                    'burst_threshold_up' => $burst['btu'],
                    'limit_at_down' => $burst['lim_at_down'],
                    'limit_at_up' => $burst['lim_at_up'],
                    'burst_time' => $plan['burst_time'],
                    'no_rules' => $plan['no_rules'],
                );


                $STATUS = $rocket->block_ppp_secrets_pcq($API,$advanced_data,$target,$debug);

                if ($debug==1) {
                    if($process->check($STATUS)){
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }


                $API->disconnect();

	            $ip = $service->ip;
                if($STATUS=='true' || $STATUS=='ac'){
                    $st='de';
                    $online='off';
                    $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                }
                else{
                    $st='ac';
                    $online ='on';
                    $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

	            // Save history for client service ban or active
	            $this->manageCortadoHistory($service, $request);

                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();
	            CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);

                if($STATUS=='true' || $STATUS=='ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');

            }
            else
                return $process->show('errorConnect');


        }

        if ($typeconf=='pc') {
            //bloqueamos PCQ

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $num_cli = Helpers::getnumcl($router_id,$typeconf,$service->plan_id); /**fix 19/06**/

                //opcion avanzada burst del plan
                $burst = Burst::get_all_burst($plan['upload'],$plan['download'],$plan['burst_limit'],$plan['burst_threshold'],$plan['limitat']);

                $advanced_data = array(

                    'name' => $nameClient.'_'.$service->id,
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
                    'rate_down' => $plan['download'].'k',
                    'rate_up' => $plan['upload'].'k',
                    'burst_rate_down' => $burst['bld'],
                    'burst_rate_up' => $burst['blu'],
                    'burst_threshold_down' => $burst['btd'],
                    'burst_threshold_up' => $burst['btu'],
                    'limit_at_down' => $burst['lim_at_down'],
                    'limit_at_up' => $burst['lim_at_up'],
                    'burst_time' => $plan['burst_time'],
	                'no_rules' => $plan['no_rules'],

                );

                $STATUS = $rocket->block_pcq($API,$advanced_data,$target,$debug);

                if ($debug==1) {
                    if($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();
	            $ip = $service->ip;
                if($STATUS=='true' || $STATUS=='ac') {
                    $st='de';
                    $online='off';
                    $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                }
                else {
                    $st='ac';
                    $online='on';
                    $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

	            // Save history for client service ban or active
	            $this->manageCortadoHistory($service, $request);

                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();
	            CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);

                if($STATUS=='true' || $STATUS=='ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');

            }
            else
                return $process->show('errorConnect');


        }

        if($typeconf=='ra' || $typeconf == 'rp' || $typeconf == 'rr'){

            /**TODO: cuando este testeado y funcionando correctamente, lo que vamos hacer es refactorizar y poner en typeconf un || para los dos casos, asi no replicamos todo esto**/
            /**bloqueamos en Radius y ademas bloqueamos segun el tipo que es, replicando el pa,pt,ps**/
            $existe = Radgroupcheck::where('groupname','locked')->where('attribute','Auth-Type')->where('value','Reject')->first();
            if(!$existe){
                Radgroupcheck::create([
                    'groupname' => 'locked',
                    'attribute' => 'Auth-Type',
                    'op' => ':=',
                    'value' => 'Reject',

                ]);
            }

            if($typeconf=='ra'){ /**aplicamos el mismo caso que pt**/
                //bloqueamos pppoe simple queue with tree

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                    $rocket = new RocketCore();

                    $STATUS = $rocket->block_ppp($API,$data,$target,$debug);

                    if ($debug==1) {
                        if($process->check($STATUS)){
                            $API->disconnect();
                            return $process->check($STATUS);
                        }
                    }
	                $ip = $service->ip;
                    if($STATUS=='true' || $STATUS=='ac'){
                        $st='de';
                        $online='off';
                        $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                        //descontamos el numero de clientes del plan
                        $counter->step_down_plan($client->plan_id);

                        Radusergroup::create([
                            'username' => $service->user_hot,
                            'groupname' => 'locked',
                            'priority' => 1
                        ]);

                    }
                    else{
                        $st='ac';
                        $online='on';
                        $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                        //incrementamos el numero de clientes en el plan
                        $counter->step_up_plan($client->plan_id);

                        Radusergroup::where('username',$service->user_hot)->delete();

                    }

                    // Save history for client service ban or active
                    $this->manageCortadoHistory($service, $request);

                    //guardamos en la base de datos
                    $service->status = $st;
                    $service->online = $online;
                    $service->save();

	                CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);


                    $STATUS = $rocket->block_simple_queue_with_tree($API,$data,$target,$debug);

                    if ($debug==1) {
                        if($process->check($STATUS)){
                            $API->disconnect();
                            return $process->check($STATUS);
                        }
                    }

                    $API->disconnect();


                    if($STATUS=='true' || $STATUS=='ac')
                        return $process->show('banned');
                    else
                        return $process->show('unbanned');

                }
                else
                    return $process->show('errorConnect');
            }
            if($typeconf=='rp'){ /**aplicamos el mismo caso que pa**/
                //bloqueo PPP-PCQ
                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $rocket = new RocketCore();

                    //$num_cli = Helpers::getnumcl($router_id,$typeconf,$client->plan_id);
                    $num_cli = Helpers::getnumcl($router_id,$typeconf,$service->plan_id); /**fix 19/06**/
                    //opcion avanzada burst del plan
                    $burst = Burst::get_all_burst($plan['upload'],$plan['download'],$plan['burst_limit'],$plan['burst_threshold'],$plan['limitat']);

                    $advanced_data = array(
                        'name' => $nameClient.'_'.$service->id,
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
                        'rate_down' => $plan['download'].'k',
                        'rate_up' => $plan['upload'].'k',
                        'burst_rate_down' => $burst['bld'],
                        'burst_rate_up' => $burst['blu'],
                        'burst_threshold_down' => $burst['btd'],
                        'burst_threshold_up' => $burst['btu'],
                        'limit_at_down' => $burst['lim_at_down'],
                        'limit_at_up' => $burst['lim_at_up'],
                        'burst_time' => $plan['burst_time'],
                        'no_rules' => $plan['no_rules'],
                    );


                    $STATUS = $rocket->block_ppp_secrets_pcq($API,$advanced_data,$target,$debug);

                    if ($debug==1) {
                        if($process->check($STATUS)){
                            $API->disconnect();
                            return $process->check($STATUS);
                        }
                    }


                    $API->disconnect();
	                $ip = $service->ip;

                    if($STATUS=='true' || $STATUS=='ac'){
                        $st='de';
                        $online='off';
                        $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                        //descontamos el numero de clientes del plan
                        $counter->step_down_plan($client->plan_id);
                        Radusergroup::create([
                            'username' => $service->user_hot,
                            'groupname' => 'locked',
                            'priority' => 1
                        ]);

                    }
                    else{
                        $st='ac';
                        $online ='on';
                        $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                        //incrementamos el numero de clientes en el plan
                        $counter->step_up_plan($client->plan_id);

                        Radusergroup::where('username',$service->user_hot)->delete();
                    }

                    // Save history for client service ban or active
                    $this->manageCortadoHistory($service, $request);

                    //guardamos en la base de datos
                    $service->status = $st;
                    $service->online = $online;
                    $service->save();
	                CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);

                    if($STATUS=='true' || $STATUS=='ac')
                        return $process->show('banned');
                    else
                        return $process->show('unbanned');

                }
                else
                    return $process->show('errorConnect');

            }
            if($typeconf=='rr'){ /**aplicamos el mismo caso que ps**/

                //bloqueamos pppoe

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $rocket = new RocketCore();

                    /**si es radius no tiene secret en el mkt**/
                    // $STATUS = $rocket->block_ppp($API,$data,$target,$debug);

                    if ($debug==1) {
                        if($process->check($data['status'])){
                            $API->disconnect();
                            return $process->check($data['status']);
                        }
                    }

                    $API->disconnect();
	                $ip = $service->ip;
                    if($data['status']=='true' || $data['status']=='ac'){

                        // con el comando coa mandamos a desconectar al cliente
                        $secret = Router::find($router_id)->radius->secret;
                        $ip_ro = Router::find($router_id)->ip;

                        Radusergroup::create([
                            'username' => $service->user_hot,
                            'groupname' => 'locked',
                            'priority' => 1
                        ]);

                        $st='de';
                        $online='off';
                        $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                        //descontamos el numero de clientes del plan
                        $counter->step_down_plan($client->plan_id);

                        $ejecucion = shell_exec('echo User-Name="'.$service->user_hot.'" | /usr/bin/radclient -c 1 -n 3 -r 3 -t 3 -x '.$ip_ro.':3799 disconnect '.$secret.' 2>&1');

                    }
                    else{
                        $st='ac';
                        $online='on';
                        $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                        //incrementamos el numero de clientes en el plan
                        $counter->step_up_plan($client->plan_id);

                        Radusergroup::where('username',$service->user_hot)->delete();
                    }

                    // Save history for client service ban or active
                    $this->manageCortadoHistory($service, $request);

                    //guardamos en la base de datos
                    $service->status = $st;
                    $service->online = $online;
                    $service->save();
	                CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);

                    if($data['status']=='true' || $data['status']=='ac')
                        return $process->show('banned');
                    else
                        return $process->show('unbanned');

                }
                else
                    return $process->show('errorConnect');


            }


        }

    }

    //metodo para elimiar clientes
    public function postDelete(Request $request, $id)
    {
        $process = new Chkerr();

        $service = ClientService::find($request->get('id'));
        $clientService = $service->toArray();
        $client = Client::find($service->client_id);
        $nameClient = $client->name;
        $target = $service->ip;
        $macClient = $service->mac;
        $router_id = $service->router_id;

        $plan = Plan::find($service->plan_id);
        $burst = Burst::get_all_burst($plan->upload,$plan->download,$plan->burst_limit,$plan->burst_threshold,$plan->limitat);

        //recuperamos el tipo de autenticacion del router
        $authType = ControlRouter::where('router_id', '=', $service->router_id)->get();

        $typeconf = $authType[0]->type_control;
        $address_list = $authType[0]->address_list;

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
            'name' => $client->name.'_'.$service->id,
            'ip' => $service->ip,
            'mac' => $service->mac,
            'typeauth' => $service->typeauth,
            'router_id' => $service->router_id,
            'address' => $service->ip,
            'comment' => 'SmartISP - '.$plan['name'],
            /////////data for simple queues with tree
            'namePlan' => $plan['name'],
            'plan_id' => $service->plan_id,
            'download' => $plan['download'],
            'upload' => $plan['upload'],
            'maxlimit' => $plan['upload'].'k/'.$plan['download'].'k',
            'aggregation' => $plan['aggregation'],
            'limitat' => $plan['limitat'],
            'bl' => $burst['blu'].'/'.$burst['bld'],
            'bth' => $burst['btu'].'/'.$burst['btd'],
            'bt' => $plan['burst_time'].'/'.$plan['burst_time'],
            'burst_limit' => $plan['burst_limit'],
            'burst_threshold' => $plan['burst_threshold'],
            'burst_time' => $plan['burst_time'],
            'priority' => $plan['priority'].'/'.$plan['priority'],
            'tree_priority' => $service->tree_priority,
        );

        //verificamos si quiere eliminar el cliente de la tabla pero no del router
        if ($plan->name == 'Importados') {
            $counter->step_down_router($router_id);
            $counter->step_down_plan($service->plan_id);
            $service->delete();
            return $process->show('success');
        }

        $conf = Helpers::get_api_options('mikrotik');
        $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
        $API->debug = $conf['d'];

        if ($typeconf == 'nc') {
            //significa que que no esta activo la conexión con el router
            //eliminamos el usuario solo del sistema
	        $ip = $service->ip;
	        $clientId = $service->client_id;
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
			$ip = $service->ip;
	        CommonService::log("Una cliente ha sido eliminada ip es $ip", $this->username, 'danger' , $this->userId, $clientId);

            return $process->show('success');

        } else { //conexión con router eliminanos mediante la API
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $ip = $service->ip;
                $clientId = $service->client_id;
	            $service->delete();
	            CommonService::log("Una cliente ha sido eliminada ip es $ip", $this->username, 'danger' , $this->userId, $clientId);
                event(new DeleteServiceClientEvent($clientService, $client->toArray(), $data, auth()->user()->name));
                return $process->show('success');
            } else {
                return Response::json(array('msg' => 'errorConnect'));
            }
        }
    }

    public function manageCortadoHistory($service, $request)
    {
	    // Save history for client ban or active
	    $cortadoActivo = new CortadoReason();
	    $cortadoActivo->client_id = $service->client_id;
	    $cortadoActivo->service_id = $service->id;
	    $cortadoActivo->reason = $request->reason;
	    $cortadoActivo->status = $service->status == 'ac' ? 'blocked' : 'active';
	    $cortadoActivo->save();

	    if($service->status == 'ac') {
		    $service->manually_cortado = 1;
	    } else {
		    $service->manually_cortado = 0;
	    }

	    $ip = $service->ip;

	    if(auth()->user()) {
	        $this->username = auth()->user()->username;
	        $this->userId = auth()->user()->id;
        }

	    CommonService::log("$ip Cliente bloqueada manualmente", $this->username, 'change' , $this->userId, $service->client_id);


	    $service->save();
    }

    public function banHistory(BanHistoryDataTable $dataTable, $id)
    {
        return $dataTable->render('clients/services/ban-history');
    }

    public function saveGeoJson(int $id, ClientService $clientService, Request $request)
    {
        $rules = array(
            'geo_json' => 'required|array',
            'geo_json_styles' => 'nullable|array',
            'id' => 'exists:client_services,id'
        );

        $request->merge(['id' => $id]);
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return Response::json(array(array('msg' => 'error', 'errors' => $validation->getMessageBag()->toArray())));
        }
        $clientService = $clientService->find($id);
        $clientService->geo_json = $request->geo_json;
        $clientService->geo_json_styles = $request->geo_json_styles;
        $clientService->save();
    }
}
