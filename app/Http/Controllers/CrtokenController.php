<?php

namespace App\Http\Controllers;
define('TWILIO_SMS', base_path() . '/public/Twilio');
require_once TWILIO_SMS . '/autoload.php';

use App\models\Sms;
use App\libraries\Psms;
use App\libraries\Slog;
use App\models\TempSms;
use Twilio\Rest\Client;
use App\libraries\Burst;
use App\models\Template;
use App\libraries\Chkerr;
use App\models\Clienttbl;
use App\libraries\GetPlan;
use App\libraries\Helpers;
use App\libraries\Mkerror;
use App\libraries\Mikrotik;
use App\libraries\RocketCore;
use App\models\ClientService;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\SuspendClient;
use App\libraries\CountClient;
use App\Service\CommonService;
use App\libraries\RouterConnect;
use App\models\Client as ModelsClient;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use SMSGatewayMe\Client\ApiClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use SMSGatewayMe\Client\Configuration;
use SMSGatewayMe\Client\Api\MessageApi;
use Illuminate\Support\Facades\Response;

class CrtokenController extends BaseController
{
	
	// metodo para ejecutar el cron desde laravel
	public function starcrn()
	{
		
		set_time_limit(0); //unlimited execution time php
		$global = GlobalSetting::all()->first();
		$tolerance = $global->tolerance;
		$debug = $global->debug;
		
		//Iniciamos las clases principales
		$log = new Slog();
		$counter = array();
		$rocket = new RocketCore();
		//GET all data for API
		$conf = Helpers::get_api_options('mikrotik');
		
		$numcli = DB::table('clients')->get();
		//recuperamos todos los routers asociados a los clientes
		$rts = array();
		foreach ($numcli as $ro) {
			$rts[] = $ro->router_id;
		}
		
		$routers = array_unique($rts);
		$routers = array_values($routers);
		
		if (count($numcli) > 0) {
			
			for ($i = 0; $i < count($routers); $i++) {
				
				$counter = new CountClient();
				//conectamos con la api
				$router = new RouterConnect();
				$con = $router->get_connect($routers[$i]);
				// creamos conexion con el router
				$API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
				$API->debug = $conf['d'];
				
				//obtenemos todos los datos del cliente al router asociado
				$clients = DB::table('clients')
					->join('plans', 'plans.id', '=', 'clients.plan_id')
					->join('suspend_clients', 'suspend_clients.client_id', '=', 'clients.id')
					->select('clients.name', 'clients.status', 'clients.ip As ipclient', 'clients.mac', 'clients.online',
						'plans.name As plan_name', 'suspend_clients.expiration', 'clients.user_hot As user', 'clients.plan_id',
						'clients.id As client_id')->where('clients.router_id', $routers[$i])->get();
				
				
				if ($con['connect'] == 0) {
					
					//verificamos tipo de control
					$type = ControlRouter::where('router_id', $routers[$i])->get();
					$control = $type[0]->type_control;
					$adv = $type[0]->adv;
					$arp = $type[0]->arpmac;
					$dhcp = $type[0]->dhcp;
					
					
					if ($adv == 1) {
						$drop = 0;
					} else {
						$drop = 1;
					}
					
					//establecemos la conexion
					if ($API->connect($con['ip'], $con['login'], $con['password'])) {
						
						//buscamos el tipo de control
						switch ($control) {
							
							case 'no':
								# block for no Shaping use in mikrotik
								foreach ($clients as $client) {
									
									//inicializamos variables principales
									$cutdate = $client->expiration;
									//tolerancia en días al corte
									if ($tolerance != 0) {
										# Significa que hay tolerancia en dias descontamos
										$newExpiries = strtotime('+' . $tolerance . ' day', strtotime($cutdate));
										$cutdate = date('Y-m-d', $newExpiries);
									}
									//set cut date
									$newExpiries = strtotime('+1 day', strtotime($cutdate));
									$dateclient = date('Y-m-d', $newExpiries);
									
									////// EXECUTION CUT SERVICE //////
									if (date('Y-m-d') >= $dateclient) {
										
										//general data
										$data = array(
											'name' => $client->name,
											'mac' => $client->mac,
											'adv' => $adv,
											'drop' => $drop
										);
										
										$error = new Mkerror();
										//cortamos el servicio
										$rocket->set_basic_config($API, $error, $data, $client->ipclient, null, 'block', $debug);
										
										//descontamos el numero de clientes del plan
										$counter->step_down_plan($client->plan_id);
										
										$cl = Clienttbl::find($client->client_id);
										$cl->status = 'de';
										$cl->online = 'off';
										$cl->save();
										//registramos en el log del sistema
										$name = $client->name;
										CommonService::log("Corte Automatico cliente: $name", 'Automatic', 'success' );
										
									}//end if cut service
									
								}//end foreach
								
								break;
							
							case 'sq':
								
								
								# block for simple queues
								foreach ($clients as $client) {
									
									//inicializamos variables principales
									$cutdate = $client->expiration;
									//tolerancia en días al corte
									if ($tolerance != 0) {
										# Significa que hay tolerancia en dias descontamos
										$newExpiries = strtotime('+' . $tolerance . ' day', strtotime($cutdate));
										$cutdate = date('Y-m-d', $newExpiries);
									}
									//set cut date
									$newExpiries = strtotime('+1 day', strtotime($cutdate));
									$dateclient = date('Y-m-d', $newExpiries);
									
									////// EXECUTION CUT SERVICE //////
									if (date('Y-m-d') >= $dateclient) {
										
										//general data
										$data = array(
											'name' => $client->name,
											'mac' => $client->mac,
											'status' => 'ac',
											'adv' => $adv,
											'drop' => $drop
										);
										
										//cortamos el servicio
										$rocket->block_simple_queues($API, $data, $client->ipclient, $debug);
										
										//descontamos el numero de clientes del plan
										$counter->step_down_plan($client->plan_id);
										
										$cl = Clienttbl::find($client->client_id);
										$cl->status = 'de';
										$cl->online = 'off';
										$cl->save();
										//registramos en el log del sistema
										
										$name = $client->name;
										CommonService::log("Corte Automatico cliente: $name", 'Automatic', 'danger' );
										
									}//end if cut service
									
									
								}//end foreach clients
								
								break;
							
							case 'st':
								# block for simple queues with tree
								
								foreach ($clients as $client) {
									
									//inicializamos variables principales
									$cutdate = $client->expiration;
									//tolerancia en días al corte
									if ($tolerance != 0) {
										# Significa que hay tolerancia en dias descontamos
										$newExpiries = strtotime('+' . $tolerance . ' day', strtotime($cutdate));
										$cutdate = date('Y-m-d', $newExpiries);
									}
									//set cut date
									$newExpiries = strtotime('+1 day', strtotime($cutdate));
									$dateclient = date('Y-m-d', $newExpiries);
									
									////// EXECUTION CUT SERVICE //////
									if (date('Y-m-d') >= $dateclient) {
										
										$pl = new GetPlan();
										$plan = $pl->get($client->plan_id);
										$burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);
										
										//general data
										$data = array(
											'name' => $client->name,
											'mac' => $client->mac,
											'status' => 'ac',
											'adv' => $adv,
											'drop' => $drop,
											////for simple queue with tree
											'plan_id' => $client->plan_id,
											'namePlan' => $plan['name'],
											'download' => $plan['download'],
											'upload' => $plan['upload'],
											'maxlimit' => $plan['maxlimit'],
											'aggregation' => $plan['aggregation'],
											'limitat' => $plan['limitat'],
											'bl' => $burst['blu'] . '/' . $burst['bld'],
											'bth' => $burst['btu'] . '/' . $burst['btd'],
											'bt' => $plan['burst_time'] . '/' . $plan['burst_time'],
											'burst_limit' => $plan['burst_limit'],
											'burst_threshold' => $plan['burst_threshold'],
											'burst_time' => $plan['burst_time'],
											'priority' => $plan['priority'] . '/' . $plan['priority'],
											'comment' => 'SmartISP - ' . $plan['name']
										);
										
										
										//descontamos el numero de clientes del plan
										$counter->step_down_plan($client->plan_id);
										
										$cl = Client::find($client->client_id);
										$cl->status = 'de';
										$cl->online = 'off';
										$cl->save();
										
										//cortamos el servicio
										$rocket->block_simple_queue_with_tree($API, $data, $client->ipclient, $debug);
										
										//registramos en el log del sistema
										$name = $client->name;
										CommonService::log("Corte Automatico cliente: $name", 'Automatic', 'danger' );
										
									}//end if cut service
									
								}//end foreach clients
								
								
								break;
							
							case 'ho':
								# block for hotspot users profiles
								foreach ($clients as $client) {
									
									//inicializamos variables principales
									$cutdate = $client->expiration;
									//tolerancia en días al corte
									if ($tolerance != 0) {
										# Significa que hay tolerancia en dias descontamos
										$newExpiries = strtotime('+' . $tolerance . ' day', strtotime($cutdate));
										$cutdate = date('Y-m-d', $newExpiries);
									}
									//set cut date
									$newExpiries = strtotime('+1 day', strtotime($cutdate));
									$dateclient = date('Y-m-d', $newExpiries);
									
									////// EXECUTION CUT SERVICE //////
									if (date('Y-m-d') >= $dateclient) {
										
										//general data
										$data = array(
											'name' => $client->name,
											'mac' => $client->mac,
											'status' => 'ac',
											'adv' => $adv,
											'drop' => $drop
										);
										
										//cortamos el servicio
										$rocket->block_hotspot($API, $data, $client->ipclient, $debug);
										
										//descontamos el numero de clientes del plan
										$counter->step_down_plan($client->plan_id);
										
										$cl = Clienttbl::find($client->client_id);
										$cl->status = 'de';
										$cl->online = 'off';
										$cl->save();
										//registramos en el log del sistema
										$name = $client->name;
										CommonService::log("Corte Automatico cliente: $name", 'Automatic', 'danger' );
										
									}//end if cut service
									
									
								}//end foreach
								
								break;
							
							case 'dl':
								# block for dhcp leases
								foreach ($clients as $client) {
									
									//inicializamos variables principales
									$cutdate = $client->expiration;
									//tolerancia en días al corte
									if ($tolerance != 0) {
										# Significa que hay tolerancia en dias descontamos
										$newExpiries = strtotime('+' . $tolerance . ' day', strtotime($cutdate));
										$cutdate = date('Y-m-d', $newExpiries);
									}
									//set cut date
									$newExpiries = strtotime('+1 day', strtotime($cutdate));
									$dateclient = date('Y-m-d', $newExpiries);
									////// EXECUTION CUT SERVICE //////
									if (date('Y-m-d') >= $dateclient) {
										
										//general data
										$data = array(
											'name' => $client->name,
											'mac' => $client->mac,
											'status' => 'ac',
											'adv' => $adv,
											'drop' => $drop
										);
										
										//cortamos el servicio
										$rocket->block_dhcp_lease($API, $data, $client->ipclient, $debug);
										
										//descontamos el numero de clientes del plan
										$counter->step_down_plan($client->plan_id);
										
										$cl = Clienttbl::find($client->client_id);
										$cl->status = 'de';
										$cl->online = 'off';
										$cl->save();
										//registramos en el log del sistema
										$name = $client->name;
										CommonService::log("Corte Automatico cliente: $name", 'Automatic', 'danger' );
										
										
									}//end if cut service
									
									
								}//end foreach
								
								break;
							
							case 'pp':
							case 'ps':
								# block for pppoe secrets
								foreach ($clients as $client) {
									
									//inicializamos variables principales
									$cutdate = $client->expiration;
									//tolerancia en días al corte
									if ($tolerance != 0) {
										# Significa que hay tolerancia en dias descontamos
										$newExpiries = strtotime('+' . $tolerance . ' day', strtotime($cutdate));
										$cutdate = date('Y-m-d', $newExpiries);
									}
									//set cut date
									$newExpiries = strtotime('+1 day', strtotime($cutdate));
									$dateclient = date('Y-m-d', $newExpiries);
									////// EXECUTION CUT SERVICE //////
									if (date('Y-m-d') >= $dateclient) {
										
										//general data
										$data = array(
											'name' => $client->name,
											'user' => $client->user,
											'mac' => $client->mac,
											'status' => 'ac',
											'adv' => $adv,
											'drop' => $drop
										);
										
										//cortamos el servicio
										$rocket->block_ppp($API, $data, $client->ipclient, $debug);
										
										//descontamos el numero de clientes del plan
										$counter->step_down_plan($client->plan_id);
										
										$cl = Clienttbl::find($client->client_id);
										$cl->status = 'de';
										$cl->online = 'off';
										$cl->save();
										//registramos en el log del sistema
										$name = $client->name;
										CommonService::log("Corte Automatico cliente: $name", 'Automatic', 'danger' );
										
									}//end if cut service
									
								}//end foreach
								
								break;
							case 'pt':
								# block ppp simple queue with tree
								foreach ($clients as $client) {
									
									//inicializamos variables principales
									$cutdate = $client->expiration;
									//tolerancia en días al corte
									if ($tolerance != 0) {
										# Significa que hay tolerancia en dias descontamos
										$newExpiries = strtotime('+' . $tolerance . ' day', strtotime($cutdate));
										$cutdate = date('Y-m-d', $newExpiries);
									}
									//set cut date
									$newExpiries = strtotime('+1 day', strtotime($cutdate));
									$dateclient = date('Y-m-d', $newExpiries);
									
									////// EXECUTION CUT SERVICE //////
									if (date('Y-m-d') >= $dateclient) {
										
										$pl = new GetPlan();
										$plan = $pl->get($client->plan_id);
										$burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);
										
										//general data
										$data = array(
											'name' => $client->name,
											'user' => $client->user,
											'mac' => $client->mac,
											'status' => 'ac',
											'adv' => $adv,
											'drop' => $drop,
											////for simple queue with tree
											'plan_id' => $client->plan_id,
											'namePlan' => $plan['name'],
											'download' => $plan['download'],
											'upload' => $plan['upload'],
											'maxlimit' => $plan['maxlimit'],
											'aggregation' => $plan['aggregation'],
											'limitat' => $plan['limitat'],
											'bl' => $burst['blu'] . '/' . $burst['bld'],
											'bth' => $burst['btu'] . '/' . $burst['btd'],
											'bt' => $plan['burst_time'] . '/' . $plan['burst_time'],
											'burst_limit' => $plan['burst_limit'],
											'burst_threshold' => $plan['burst_threshold'],
											'burst_time' => $plan['burst_time'],
											'priority' => $plan['priority'] . '/' . $plan['priority'],
											'comment' => 'SmartISP - ' . $plan['name']
										);
										
										
										//cortamos el servicio
										$rocket->block_ppp($API, $data, $client->ipclient, $debug);
										
										//descontamos el numero de clientes del plan
										$counter->step_down_plan($client->plan_id);
										
										$cl = Client::find($client->client_id);
										$cl->status = 'de';
										$cl->online = 'off';
										$cl->save();
										
										//cortamos el servicio
										$rocket->block_simple_queue_with_tree($API, $data, $client->ipclient, $debug);
										
										//registramos en el log del sistema
										$name = $client->name;
										CommonService::log("Corte Automatico cliente: $name", 'Automatic', 'danger' );
										
									}//end if cut service
									
								}//end foreach clients
								
								break;
							
							case 'pc':
								# block for pcq address list
								foreach ($clients as $client) {
									
									//inicializamos variables principales
									$cutdate = $client->expiration;
									//tolerancia en días al corte
									if ($tolerance != 0) {
										# Significa que hay tolerancia en dias descontamos
										$newExpiries = strtotime('+' . $tolerance . ' day', strtotime($cutdate));
										$cutdate = date('Y-m-d', $newExpiries);
									}
									//set cut date
									$newExpiries = strtotime('+1 day', strtotime($cutdate));
									$dateclient = date('Y-m-d', $newExpiries);
									////// EXECUTION CUT SERVICE //////
									if (date('Y-m-d') >= $dateclient) {
										
										//recover num clients for plans
										$num_cli = Helpers::getnumcl($routers[$i], $control, $client->plan_id);
										
										$pl = new GetPlan();
										$plan = $pl->get($client->plan_id);
										//opcion avanzada burst del plan
										$burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);
										
										$advanced_data = array(
											'name' => $client->name,
											'status' => 'ac',
											'arp' => $arp,
											'adv' => $adv,
											'dhcp' => $dhcp,
											'drop' => $drop,
											'mac' => $client->mac,
											'lan' => $con['lan'],
											'namePlan' => $plan['name'],
											'num_cl' => $num_cli,
											'speed_down' => $plan['download'],
											'speed_up' => $plan['upload'],
											//advanced for pcq
											'priority_a' => $plan['priority'],
											'rate_down' => $plan['download'] . 'k',
											'rate_up' => $plan['upload'] . 'k',
											'burst_rate_down' => $burst['bld'],
											'burst_rate_up' => $burst['blu'],
											'burst_threshold_down' => $burst['btd'],
											'burst_threshold_up' => $burst['btu'],
											'limit_at_down' => $burst['lim_at_down'],
											'limit_at_up' => $burst['lim_at_up'],
											'burst_time' => $plan['burst_time'],
										);
										
										$rocket->block_pcq($API, $advanced_data, $client->ipclient, $debug);
										
										
										//descontamos el numero de clientes del plan
										$counter->step_down_plan($client->plan_id);
										
										$cl = Clienttbl::find($client->client_id);
										$cl->status = 'de';
										$cl->online = 'off';
										$cl->save();
										//registramos en el log del sistema
										$name = $client->name;
										CommonService::log("Corte Automatico cliente: $name", 'Automatic', 'danger' );
										
									}//end if cut service
									
								}//end foreach
								
								break;
							
							case 'ha':
								# block for hotspot pcq address list
								foreach ($clients as $client) {
									//inicializamos variables principales
									$cutdate = $client->expiration;
									//tolerancia en días al corte
									if ($tolerance != 0) {
										# Significa que hay tolerancia en dias descontamos
										$newExpiries = strtotime('+' . $tolerance . ' day', strtotime($cutdate));
										$cutdate = date('Y-m-d', $newExpiries);
									}
									//set cut date
									$newExpiries = strtotime('+1 day', strtotime($cutdate));
									$dateclient = date('Y-m-d', $newExpiries);
									////// EXECUTION CUT SERVICE //////
									if (date('Y-m-d') >= $dateclient) {
										
										//recover num clients for plans
										$num_cli = Helpers::getnumcl($routers[$i], $control, $client->plan_id);
										
										$pl = new GetPlan();
										$plan = $pl->get($client->plan_id);
										//opcion avanzada burst del plan
										$burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);
										
										$advanced_data = array(
											'name' => $client->name,
											'user' => $client->user,
											'status' => 'ac',
											'arp' => $arp,
											'adv' => $adv,
											'dhcp' => $dhcp,
											'drop' => $drop,
											'mac' => $client->mac,
											'lan' => $con['lan'],
											'namePlan' => $plan['name'],
											'num_cl' => $num_cli,
											'speed_down' => $plan['download'],
											'speed_up' => $plan['upload'],
											//advanced for pcq
											'priority_a' => $plan['priority'],
											'rate_down' => $plan['download'] . 'k',
											'rate_up' => $plan['upload'] . 'k',
											'burst_rate_down' => $burst['bld'],
											'burst_rate_up' => $burst['blu'],
											'burst_threshold_down' => $burst['btd'],
											'burst_threshold_up' => $burst['btu'],
											'limit_at_down' => $burst['lim_at_down'],
											'limit_at_up' => $burst['lim_at_up'],
											'burst_time' => $plan['burst_time'],
										);
										
										$rocket->block_hotspot_pcq($API, $advanced_data, $client->ipclient, $debug);
										
										//descontamos el numero de clientes del plan
										$counter->step_down_plan($client->plan_id);
										
										$cl = Clienttbl::find($client->client_id);
										$cl->status = 'de';
										$cl->online = 'off';
										$cl->save();
										//registramos en el log del sistema
										$name = $client->name;
										CommonService::log("Corte Automatico cliente: $name", 'Automatic', 'danger' );
										
									}//end if cut service
									
								}//end foreach
								
								break;
							
							case 'pa':
								# block for pppoe pcq address list
								foreach ($clients as $client) {
									//inicializamos variables principales
									$cutdate = $client->expiration;
									//tolerancia en días al corte
									if ($tolerance != 0) {
										# Significa que hay tolerancia en dias descontamos
										$newExpiries = strtotime('+' . $tolerance . ' day', strtotime($cutdate));
										$cutdate = date('Y-m-d', $newExpiries);
									}
									//set cut date
									$newExpiries = strtotime('+1 day', strtotime($cutdate));
									$dateclient = date('Y-m-d', $newExpiries);
									////// EXECUTION CUT SERVICE //////
									if (date('Y-m-d') >= $dateclient) {
										
										//recover num clients for plans
										$num_cli = Helpers::getnumcl($routers[$i], $control, $client->plan_id);
										
										$pl = new GetPlan();
										$plan = $pl->get($client->plan_id);
										//opcion avanzada burst del plan
										$burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);
										
										$advanced_data = array(
											'name' => $client->name,
											'user' => $client->user,
											'status' => 'ac',
											'arp' => $arp,
											'adv' => $adv,
											'dhcp' => $dhcp,
											'drop' => $drop,
											'mac' => $client->mac,
											'lan' => $con['lan'],
											'namePlan' => $plan['name'],
											'num_cl' => $num_cli,
											'speed_down' => $plan['download'],
											'speed_up' => $plan['upload'],
											//advanced for pcq
											'priority_a' => $plan['priority'],
											'rate_down' => $plan['download'] . 'k',
											'rate_up' => $plan['upload'] . 'k',
											'burst_rate_down' => $burst['bld'],
											'burst_rate_up' => $burst['blu'],
											'burst_threshold_down' => $burst['btd'],
											'burst_threshold_up' => $burst['btu'],
											'limit_at_down' => $burst['lim_at_down'],
											'limit_at_up' => $burst['lim_at_up'],
											'burst_time' => $plan['burst_time'],
										);
										
										$rocket->block_ppp_secrets_pcq($API, $advanced_data, $client->ipclient, $debug);
										
										//descontamos el numero de clientes del plan
										$counter->step_down_plan($client->plan_id);
										
										$cl = Clienttbl::find($client->client_id);
										$cl->status = 'de';
										$cl->online = 'off';
										$cl->save();
										//registramos en el log del sistema
										$name = $client->name;
										CommonService::log("Corte Automatico cliente: $name", 'Automatic', 'danger' );
										
									}//end if cut service
									
								}//end foreach
								
								break;
							
							
						}//end switch
						
