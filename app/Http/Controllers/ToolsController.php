<?php

namespace App\Http\Controllers;

use App\libraries\Helpers;
use App\libraries\Mikrotik;
use App\libraries\RouterConnect;
use App\libraries\Validator;
use App\models\ClientService;
use App\models\GlobalSetting;
use App\models\Plan;
use App\models\Router;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use SMSGatewayMe\Client\ApiClient;
use SMSGatewayMe\Client\Configuration;
use SMSGatewayMe\Client\Api\MessageApi;
use SMSGatewayMe\Client\Model\SendMessageRequest;

class ToolsController extends BaseController
{

    public function __construct()
    {
//        $this->beforeFilter('auth'); //bloqueo de acceso
    }

    public function getIndex()
    {

        $id = Auth::user()->id;
        $level = Auth::user()->level;
        //control permissions only access super administrator (sa)
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
        $access = $perm[0]->access_system;

        if ($level == 'ad' || $access == true) {


            $global = GlobalSetting::all()->first();
            $allPlans = Plan::all();
            $routers = Router::all();
            $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
            $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
                "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
                "v" => $global->version, "st" => $global->status,
                "lv" => $global->license, "ms" => $global->message, "allPlans" => $allPlans,
                "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
                "reports" => $perm[0]->access_reports, "company" => $global->company, "routers" => $routers,
                'permissions' => $perm->first(),
                // menu options
                
            );



            if (Auth::user()->level == 'ad')
                @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);

