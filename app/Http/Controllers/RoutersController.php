<?php

namespace App\Http\Controllers;
use App\Classes\Reply;
use App\Console\Commands\UpdateRouterStatus;
use App\DataTables\RouterDataTable;
use App\Events\AddClientsOnNewRouter;
use App\Events\RemoveClientsFromOldRouter;
use App\Http\Requests\ChangeIpRequest;
use App\Http\Requests\UpdateRouterDetailRequest;
use App\libraries\Chkerr;
use App\libraries\Helpers;
use App\libraries\Mikrotik;
use App\libraries\Pencrypt;
use App\libraries\Radius as RadiusLibrary;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\libraries\Slog;
use App\libraries\Validator;
use App\models\AddressRouter;
use App\models\ClientService;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\Network;
use App\models\Permission;
use App\models\radius\Nas;
use App\models\radius\Radius;
use App\models\Router;
use App\Service\CommonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use Config;

class RoutersController extends BaseController
{
    public function __construct()
    {
    	parent::__construct();
	    $this->middleware(function ($request, $next) {
		    $this->username = auth()->user()->username;
		    $this->userId = auth()->user()->id;
		    return $next($request);
	    });
    }

    public function getIndex(RouterDataTable $dataTable)
    {

        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = Permission::where('user_id','=',$id)->first();
        $access = $perm->access_routers;

        if($level=='ad' || $access == true)
        {

            $global = GlobalSetting::first();
            $GoogleMaps = Helpers::get_api_options('googlemaps');

            if (count($GoogleMaps) > 0 || $global->map_type == 'open_street_map') {
                $key = $GoogleMaps['k'] ?? 1;
            } else {
                $key = 0;
            }

            $permissions = array("clients"=> $perm->access_clients, "plans" => $perm->access_plans, "routers" => $perm->access_routers,
                "users" => $perm->access_users, "system" => $perm->access_system, "bill" => $perm->access_pays,
                "template" =>$perm->access_templates, "ticket" => $perm->access_tickets, "sms" => $perm->access_sms,
                "reports" => $perm->access_reports, "ms" => '',
                "v" => $global->version, "st" => $global->status, "map" => $key,
                "lv" => $global->license, "company" => $global->company,
                'permissions' => $perm,
							 // menu options
				"menu" => 'routers', "submenu" => 1,
                "global" => $global
			);

            if(Auth::user()->level=='ad')
                @setcookie("hcmd", 'kR2RsakY98pHL', time()+7200,"/","",0, true);

            return $dataTable->render('routers.index',$permissions);

        }
        else
            return Redirect::to('admin');

    }

    public function postCreate(Request $request)
    {

        if ($request->get('connection')==1) {
            //Sin conexion solo controlamos nombre y ubicación
            $friendly_names = array(
                'name' => __('app.name'),
                'address' => __('app.address')
            );

            $rules = array(
                'name' => 'required|unique:routers',
                'address' => 'required'
            );
        }
        else{

            $friendly_names = array(
                'name' => __('app.name'),
                'address' => __('app.address'),
                'ip' => 'IP',
                'port' => __('app.port')
            );

            $rules = array(
                'name' => 'required|unique:routers',
                'address' => 'required',
                'login' => 'required',
                'password' => 'required|min:3',
                'ip' => 'required',
                'port' => 'required|numeric|integer'
            );
        }

        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);
        if($validation->fails())
            return Response::json(['msg'=>'error','errors' => $validation->getMessageBag()->toArray()]);

        $encrypt = new Pencrypt();
        $router = new Router();

        $id = DB::table('routers')->insertGetId(
            array('name' => $request->get('name'), 'model' => 'none', 'location' => $request->get('address'), 'coordinates' => empty($request->get('location')) ? '0' : $request->get('location'),
                'ip' => empty($request->get('ip')) ? '0.0.0.0' : $request->get('ip'), 'login' => empty($request->get('login')) ? 'none' : $request->get('login'), 'password' => $encrypt->encode($request->get('password','none')),
                'port' => $request->get('port',8728), 'lan' => 'none', 'clients' => 0, 'connection' => $request->get('connection'), 'created_at' => date('Y-m-d h:i:s'), 'updated_at' => date('Y-m-d h:i:s'))
        );


        //registramos el control del router por defecto
        $newControl = new ControlRouter();
        $newControl->router_id = $id;
        $newControl->type_control = 'un'; //un  (unset) no configurado
        $newControl->dhcp = 0;
        $newControl->arpmac = 0;
        $newControl->adv = 0;
        $newControl->save();
		$name = $request->get('name');
	    CommonService::log("Se ha registrado un router: $name", $this->username, 'success' , $this->userId);

