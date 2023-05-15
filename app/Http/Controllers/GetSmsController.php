<?php

namespace App\Http\Controllers;
use App\libraries\Helpers;
use App\libraries\Mikrotik;
use App\libraries\RouterConnect;
use App\models\GlobalSetting;
use App\models\SmsInbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class GetSmsController extends BaseController
{

    public function postGateway()
    {

        $modem = Helpers::get_api_options('modem');
        $smsgateway = Helpers::get_api_options('smsgateway');
        //recuperamos informacion adicional
        $global = GlobalSetting::all()->first();
		if(!isset($modem['r'])){
			$modem['r']="";
		}
		if(!isset($modem['p'])){
			$modem['p']="";
		}
		if(!isset($modem['c'])){
			$modem['c']="";
		}
		if(!isset($modem['e'])){
			$modem['e']="";
		}

		if(!isset($smsgateway['t'])){
			$smsgateway['t']="";
		}

		if(!isset($smsgateway['d'])){
			$smsgateway['d']="";
		}

		if(!isset($smsgateway['e'])){
			$smsgateway['e']="";
		}

        if (count($modem) > 0) {

            $router = $modem['r'];
            $port = $modem['p'];
            $channel = $modem['c'];
            $em = $modem['e'];

        } else {

            $router = "no";
            $port = 'no';
            $channel = 'no';
            $em = false;
        }

        if (count($smsgateway) > 0) {

            $token = $smsgateway['t'];
            $deviceid = $smsgateway['d'];
            $esmsg = $smsgateway['e'];

        } else {

            $token = '';
            $deviceid = 'no';
            $esmsg = false;
        }


        $data = array(
            "router" => $router,
            "port" => $port,
            "channel" => $channel,
            "smsmodem" => $em,
            //sms gateway
            "token" => $token,
            "deviceid" => $deviceid,
            "smsgateway" => $esmsg,
            //other info
            "phonecode" => $global->phone_code,
            "delaysms" => $global->delay_sms
        );

        return Response::json($data);
    }


    public function postUsb(Request $request)
    {

        $router = new RouterConnect();

        $con = $router->get_connect($request->get('id'));
        //GET all data for API
        $conf = Helpers::get_api_options('mikrotik');
        //inicializacion de clases principales
        $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
        $API->debug = $conf['d'];

        if ($API->connect($con['ip'], $con['login'], $con['password'])) {

            $API->write("/port/print", true);
            $READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);

            if (count($ARRAY) > 0) {
                return Response::json($ARRAY);
            } else {
                return Response::json(array('msg' => 'noports'));
            }
        } else
            return Response::json(array('msg' => 'errorConnect'));

    }

    public function postAnswersms(Request $request)
    {
        $sms = SmsInbox::find($request->get('id', 0));

        if (is_null($sms)) {
            return Response::json(array('msg' => 'nosms'));
        }

        $data = array(
            'client' => $sms->client,
            'phone' => $sms->phone,
            'date_in' => $sms->received_date,
            'message' => $sms->message
        );
        //cambiamos el estado del mensaje
        $sms->open = 1;
        $sms->save();

        return Response::json($data);
    }


}
