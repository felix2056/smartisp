<?php

namespace App\Http\Controllers;
use App\libraries\Helpers;
use App\models\OdbSplitter;
use App\models\Router;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class GetMapController extends BaseController
{
    private $icons = [
        'client' => [
            'type' => 'image',
            'size' => [32, 32],
            'url' => '/assets/markers/device.png',
            'code' => 'image_device',
            'color' => ''
        ],
        'router' => [
            'type' => 'image',
            'size' => [48, 48],
            'url' => '/assets/markers/tower.png',
            'code' => 'image_tower',
            'color' => ''
        ]
    ];

    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }

     //metodo para recuperar coordenadas de routers y clientes
    public function postGpsmap2(Request $request)
    {
        $router = $request->get('router');



        if ($router == 0) {
            //obtenemos todos los routers y todos los clientes
            $routers = OdbSplitter::all();
            //Obtenemos todos los clientes
            //generamos los clientes segun el router
            $clients = DB::table('clients')
            ->join('client_services', 'client_services.client_id', '=', 'clients.id')
            ->join('plans', 'plans.id', '=', 'client_services.plan_id')
            ->join('odb_splitter', 'odb_splitter.id', '=', 'clients.odb_id')
            ->select('clients.id', 'clients.name', 'client_services.ip', 'clients.address', 'clients.coordinates As cl_coordinates', 'clients.phone',
                'plans.name As plan_name', 'client_services.status', 'odb_splitter.coordinates As ro_coordinates', 'clients.odb_geo_json', 'clients.odb_geo_json_styles', 'clients.map_marker_icon')->get();

        } else {
            //mostramos un router seleccionado
            $routers = OdbSplitter::where('id', $router)->get();

            //generamos los clientes segun el router
            $clients = DB::table('clients')
            ->join('client_services', 'client_services.client_id', '=', 'clients.id')
            ->join('plans', 'plans.id', '=', 'client_services.plan_id')
            ->join('odb_splitter', 'odb_splitter.id', '=', 'clients.odb_id')
            ->select('clients.id', 'clients.name', 'client_services.ip', 'clients.address', 'clients.coordinates As cl_coordinates', 'clients.phone',
                'plans.name As plan_name', 'client_services.status', 'odb_splitter.coordinates As ro_coordinates', 'clients.odb_geo_json', 'clients.odb_geo_json_styles', 'clients.map_marker_icon')->where('clients.odb_id', $router)->get();
        }


        if (count($routers) > 0) {
            # code...

            $map_routers = array();//array nodes

            //Google street view
            $GoogleStreet = Helpers::get_api_options('googlestreetview');

            if (count($GoogleStreet) > 0) {
                $map_streetkey = $GoogleStreet['k'];
            } else {
                $map_streetkey = '';
            }


            //generamos la ubicacion de los nodos o routers
            $i = 0;
            foreach ($routers as $router) {

                //generamos solo aquellos que poseean coordenadas
                if ($router->coordinates != '0') {

                    $map_routers[$i]['descr'] = '<h5><i class="fa fa-bullseye"></i> ' . $router->name . '</h5>';
                    //get coordinates
                    $cor = explode(',', $router->coordinates);
                    if(count($cor) > 1) {
                        $map_routers[$i]['latitud'] = $cor[0];
                        $map_routers[$i]['longitud'] = $cor[1];
                    }
                    $map_routers[$i]['id'] = $router->id;
                    $map_routers[$i]['type'] = 'router';
                    $map_routers[$i]['skey'] = $map_streetkey;
                    $map_routers[$i]['icon'] = !empty($router->map_marker_icon)? $router->map_marker_icon : $this->icons['router'];
                    $map_routers[$i]['icono'] = '/assets/markers/tower.png';
                }

                $i++;

            }//end for

            if (count($clients) > 0) {

                $map_clients = array();//array nodes
                $c = 0;
                foreach ($clients as $client) {

                    if (!empty($client->cl_coordinates) && !empty($client->ro_coordinates)) {

                        if ($client->status == 'ac')
                            $st = 'Servicio activo';
                        else
                            $st = 'Servicio cortado';

                        $map_clients[$c]['descr'] = '<h5><i class="fa fa-user"></i> ' . $client->name . '</h5><p><i class="fa fa-wifi"></i> ' . $st . '</p><p><i class="fa fa-tachometer"></i> ' . $client->plan_name . '</p><p><i class="fa fa-phone-square"></i> ' . $client->phone . '</p><p><i class="fa fa-laptop"></i> ' . $client->ip . '</p><p><i class="fa fa-home"></i> ' . $client->address . '</p>';
                        //get coordinates for clients
                        $cor = explode(',', $client->cl_coordinates);

                        if(count($cor) > 1) {
                            $map_clients[$c]['latitud'] = $cor[0];
                            $map_clients[$c]['longitud'] = $cor[1];
                        }

                        $map_clients[$c]['icono'] = '/assets/markers/device.png';
                        $map_clients[$c]['icon'] = !empty(json_decode($client->map_marker_icon)) ? json_decode($client->map_marker_icon) : $this->icons['client'];
                        $map_clients[$c]['id'] = $client->id;
                        $map_clients[$c]['type'] = 'client';

                        //get coordinates router parent
                        $cor2 = explode(',', $client->ro_coordinates);
                        if(count($cor2) > 1) {
                            $map_clients[$c]['elatitud'] = $cor2[0];
                            $map_clients[$c]['elongitud'] = $cor2[1];
                            $map_clients[$c]['geo_json'] = json_decode($client->odb_geo_json);
                            $map_clients[$c]['geo_json_styles'] = json_decode($client->odb_geo_json_styles);
                        }

                        $map_clients[$c]['skey'] = $map_streetkey;
                    }

                    $c++;
                }

                //$skey = array("skey" => $map_streetkey);

                $result = array_merge($map_routers, $map_clients);

                return Response::json($result);


            } else {

                return Response::json([]);
            }

        } else {

            return Response::json([]);
        }


    }



    //metodo para recuperar coordenadas de routers y clientes
    public function postGpsmap(Request $request)
    {
        $router = $request->get('router');

        if ($router == 0) {
            //obtenemos todos los routers y todos los clientes
            $routers = Router::all();
            //Obtenemos todos los clientes
            //generamos los clientes segun el router
            $clients = DB::table('clients')
            ->join('client_services', 'client_services.client_id', '=', 'clients.id')
            ->join('plans', 'plans.id', '=', 'client_services.plan_id')
            ->join('routers', 'routers.id', '=', 'client_services.router_id')
            ->select('clients.id', 'clients.name', 'client_services.ip', 'clients.address', 'clients.coordinates As cl_coordinates', 'clients.phone',
                'plans.name As plan_name', 'client_services.status', 'routers.coordinates As ro_coordinates', 'client_services.id as client_service_id', 'client_services.geo_json', 'client_services.geo_json_styles', 'clients.map_marker_icon')->get();

        } else {
            //mostramos un router seleccionado
            $routers = Router::where('id', $router)->get();
            //generamos los clientes segun el router
            $clients = DB::table('clients')
                ->join('client_services', 'client_services.client_id', '=', 'clients.id')
                ->join('plans', 'plans.id', '=', 'client_services.plan_id')
                ->join('routers', 'routers.id', '=', 'client_services.router_id')
                ->select('clients.id', 'clients.name', 'client_services.ip', 'clients.address', 'clients.coordinates As cl_coordinates', 'clients.phone',
                'plans.name As plan_name', 'client_services.status', 'routers.coordinates As ro_coordinates', 'client_services.id as client_service_id', 'client_services.geo_json', 'client_services.geo_json_styles', 'clients.map_marker_icon')->where('client_services.router_id', $router)->get();
        }


        if (count($routers) > 0) {
            # code...

            $map_routers = array();//array nodes

            //Google street view
            $GoogleStreet = Helpers::get_api_options('googlestreetview');

            if (count($GoogleStreet) > 0) {
                $map_streetkey = $GoogleStreet['k'];
            } else {
                $map_streetkey = '';
            }


            //generamos la ubicacion de los nodos o routers
            $i = 0;
            foreach ($routers as $router) {

                //generamos solo aquellos que poseean coordenadas
                if ($router->coordinates != '0') {

                    $map_routers[$i]['descr'] = '<h5><i class="fa fa-bullseye"></i> ' . $router->name . '</h5><p>IP: ' . $router->ip . '</p>';
                    //get coordinates
                    $cor = explode(',', $router->coordinates);

                    $map_routers[$i]['latitud'] = $cor[0];
                    $map_routers[$i]['longitud'] = $cor[1];
                    $map_routers[$i]['id'] = $router->id;
                    $map_routers[$i]['type'] = 'router';
                    $map_routers[$i]['skey'] = $map_streetkey;
                    $map_routers[$i]['icon'] = !empty($router->map_marker_icon)? $router->map_marker_icon : $this->icons['router'];
                    $map_routers[$i]['icono'] = '/assets/markers/tower.png';
                }

                $i++;

            }//end for

            if (count($clients) > 0) {

                $map_clients = array();//array nodes
                $c = 0;
                foreach ($clients as $client) {

                    if (!empty($client->cl_coordinates) && !empty($client->ro_coordinates)) {

                        if ($client->status == 'ac')
                            $st = 'Servicio activo';
                        else
                            $st = 'Servicio cortado';

                        $map_clients[$c]['descr'] = '<h5><i class="fa fa-user"></i> ' . $client->name . '</h5><p><i class="fa fa-wifi"></i> ' . $st . '</p><p><i class="fa fa-tachometer"></i> ' . $client->plan_name . '</p><p><i class="fa fa-phone-square"></i> ' . $client->phone . '</p><p><i class="fa fa-laptop"></i> ' . $client->ip . '</p><p><i class="fa fa-home"></i> ' . $client->address . '</p>';
                        //get coordinates for clients
                        $cor = explode(',', $client->cl_coordinates);
                        if(count($cor) > 1) {
                            $map_clients[$c]['latitud'] = $cor[0];
                            $map_clients[$c]['longitud'] = $cor[1];
                            $map_clients[$c]['id'] = $client->id;
                            $map_clients[$c]['type'] = 'client';
                            $map_clients[$c]['icon'] = !empty(json_decode($client->map_marker_icon)) ? json_decode($client->map_marker_icon) : $this->icons['client'];
                            $map_clients[$c]['icono'] = '/assets/markers/device.png';

                        }


                        //get coordinates router parent
                        $cor2 = explode(',', $client->ro_coordinates);
                        if(count($cor2) > 1) {
                            $map_clients[$c]['elatitud'] = $cor2[0];
                            $map_clients[$c]['elongitud'] = $cor2[1];
                            $map_clients[$c]['geo_json'] = json_decode($client->geo_json);
                            $map_clients[$c]['geo_json_styles'] = json_decode($client->geo_json_styles);
                            $map_clients[$c]['client_service_id'] = $client->client_service_id;
                            $map_clients[$c]['skey'] = $map_streetkey;
                        }
                    }

                    $c++;
                }

                //$skey = array("skey" => $map_streetkey);

                $result = array_merge($map_routers, $map_clients);

                return Response::json($result);


            } else {

                return Response::json([]);
            }

        } else {

            return Response::json([]);
        }


    }


}
