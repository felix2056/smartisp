<?php

namespace App\Http\Controllers;

define('TWILIO_SMS', base_path().'/public/Twilio');
require_once TWILIO_SMS.'/autoload.php';

use Illuminate\Http\Request;
use Twilio\Rest\Clientsms;
use App\libraries\Helpers;
use App\libraries\Mikrotik;
use App\libraries\Psms;
use App\libraries\RouterConnect;
use App\libraries\Slog;
use App\libraries\Validator;
use App\models\Client;
use App\models\GlobalSetting;
use App\models\Sms;
use App\models\SmsInbox;
use App\models\Template;
use App\models\TempSms;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use SMSGatewayMe\Client\ApiClient;
use SMSGatewayMe\Client\Configuration;
use SMSGatewayMe\Client\Api\MessageApi;
use SMSGatewayMe\Client\Model\SendMessageRequest;
use App\models\GlobalApi;

class SmsController extends BaseController
{

    public function __construct()
    {
//		$this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function getIndex()
    {
        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
        $access = $perm[0]->access_pays;
        //control permissions only access super administrator (sa)
        if ($level == 'ad' || $access == true) {

            $global = GlobalSetting::all()->first();

            $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
                "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
                "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
                "reports" => $perm[0]->access_reports,
                "v" => $global->version, "st" => $global->status,
                "lv" => $global->license, "company" => $global->company,
                'permissions' => $perm->first(),
                // menu options
            );

            if (Auth::user()->level == 'ad')
                @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);

            $contents = View::make('sms.index', $permissions);
            $response = Response::make($contents, 200);
            $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            $response->header('Pragma', 'no-cache');
            return $response;
        } else
            return Redirect::to('admin');
    }

    //metodo para listar envios
    public function postListsend()
    {

        $sms = DB::table('sms')
            ->join('routers', 'sms.router_id', '=', 'routers.id')
            ->select('sms.client As clname', 'sms.id',
                'routers.name As roname', 'sms.send_date', 'sms.phone',
                'sms.total_clients As tcl', 'sms.send_rate', 'sms.gateway', 'sms.message')
            ->get();

        return Response::json($sms);
    }

    //metodo para listar recibidos
    public function postInbox()
    {

        //verificamos los gateways
        $modem = Helpers::get_api_options('modem');

        $global = GlobalSetting::all()->first();

        if (count($modem) > 0) {

            if ($modem['e'] == '1') {

                //get connection data for login ruter
                $router = new RouterConnect();
                $con = $router->get_connect($modem['r']);
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $all_sms = Psms::get_sms($API);

                    if (count($all_sms) > 0) {

                        return $modem;

                        foreach ($all_sms as $key) {

                            //buscamos la id del mensaje
                            $sms_id = SmsInbox::where('smsgateway_id', $key['.id'])->count();
                            if ($sms_id == 0) {

                                if (!empty($key['phone']) && !empty($key['message'])) {

                                    $client = Client::where('phone', $key['phone'])->get();
                                    if (count($client) > 0) {
                                        //encontro el nombre
                                        $name_client = $client[0]->name;
                                    } else {
                                        //no se encontro asignamos por defaul
                                        $name_client = 'Desconocido';
                                    }
                                    $timestamp = $key['timestamp'];
                                    $timestamp = explode(' ', $timestamp);
                                    $timestamp = $timestamp[0] . ' ' . $timestamp[1];

                                    $fecha = DateTime::createFromFormat("M/d/Y H:i:s", "$timestamp");

                                    $newsms = new SmsInbox();
                                    $newsms->client = $name_client;
                                    $newsms->received_date = $fecha->format('Y-m-d H:i:s');
                                    $newsms->phone = $key['phone'];
                                    $newsms->open = 0;
                                    $newsms->message = $key['message'];
                                    $newsms->gateway = 'modem';
                                    $newsms->smsgateway_id = $key['.id'];
                                    $newsms->save();
                                }

                            }//end if checkexistanse
                        }//end foreach
                        $API->disconnect();
                    }//end if count

                }


                $sms_inbox = DB::table('sms_inbox')->orderBy('received_date', 'desc')->get();
                return Response::json($sms_inbox);

            }
        }

        $smsg = Helpers::get_api_options('smsgateway');

        if (count($smsg) > 0) {
            if ($smsg['e'] == '1') {

                try {

                    // Configure client
                    $config = Configuration::getDefaultConfiguration();
                    $config->setApiKey('Authorization', $smsg['t']);
                    $apiClient = new ApiClient($config);
                    $messageClient = new MessageApi($apiClient);
                    $messages = $messageClient->searchMessages();

                    $all_sms = $messages['results'];

                    if (count($all_sms) > 0) {

                        foreach ($all_sms as $key) {

                            if ($key['status'] == 'received') {
                                //buscamos la id del mensaje
                                $sms_id = SmsInbox::where('smsgateway_id', $key['id'])->count();

                                if ($sms_id == 0) {
                                    // El mensaje no existe agregamos
                                    // Buscamos el nombre y el router del cliente segun su numero
                                    //no se encontro asignamos por defaul

                                    $date = new DateTime(date_format($key['createdAt'], 'Y-m-d H:i:s'));
                                    $zone = $global->zone;
                                    $date->setTimezone(new DateTimeZone("$zone"));

                                    $newsms = new SmsInbox();
                                    $newsms->client = 'Desconocido';
                                    $newsms->received_date = $date->format('Y-m-d H:i:s');
                                    $newsms->phone = '0';
                                    $newsms->open = 0;
                                    $newsms->message = $key['message'];
                                    $newsms->gateway = 'smsgateway.me';
                                    $newsms->smsgateway_id = $key['id'];
                                    $newsms->save();
                                }//end if search sms gateway id
                            }//endif estatus recived
                        }//end foreach

                        $sms_inbox = DB::table('sms_inbox')->orderBy('received_date', 'desc')->get();
                        return Response::json($sms_inbox);

                    }//end if count

                } catch (\Exception $e) {

                    //end if response
                    //no se pudo conectar con el servidor remoto mostramos los ya registrados
                    $sms_inbox = DB::table('sms_inbox')->orderBy('received_date', 'desc')->get();
                    return Response::json($sms_inbox);
                }
            }//end if enabled gateway
        }//end if check config gateway


        return Response::json([]);
    }

    //metodo enviar sms
    public function postSend(Request $request)
    {

        # ingreso
        $friendly_names = array(
            'router_id' => 'Router',
            'template' => 'Plantilla',
            'clients' => 'Cliente (s)'
        );

        $rules = array(
            'router_id' => 'required',
            'template' => 'required',
            'clients' => 'required'
        );

        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);

        if ($validation->fails())
            return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

        $log = new Slog();
        $nclients = $request->get('clients');
        $count = count($nclients);
        $global = GlobalSetting::all()->first();
        $tol = $global->tolerance;


        if ($request->get('template') != 'none') {
            $template = Template::find($request->get('template'));
            $name = explode('.', $template->filename);
            $men = 'Plantilla ' . $template->name;
        } else {
            $men = $request->get('message');
        }

        if ($count == 1) {

            $client = DB::table('clients')->where('id', $nclients)->get();
            //save log
            $clname = $client[0]->name;
            $phone = $client[0]->phone;
            $response = 'process';
        } else {

            $clname = "Grupo";
            $phone = "Grupo";
            $response = 'success';
        }

        //verificamos el tipo de gateway usado
        $modem = Helpers::get_api_options('modem');
        if ($modem['e'] == '1') {
            $gateway = 'modem';
        }

        $smsg = Helpers::get_api_options('smsgateway');
        if ($smsg['e'] == '1') {
            $gateway = 'smsgateway.me';
        }

        $id = DB::table('sms')->insertGetId(
            array('client' => $clname, 'router_id' => $request->get('router_id'), 'send_date' => date('Y-m-d h:i:s'), 'phone' => $phone,
                'total_clients' => $count, 'send_rate' => 0, 'gateway' => $gateway, 'message' => $men));

        foreach ($nclients as $key => $value) {

            $sms_temp = new TempSms();
            $sms_temp->sms_id = $id;
            $sms_temp->client_id = $value;
            if ($request->get('template') != 'none') {
                $sms_temp->template_id = $request->get('template');
            } else {
                $sms_temp->template_id = 0;
            }
            $sms_temp->status = 'wa';
            $sms_temp->save();
        }//end foreach

        //save log
        $log->save("Se ha enviado sms a ", "info", $clname);
        return Response::json(array('msg' => $response));

    }//end method

    //metodo para eliminar
    public function postDelete(Request $request)
    {

        //eliminamos en la tabla principal
        $sms = Sms::find($request->get('id', 0));
        $client = $sms->client;
        $sms->delete();
        //liminamos todos los sms temporales
        TempSms::where('sms_id', $request->get('id', 0))->delete();
        $log = new Slog();
        //save log
        $log->save("Se ha eliminado un sms: ", "danger", $client);

        return Response::json(array('msg' => 'success'));
    }

    //metodo para reenviar sms
    public function postForward(Request $request)
    {

        $SM = Sms::find($request->get('id', 0));
        $client = $SM->client;

        $sms = TempSms::where('sms_id', $request->get('id', 0))->get();

        if (count($sms) > 0) {

            foreach ($sms as $forward) {

                $sms_pro = TempSms::find($forward->id);
                $sms_pro->smsgateway_id = 0;
                $sms_pro->status = 'wa';
                $sms_pro->save();
            }

            $SM->send_rate = 0;
            $SM->save();

            $log = new Slog();
            //save log
            $log->save("Se ha reenviado un sms: ", "info", $client);

            return Response::json(array('msg' => 'success'));

        } else {
            return Response::json(array("msg" => "notfound"));
        }
    }

    //metodo para enviar respuesta a un mensaje
    public function postSendanswer(Request $request)
    {


        if ($request->get('phone') == 0) {
            return Response::json(array('msg' => 'errorPhone'));
        }

        //verificamos que gateway esta activo
        $modem = Helpers::get_api_options('modem');

        if ($modem['e'] == '1') {

            //get connection data for login ruter
            $router = new RouterConnect();
            $con = $router->get_connect($modem['r']);
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                Psms::send_sms($API, $modem['p'], $modem['c'], $request->get('phone'), $request->get('message'));

                return Response::json(array('msg' => 'success'));

            } else {

                return Response::json(array('msg' => 'errorConnect'));
            }

        }//end enabled modem

        $smsg = Helpers::get_api_options('smsgateway');

        if ($smsg['e'] == '1') {


            try {

                // Configure client
                $config = Configuration::getDefaultConfiguration();
                $config->setApiKey('Authorization', $smsg['t']);
                $apiClient = new ApiClient($config);
                $messageClient = new MessageApi($apiClient);
                //buscamos el
                $sendMessageRequest1 = new SendMessageRequest([
                    'phoneNumber' => $request->get('phone'),
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


        }//end enabled sms gateway

    }

    //metodo para listar grupo de sms
    public function postListgroup(Request $request)
    {

        $group_id = $request->get('extra_search', 0);

        if (!empty($group_id)) {

            $sms_temp = TempSms::where('sms_id', $group_id)->get();

            $counter = count($sms_temp);
            $c = 0;
            foreach ($sms_temp as $key) {

                $client = Client::find($key->client_id);
                if ($c <= $counter) {
                    $associativeArray[$c]['name'] = $client->name;
                    $associativeArray[$c]['phone'] = $client->phone;
                    $associativeArray[$c]['status'] = $key->status;
                }

                $c++;
            }

            return Response::json($associativeArray);


        } else {


            return Response::json([]);

        }

    }

	public function getTwiliosms()
		{

			$twilioo = GlobalApi::where('name', '=', 'twiliosms')->first();
			$twsidtoken = array();
			$twsidtoken['options'] =json_decode($twilioo->options, true);
			$account_sid = $twsidtoken['options']['t'];
			$auth_token = $twsidtoken['options']['d'];
			//die;
			$twilio_number = "+17867890020";
			$client = new Client($account_sid, $auth_token);
			$client->messages->create(
					'+918591669156',
				array(
					'from' => $twilio_number,
					'body' => 'I sent this message from Twilio!'
				)
			);
		}


}
