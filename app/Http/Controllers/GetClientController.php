<?php

namespace App\Http\Controllers;
use App\libraries\Helpers;
use App\libraries\Mikrotik;
use App\libraries\Numbill;
use App\libraries\RouterConnect;
use App\models\ClientService;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\Plan;
use App\models\Router;
use App\models\OdbSplitter;
use App\models\OnuType;
use App\models\Zone;
use App\models\Client;
use App\models\SuspendClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use App\models\Factel;
class GetClientController extends BaseController
{

    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function postPlans()
    {
        //mostramos solo los planes hotspot
        $plans = Plan::all();
        
        if (count(json_decode($plans, 1)) == 0)
            return Response::json(array('msg' => 'noplans'));

        return Response::json($plans);
    }


    public function postFactel()
    {
        //mostramos el tipo de facturacion electronica
        
        $factelStatus = Factel::all()->first()->status;
        
        return Response::json(array('factelStatus' => $factelStatus));
    }



    public function postRouters()
    {
        //recuperamos los routers
        $routers = Router::all();
        if (count(json_decode($routers, 1)) == 0)
            return Response::json(array('msg' => 'norouters'));

        return Response::json($routers);
    }

    public function postRoutersCajas()
    {
        //recuperamos los routers
        $routers = OdbSplitter::all();
        if (count(json_decode($routers, 1)) == 0)
            return Response::json(array('msg' => 'norouters'));

        return Response::json($routers);
    }

    public function postDhcp()
    {

        $dhcp = Client::where('mac', '00:00:00:00:00:00')->count();
        return Response::json(array('dhcp' => $dhcp));

    }

    public function postRouter()
    {
        //recuperamos routers solo los tipos de control pppoe o hotspot
        $routers = DB::table('control_routers')
        ->join('routers', 'routers.id', '=', 'control_routers.router_id')
        ->select('routers.id', 'routers.name')->where('control_routers.type_control', 'pp')->orWhere('control_routers.type_control', 'ho')->get();

        if (count($routers) > 0)
            return Response::json($routers);
        else
            return Response::json(array('msg' => 'norouters'));
    }


    public function info_caja(Request $request)
    {
        $OdbSplitter=OdbSplitter::find($request->id);
        $Client = Client::select('port')->where('odb_id',$request->id)->get();
        $zone = Zone::find($OdbSplitter->zone_id);
        $array=[];
        foreach ($Client as $key){
            $array[]=$key->port;
        }
        return Response::json(["success" => true, "detail" => $OdbSplitter,'port'=>$array,'zone'=>$zone->name,'zone_id'=>$zone->id]);

    }

    public function postListprofilesppp(Request $request)
    {


        $router = new RouterConnect();
        $con = $router->get_connect($request->get('id'));
        $option = $request->get('op', 'add');


        //GET all data for API
        $conf = Helpers::get_api_options('mikrotik');
        $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
        $API->debug = $conf['d'];

        if ($API->connect($con['ip'], $con['login'], $con['password'])) {

            $API->write("/ppp/profile/print", true);
            $READ = $API->read(false);
            $PROFILES = $API->parseResponse($READ);
            //opcion editar o nuevo

            if ($option == 'ed') {
                //recuperamos el perfil del cliente en mikrotik
                $cl = Client::find($request->get('c'));

                $API->write("/ppp/secret/print", false);
                $API->write("?name=" . $cl->user_hot, true);
                $READ = $API->read(false);
                $PROFILE = $API->parseResponse($READ);

                if (count($PROFILE) > 0) { //encontro un perfil

                    $API->disconnect();
                    return Response::json(["success" => true, "profile" => $PROFILE[0]['profile'], "profiles" => $PROFILES]);

                } else { //no encontro perfil

                    $API->disconnect();
                    return Response::json(["success" => true, "profile" => '*0', "profiles" => $PROFILES]);

                }

            }


            $API->disconnect();
            return Response::json(["success" => true, "profiles" => $PROFILES]);

        } else {

            $API->disconnect();
            return Response::json(["success" => false]);
        }

    }

