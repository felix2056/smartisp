<?php

namespace App\Http\Controllers;
use App\libraries\Helpers;
use App\libraries\Mikrotik;
use App\libraries\RouterConnect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class GetRouterInfoController extends BaseController
{

    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function postData(Request $request)
    {
        $router_id = $request->get('id');

        if (empty($router_id))
            return Response::json(array('msg' => 'error'));

        $router = new RouterConnect();
        $con = $router->get_connect($router_id);
        //GET all data for API
        $conf = Helpers::get_api_options('mikrotik');
        $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
        $API->debug = $conf['d'];

        if ($API->connect($con['ip'], $con['login'], $con['password'])) {

            $API->write('/system/resource/print', true);
            $READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);

            $response = $ARRAY;
            // hardware get model routerboard
            $model = $response[0]['board-name'];
            $ar = $response[0]['architecture-name'];
            $os = $response[0]['version'];
            $uptime = $response[0]['uptime'];
            $cpu = $response[0]['cpu-load'];
            $cpu2 = $response[0]['cpu'];
            $cpu3 = $response[0]['cpu-frequency'];
            $cpu4 = $response[0]['cpu-count'];
            $memory = $response[0]['total-memory'];
            $memory = ($memory / 1024 / 1024);
            $memory = number_format($memory, 2);
            $memory2 = $response[0]['free-memory'];
            $memory2 = ($memory2 / 1024 / 1024);
            $memory2 = number_format($memory2, 2);

            $hdd = $response[0]['total-hdd-space'];
            $hdd = ($hdd / 1024 / 1024);
            $hdd = number_format($hdd, 2);
            $hdd2 = $response[0]['free-hdd-space'];
            $hdd2 = ($hdd2 / 1024 / 1024);
            $hdd2 = number_format($hdd2, 2);

            if (isset($response[0]['bad-blocks'])) {
                $blocks = $response[0]['bad-blocks'];
            } else {
                $blocks = 0;
            }


            $data = array(
                'connect' => true,
                'success' => true,
                'hardware' => $model . ' | Arquitectura - ' . $ar,
                'os' => $os,
                'active' => $uptime,
                'cpu' => $cpu . ' %',
                'cpu2' => $cpu2 . ' | ' . $cpu3 . ' Mhz | Nucleos: ' . $cpu4,
                'ram' => 'Total: ' . $memory . 'MB | Libre: ' . $memory2 . 'MB',
                'disk' => 'Total: ' . $hdd . 'MB | Libre: ' . $hdd2 . 'MB',
                'blocks' => $blocks . ' % Dañados.'
            );

            $API->disconnect();

        } else {

            return Response::json(array('msg' => 'error'));
        }
        //end
        return Response::json($data);


    }

    //metodo para recuperar el trafico de la interfaz lan en tiempo real
    public function postLan(Request $request)
    {
        $router_id = $request->get('id');

        $router = new RouterConnect();
        $con = $router->get_connect($router_id);
        //GET all data for API
        $conf = Helpers::get_api_options('mikrotik');
        $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
        $API->debug = $conf['d'];

        $lan = $con['lan'];

        if ($API->connect($con['ip'], $con['login'], $con['password'])) {

            $rows = array();
            $rows2 = array();
            $API->write("/interface/monitor-traffic", false);
            $API->write("=interface=" . $lan, false);
            $API->write("=once=", true);
            $READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);
            if (count($ARRAY) > 0) {
                $rx = $ARRAY[0]["rx-bits-per-second"];
                $tx = $ARRAY[0]["tx-bits-per-second"];
                $rows['name'] = 'Tx';
                $rows['data'][] = $tx;
                $rows2['name'] = 'Rx';
                $rows2['data'][] = $rx;
            }

        }


        $API->disconnect();

        $result = array();
        array_push($result, $rows);
        array_push($result, $rows2);
        print json_encode($result, JSON_NUMERIC_CHECK);

    }

}