        return Response::json(array('msg'=>'success','id' => $id));
    }

    public function postDelete(Request $request)
    {

        $router_id = $request->get('id',0);

        $router = Router::find($router_id);

        if(is_null($router))
            return Response::json(array('msg'=>'notfound'));

        $routerName = $router->name;

        //verificamos si tiene clientes
        $clients = ClientService::where('router_id',$router_id)->count();

        if ($clients>0) {
            return Response::json(array('msg'=>'existclients'));
        }

        $global = GlobalSetting::first();
        $debug = $global->debug;
        //recuperamos el tipo de configuracion
        $type = ControlRouter::where('router_id','=',$router_id)->first();
        $adv = $type->adv;
        $type_control = $type->type_control;

        if ($adv==1) {
            $process = new Chkerr();
            //eliminamos los advs
            $router_con = new RouterConnect();
            $con = $router_con->get_connect($router_id);

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                if ($type_control=='pp' || $type_control=='pa' || $type_control=='ps' || $type_control=='pt') {
                    //quitamos reglas de bloqueo pppoe si existen
                    $networks = AddressRouter::where('router_id',$router_id)->get();
                    if (count($networks) > 0) {
                        foreach ($networks as $net) {
                            //iteramos las ip redes
                            $STATUS = $rocket->remove_advs_ppp($API,$net->network,$debug);

                            if ($debug==1) {
                                if($process->check($STATUS)){
                                    $API->disconnect();
                                    return $process->check($STATUS);
                                }//end if
                            }

                        }//end foreach
                    }//end if count networks

                    $STATUS = $rocket->remove_proxy_ppp($API,$debug);

                    if ($debug==1) {
                        if($process->check($STATUS)){
                            return $process->check($STATUS);
                        }
                    }

                }else{

                    //quitamos las reglas del portal cliente si existen y del web proxy
                    $STATUS = $rocket->remove_advs($API,$debug);

                    if ($debug==1) {
                        if($process->check($STATUS)){
                            return $process->check($STATUS);
                        }
                    }

                }

                if ($type_control=='pc' || $type_control=='pa' || $type_control=='ha') {
                    # eliminamos los parents de queuetree
                    $DELETE = $rocket->remove_queuetree_parent($API,$debug);

                    if ($debug==1) {
                        if($process->check($DELETE)){
                            return $process->check($DELETE);
                        }
                    }
                }

            }//end if connect

        }//end if adv

	    if ($type_control=='ra' || $type_control=='rp' || $type_control=='rr') {
            /** aplicamos la configuracion sobre el router **/
            $radius = new RadiusLibrary();
            $secret = Radius::where('router_id', $router->id)->first()->secret;

            $router_con = new RouterConnect();
            $con = $router_con->get_connect($router_id);

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {
                $radius->radius_delete($API, $secret,$request->radius_server);
            }

            Radius::where('router_id', $router->id)->delete();
            Nas::where('nasname', $router->ip)->delete();

        }


        $router->delete();

        ControlRouter::where('router_id','=',$router_id)->delete();

        $address = AddressRouter::where('router_id','=',$router_id)->first();

        if ($address) {
            $address->router_id = 0;
            $address->save();
        }

        //eliminamos las reglas qeu creo el sistema en el router

        //save log
	    CommonService::log("Se ha eliminado un router: $routerName", $this->username, 'danger' , $this->userId);

        return Response::json(array('msg'=>'success'));
    }

	public function postUpdate(Request $request)
	{

	    /**CUANDO ES VPN HAY QUE PONER LA PRIVADA DEL SERVIDOR (NO LA PUBLICA) EN EL ADDRESS DE LA CONF DE RADIUS DEL MKT
         * EN LA BASE DE DATOS RADIUS, EN LA TABLA NAS HAY QUE PONER LA PRIVADA DEL MKT
         * POR EJEMPLO SI EN EL MKT TENGO LA 10.0.1.18 --> EL REGISTRO NAS DEBE TENER ESA  IP
         * Y SI EL SERVER TIENE LA  10.0.1.1 --> EN LA CONF DEL RADIUS DEL MKT HAY QUE PONER ESA IP
         **/


        /**check connection to radius**/
        if($request->control == 'ra' || $request->control == 'rp' || $request->control == 'rr'){
            try {
                $nas = Nas::all();
            }
            catch (\Exception $e){
                return Response::json(array('msg'=>'errorConnectRadius')); //no se pudo conectar al router
            }

        }
        /**check if is '' new server radius, set previous value**/

        if($request->control == 'ra' || $request->control == 'rp' || $request->control == 'rr'){
            if($request->radius_server == "")
                $request->radius_server = env('DB_HOST_RADIUS');

            if($request->radius_server == "")
                $request->radius_server = $_SERVER["HTTP_HOST"];

        }

		$router_id = $request->get('router_id');
		$router = Router::find($router_id);
		$old_router = Router::find($router_id);
		$changeRouter = ($request->get('ip_edit') == $router->ip) ? false : true;

		$controlRouter = ControlRouter::where('router_id','=',$router_id)->first(); // es el control anterior

		if($request->control == 'dl') {
			$clientServices = ClientService::where('router_id',$router_id)->whereNull('mac')->orWhere('mac', '00:00:00:00:00:00')->first();
			if($clientServices) {
				return Response::json(array('msg'=>'mkerror'));
			}
		}

		if($changeRouter) {
			$encrypt = new Pencrypt();
			$routerDetails = [
				'ip' => $old_router->ip,
				'login' => $old_router->login,
				'password' => $encrypt->decode($old_router->password),
				'connect' => $old_router->connection,
				'port' => $old_router->port,
			];
			//GET all data for API
			$conf = Helpers::get_api_options('mikrotik');
			$API = new Mikrotik($routerDetails['port'],$conf['a'],$conf['t'],$conf['s']);
			$API->debug = $conf['d'];
			if ($API->connect($routerDetails['ip'], $routerDetails['login'], $routerDetails['password'])) {
				/**
				 * Si el tipo de control es por radius, entonces debemos:
				 * eliminar el nas en la bd de radius
				 * reiniciar el servicio de freradius para que tome el nas
				 * deshabilitar pppoe use radius por api de mkt
				 * sacar radius en mkt (con el secret)
				 **/
                $this->removeClientFromMikrotik($old_router, $controlRouter,$request->radius_server);
			} else{

				$API->disconnect();

				$connection = $router->connection;
				$router->name = $request->get('name_edit');
				$router->location = $request->get('address_edit');
				$router->coordinates = empty($request->get('location_edit')) ? '0' : $request->get('location_edit');

				if($connection == 0 && $request->get('status') != 'badConnect'){

					$router->ip = $request->get('ip_edit');
					$router->login = $request->get('login_edit');
					$router->port = $request->get('port_edit');
					$router->lan = $request->get('lan');
				}

				if ($connection == 0 && $request->get('status') == 'badConnect') {

					$router->ip = $request->get('ip_edit');
					$router->login = $request->get('login_edit');
					$router->port = $request->get('port_edit');
				}

				$encrypt = new Pencrypt();

				if($request->get('password') && $connection == 0){
					$new_pass = $request->get('password');
					$router->password = $encrypt->encode($new_pass);
				}

				$router->save();

				$encrypt = new Pencrypt();
				$routerDetails = [
					'ip' => $router->ip,
					'login' => $router->login,
					'password' => $encrypt->decode($router->password),
					'connect' => $router->connection,
					'port' => $router->port,
				];
				//GET all data for API
				$conf = Helpers::get_api_options('mikrotik');
				$API = new Mikrotik($routerDetails['port'],$conf['a'],$conf['t'],$conf['s']);
				$API->debug = $conf['d'];
				if ($API->connect($routerDetails['ip'], $routerDetails['login'], $routerDetails['password'])) {
					/**Si el tipo de control es 'ra'--> radius, entonces creamos el radius antes de mandar el router**/
					if($request->control == 'ra' || $request->control == 'rp' || $request->control == 'rr'){
						$gat = AddressRouter::where('router_id',$router->id)->first();

							$radius = Radius::where('router_id',$router->id)->first();
							if($radius) {
								$radius->secret = $request->radius_secret;
								$radius->save();

                                Nas::updateOrCreate(['nasname' => $router->ip],
                                    ['secret' => $request->radius_secret]
                                );

								/** aplicamos la configuracion sobre el router **/
								$radius = new RadiusLibrary();
								$radius->radius_update($API,$request->radius_secret);
							} else {
                                Radius::updateOrCreate(
                                    ['router_id' => $router->id],
                                    ['secret' => $request->radius_secret]
                                );
                                Nas::updateOrCreate([
                                    'nasname' => $router->ip,
                                    'shortname' => $router->name,
                                    'secret' =>$request->radius_secret
                                ]);

                                /** aplicamos la configuracion sobre el router **/
                                $radius_lib = new RadiusLibrary();
                                $radius_lib->radius_add($API,$request->radius_secret,$request->radius_server);
							}


                        if(env('DB_HOST_RADIUS') != $request->radius_server){

                            $old_ip_server_radius = env('DB_HOST_RADIUS');
                            /**update server radius if is changed**/
                            file_put_contents(app()->environmentFilePath(), str_replace(
                                'DB_HOST_RADIUS' . '=' . env('DB_HOST_RADIUS'),
                                'DB_HOST_RADIUS' . '=' . $request->radius_server,
                                file_get_contents(app()->environmentFilePath())
                            ));

                            Artisan::call('cache:clear');
                            Artisan::call('config:clear');
                            $radius_conf = Radius::where('router_id',$request->get('router_id'))->first();
                            $radius_librari = new RadiusLibrary();
                            $radius_librari->radius_update_ip($API,$old_ip_server_radius,$request->radius_server,$radius_conf->secret);
                        }
						$ejecucion=exec("/usr/bin/sudo /etc/init.d/freeradius stop");
						$ejecucion=exec("sudo killall freeradius");
						$ejecucion=exec('sudo /etc/init.d/freeradius restart');

					}

					$this->addClientsToMikrotik($router);

					$API->disconnect();
                    $radius_despues = Radius::where('router_id',$router->id)->first();

					return Response::json(array('msg'=>'success'));
				} else{

					$API->disconnect();

					return Response::json(array('msg'=>'errorConnect')); //no se pudo conectar al router
				}//end else connection

			}//end else connection

		}

		$connection = $router->connection;
		//verificamos modo de conexion
		if($connection == 1) {
			//sin conexion
			$friendly_names = array(
				'name_edit' => __('app.name'),
				'address_edit' => __('app.address')
			);

			$rules = array(
				'name_edit' => 'required|unique:routers,name,'.$router_id,
				'address_edit' => 'required'
			);

		}else{

			$friendly_names = array(
				'name_edit' => __('app.name'),
				'address_edit' => __('app.address'),
				'ipe_dit' => 'IP',
				'login_edit' => 'login',
				'port_edit' => __('app.port')
			);

			$rules = array(
				'name_edit' => 'required|unique:routers,name,'.$router_id,
				'address_edit' => 'required',
				'ip_edit' => 'required',
				'login_edit' => 'required',
				'port_edit' => 'required|numeric|integer'
			);

		}
		$validation = Validator::make($request->all(), $rules);
		$validation->setAttributeNames($friendly_names);
		if($validation->fails())
			return Response::json(['msg'=>'error','errors' => $validation->getMessageBag()->toArray()]);

		$router->name = $request->get('name_edit');
		$router->location = $request->get('address_edit');
		$router->coordinates = empty($request->get('location_edit')) ? '0' : $request->get('location_edit');

		if($connection == 0 && $request->get('status') != 'badConnect'){

			$router->ip = $request->get('ip_edit');
			$router->login = $request->get('login_edit');
			$router->port = $request->get('port_edit');
			$router->lan = $request->get('lan');
		}

		if ($connection == 0 && $request->get('status') == 'badConnect') {

			$router->ip = $request->get('ip_edit');
			$router->login = $request->get('login_edit');
			$router->port = $request->get('port_edit');
		}

		$encrypt = new Pencrypt();

		if($request->get('password') && $request->password != '' && $connection == 0){
			$new_pass = $request->get('password');
			$router->password = $encrypt->encode($new_pass);
		}

		$router->save();
		//Añadir o actualizamos el control
		$log = new Slog();
		//new data
		$adv = $request->get('adv',0);
		$arp = $request->get('arp',0);
		$dhcp = $request->get('dhcp',0);
		$address_list = $request->get('address_list',0);

		if($connection == 0 && $request->get('status') != 'badConnect') { //modo conexion

			//actualizamos la configuración//
			$newControl = ControlRouter::where('router_id',$router_id)->first();
			$newControl->type_control = $request->get('control');
			$newControl->dhcp = $request->get('dhcp',0);
			$newControl->arpmac = $request->get('arp',0);
			$newControl->adv = $request->get('adv',0);
			$newControl->address_list = $request->get('address_list',0);
			$newControl->save();

			$requestData = [];
			$requestData['control'] = $request->get('control');
			$requestData['dhcp'] = $request->get('dhcp',0);
			$requestData['arp'] = $request->get('arp',0);
			$requestData['adv'] = $request->get('adv',0);
			$requestData['address_list'] = $request->get('address_list',0);

			if(!$changeRouter) {
				$encrypt = new Pencrypt();

				$routerDetails = [
					'ip' => $old_router->ip,
					'login' => $old_router->login,
					'password' => $encrypt->decode($old_router->password),
					'connect' => $old_router->connection,
					'port' => $old_router->port,
				];
				//GET all data for API
				$conf = Helpers::get_api_options('mikrotik');
				$API = new Mikrotik($routerDetails['port'],$conf['a'],$conf['t'],$conf['s']);
				$API->debug = $conf['d'];
				if ($API->connect($routerDetails['ip'], $routerDetails['login'], $routerDetails['password'])) {
                    $this->removeClientFromMikrotik($old_router, $controlRouter,$request->radius_server);
				} else{

					$API->disconnect();

					return Response::json(array('msg'=>'errorConnect')); //no se pudo conectar al router
				}//end else connection

				$newRouterDetails = [
					'ip' => $router->ip,
					'login' => $router->login,
					'password' => $encrypt->decode($router->password),
					'connect' => $router->connection,
					'port' => $router->port,
				];
				//GET all data for API
				$conf = Helpers::get_api_options('mikrotik');
				$API = new Mikrotik($newRouterDetails['port'],$conf['a'],$conf['t'],$conf['s']);
				$API->debug = $conf['d'];
				try {
					if ($API->connect($newRouterDetails['ip'], $newRouterDetails['login'], $newRouterDetails['password'])) {
						$gat = AddressRouter::where('router_id',$router->id)->first();
						/**Si el tipo de control es 'ra'--> radius, entonces creamos el radius antes de mandar el router**/
						if($request->control == 'ra' || $request->control == 'rp' || $request->control == 'rr'){
							/**si el tipo de control anterior no era con radius, lo creamos, si no actualizamos**/
							    $radius = Radius::where('router_id',$router->id)->first();

								if($radius) {
									$radius->secret = $request->radius_secret;
									$radius->save();

                                    Nas::updateOrCreate(['nasname' => $router->ip],
                                        ['secret' => $request->radius_secret]
                                    );

									/** aplicamos la configuracion sobre el router **/
									$radius = new RadiusLibrary();
									$radius->radius_update($API,$request->radius_secret);

								} else {

                                    $radis_creado = Radius::updateOrCreate(
                                        ['router_id' => $router->id],
                                        ['secret' => $request->radius_secret]
                                    );

                                    Nas::updateOrCreate([
                                        'nasname' => $router->ip,
                                        'shortname' => $router->name,
                                        'secret' =>$request->radius_secret
                                    ]);

									/** aplicamos la configuracion sobre el router **/
									$radius_lib = new RadiusLibrary();
                                    $radius_lib->radius_add($API,$request->radius_secret,$request->radius_server);

								}


                            if(env('DB_HOST_RADIUS') != $request->radius_server){


                                $old_ip_server_radius = env('DB_HOST_RADIUS');
                                /**update server radius if is changed**/
                                file_put_contents(app()->environmentFilePath(), str_replace(
                                    'DB_HOST_RADIUS' . '=' . env('DB_HOST_RADIUS'),
                                    'DB_HOST_RADIUS' . '=' . $request->radius_server,
                                    file_get_contents(app()->environmentFilePath())
                                ));

                                Artisan::call('cache:clear');
                                Artisan::call('config:clear');
                                $radius_conf = Radius::where('router_id',$request->get('router_id'))->first();
                                $radius_librari = new RadiusLibrary();
                                $radius_librari->radius_update_ip($API,$old_ip_server_radius,$request->radius_server,$radius_conf->secret);

                            }

							$ejecucion=exec("/usr/bin/sudo /etc/init.d/freeradius stop");
							$ejecucion=exec("sudo killall freeradius");
							$ejecucion=exec('sudo /etc/init.d/freeradius restart');
						}
						$this->addClientsToMikrotik($router);
					} else{

						$API->disconnect();

						return Response::json(array('msg'=>'errorConnect')); //no se pudo conectar al router
					}//end else connection

				} catch(\Exception $exception) {
					throw $exception;
				}

			}



			if($changeRouter) {

				$encrypt = new Pencrypt();
				$routerDetails = [
					'ip' => $router->ip,
					'login' => $router->login,
					'password' => $encrypt->decode($router->password),
					'connect' => $router->connection,
					'port' => $router->port,
				];
				//GET all data for API
				$conf = Helpers::get_api_options('mikrotik');
				$API = new Mikrotik($routerDetails['port'],$conf['a'],$conf['t'],$conf['s']);
				$API->debug = $conf['d'];
				if ($API->connect($routerDetails['ip'], $routerDetails['login'], $routerDetails['password'])) {
					$gat = AddressRouter::where('router_id',$router->id)->first();
					/**Si el tipo de control es 'ra'--> radius, entonces creamos el radius antes de mandar el router**/
					if($request->control == 'ra' || $request->control == 'rp' || $request->control == 'rr'){
						/**si el tipo de control anterior no era con radius, lo creamos, si no actualizamos**/
							$radius = Radius::where('router_id',$router->id)->first();
							if($radius) {
								$radius->secret = $request->radius_secret;
								$radius->save();

                                Nas::updateOrCreate(['nasname' => $router->ip],
                                    ['secret' => $request->radius_secret]
                                );

								/** aplicamos la configuracion sobre el router **/
								$radius = new RadiusLibrary();
								$radius->radius_update($API,$request->radius_secret);
							} else {
                                Radius::updateOrCreate(
                                    ['router_id' => $router->id],
                                    ['secret' => $request->radius_secret]
                                );
                                Nas::updateOrCreate([
                                    'nasname' => $router->ip,
                                    'shortname' => $router->name,
                                    'secret' =>$request->radius_secret
                                ]);

                                /** aplicamos la configuracion sobre el router **/
                                $radius_lib = new RadiusLibrary();
                                $radius_lib->radius_add($API,$request->radius_secret,$request->radius_server);
							}

                        if(env('DB_HOST_RADIUS') != $request->radius_server){

                            $old_ip_server_radius = env('DB_HOST_RADIUS');
                            /**update server radius if is changed**/
                            file_put_contents(app()->environmentFilePath(), str_replace(
                                'DB_HOST_RADIUS' . '=' . env('DB_HOST_RADIUS'),
                                'DB_HOST_RADIUS' . '=' . $request->radius_server,
                                file_get_contents(app()->environmentFilePath())
                            ));

                            Artisan::call('cache:clear');
                            Artisan::call('config:clear');
                            $radius_conf = Radius::where('router_id',$request->get('router_id'))->first();
                            $radius_librari = new RadiusLibrary();
                            $radius_librari->radius_update_ip($API,$old_ip_server_radius,$request->radius_server,$radius_conf->secret);
                        }

						$ejecucion=exec("/usr/bin/sudo /etc/init.d/freeradius stop");
						$ejecucion=exec("sudo killall freeradius");
						$ejecucion=exec('sudo /etc/init.d/freeradius restart');
					}

					$this->addClientsToMikrotik($router);
				} else{

					$API->disconnect();

					return Response::json(array('msg'=>'errorConnect')); //no se pudo conectar al router
				}//end else connection


			}

			$router_con = new RouterConnect();
			$con = $router_con->get_connect($router->id);

			//save log
			$name = $con['name'];
			CommonService::log("Se ha registrado la configuración del router: $name", $this->username, 'success' , $this->userId);

			$radius_despues = Radius::where('router_id',$router->id)->first();

			return Response::json(array('msg'=>'success'));


		}//end if modo conexion
		else{ //modo sin conexion

//			$newControl = ControlRouter::where('router_id',$router_id)->first();
//			$newControl->type_control = 'nc';
//			$newControl->dhcp = 0;
//			$newControl->arpmac = 0;
//			$newControl->adv = 0;
//			$newControl->address_list = 0;
//			$newControl->save();
//			//save log
//			$log->save("Se ha registrado la configuración del router:","success",$request->get('name_edit'));

			return Response::json(array('msg'=>'success'));

		}//end else

	}

    public function postIps(Request $request)
    {
        $ips = AddressRouter::where('router_id','=',$request->id)->get();
        return Response::json($ips);
    }

    public function postNetworks(Request $request)
    {

        $router_id = $request->get('id');
        $network_id = $request->get('network');
        $interface = $request->get('interface');
        $sendmk = $request->get('sendmk',0);

        $friendly_names = array(
            'network' => 'IP/Red'
        );

        $rules = array(
            'id' => 'required',
            'network' => 'required'
        );

        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);
        if($validation->fails())
            return Response::json(['msg'=>'error','errors' => $validation->getMessageBag()->toArray()]);

        $net = AddressRouter::find($network_id);

        if(is_null($net))
            return Response::json(array('msg'=> 'notfound'));


        $process = new Chkerr();

        //verificamos si el router ya fue configurado
        $config = ControlRouter::where('router_id','=',$router_id)->first();
        $typeconf = $config->type_control;
        $adv = $config->adv;

        $global = GlobalSetting::all()->first();
        $debug = $global->debug;


        if ($typeconf=='pp' || $typeconf=='pa') {

            if ($adv == 1) {
                //conectamos con el router
                $router = new RouterConnect();
                $con = $router->get_connect($router_id);
                //GET all data for API Mikrotik
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
                $API->debug = $conf['d'];
                //Bloqueamos simple
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $rocket = new RocketCore();
                    $STATUS = $rocket->enabled_pppoe_advs($API,$net->network,$debug);
                    if ($debug == 1) {
                        if($process->check($STATUS))
                            return $process->check($STATUS);
                    }

                }
            }
        }

        //$ip = new Calcip($network);
        $router = Router::find($router_id);
        $net->router_id = $router_id;
        $net->save();
        $router->lan = 'none';
        $router->save();

        $routerName = $router->name;
	    CommonService::log("Se ha registrado nueva ip/red del router: $routerName", $this->username, 'success' , $this->userId);

        return Response::json(array('msg'=>'success'));
    }

    //metodo para eliminar ip/red
    public function postInte(Request $request)
    {

        $process = new Chkerr();

        $router_id = $request->get('idro');

        //verificamos si el router ya fue configurado
        $config = ControlRouter::where('router_id','=',$router_id)->first();

        $typeconf = $config->type_control;
        $adv = $config->adv;

        $router = Router::find($router_id);
        $routerName = $router->name;
        $address = AddressRouter::find($request->get('id'));
        $net = $address;
        $address->router_id = '0';
        $address->save();

        $global = GlobalSetting::first();
        $debug = $global->debug;

        if ($typeconf=='pp' || $typeconf=='pa' || $typeconf=='ps' || $typeconf=='pt') {

            if ($adv == 1) {
                //conectamos con el router
                $router = new RouterConnect();
                $con = $router->get_connect($router_id);
                //GET all data for API Mikrotik
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
                $API->debug = $conf['d'];
                //Bloqueamos simple
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $rocket = new RocketCore();
                    $STATUS = $rocket->remove_advs_ppp($API,$net->network,$debug);

                    if ($debug == 1) {
                        if($process->check($STATUS))
                            return $process->check($STATUS);
                    }

                }
            }
        }

	    CommonService::log("Se ha eliminado ip/red del router: $routerName", $this->username, 'danger' , $this->userId);

        return Response::json(array('msg'=>'success'));

    }


    public function postInterface(Request $request)
    {
        //conectamos con el router
        $router = new RouterConnect();
        $con = $router->get_connect($request->id);

        //obtenemos datos generales del API
        $conf = Helpers::get_api_options('mikrotik');

        $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
        $API->debug = $conf['d'];

        if ($API->connect($con['ip'], $con['login'], $con['password'])) {
            $API->write('/interface/getall');
            $READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);
            $interfaces = $ARRAY;

            $i = 0;

            while ($i < count($interfaces)) {

                $interf[$i]['name'] = $interfaces[$i]['name'];

                if(array_key_exists('default-name',$interfaces[$i]))
                    $interf[$i]['default-name'] = $interfaces[$i]['default-name'];
                else
                    $interf[$i]['default-name'] = $interfaces[$i]['name'];

                $i++;
            }

            $API->disconnect();

            return Response::json($interf);
        }
        else
            return Response::json(array('msg'=>'errorConnect'));
    }//fin de la funcion


    public function postRouterinterface(Request $request)
    {
        $router = Router::find($request->id);
        if ($router) {
            $lan = array("sel" => $router->lan);
            return Response::json($lan);
        } else {
            return Response::json([]);
        }

    }


    public function postList(){
        set_time_limit(0); //unlimited execution time php
        $rout = Router::select('id','name', 'ip', 'model', 'status', 'clients', 'password', 'login', 'port', 'connection');

        return DataTables::of($rout)
            ->addColumn('action', function ($row) {
                $styleb = '<div class="action-buttons">';

                if($row->status == 'of' || $row->status == 'er' || $row->status == 'nc'){
                    return $styleb.'<a class="grey" href="#"><i class="ace-icon fa fa-info-circle bigger-130"></i></a>
                            <a class="default" title="Update Router Basic Details" href="javascript:;" onclick="getChangeIp('.$row->id.')" id="'.$row->id.'"><i class="ace-icon fa fa-gear bigger-130"></i></a>
                            <a class="green editar" title="Update Control (It will sync mikrotik api When you update)" href="#Edit" id="'.$row->id.'"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
                            <a class="red del" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>
                            <a class="default refresh" title="Check Status" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-refresh bigger-130"></i></a>
                            </div>';
                }
                else {

                    return $styleb.'<a class="blue infor" href="#" data-toggle="modal" data-target="#info-router" id="'.$row->id.'">
                            <i class="ace-icon fa fa-info-circle bigger-130"></i></a>

                            <a class="default" title="Update Router Basic Details" href="javascript:;" onclick="getChangeIp('.$row->id.')" id="'.$row->id.'"><i class="ace-icon fa fa-gear bigger-130"></i></a>
                            <a class="green editar" title="Update Control (It will sync mikrotik api When you update)" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
                            <a class="red del" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>
                            <a class="default refresh" title="Check Status" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-refresh bigger-130"></i></a>
                            </div>';
                }

            })
            ->editColumn('model', function ($row) {
                if($row->model =='none') {
                    return $row->model;
                }
                else {
                    $str = $row->model;
                    return '<a href="http://routerboard.com/'.$str.'" target="_blank">'.$str.'</a>';
                }
            })
            ->editColumn('status', function ($row) {
                if($row->status == 'on')
                    return '<span class="label label-success arrowed">En línea</span>';
                if($row->status == 'of')
                    return '<span class="label label-danger">Apagado</span>';
                if($row->status == 'nc')
                    return '<span class="label label-grey">Sin conexión</span>';

            })
            ->editColumn('clients', function ($row) {
                $clients = ClientService::join('clients', 'clients.id', '=', 'client_services.client_id')
                    ->where('client_services.router_id', $row->id)
                    ->groupBy('clients.id')
                    ->get()
                    ->count();
                return '<span class="badge badge-success">'.$clients.'</span>';

            })
            ->rawColumns(['action', 'model', 'status', 'clients'])
            ->make(true);

    }

    public function restartFreeradius(){
        $ejecucion=exec("/usr/bin/sudo /etc/init.d/freeradius stop");
        $ejecucion=exec("sudo killall freeradius");
        $ejecucion=exec('sudo /etc/init.d/freeradius restart');
        return Reply::success('Freeradius Restart successfully');
    }

    public function routerStatus()
    {
        $newRouterStatus = new UpdateRouterStatus();
        $newRouterStatus->handle();
        return \redirect()->back();
    }

    public function refresh($id)
    {
        $router = Router::select('id','name', 'ip', 'model', 'status', 'clients', 'password', 'login', 'port', 'connection')
            ->findOrFail($id);

        $encrypt = new Pencrypt();

        if($router->connection == 1) {
            $stat = 'nc';
            $model = 'none';
        }
        else {
            $password  = $router->password;
            $password = $encrypt->decode($password);
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($router->port,2,$conf['t'],$conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($router->ip, $router->login, $password)) {
                $API->write('/system/resource/print',true);
                $READ = $API->read(false);
                $ARRAY = $API->parseResponse($READ);
                $stat = 'on';
                $model = $ARRAY[0]['board-name'];
                $API->disconnect();

            }else{
                $stat = 'of';
                $model = 'none';
            }

        }

        $router->status = $stat;
        $router->model = $model;
        $router->save();

        return Reply::success('Router successfully refreshed.');
    }

    public function addClientsToMikrotik($router)
    {
        $newRouter = $router->toArray();
        event(new AddClientsOnNewRouter($newRouter));
    }

    public function removeClientFromMikrotik($router, $controlRouter,$radius_server)
    {

        $encrypt = new Pencrypt();
        $routerDetails = [
            'id' => $router->id,
            'ip' => $router->ip,
            'login' => $router->login,
            'port' => $router->port,
            'password' => $encrypt->decode($router->password),
            'lan' => $router->lan,
            'name' => $router->name,
            'connect' => $router->connection,
            'radius_server' => $radius_server
        ];

        $controlRouterDetails = [
            'id' => $controlRouter->id,
            'router_id' => $controlRouter->router_id,
            'type_control' => $controlRouter->type_control,
            'dhcp' => $controlRouter->dhcp,
            'arpmac' => $controlRouter->arpmac,
            'adv' => $controlRouter->adv,
            'address_list' => $controlRouter->address_list,
            'radius_server' => $radius_server
        ];
        event(new RemoveClientsFromOldRouter($routerDetails, $controlRouterDetails));
    }

    public function getChangeIp(Request $request)
    {
        $this->router = Router::find($request->router);
        return view('routers.change-ip', $this->data);
    }

    public function changeIp(ChangeIpRequest $request, $id)
    {
        $router = Router::find($id);

        $oldIp = $router->ip;
        $newIp = $request->newIp;
        $router->name  = $request->name;
        $router->ip  = $request->newIp;
        $router->login  = $request->login_edit;

        $encrypt = new Pencrypt();

        if($request->has('password') && $request->password != ''){
            $router->password = $encrypt->encode($request->password);
        }

        $router->port  = $request->port_edit;
        $router->location  = $request->location;

        if($request->has('coordinates')) {
            $router->coordinates  = $request->coordinates;
        }

        $router->save();

	    CommonService::log("La ip del enrutador cambia de ip #$oldIp a ip #$newIp: ", $this->username, 'success' , $this->userId);

        return Reply::success('Router details successfully updated.');

    }

    public function updateCoordinates(int $id, Router $router, Request $request)
    {
        $rules = array(
            'coordinates' => 'required|string',
            'id' => 'exists:routers,id'
        );
        $request->merge(['id' => $id]);
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return Response::json(array(array('msg' => 'error', 'errors' => $validation->getMessageBag()->toArray())));
        }
        $router = $router->find($id);
        $router->coordinates = $request->coordinates;
        $router->save();

        Helpers::resetGeoJsonByRouter($id);
    }

    public function updateMapMarkerIcon(int $id, Router $router, Request $request)
    {
        $rules = array(
            'map_marker_icon' => 'required|array',
            'id' => 'exists:routers,id'
        );

        $request->merge(['id' => $id]);
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return Response::json(array(array('msg' => 'error', 'errors' => $validation->getMessageBag()->toArray())));
        }
        $router = $router->find($id);
        $router->map_marker_icon = $request->map_marker_icon['type'] == 'image' ? null : $request->map_marker_icon;
        $router->save();
    }

}