    public function postTrafic(Request $request)
    {

        $router = new RouterConnect();

        $client = Client::find($request->get('id'));

        $con = $router->get_connect($client->router_id);
        $config = ControlRouter::where('router_id', '=', $client->router_id)->get();
        $typeconf = $config[0]->type_control;
        //GET all data for API
        $conf = Helpers::get_api_options('mikrotik');
        $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
        $API->debug = $conf['d'];


        //verificamos el tipo de control
        switch ($typeconf) {
            case 'sq':
            case 'st':
                # simple queues trafic monitor
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $API->write("/queue/simple/print", false);
                $API->write("=stats", false);
                $API->write("?name=" . $client->name, true);
                $READ = $API->read(false);
                $ARRAY = $API->parseResponse($READ);
                    //print_r($ARRAY[0]);

                $rx = explode("/", $ARRAY[0]["rate"])[0];
                $tx = explode("/", $ARRAY[0]["rate"])[1];
                $rows['name'] = 'Tx';
                $rows['data'][] = $tx;
                $rows2['name'] = 'Rx';
                $rows2['data'][] = $rx;

                }//end if connection

                $result = array();
                array_push($result, $rows);
                array_push($result, $rows2);
                print json_encode($result, JSON_NUMERIC_CHECK);

                break;


                case ($typeconf == 'dl' || $typeconf == 'ho'):
                # Dhcp leases
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $rows = array();
                    $rows2 = array();

                    $API->write("/queue/simple/print", false);
                    $API->write("=stats", false);
                    $API->write("?target=" . $client->ip . '/32', true);
                    $READ = $API->read(false);
                    $ARRAY = $API->parseResponse($READ);

                    if (count($ARRAY) > 0) {
                        $rx = explode("/", $ARRAY[0]["rate"])[0];
                        $tx = explode("/", $ARRAY[0]["rate"])[1];
                        $rows['name'] = 'Tx';
                        $rows['data'][] = $tx;
                        $rows2['name'] = 'Rx';
                        $rows2['data'][] = $rx;
                    }

                }

                $result = array();
                array_push($result, $rows);
                array_push($result, $rows2);
                print json_encode($result, JSON_NUMERIC_CHECK);

                break;

                case ($typeconf == 'pp' || $typeconf == 'pa' || $typeconf == 'ps' || $typeconf == 'pt'):
                # PppoE Sectres and ppp secrets pcq address list
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $rows = array();
                    $rows2 = array();

                    $API->write("/interface/monitor-traffic", false);
                    $API->write("=interface=" . '<pppoe-' . $client->user_hot . '>', false);
                    $API->write("=once=", true);
                    $READ = $API->read(false);
                    $ARRAY = $API->parseResponse($READ);

