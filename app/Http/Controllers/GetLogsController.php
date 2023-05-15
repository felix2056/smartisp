<?php

namespace App\Http\Controllers;
use App\libraries\Helpers;
use App\libraries\Mikrotik;
use App\libraries\RouterConnect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class GetLogsController extends BaseController
{

    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function postLog(Request $request)
    {
        $router_id = $request->get('id');

        $router = new RouterConnect();
        $con = $router->get_connect($router_id);
        //GET all data for API
        $conf = Helpers::get_api_options('mikrotik');
        $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
        $API->debug = $conf['d'];

        if ($API->connect($con['ip'], $con['login'], $con['password'])) {

            $API->write('/log/print');
            $READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);
            $general = $ARRAY;
            $API->disconnect();
        } else
            return Response::json(array('msg' => 'errorConnect'));

        return Response::json($general);
    }
}
