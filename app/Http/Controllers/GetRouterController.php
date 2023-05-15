<?php

namespace App\Http\Controllers;

use App\libraries\Chkerr;
use App\libraries\Helpers;
use App\libraries\Hotspot;
use App\libraries\Mikrotik;
use App\libraries\Pencrypt;
use App\libraries\Ppp;
use App\libraries\RouterConnect;
use App\libraries\Slog;
use App\models\AddressRouter;
use App\models\ControlRouter;
use App\models\Network;
use App\models\radius\Nas;
use App\models\radius\Radius;
use App\models\Router;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;


class GetRouterController extends BaseController
{

    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function postData(Request $request)
    {

        $router = Router::find($request->router);

        if (!$router)
            return Response::json(array('success' => false));
        // check connect router

        //verificamos si el router esta configurado para trabajar sin conexión
        if ($router->connection == 1) {

            $data = array(
                'connect' => 'nc',
                'success' => true,
                'id' => $router->id,
                'name' => $router->name,
                'model' => $router->model,
                'location' => $router->coordinates,
                'address' => $router->location,
                'ip' => $router->ip,
                'login' => $router->login
            );

        } else {

            $encrypt = new Pencrypt();
            $password = $router->password;
            $password = $encrypt->decode($password);
            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($router->port, $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($router->ip, $router->login, $password)) {

                $data = array(
                    'connect' => 'ok',
                    'success' => true,
                    'id' => $router->id,
                    'name' => $router->name,
                    'model' => $router->model,
                    'location' => $router->coordinates,
                    'address' => $router->location,
                    'ip' => $router->ip,
                    'port' => $router->port,
                    'login' => $router->login
                );

            } else {

                $data = array(
                    'connect' => 'bad',
                    'success' => true,
                    'id' => $router->id,
                    'name' => $router->name,
                    'model' => $router->model,
                    'location' => $router->coordinates,
                    'address' => $router->location,
                    'ip' => $router->ip,
                    'port' => $router->port,
                    'login' => $router->login
                );
            }

        }

        /**agregamos para verificar si existe un radius configurado**/
        $existe_radius = Radius::where('router_id',$request->router)->first();
        if($existe_radius){
/*            $data['radius_server'] = $existe_radius->server_ip;
            $data['radius_port'] = $existe_radius->server_port;
            $data['radius_user'] = $existe_radius->db_user;
            $data['radius_pass'] = $existe_radius->db_pass;
            $data['radius_dbname'] = $existe_radius->db_name;*/
//            $data['secret'] = $existe_radius->secret;
        }
        $data['radius_secret'] = isset($existe_radius->secret) && $existe_radius->secret != "" ? $existe_radius->secret : md5($router->name);

        return Response::json($data);

    }

    //metodo para obtener hubicación gps del router
    public function postLocation()
    {

        $location = Router::all()->first();

        if (count($location) > 0) {
            return Response::json(["coordinates" => $location->coordinates]);
        } else {
            return Response::json(["coordinates" => '0']);
        }

    }

    //metodo recupera la conficuracion de control y seguridad
    public function postControl(Request $request)
    {
        $control = ControlRouter::where('router_id', '=', $request->id)->first();

        if (count(json_decode($control, 1)) == 0)
            return Response::json(array('success' => false));

        $num = Router::find($request->id);

        $data = array(
            'success' => true,
            'control' => $control->type_control,
            'arpmac' => $control->arpmac,
            'dhcp' => $control->dhcp,
            'adv' => $control->adv,
            'address_list' => $control->address_list,
            'count' => $num->clients
        );

        return Response::json($data);
    }

    //metodo para obtener configuracion de planes mikrotik
    public function postConfigplan(Request $request)
    {
        $control = ControlRouter::where('router_id', '=', $request->get('id'))->get();
        if (count(json_decode($control, 1)) == 0)
            return Response::json(array('success' => false));

        //no se encontro el perfil
        $router = new RouterConnect();
        $con = $router->get_connect($request->get('id'));
        //GET all data for API
        $conf = Helpers::get_api_options('mikrotik');
        $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
        $API->debug = $conf['d'];
        $log = new Slog();
        $process = new Chkerr();

        //verificamos el tipo de control del router
        switch ($control[0]->type_control) {

            case 'ho':

                # conectamos con el router para recuperar los profiles
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {
                    //recuperamos todos los pool de ips
                    $PROFILES = Hotspot::get_user_profiles_list($API);

                    return Response::json(array('msg' => true, $PROFILES));

                } else {
                    return $process->show('errorConnect');
                }

                break;

            case 'pp':

                # conectamos con el router para recuperar los profiles
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {
                    //recuperamos todos los pool de ips
                    $PROFILES = Ppp::get_ppp_profile_list($API);

                    return Response::json(array('msg' => true, $PROFILES));

                } else {
                    return $process->show('errorConnect');
                }

                break;
        }//end switch

    }

    //metodo para obtener todas las ip/resdes del router seleccionado
    public function postIpnet(Request $request)
    {
        $router_id = $request->get('id');
        $networks = AddressRouter::where('router_id', $router_id)->get();

        if (count($networks) > 0) { //hay redes
            if (Network::where('address_id', $networks[0]->id)->where('is_used', '0')->count() > 0) {
                return Response::json(["net" => "ok"]);
            } else {
                return Response::json(["net" => "full"]);
            }
        } else {
            return Response::json(["net" => "notfound"]);
        }
    }

}