                    if (count($ARRAY) > 0) {

                        if (isset($ARRAY[0]["rx-bits-per-second"]) && isset($ARRAY[0]["tx-bits-per-second"])) {

                            $rx = $ARRAY[0]["rx-bits-per-second"];
                            $tx = $ARRAY[0]["tx-bits-per-second"];
                            $rows['name'] = 'Tx';
                            $rows['data'][] = $tx;
                            $rows2['name'] = 'Rx';
                            $rows2['data'][] = $rx;
                        }

                    }

                }

                $result = array();
                array_push($result, $rows);
                array_push($result, $rows2);
                print json_encode($result, JSON_NUMERIC_CHECK);

                break;

                default:
                #no trafic

                break;
            }

            $API->disconnect();

        }

    public function postServiceTrafic(Request $request)
    {

        $router = new RouterConnect();

        $clientService = ClientService::find($request->get('id'));
        $client = Client::find($clientService->client_id);

        $con = $router->get_connect($clientService->router_id);
        $config = ControlRouter::where('router_id', '=', $clientService->router_id)->get();
        $typeconf = $config[0]->type_control;
        //GET all data for API
        $conf = Helpers::get_api_options('mikrotik');
        $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
        $API->debug = $conf['d'];


        //verificamos el tipo de control
        switch ($typeconf) {
            case 'sq':
            case 'st':
                # simple queues trafic monitor
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $API->write("/queue/simple/print", false);
                $API->write("=stats", false);
                $API->write("?name=" . $client->name.'_'.$clientService->id, true);
                $READ = $API->read(false);
                $ARRAY = $API->parseResponse($READ);
                    //print_r($ARRAY[0]);

                $rx = explode("/", $ARRAY[0]["rate"])[0];
                $tx = explode("/", $ARRAY[0]["rate"])[1];
                $rows['name'] = 'Tx';
                $rows['data'][] = $tx;
                $rows2['name'] = 'Rx';
                $rows2['data'][] = $rx;

                }//end if connection

                $result = array();
                array_push($result, $rows);
                array_push($result, $rows2);
                print json_encode($result, JSON_NUMERIC_CHECK);

                break;


                case ($typeconf == 'dl' || $typeconf == 'ho'):
                # Dhcp leases
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $rows = array();
                    $rows2 = array();

                    $API->write("/queue/simple/print", false);
                    $API->write("=stats", false);
                    $API->write("?target=" . $clientService->ip . '/32', true);
                    $READ = $API->read(false);
                    $ARRAY = $API->parseResponse($READ);

                    if (count($ARRAY) > 0) {
                        $rx = explode("/", $ARRAY[0]["rate"])[0];
                        $tx = explode("/", $ARRAY[0]["rate"])[1];
                        $rows['name'] = 'Tx';
                        $rows['data'][] = $tx;
                        $rows2['name'] = 'Rx';
                        $rows2['data'][] = $rx;
                    }

                }

                $result = array();
                array_push($result, $rows);
                array_push($result, $rows2);
                print json_encode($result, JSON_NUMERIC_CHECK);

                break;

                case ($typeconf == 'pp' || $typeconf == 'pa' || $typeconf == 'ps' || $typeconf == 'pt'):
                # PppoE Sectres and ppp secrets pcq address list
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $rows = array();
                    $rows2 = array();

                    $API->write("/interface/monitor-traffic", false);
                    $API->write("=interface=" . '<pppoe-' . $clientService->user_hot . '>', false);
                    $API->write("=once=", true);
                    $READ = $API->read(false);
                    $ARRAY = $API->parseResponse($READ);

                    if (count($ARRAY) > 0) {

                        if (isset($ARRAY[0]["rx-bits-per-second"]) && isset($ARRAY[0]["tx-bits-per-second"])) {

                            $rx = $ARRAY[0]["rx-bits-per-second"];
                            $tx = $ARRAY[0]["tx-bits-per-second"];
                            $rows['name'] = 'Tx';
                            $rows['data'][] = $tx;
                            $rows2['name'] = 'Rx';
                            $rows2['data'][] = $rx;
                        }

                    }

                }

                $result = array();
                array_push($result, $rows);
                array_push($result, $rows2);
                print json_encode($result, JSON_NUMERIC_CHECK);

                break;

                default:
                #no trafic

                break;
            }

            $API->disconnect();

        }

        public function postListprofileshotspot(Request $request)
        {

            $router = new RouterConnect();
            $con = $router->get_connect($request->get('id'));
            $option = $request->get('op', 'add');
        //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $API->write("/ip/hotspot/user/profile/print", true);
                $READ = $API->read(false);
                $PROFILES = $API->parseResponse($READ);

                if ($option == 'ed') {

                //recuperamos el perfil del cliente en mikrotik
                    $cl = Client::find($request->get('c'));
                    $API->write("/ip/hotspot/user/print", false);
                    $API->write("?name=" . $cl->user_hot, true);
                    $READ = $API->read(false);
                    $PROFILE = $API->parseResponse($READ);

                    if (count($PROFILE) > 0) {
                        $API->disconnect();
                        return Response::json(["success" => true, "profile" => $PROFILE[0]['profile'], "profiles" => $PROFILES]);
                    } else {
                        $API->disconnect();
                        return Response::json(["success" => true, "profile" => '*0', "profiles" => $PROFILES]);
                    }
                }

                $API->disconnect();
                return Response::json(["success" => true, "profiles" => $PROFILES]);

            } else {
                $API->disconnect();
                return Response::json(["success" => false]);
            }

        }

        public function postControl(Request $request)
        {
            $router_id = $request->get('id');

            $router = Router::find($router_id);

            $type = ControlRouter::where('router_id', '=', $router_id)->get();

            if (count($type) > 0) {
                if ($type[0]->type_control == 'no' && $router->connection == 1) {
                    $data = array("type" => 'nc');
                } else {
                    $data = array("type" => $type[0]->type_control);
                }
            } else {
                $data = array("type" => "no");
            }

            return Response::json($data);
        }

        public function postData(Request $request)
        {
            $id_client = $request->get('client');
            $client = Client::find($id_client);
            if (is_null($client))
                return Response::json(array('success' => false));
            $type_i="";
            if(isset($client->onu_id) and $client->onu_id!=""){
                $type_data =   OnuType::find($client->onu_id);
                $type_i = $type_data->pontype;

            }


            $data = array(
                'success' => true,
                'id' => $client->id,
                'name' => $client->name,
                'phone' => $client->phone,
                'email' => $client->email,
                'dni' => $client->dni,
                'dir' => $client->address,
                'coordinates' => $client->coordinates,
                'odb_id' => $client->odb_id,
                'onu_id' => $client->onu_id,
                'port' => $client->port,
                'onusn' => $client->onusn,
                'zona_id' => $client->zona_id,
                'type_onu' =>  $type_i,
                'typedoc_cod' =>  $client->typedoc_cod,
                'economicactivity_cod' =>  $client->economicactivity_cod,
                'municipio_cod' =>  $client->municipio_cod,
                'typeresponsibility_cod' =>  $client->typeresponsibility_cod,
                'typetaxpayer_cod' =>  $client->typetaxpayer_cod,
            );

            return Response::json($data);
        }

    //metodo para recuperar coordenadas de los clientes
        public function postGpsmap()
        {

            $clientsgps = DB::table('clients')
            ->join('plans', 'plans.id', '=', 'clients.plan_id')
            ->join('routers', 'routers.id', '=', 'clients.router_id')
            ->select('clients.name', 'clients.status As stclient', 'clients.phone', 'clients.ip As ipclient', 'clients.coordinates As client_location',
                'plans.name As plan_name', 'routers.name As routername', 'routers.coordinates As router_location')->get();

            return Response::json($clientsgps);
        }

    //metodo para recuperar datos para herramientas clientes
        public function postTools(Request $request)
        {

            $client = Client::find($request->get('id', 0));

            $router = new RouterConnect();
            $con = $router->get_connect($client->router_id);
            $type = ControlRouter::where('router_id', '=', $client->router_id)->get();

        //obtenemos datos generales del API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $API->write('/interface/print', false);
                $API->write('=.proplist=name', true);
                $READ = $API->read(false);
                $NAME = $API->parseResponse($READ);

                $NAME = array_filter($NAME);

                $API->write('/interface/print', false);
                $API->write('=.proplist=default-name', true);
                $READ = $API->read(false);
                $DNAME = $API->parseResponse($READ);

                $DNAME = array_filter($DNAME);

                $API->disconnect();

                $interfaces = array();

                for ($i = 0; $i < count($DNAME); $i++) {

                    $interfaces[$i]['name'] = $NAME[$i]['name'];
                    $interfaces[$i]['default-name'] = $DNAME[$i]['default-name'];
                }

            } else {
                $API->disconnect();
                return Response::json(array('success' => false));
            }


            $data = array(
                'name' => $client->name,
                'success' => true,
                'ip' => $client->ip,
                'router_id' => $client->router_id,
                'interfaces' => $interfaces,
                'lan' => $con['lan'],
                'typecontrol' => $type[0]->type_control
            );

            return Response::json($data);

        }

    //metodo para recuperar datos para herramientas clientes
        public function postServiceTools(Request $request)
        {

            $clientService = ClientService::find($request->get('id', 0));
            $client = Client::find($clientService->client_id);

            $router = new RouterConnect();
            $con = $router->get_connect($clientService->router_id);
            $type = ControlRouter::where('router_id', '=', $clientService->router_id)->get();

        //obtenemos datos generales del API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $API->write('/interface/print', false);
                $API->write('=.proplist=name', true);
                $READ = $API->read(false);
                $NAME = $API->parseResponse($READ);

                $NAME = array_filter($NAME);

                $API->write('/interface/print', false);
                $API->write('=.proplist=default-name', true);
                $READ = $API->read(false);
                $DNAME = $API->parseResponse($READ);

                $DNAME = array_filter($DNAME);

                $API->disconnect();

                $interfaces = array();

                for ($i = 0; $i < count($DNAME); $i++) {
                	if(isset($DNAME[$i]) && $NAME[$i]) {
		                $interfaces[$i]['name'] = $NAME[$i]['name'];
		                $interfaces[$i]['default-name'] = $DNAME[$i]['default-name'];
	                }
                }

            } else {
                $API->disconnect();
                return Response::json(array('success' => false));
            }


            $data = array(
                'service_id' => $clientService->id,
                'name' => $client->name,
                'success' => true,
                'ip' => $clientService->ip,
                'router_id' => $clientService->router_id,
                'interfaces' => $interfaces,
                'lan' => $con['lan'],
                'typecontrol' => $type[0]->type_control
            );
            $data = mb_convert_encoding($data, "UTF-8", "auto");
            return Response::json($data);

        }

    //metodo para recuperar info general del cliente
        public function postInfo(Request $request)
        {
            $id = $request->get('id', 0);
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


            return Response::json($data);


        }

    //metodo para recuperar info general del cliente
    public function postServiceInfo(Request $request)
    {

        $id = $request->get('id', 0);
        //recuperamos info del cliente
        $client = DB::table('clients')
            ->join('client_services', 'client_services.client_id', '=', 'clients.id')
            ->join('plans', 'plans.id', '=', 'client_services.plan_id')
            ->join('routers', 'routers.id', '=', 'client_services.router_id')
            ->join('suspend_clients', 'suspend_clients.service_id', '=', 'client_services.id')
            ->join('control_routers', 'control_routers.router_id', '=', 'client_services.router_id')
            ->select('clients.name','clients.email','clients.phone','clients.dni','clients.address','clients.coordinates', 'client_services.status As stclient', 'client_services.ip As ipclient', 'client_services.mac',
                'plans.name As plan_name', 'suspend_clients.expiration', 'routers.name As routername',
                'control_routers.type_control', 'control_routers.arpmac', 'control_routers.dhcp',
                'control_routers.adv')->where('client_services.id', $id)->get();

        $global = GlobalSetting::all()->first();

        $before = $global->before_days;

        $tolerance = $global->tolerance;

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
            'status' => $client[0]->stclient,
            'clientEmail' => $client[0]->email,
            'clientPhone' => $client[0]->phone,
            'clientAddress' => $client[0]->address,
            'clientDni' => $client[0]->dni,
            'clientCoordinates' => $client[0]->coordinates,
        );


        return Response::json($data);


    }

    //metodo para recuperar el nombre e id del cliente
        public function postClient(Request $request)
        {
            $name = $request->get('search');

            switch ($request->get('filter')) {
                case 'name':
                $client = Client::where('name', 'LIKE', '%' . $name . '%')->select('clients.name As name', 'clients.id')->get();
                break;

                case 'ip':
                $client = Client::where('ip', 'LIKE', '%' . $name . '%')->select('clients.ip As name', 'clients.id')->get();
                break;

                case 'dni':
                $client = Client::where('dni', 'LIKE', '%' . $name . '%')->select('clients.dni As name', 'clients.id')->get();
                break;

                case 'phone':
                $client = Client::where('phone', 'LIKE', '%' . $name . '%')->select('clients.phone As name', 'clients.id')->get();
                break;

                case 'mac':
                $client = Client::where('mac', 'LIKE', '%' . $name . '%')->select('clients.mac As name', 'clients.id')->get();
                break;

                case 'email':
                $client = Client::where('email', 'LIKE', '%' . $name . '%')->select('clients.email As name', 'clients.id')->get();
                break;
            }

            return Response::json($client);
        }

    //metodo para recuperar todos los clientes
        public function postClients()
        {

            $clients = DB::table('clients')->select('id', 'name')->get();

            return Response::json($clients);
        }

    //metodo para recuperar todos los datos del cliente
        public function postGcl(Request $request)
        {
            $client_id = $request->get('id');
            $client = Client::find($client_id);
            $plan_id = $client->plan_id;
            $plan = Plan::find($plan_id);
            $router_id = $client->router_id;
            $router = Router::find($router_id);
            $expiring = SuspendClient::where('client_id', '=', $client_id)->get();
            $numBill = GlobalSetting::all()->first();
            $nbill = $numBill->num_bill + 1;
            $num = new Numbill();
            $nbill = $num->get_format($nbill);
        //verificamos si ya vencio su pago
            if (date('Y-m-d') == $expiring[0]->expiration)
                $show = 'y';
            if ($expiring[0]->expiration > date('Y-m-d'))
                $show = 'g';
            if (date('Y-m-d') > $expiring[0]->expiration)
                $show = 'r';

            $data = array(
                'success' => true,
                'id' => $client->id,
                'amount' => $plan->cost,
                'nbill' => 0,
                'plan' => $plan->name,
                'router' => $router->name,
                'expiring' => $expiring[0]->expiration,
                'show' => $show,
                'nbill' => $nbill
            );

            return Response::json($data);
        }

    //metodo para recuperar todos los clientes activos
        public function postAllclients()
        {
            $clients = DB::table('clients')->select('clients.id', 'clients.name')->get();

            if (count($clients) > 0)
                return Response::json($clients);
            else
                return Response::json(array("msg" => "noclients"));

        }

    }