            $contents = View::make('tools.index', $permissions);
            $response = Response::make($contents, 200);
            $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            $response->header('Pragma', 'no-cache');
            return $response;
        } else return Redirect::to('admin');
    }

    //metodo para test de envio de emails
    public function postSend(Request $request)
    {
        $friendly_names = array('email' => 'Para', 'subje' => 'Asunto');
        $rules = array('email' => 'required|email', 'subje' => 'required');

        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);

        if ($validation->fails()) return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

        //recuperamos configuracion de la Base de datos
        $global = GlobalSetting::all()->first();

        //verificamos que a cambiado la configuracin
        if ($global->email == '') return Response::json(array('msg' => 'noconfig'));
        if ($global->password == '') return Response::json(array('msg' => 'noconfig'));

        //enviamos email
        $user = array('email' => $request->get('email'), 'name' => 'Rocket Mail', 'esys' => $global->email, 'sub' => $request->get('subje'));

        // the data that will be passed into the mail view blade template
        $data = array('detail' => 'Esto es una Prueba');
        $men = $request->get('message');

        // use Mail::send function to send email passing the data and using the $user variable in the closure
        $m = Mail::send('emails.temple', $data, function ($message) use ($user) {
            $message->from($user['esys'], 'SmartISP');
            $message->to($user['email'])->subject($user['sub']);
        });

        return Response::json(array('msg' => 'success'));
    }

    //metodo para hacer ping
    public function postPing(Request $request)
    {
        $friendly_names = array(
            'ipt' => 'Ping a',
            'packages' => __('app.packages')
        );
        $rules = array(
            'ipt' => 'required|ip',
            'packages' => 'required|min:1'
        );

        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);
        if ($validation->fails())
            return Response::json(array(array('msg' => 'error', 'errors' => $validation->getMessageBag()->toArray())));

        $address = $request->get('ipt');
        $ping_count = $request->get('packages', 1);
        $arp = $request->get('arp', false);
        $interface = $request->get('interface', false);

        //conectamos al router
        $router = new RouterConnect();
        $con = $router->get_connect($request->get('router', 0));
        //obtenemos datos generales del API
        $conf = Helpers::get_api_options('mikrotik');
        $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
        $API->debug = $conf['d'];

        if ($API->connect($con['ip'], $con['login'], $con['password'])) {

            $API->write('/ping', false);
            $API->write('=address=' . $address, false);

            if ($arp) {
                $API->write('=arp-ping=true', false);
            }
            if ($interface) {
                $API->write('=interface=' . $interface, false);
            }

            $API->write('=count=' . $ping_count, true);

            $READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);
            $service = ClientService::find($request->get('service_id'));

            $API->disconnect();

            $match = strpos(json_encode($ARRAY), "timeout") > 0 ? "found" : "not found";

            if ($match == "found") {
                $service->online = 'off';
                $service->save();
            } else {
                $service->online = 'on';
                $service->save();
            }
        } else {
            $API->disconnect();
            return Response::json(array('msg' => 'errorConnect'));
        }

        return Response::json($ARRAY);
    }

    //metodo para hacer torch
    public function postTorch(Request $request)
    {
        $friendly_names = array(
            'interface' => __('app.interface'),
            'srcaddress' => 'Src. Address',
            'dstaddress' => 'Dst. Address',
            'duration' => __('app.duration')
        );
        $rules = array(
            'interface' => 'required',
            'srcaddress' => 'required',
            'dstaddress' => 'required',
            'duration' => 'required'
        );

        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);
        if ($validation->fails())
            return Response::json(array(array('msg' => 'error', 'errors' => $validation->getMessageBag()->toArray())));


        $src_address = $request->get('srcaddress');
        $dst_address = $request->get('dstaddress');
        $interface = $request->get('interface');
        $duration = $request->get('duration');

        //conectamos al router
        $router = new RouterConnect();
        $con = $router->get_connect($request->get('router', 0));
        //obtenemos datos generales del API
        $conf = Helpers::get_api_options('mikrotik');
        $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
        $API->debug = $conf['d'];

        if ($API->connect($con['ip'], $con['login'], $con['password'])) {

            $API->write('/tool/torch', false);
            $API->write('=src-address=' . $src_address, false);
            $API->write('=dst-address=' . $dst_address, false);
            $API->write('=interface=' . $interface, false);
            $API->write('=port=any', false);
            $API->write('=duration=' . $duration, true);
            $READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);
            $API->disconnect();
        } else {
            $API->disconnect();
            return Response::json(array('msg' => 'errorConnect'));
        }

        return Response::json($ARRAY);
    }

    //metodo para test de envio de sms
    public function postSendsms(Request $request)
    {
        $friendly_names = array('phone' => 'Para Nº', 'message' => __('app.message'));
        $rules = array('phone' => 'required', 'message' => 'required');

        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);

        if ($validation->fails()) return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

        //recuperamos informacion adicional
        $global = GlobalSetting::all()->first();
        //verificamos si ya configuro el modem usb
        $sms = Helpers::get_api_options('modem');

        if (count($sms) > 0) {

            if ($sms['e'] == '1') {

                //get connection data for login ruter
                $router = new RouterConnect();
                $con = $router->get_connect($sms['r']);
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $phone = '+' . $global->phone_code . $request->get('phone');

                    Psms::send_sms($API, $sms['p'], $sms['c'], $phone, $request->get('message'));

                    return Response::json(array('msg' => 'success'));
                } else {
                    return Response::json(array('msg' => 'errorConnect'));
                }
            }//end if
        }//end if

        $smsg = Helpers::get_api_options('smsgateway');

        if (count($smsg) > 0) {

            if ($smsg['e'] == '1') {

                //recuperamos información del gateway
                $number = '+' . $global->phone_code . $request->get('phone');

                try {

                    $config = Configuration::getDefaultConfiguration();
                    $config->setApiKey('Authorization', $smsg['t']);
                    $apiClient = new ApiClient($config);
                    $messageClient = new MessageApi($apiClient);
                    $sendMessageRequest1 = new SendMessageRequest([
                        'phoneNumber' => $number,
                        'message' => $request->get('message'),
                        'deviceId' => $smsg['d']
                    ]);

                    $sendMessages = $messageClient->sendMessages([
                        $sendMessageRequest1
                    ]);

                    return Response::json(array('msg' => 'success'));

                } catch (\Exception $e) {

                    return Response::json(['msg' => 'error', 'errors' => [$e->getMessage()]]);
                }

            }//end if
        }//end if

        return Response::json(array('msg' => 'noconfig'));

    }//end method

}
