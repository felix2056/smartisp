<?php

namespace App\Http\Controllers;

use App\libraries\Getip;
use App\libraries\Helpers;
use App\libraries\Validator;
use App\models\AdvSetting;
use App\models\GlobalSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use SMSGatewayMe\Client\ApiClient;
use SMSGatewayMe\Client\Configuration;
use SMSGatewayMe\Client\Api\DeviceApi;
use App\models\GlobalApi;
class GetConfigController extends BaseController
{

    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function postAdv()
    {
        //recuperamos los planes
        $adv = AdvSetting::all()->first();
        $ip = new Getip();
        $ipServer = $ip->getServer();
        if (count(json_decode($adv, 1)) == 0)
            return Response::json(array('msg' => 'noadv'));

        $data = array(
            'success' => true,
            'id' => $adv->id,
            'url' => $adv->ip_server,
            'ips' => $ipServer,
            'path' => $adv->server_path

        );

        return Response::json($data);
    }

    //metodo para recuperar informacion de las apis
    public function postApis()
    {
        //recuperamos la informacion de la api de mikrotik
        $mikrotik = Helpers::get_api_options('mikrotik');
        //otros apis
        //Google masp api
        $GoogleMaps = Helpers::get_api_options('googlemaps');

        $smartolt = Helpers::get_api_options('smartolt');

        if (count($GoogleMaps) > 0) {
            $GoogleMaps = $GoogleMaps['k'];
        } else {
            $GoogleMaps = '';
        }
        //Google street view
        $GoogleStreet = Helpers::get_api_options('googlestreetview');

        if (count($GoogleStreet) > 0) {
            $GoogleStreet = $GoogleStreet['k'];
        } else {
            $GoogleStreet = '';
        }
		if(!isset($mikrotik['d'])){
			$mikrotik['d']="";
		}
		if(!isset($mikrotik['a'])){
			$mikrotik['a']="";
		}
		if(!isset($mikrotik['s'])){
			$mikrotik['s']="";
		}
		if(!isset($mikrotik['t'])){
			$mikrotik['t']="";
		}

        if(!isset($smartolt['l'])){
            $smartolt['l']="";
        }
        if(!isset($smartolt['a'])){
            $smartolt['a']="";
        }
        if(!isset($smartolt['c'])){
            $smartolt['c']="";
        }
        #......
        $data = array(
            'msg' => true,
            //Mikrotik API
            'mk_debug' => $mikrotik['d'],
            'mk_attempts' => $mikrotik['a'],
            'mk_ssl' => $mikrotik['s'],
            'mk_timeout' => $mikrotik['t'],
            //other apis
            //////Google Maps/////

            'gmap_key' => $GoogleMaps,
            /////Google Street View ////
            'gstreet_key' => $GoogleStreet,


            'url_smartolt' => $smartolt['l'],
            'apikey_smartolt' => $smartolt['a'],
            'check_smartolt' => $smartolt['c'],

        );

        return Response::json($data);
    }