						$API->disconnect();
						
					}//end if connect to api
					
				}//end if modo conexión
				else {//modo sin conexión nc
					
					# block for no nonnection router
					foreach ($clients as $client) {
						//inicializamos variables principales
						$cutdate = $client->expiration;
						//tolerancia en días al corte
						if ($tolerance != 0) {
							# Significa que hay tolerancia en dias descontamos
							$newExpiries = strtotime('+' . $tolerance . ' day', strtotime($cutdate));
							$cutdate = date('Y-m-d', $newExpiries);
						}
						//set cut date
						$newExpiries = strtotime('+1 day', strtotime($cutdate));
						$dateclient = date('Y-m-d', $newExpiries);
						////// EXECUTION CUT SERVICE //////
						if (date('Y-m-d') >= $dateclient) {
							
							//descontamos el numero de clientes del plan
							$counter->step_down_plan($client->plan_id);
							
							$cl = Clienttbl::find($client->client_id);
							$cl->status = 'de';
							$cl->online = 'off';
							$cl->save();
							//registramos en el log del sistema
							$name = $client->name;
							CommonService::log("Corte Automatico cliente: $name", 'Automatic', 'danger' );
							
						}//end if cut service
						
					}//end foreach
					
				}//end else
				
			}//end for iterate routers
			
		}//end if count
		
	}// end function
	
	/////////method for send sms///////
	public function waitingsms()
	{
		set_time_limit(0);
		$waiting_sms = TempSms::where('status', 'wa')->orWhere('status', 'pe')->get();
		$global = GlobalSetting::all()->first();
		$tol = $global->tolerance;
		$wqresponse = 1;
		if (count($waiting_sms) > 0) {
			foreach ($waiting_sms as $sms) {
				if ($sms->status == 'wa') {
					$client = Clienttbl::find($sms->client_id);
					if (!empty($client->phone)) {
						$phone = '+' . $global->phone_code . $client->phone;
						
						$message = Sms::find($sms->sms_id);
						$send_from = $message->gateway;
						
						if ($sms->template_id == 0) {
							if ($send_from == 'Twilio Whatsapp SMS') {
								$message_dtl = $this->send_twilio_whatsapp($phone, $message->message);
							}
							if ($send_from == 'Twilio SMS') {
								$message_dtl = $this->send_twilio_sms($phone, $message->message);
							}
							if ($send_from == 'Waboxapp SMS') {
								$phone = $global->phone_code . $client->phone;
								$message_dtl = $this->send_waboxapp_message($phone, $message->message);
								$wqresponse = $message_dtl->wqreponse;
								
							}
							if ($send_from == 'Whatsapp Cloud API') {
								$message_dtl = $this->send_custom_whatsappcloudapi_sms($global->phone_code . $client->phone, $message->message);
							}
						} else {
							$template = Template::find($sms->template_id);
//       $plan = Plan::find($client->plan_id);
//							$exp = SuspendClient::where('client_id', '=', $client->id)->get();
							$exp = CommonService::getServiceCortadoDate($sms->client_id);

							if($exp['cortado_date']) {
								$timestamp = strtotime($exp['cortado_date']);
								$cutday = strtotime('+' . $tol . ' day', strtotime($exp['cortado_date']));
								$Totalcost = $client->balance;
								$Totalcost = round($Totalcost, 2);
								
								$data = array(
									"empresa" => $global->company,
									"cliente" => $client->name,
									"vencimiento" => date("d/m/Y", $timestamp),
									"corte" => date('d/m/Y', $cutday),
									"plan" => "",
									"costo" => $client->balance,
									"total" => $Totalcost,
									"emailCliente" => $client->email,
									"direccionCliente" => $client->address,
									"telefonoCliente" => $client->phone,
									"dniCliente" => $client->dni,
									"moneda" => $global->nmoney,
									"Smoneda" => $global->smoney
								);
								$tem = str_replace('{{$vencimiento}}', $data['vencimiento'], $template->content);
								$tem = str_replace('{{$costo}}', $data['costo'], $tem);
								if ($send_from == 'Twilio Whatsapp SMS') {
									$message_dtl = $this->send_twilio_whatsapp($phone, $tem);
									
									
								}
								if ($send_from == 'Twilio SMS') {
									$message_dtl = $this->send_twilio_sms($phone, $tem);

								}
								
								if ($send_from == 'Waboxapp SMS') {
									
									$phone = $global->phone_code . $client->phone;
									$message_dtl = $this->send_waboxapp_message($phone, $tem);
									$wqresponse = $message_dtl->wqreponse;
									
								}

								if ($send_from == 'Whatsapp Cloud API') {
									$message_dtl = $this->send_whatsappcloudapi_sms($global->phone_code . $client->phone, $template->provider_template_name, $global->locale, $data['vencimiento'], $data['costo'], $client->name);
								}
							}
							
						}

						if ($wqresponse == 1 && isset($message_dtl)) {
							$ms = TempSms::find($sms->id);
							$ms->status = 'ok';
							$ms->smsgateway_id = $message_dtl->sid;
							$ms->save();
							$message = Sms::find($sms->sms_id);
							$percent = $message->send_rate + 1;
							$message->send_rate = $percent;
							if (!empty($template) && $template->type == 'whatsapp') {
								$message_content = str_replace('{{1}}', $client->name, $template->content);
								$message_content = str_replace('{{2}}', $data['vencimiento'], $message_content);
								$message_content = str_replace('{{3}}', $data['costo'], $message_content);
								$message->message = $message_content;
							}
							$message->save();
						}
						
					}
				}
			}
		}
		return Response::json(array('result' => 'success'));
	}//end method
	
	
	/////////method for send sms///////
	public function checkonline()
	{
		set_time_limit(0); //unlimited execution time php
		//GET all data for API
		$conf = Helpers::get_api_options('mikrotik');
		//inicializacion de clases principales
		
		$routers = DB::table('routers')
			->join('control_routers', 'control_routers.router_id', '=', 'routers.id')
			->get();
		
		
		foreach ($routers as $router) {
			
			if ($router->type_control == 'ho' || $router->type_control == 'ha') {
				
				$router_c = new RouterConnect();
				$con = $router_c->get_connect($router->id);
				$API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
				$API->debug = $conf['d'];
				
				$clients = ClientService::where('router_id', $router->id)->where('status', 'ac')->get();
				
				if (count($clients) > 0) {
					
					if ($API->connect($con['ip'], $con['login'], $con['password'])) {
						
						foreach ($clients as $client) {
							
							
							if ($client->typeauth == 'userpass' || $client->typeauth == 'mac') {
								
								$API->write('/ip/hotspot/active/print', false);
								$API->write("?address=" . $client->ip, true);
								$READ = $API->read(false);
								$ARRAY = $API->parseResponse($READ);
								
							} else {
								
								$API->write('/ip/hotspot/host/print', false);
								$API->write("?address=" . $client->ip, true);
								$READ = $API->read(false);
								$ARRAY = $API->parseResponse($READ);
							}
							
							
							if (count($ARRAY) > 0) {
								
								//esta en linea actualiamos
								$cl = ClientService::find($client->id);
								$cl->online = 'on';
								$cl->save();
								
							} else {
								//no esta en linea actuliazamos
								$cl = ClientService::find($client->id);
								$cl->online = 'off';
								$cl->save();
							}
							
							
						}//end foreach
					} else {
						//desconectamos
						$API->disconnect();
						//ponemos a todos los clientes como desconectados
						
						$cli = ClientService::where('router_id', $router->id)->get();
						
						foreach ($cli as $cl) {
							$cl->online = 'off';
							$cl->save();
						}
					}
					
				}
				
			} elseif ($router->type_control == 'pp' || $router->type_control == 'pa' || $router->type_control == 'ps') {
				
				
				$router_c = new RouterConnect();
				$con = $router_c->get_connect($router->id);
				$API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
				$API->debug = $conf['d'];
				
				$clients = ClientService::where('router_id', $router->id)->where('status', 'ac')->get();
				
				if (count($clients) > 0) {
					
					if ($API->connect($con['ip'], $con['login'], $con['password'])) {
						
						foreach ($clients as $client) {
							
							$API->write('/ppp/active/print', false);
							$API->write("?address=" . $client->ip, true);
							$READ = $API->read(false);
							$ARRAY = $API->parseResponse($READ);
							
							if (count($ARRAY) > 0) {
								
								//esta en linea actualiamos
								$cl = ClientService::find($client->id);
								$cl->online = 'on';
								$cl->save();
								
							} else {
								//no esta en linea actuliazamos
								$cl = ClientService::find($client->id);
								$cl->online = 'off';
								$cl->save();
							}
							
						}
						
					} else {
						
						//desconectamos
						$API->disconnect();
						//ponemos a todos los clientes como desconectados
						
						$cli = ClientService::where('router_id', $router->id)->get();
						
						foreach ($cli as $cl) {
							$cl->online = 'off';
							$cl->save();
						}
					}
					
				}
				
			} else {
				
				//for simple Queues, PCQ Address list, DHCP, No shaping
				
				$router_c = new RouterConnect();
				$con = $router_c->get_connect($router->id);
				$API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
				$API->debug = $conf['d'];
				$ping_count = 2;//num package send to ping
				
				$clients = ClientService::where('router_id', $router->id)->where('status', 'ac')->get();
				
				if (count($clients) > 0) {
					
					if ($API->connect($con['ip'], $con['login'], $con['password'])) {
						
						foreach ($clients as $client) {
							
							$API->write('/ping', false);
							$API->write('=address=' . $client->ip, false);
							$API->write('=count=' . $ping_count, true);
							$READ = $API->read(false);
							$ARRAY = $API->parseResponse($READ);
							if ($ARRAY[0]['received'] == '1') {
								//hay respuesta cliente online
								$cli = ClientService::find($client->id);
								$cli->online = 'on';
								$cli->save();
							} else {
								//no hay respuesta cliente offline
								$cli = ClientService::find($client->id);
								$cli->online = 'off';
								$cli->save();
							}
							
						}//end foreach
						
					} else {
						//desconectamos
						$API->disconnect();
						//ponemos a todos los clientes como desconectados
						
						$cli = ClientService::where('router_id', $router->id)->get();
						
						foreach ($cli as $cl) {
							$cl->online = 'off';
							$cl->save();
						}
						
					}//end else
				}
				
			}//end swich
			
		}//end foreach
		
		return Response::json(array('result' => 'success'));
		
	}//end method
	
	
	public function starnotifcrn()
	{
		
		/*set_time_limit(0); //unlimited execution time php
		$global = GlobalSetting::all()->first();
		$prebill = $global->send_prebill;
		$presms = $global->send_presms;
		$prewhatsapp = $global->send_prewhatsapp;
		$prewaboxapp = $global->send_prewaboxapp;
		$hrs = $global->send_hrs;
		
		Log::debug("Hello from starnotifcrn");
		//send email or sms
		if ($prebill == 1 || $presms == 1 || $prewhatsapp == 1 || $prewaboxapp == 1) {
			if ($hrs == date('H:i') . ':00') { //verificamos la hora para el envio
				//obtenemos todos los clientes planes routers y control
				
				$clients = DB::table('client_services')
					->join('clients', 'clients.id', '=', 'client_services.client_id')
					->join('plans', 'plans.id', '=', 'client_services.plan_id')
					->join('routers', 'routers.id', '=', 'client_services.router_id')
					->join('billing_settings', 'billing_settings.client_id', '=', 'clients.id')
					->join('control_routers', 'control_routers.router_id', '=', 'client_services.router_id')
					->select('clients.name', 'clients.balance', 'client_services.status As stclient', 'client_services.ip As ipclient', 'clients.email As clientEmail', 'client_services.mac',
						'plans.name As plan_name', 'plans.cost', 'plans.iva', 'routers.ip As iprouter', 'routers.name As routername',
						'routers.login', 'routers.password', 'routers.lan', 'control_routers.type_control', 'control_routers.arpmac', 'clients.dni',
						'control_routers.adv', 'client_services.router_id As routerid', 'clients.id As client_id', 'clients.address', 'clients.phone', 'billing_settings.billing_grace_period')->where('client_services.status', 'ac')
					->where('clients.balance', '<', 0)
					->get();
				
				$days = $global->before_days;
				$prebill = $global->send_prebill;
				$company = $global->company;
				$tol = $global->tolerance;
				$money = $global->nmoney;
				
				$subject = "Esto es un pre Aviso de corte de servicio";
				//Iniciamos las clases principales
				//for sms gateway.me
				if ($presms == 1) {
					$smsg = Helpers::get_api_options('smsgateway');
					
					
					if (count($smsg) > 0) {
						if ($smsg['e'] == '1') {
							$config = Configuration::getDefaultConfiguration();
							$config->setApiKey('Authorization', $smsg['t']);
							$apiClient = new ApiClient($config);
							$messageClient = new MessageApi($apiClient);
						}
					}
				}
				//verificamos si hay clientes para cortar o notificar
				foreach ($clients as $client) {
					
					
					//inicializamos variables principales
					$cutdate = CommonService::getCortadoDateWithTolerence($client->client_id, $client->billing_grace_period, $global->tolerance);
					
					$name = $client->name;
					
					$newAdvice = strtotime('-' . $days . ' day', strtotime($cutdate));
					$dc = date('Y-m-d', $newAdvice);
					
					if ($dc <= date('Y-m-d') && $cutdate >= date('Y-m-d')) {
						
						//data general para las plantillas email sms
						$timestamp = strtotime($cutdate);
						$cutday = strtotime('+' . $tol . ' day', strtotime($cutdate));
						
						$Totalcost = $client->balance + ($client->iva * ($client->balance / 100));
						$Totalcost = round($Totalcost, 2);
						
						$data = array(
							"empresa" => $company,
							"cliente" => $name,
							"vencimiento" => date("d/m/Y", $timestamp),
							"corte" => date('d/m/Y', $cutday),
							"plan" => $client->plan_name,
							"costo" => $client->balance,
							"total" => $Totalcost,
							"moneda" => $money,
							"Smoneda" => $global->smoney,
							"emailCliente" => $client->clientEmail,
							"direccionCliente" => $client->address,
							"telefonoCliente" => $client->phone,
							"dniCliente" => $client->dni
						);
						//enviamos el email si este esta activo
						if ($prebill == 1) {
							if (!empty($client->clientEmail)) {
								$emails = $client->clientEmail;
								
								try {
									Mail::send('templates.Recordatorio_de_pago_email', $data, function ($mesage) use ($emails, $subject, $company, $global) {
										$mesage->from($global->email, $company);
										$mesage->to($emails)->subject($subject);
									});
								} catch (\Exception $exception) {
									throw $exception;
								}
								
							}
						}
						
						if ($presms == 1) {
							
							//configuramos el envio de mensajes
							$sms = Helpers::get_api_options('twiliosms');
							if (count($sms) > 0) {
								if ($sms['e'] == '1') {
									if (!empty($client->phone)) {
										//enviamos el mensaje
										$messagetem = View::make('templates.Recordatorio_de_pago_sms', $data)->render();
										$global = GlobalSetting::all()->first();
										$phone = '+' . $global->phone_code . $client->phone;
										$message_dtl = $this->send_twilio_sms($phone, $messagetem);
									}
								}
							}
						}
						
						if ($prewhatsapp == 1) {
							
							//configuramos el envio de mensajes
							$smsw = Helpers::get_api_options('twiliowhatsappsms');
							if (count($smsw) > 0) {
								if ($smsw['e'] == '1') {
									if (!empty($client->phone)) {
										//enviamos el mensaje
										$messagetem = View::make('templates.Recordatorio_de_pago_sms', $data)->render();
										$global = GlobalSetting::all()->first();
										$phone = '+' . $global->phone_code . $client->phone;
										$message_dtl = $this->send_twilio_whatsapp($phone, $messagetem);
									}
								}
							}
						}
						
						if ($prewaboxapp == 1) {
							
							//configuramos el envio de mensajes
							$smsw = Helpers::get_api_options('weboxapp');
							
							if (count($smsw) > 0) {
								if ($smsw['e'] == '1') {
									if (!empty($client->phone)) {
										//enviamos el mensaje
										$messagetem = View::make('templates.Recordatorio_de_pago_sms', $data)->render();
										$global = GlobalSetting::all()->first();
										$phone = '+' . $global->phone_code . $client->phone;
										
										$message_dtl = $this->send_waboxapp_message($phone, $messagetem);
									}
								}
							}
						}
						
						
						//enviamos el sms si este esta activo
						if ($presms == 1) {
							//verificamos los gateways
							$sms = Helpers::get_api_options('modem');
							
							if (count($sms) > 0) {
								
								if ($sms['e'] == '1') {
									//solo enviamos sms a los clientes que tengan un telefono registrado
									if (!empty($client->phone)) {
										//recuperamos la plantilla
										$messagetem = View::make('templates.Recordatorio_de_pago_sms', $data)->render();
										$process = new Chkerr();
										//get connection data for login ruter
										$router = new RouterConnec();
										$con = $router->get_connect($sms['r']);
										$conf = Helpers::get_api_options('mikrotik');
										$API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
										$API->debug = $conf['d'];
										if ($API->connect($con['ip'], $con['login'], $con['password'])) {
											$phone = '+' . $global->phone_code . $client->phone;
											Psms::send_sms($API, $sms['p'], $sms['c'], $phone, $messagetem);
											
										}
										
										$API->disconnect();
									}//end if
								}//end if
							}//end if
							
							// if ($smsg['e']=='1') {
							// //solo enviamos sms a los clientes que tengan un telefono registrado
							// if (!empty($client->phone)) {
							// //recuperamos la plantilla
							// $messagetem = View::make('templates.Recordatorio_de_pago_sms',$data)->render();
							// //recuperamos información del gateway
							// $number = '+'.$global->phone_code.$client->phone;
							// // Sending a SMS Message
							// $sendMessageRequests[] = new SendMessageRequest([
							// 'phoneNumber' => $number,
							// 'message' => "$messagetem",
							// 'deviceId' => $smsg['d']
							// ]);
							
							// }//end if
							// }//end if
							
						}//end if presms
						
					}//end if
				} //end foreach
				
				//enviamos loes emails y sms
				
				//send sms for smsgateway.me
				if ($presms == 1) {
					if (count($smsg) > 0) {
						if ($smsg['e'] == '1') {
							if (isset($sendMessageRequests)) {
								if (count($sendMessageRequests) > 0) {
									$sendMessages = $messageClient->sendMessages($sendMessageRequests);
								}
							}
						}
					}
				}
				
			}//end if hrs
		}//end if send or not send prebill*/
	}
	
	public function send_twilio_sms($phone, $msg)
	{
		$twsidtoken = array();
		$twsidtoken['options'] = Helpers::get_api_options('twiliosms');
		$account_sid = $twsidtoken['options']['t'];
		$auth_token = $twsidtoken['options']['d'];
		$twilio_number = "";
		if (isset($twsidtoken['options']['n'])) {
			$twilio_number = $twsidtoken['options']['n'];
		}
		$client = new Client($account_sid, $auth_token);
		try {
			$message = $client->messages->create($phone, array('from' => $twilio_number, 'body' => $msg));
		} catch (\Exception $e) {
			$message = array();
			$message['sid'] = 0;
			$message = (object)$message;
		}
		return $message;
	}

	public function send_whatsappcloudapi_sms($phone, $template, $language, $date, $amount, $name)
	{
		$data = Helpers::get_whatsappcloud_api_options();
		$message = [];

		try {
			$response = Http::withHeaders([
				'Authorization' => 'Bearer ' . $data['access_token']
			])->withBody(
				json_encode([
					"messaging_product" => "whatsapp",
					"to" => $phone,
					"type" => "template",
					"template" => [
						"name" => $template,
						"language" => [
							"code" => $language
						],
						"components" => [
						[
							"type" => "body",
							"parameters" => [[
								"type" => "text",
								"text" => $name
							],
							[
								"type" => "text",
								"text" => $date
							],
							[
								"type" => "text",
								"text" => $amount
							]]
						]]
					]
				]), 'application/json'
			)->post("https://graph.facebook.com/v14.0/{$data['phonenumberid']}/messages");
			$mesage['sid'] = $response->json()->messages[0]->id;
		} catch (\Exception $e) {
			$message = array();
			$message['sid'] = 0;
			$message = (object)$message;
		}
		return $message;
	}

	public function send_custom_whatsappcloudapi_sms($phone, $message_text)
	{
		$data = Helpers::get_whatsappcloud_api_options();

		try {
			$response = Http::withHeaders([
				'Authorization' => 'Bearer ' . $data['access_token']
			])->withBody(
				json_encode([
					"messaging_product" => "whatsapp",
					"to" => $phone,
					"text" => [
						"body" => $message_text
						]
				]), 'application/json'
			)->post("https://graph.facebook.com/v14.0/{$data['phonenumberid']}/messages");
			$mesage['sid'] = $response->json()->messages[0]->id;
		} catch (\Exception $e) {
			$message = array();
			$message['sid'] = 0;
			$message = (object)$message;
		}
		return $message;
	}
	
	public function send_twilio_whatsapp($phone, $msg)
	{
		$twsidtoken = array();
		$twsidtoken['options'] = Helpers::get_api_options('twiliowhatsappsms');
		$account_sid = $twsidtoken['options']['t'];
		$auth_token = $twsidtoken['options']['d'];
		$twilio_number = "";
		if (isset($twsidtoken['options']['n'])) {
			$twilio_number = $twsidtoken['options']['n'];
		}
		$client = new Client($account_sid, $auth_token);
		try {
			$message = $client->messages->create("whatsapp:" . $phone, array("from" => "whatsapp:" . $twilio_number, "body" => $msg));
		} catch (\Exception $e) {
			$message = array();
			$message['sid'] = 0;
			$message = (object)$message;
		}
		return $message;
	}
	
	
	public function send_waboxapp_message($phone, $msg)
	{
		//echo $phone;
		$webidtoken = array();
		$webidtoken['options'] = Helpers::get_api_options('weboxapp');
		$token = $webidtoken['options']['t'];
		$uid = $webidtoken['options']['d'];
		//$custom_uid = $webidtoken['options']['s'];
		$custom_uid = rand(100000000, 999999999);
		
		$url = "https://www.waboxapp.com/api/send/chat?token=" . $token . "&uid=" . $uid . "&to=" . $phone . "&custom_uid=" . $custom_uid;
		$url = $url . "&text=" . urlencode($msg);
		$ch = curl_init();
		$optArray = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true
		);
		curl_setopt_array($ch, $optArray);
		$result = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($result, true);
		
		if (isset($result['success'])) {
			$wqsuccess = 1;
		} else {
			$wqsuccess = 0;
		}
		
		$message = array();
		$message['sid'] = 0;
		$message['wqreponse'] = $wqsuccess;
		
		$message = (object)$message;
		return $message;
	}

	public function whatsappWebhook()
	{
		Log::info('Debug', ['getContent' => request()->getContent(), 'all' => request()->all()]);

		$entry = request()->json('entry');

		if(!empty($entry)) {
			foreach ($entry[0]['changes'] as $change) {
				if ($change['field'] != 'messages') {
					continue;
				}
				foreach ($change['value']['messages'] as $mesage) {
					$phone_code = GlobalSetting::first()->value('phone_code');
					$from_number = substr(str_replace('+', '', $mesage['from']), strlen($phone_code));
	
					$client = ModelsClient::where('phone', $from_number)->first();
					if ($client) {
						Sms::create([
							'client' => $client->name,
							'phone' => $from_number,
							'gateway' => 'Whatsapp Cloud API',
							'message' => $mesage['text']['body'],
							'type' => 2,
							'received_at' => Carbon::createFromTimestamp($mesage['timestamp'])->setTimezone(config('app.timezone'))->toDateTimeString()
						]);
					}
				}
			}
		}

		$token = request()->get('hub_verify_token');
		$challenge = request()->get('hub_challenge');
		$mode = request()->get('hub_mode');

		if ($mode && $token) {
			if ($mode === "subscribe" && $token === config('app.whatsapp_verify_token')) {
				return $challenge;
			}
		}
	}
	
	
}//end class