    //metodo para obtener la id del dispositivo para SMS Gateway
    public function postDeviceid(Request $request)
    {


        if (!empty($request->get('token'))) {


            $friendly_names = array(
                'token' => 'API Token'
            );

            $rules = array(
                'token' => 'required'
            );

            $validation = Validator::make($request->all(), $rules);
            $validation->setAttributeNames($friendly_names);

            if ($validation->fails())
                return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);


            try {
                // Configure client
                $config = Configuration::getDefaultConfiguration();
                $config->setApiKey('Authorization', $request->get('token'));
                $apiClient = new ApiClient($config);
                $deviceClient = new DeviceApi($apiClient);
                $devices = $deviceClient->searchDevices();

                //////////// Generador de array clave valor dinamico ///////////
                $result = array();

                for ($i = 0; $i < count($devices['results']); $i++) {

                    $result[$i]['name'] = $devices['results'][$i]['name'];
                    $result[$i]['id'] = $devices['results'][$i]['id'];

                }

                ///////////// fin del generador ////////////////////////////////

                return Response::json($result);


            } catch (\Exception $e) {

                return Response::json(['msg' => 'error', 'errors' => [$e->getMessage()]]);
            }

        } else {
            return Response::json([]);
        }

    }

    //metodo para recuperar informacion general
    public function postGeneral()
    {
        $global = GlobalSetting::first();
        if (count(json_decode($global, 1)) == 0)
            return Response::json(array('success' => false));

        $data = array(
            'success' => true,
            'company' => $global->company,
            'smoney' => $global->smoney,
            'money' => $global->nmoney,
            'numbill' => $global->num_bill,
            'sendprebill' => $global->send_prebill,
            'sendpresms' => $global->send_presms,
			'sendprewhatsapp' => $global->send_prewhatsapp,
			'sendprewaboxapp' => $global->send_prewaboxapp,
            'sendprewhatsappcloudapi' => $global->send_prewhatsappcloudapi,
            'tolerance' => $global->tolerance,
            'email' => $global->email,
            'email_ticket' => $global->email_tickets,
            'server' => $global->server,
            'port' => $global->port,
            'protocol' => $global->protocol,
            'zone' => $global->zone,
            'debug' => $global->debug,
            'numdays' => $global->before_days,
            'hrs' => $global->send_hrs,
            'backups' => $global->backups,
            'hrsbackups' => $global->create_copy,
            'default_map' => $global->default_location,
            'dni' => $global->dni,
            'phone' => $global->phone,
            'phone_code' => $global->phone_code,
            'delay_sms' => $global->delay_sms,
            'company_email' => $global->company_email,
        );

        return Response::json($data);
    }

    //metodo para recuperar ubicacion por defecto google maps
    public function getDefaultlocation()
    {

        $global = GlobalSetting::all()->first();
        $data = array('coordinates' => $global->default_location);

        return Response::json($data);
    }

    //metodo para recuperar el modo debug
    public function getDebug()
    {

        $global = GlobalSetting::all()->first();
        if (count(json_decode($global, 1)) == 0)
            return Response::json(array('success' => false));

        $data = array('debug' => $global->debug);

        return Response::json($data);
    }

    //metodo para recuperar la configuracion de email
    public function postEmail()
    {
        $global = GlobalSetting::all()->first();
        if ($global->email == 'ejemplo@ejemplo.com' || $global->password == '') {
            return Response::json(array('status' => true));
        } else {
            return Response::json(array('status' => false));
        }
    }

    //metodo para recuperar la configuracion de sms
    public function postSms()
	{
		$sts=0;
		$gateway_wht = GlobalApi::where('name', 'twiliowhatsappsms')->first();
		if(isset($gateway_wht->id)){
			$what_options=json_decode($gateway_wht->options,TRUE);
		}
		else{
			$what_options=array('t'=>'','d'=>'','e'=>'');
		}
		$gateway_sms = GlobalApi::where('name', 'twiliosms')->first();
		if(isset($gateway_sms->id)){
			$sms_options=json_decode($gateway_sms->options,TRUE);
		}
		else{
			$sms_options=array('t'=>'','d'=>'','e'=>'');
		}				$gateway_wabq = GlobalApi::where('name', 'weboxapp')->first();		if(isset($gateway_wabq->id)){			$wabq_options=json_decode($gateway_wabq->options,TRUE);		}		else{			$wabq_options=array('t'=>'','d'=>'','e'=>'');		}
        $whatsappcloudapi = GlobalApi::where('name', 'whatsappcloudapi')->first();
        $whatsappcloudapi = $whatsappcloudapi && !empty($whatsappcloudapi->status);
        if($what_options['e']==1 || $sms_options['e']==1 || $wabq_options['e']==1 || $whatsappcloudapi){
			return Response::json(array('status' => false));
		}
		else{
			return Response::json(array('status' => true));
		}
	}

    //metodo para recuperar la cinfiguracion ip servidor
    public function postIpserver()
    {
        $ip = AdvSetting::all()->first();
        if (empty($ip->ip_server)) {
            return Response::json(array('status' => true));
        } else {
            return Response::json(array('status' => false));
        }
    }


}
