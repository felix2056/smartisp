<?php

namespace App\Http\Controllers;
use App\libraries\Burst;
use App\libraries\Chkerr;
use App\libraries\CountClient;
use App\libraries\GetDate;
use App\libraries\GetPlan;
use App\libraries\Helpers;
use App\libraries\Mikrotik;
use App\libraries\Mkerror;
use App\libraries\Numbill;
use App\libraries\RegPay;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\libraries\SendQueue;
use App\libraries\Slog;
use App\models\Client;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\Payment;
use App\models\PaymentRecord;
use App\models\Plan;
use App\models\SuspendClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;


class BillController extends BaseController
{

    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
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

            $contents = View::make('bill.index', $permissions);
            $response = Response::make($contents, 200);
            $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            $response->header('Pragma', 'no-cache');
            return $response;
        } else
        return Redirect::to('admin');

    }

    //metodo para listar pagos
    public function postList()
    {
        $payments = DB::table('payments')
        ->join('plans', 'plans.id', '=', 'payments.plan_id')
        ->join('routers', 'routers.id', '=', 'payments.router_id')
        ->join('clients', 'clients.id', '=', 'payments.client_id')
        ->select('payments.id', 'payments.pay_date', 'payments.total_amount',
            'plans.name As plan_name', 'payments.num_bill', 'payments.month_pay',
            'payments.expiries_date', 'payments.after_date', 'routers.name As router_name', 'clients.name As client_name')->get();

        return Response::json($payments);
    }

    //metodo para registrar pago
    public function postCreate(Request $request)
    {
        $process = new Chkerr();

        $client_id = $request->get('id');
        $cant = $request->get('cant');
        //recuperamos informacion del cliente
        $client = Client::find($client_id);
        $nameClient = $client->name;
        $statusClient = $client->status;
        $mac = $client->mac;
        $userClient = $client->user_hot;
        //verificamos si el cliente ya se encuentra suspendido
        $pl = new GetPlan();
        $plan_id = $client->plan_id;
        $router_id = $client->router_id;
        $plan = $pl->get($plan_id);

        $dp = new GetDate();
        $log = new Slog();
        $regp = new RegPay();

        //////////////////////////
        if ($statusClient == 'de') {
            //significa que esta cortado verificamos si termino de pagar todos los meses adeudados

            $target = $client->ip;
            //get data for login ruter
            $router = new RouterConnect();
            $con = $router->get_connect($router_id);

            $config = ControlRouter::where('router_id', '=', $client->router_id)->get();

            $typeconf = $config[0]->type_control;
            $arp = $config[0]->arpmac;
            $adv = $config[0]->adv;
            $dhcp = $config[0]->dhcp;

            if ($adv == 1) {
                $drop = 0;
            } else {
                $drop = 1;
            }

            $num_cli = Helpers::getnumcl($router_id, $typeconf, $plan_id);

            //opcion avanzada burst del plan
            $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

            $data = array(
                //standar data
                'name' => $nameClient,
                'user' => $userClient,
                'status' => $statusClient,
                'arp' => $arp,
                'adv' => $adv,
                'dhcp' => $dhcp,
                'drop' => $drop,
                'mac' => $mac,
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
                //for simple queue with tree
                'download' => $plan['download'],
                'upload' => $plan['upload'],
                'aggregation' => $plan['aggregation'],
                'limitat' => $plan['limitat'],
                'burst_limit' => $plan['burst_limit'],
                'burst_threshold' => $plan['burst_threshold'],
                'plan_id' => $client->plan_id,
                'download' => $plan['download'],
                'upload' => $plan['upload'],
                'maxlimit' => $plan['maxlimit'],
                'bl' => $burst['blu'].'/'.$burst['bld'],
                'bth' => $burst['btu'].'/'.$burst['btd'],
                'bt' => $plan['burst_time'].'/'.$plan['burst_time'],
                'priority' => $plan['priority'].'/'.$plan['priority'],
                'comment' => 'SmartISP - '.$plan['name']

            );

            $counter = new CountClient();

            if ($typeconf == 'nc') { //modo sin conexión

                $df = $dp->get_date($cant, 0);
                //actualizamos pagos y nueva fecha de pago
                $pay_date = SuspendClient::where('client_id', '=', $client_id)->get();

                $regp->add($client_id, $pay_date[0]->expiration, $plan['cost'], $plan['iva'], $plan_id, $router_id, $cant, $df);

                $client->status = 'ac';
                $client->save();

                // aumentamos el contador del plan
                $counter->step_up_plan($plan_id);

                $newExpiries = strtotime('+' . $cant . ' month', strtotime($pay_date[0]->expiration));
                $nuevafecha = date('Y-m-d', $newExpiries);

                $pay_date[0]->expiration = $nuevafecha;
                $pay_date[0]->save();

                //save log
                $log->save("Se ha activado el servicio por pago al cliente: ", "info", $nameClient);

                return $process->show('success');
            }

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $router = new RouterConnect();
            $con = $router->get_connect($router_id);

            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            $global = GlobalSetting::all()->first();
            $debug = $global->debug;

            $rocket = new RocketCore();

            if ($typeconf == 'no') { //modo sin shaping solo adv, arp, drop
                //enviamos la configuracion al mikrotik
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    if ($data['status'] == 'de') { //esta activo bloqueamos

                        $error = new Mkerror();

                        $STATUS = $rocket->set_basic_config($API, $error, $data, $target, null, 'unblock', $debug);

                        if ($debug == 1) {
                            if ($STATUS != false) {
                                return $STATUS;
                            }
                        }


                        $CON = true;

                        $STATUS = 'true';
                    }

                    $API->disconnect();

                }//end connect api mikrotik
                else
                    $CON = false;

                $df = $dp->get_date($cant, 0);
                //actualizamos pagos y nueva fecha de pago
                $pay_date = SuspendClient::where('client_id', '=', $client_id)->get();

                $regp->add($client_id, $pay_date[0]->expiration, $plan['cost'], $plan['iva'], $plan_id, $router_id, $cant, $df);
                //guardamos en la base de datos
                if ($CON) {
                    $client->status = 'ac';
                    $client->save();
                    // aumentamos el contador del plan
                    $counter->step_up_plan($plan_id);
                }

                $newExpiries = strtotime('+' . $cant . ' month', strtotime($pay_date[0]->expiration));
                $nuevafecha = date('Y-m-d', $newExpiries);

                $pay_date[0]->expiration = $nuevafecha;
                $pay_date[0]->save();

                if ($CON) {
                    //save log
                    $log->save("Se ha activado el servicio por pago al cliente: ", "info", $nameClient);
                    return $process->show('success');
                } else {

                    # El proceso no fue compledado con exito no se pudo conectar con el router
                    # enviamos a la cola de procesos
                    $queue = new SendQueue(); //send to queue proccess
                    //extra data
                    $extra_data = array(
                        'client_id' => $client_id,
                        'detail' => 'activar servicio al cliente: ' . $nameClient
                    );

                    $queue->unlock_client_mikrotik($extra_data);
                    //save log
                    $log->save("No se pudo activar el servicio al cliente, no se tiene acceso al router cliente enviado a la cola de procesos: ", "info", $nameClient);
                    return $process->show('success');
                }

            }//end if control


			if($typeconf=='st'){ //tipo cola simples with tree
			//enviamos la configuracion al mikrotik

				if ($API->connect($con['ip'], $con['login'], $con['password'])) {

					//intentamos desbloquear al cliente

					$client->status = 'ac';
					$client->save();
					// aumentamos el contador del plan
					$counter->step_up_plan($plan_id);

					$STATUS = $rocket->block_simple_queue_with_tree($API,$data,$target,$debug);

					if ($debug==1) {
						if($process->check($STATUS)){
							$API->disconnect();
							return $process->check($STATUS);
						}
					}


					$API->disconnect();

					$CON = true;

				} //end connect api mikrotik
				else
					$CON = false;

				//register pay process
				$df = $dp->get_date($cant,0);
				//actualizamos pagos y nueva fecha de pago
				$pay_date = SuspendClient::where('client_id','=',$client_id)->get();

				$regp->add($client_id,$pay_date[0]->expiration,$plan['cost'],$plan['iva'],$plan_id,$router_id,$cant,$df);

				$newExpiries = strtotime ( '+'.$cant.' month' , strtotime ($pay_date[0]->expiration));
				$nuevafecha = date ( 'Y-m-d' , $newExpiries );

				$pay_date[0]->expiration = $nuevafecha;
				$pay_date[0]->save();

				if ($CON) {
					# El proceso fue completado con exito
					//save log
					$log->save("Se ha activado el servicio por pago al cliente: ","info",$nameClient);
					return $process->show('success');

				}else{
					# El proceso no fue compledado con exito no se pudo conectar con el router
					# enviamos a la cola de procesos
					$queue = new SendQueue(); //send to queue proccess
					//extra data
					$extra_data = array(
						'client_id' => $client_id,
						'detail' => 'activar servicio al cliente: '.$nameClient
					);

					$queue->unlock_client_mikrotik($extra_data);
					//save log
					$log->save("No se pudo activar el servicio al cliente, no se tiene acceso al router cliente enviado a la cola de procesos: ","info",$nameClient);
					return $process->show('success');
				}

			}// end simple queues control



			if($typeconf=='sq'){ //tipo cola simples
                //enviamos la configuracion al mikrotik

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    //intentamos desbloquear al cliente
                    $STATUS = $rocket->block_simple_queues($API, $data, $target, $debug);

                    if ($debug == 1) {
                        if ($process->check($STATUS)) {
                            $API->disconnect();
                            return $process->check($STATUS);
                        }
                    }


                    $API->disconnect();

                    $CON = true;

                } //end connect api mikrotik
                else
                    $CON = false;

                //register pay process
                $df = $dp->get_date($cant, 0);
                //actualizamos pagos y nueva fecha de pago
                $pay_date = SuspendClient::where('client_id', '=', $client_id)->get();

                $regp->add($client_id, $pay_date[0]->expiration, $plan['cost'], $plan['iva'], $plan_id, $router_id, $cant, $df);
                //guardamos en la base de datos
                if ($CON) {
                    $client->status = 'ac';
                    $client->save();
                    // aumentamos el contador del plan
                    $counter->step_up_plan($plan_id);
                }

                $newExpiries = strtotime('+' . $cant . ' month', strtotime($pay_date[0]->expiration));
                $nuevafecha = date('Y-m-d', $newExpiries);

                $pay_date[0]->expiration = $nuevafecha;
                $pay_date[0]->save();

                if ($CON) {
                    # El proceso fue completado con exito
                    //save log
                    $log->save("Se ha activado el servicio por pago al cliente: ", "info", $nameClient);
                    return $process->show('success');

                } else {
                    # El proceso no fue compledado con exito no se pudo conectar con el router
                    # enviamos a la cola de procesos
                    $queue = new SendQueue(); //send to queue proccess
                    //extra data
                    $extra_data = array(
                        'client_id' => $client_id,
                        'detail' => 'activar servicio al cliente: ' . $nameClient
                    );

                    $queue->unlock_client_mikrotik($extra_data);
                    //save log
                    $log->save("No se pudo activar el servicio al cliente, no se tiene acceso al router cliente enviado a la cola de procesos: ", "info", $nameClient);
                    return $process->show('success');
                }

            }// end simple queues control

            if ($typeconf == 'ho') {
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    //significa que esta bloqueado en ese caso desbloqueamos
                    $STATUS = $rocket->block_hotspot($API, $data, $target, $debug);

                    if ($debug == 1) {
                        if ($process->check($STATUS)) {
                            $API->disconnect();
                            return $process->check($STATUS);
                        }
                    }

                    $API->disconnect();
                    $CON = true;

                } //end connect api mikrotik
                else
                    $CON = false;


                $df = $dp->get_date($cant, 0);

                //actualizamos pagos y nueva fecha de pago
                $pay_date = SuspendClient::where('client_id', '=', $client_id)->get();

                $regp->add($client_id, $pay_date[0]->expiration, $plan['cost'], $plan['iva'], $plan_id, $router_id, $cant, $df);
                //guardamos en la base de datos
                if ($CON) {
                    $client->status = 'ac';
                    $client->save();
                    // aumentamos el contador del plan
                    $counter->step_up_plan($plan_id);
                }

                $newExpiries = strtotime('+' . $cant . ' month', strtotime($pay_date[0]->expiration));
                $nuevafecha = date('Y-m-d', $newExpiries);

                $pay_date[0]->expiration = $nuevafecha;
                $pay_date[0]->save();


                if ($CON) {
                    //save log
                    $log->save("Se ha activado el servicio al cliente: ", "info", $nameClient);

                    return $process->show('success');

                } else {
                    # El proceso no fue compledado con exito no se pudo conectar con el router
                    # enviamos a la cola de procesos
                    $queue = new SendQueue(); //send to queue proccess
                    //extra data
                    $extra_data = array(
                        'client_id' => $client_id,
                        'detail' => 'activar servicio al cliente: ' . $nameClient
                    );

                    $queue->unlock_client_mikrotik($extra_data);
                    //save log
                    $log->save("No se pudo activar el servicio al cliente, no se tiene acceso al router cliente enviado a la cola de procesos: ", "info", $nameClient);
                    return $process->show('success');
                }

            }//en if hotspot users profiles control

            //for DHCP LEASES

            if ($typeconf == 'dl') {
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    //significa que esta bloqueado en ese caso desbloqueamos
                    $STATUS = $rocket->block_dhcp_lease($API, $data, $target, $debug);

                    if ($debug == 1) {
                        if ($process->check($STATUS)) {
                            $API->disconnect();
                            return $process->check($STATUS);
                        }
                    }

                    $API->disconnect();
                    $CON = true;

                } //end connect api mikrotik
                else
                    $CON = false;


                $df = $dp->get_date($cant, 0);

                //actualizamos pagos y nueva fecha de pago
                $pay_date = SuspendClient::where('client_id', '=', $client_id)->get();

                $regp->add($client_id, $pay_date[0]->expiration, $plan['cost'], $plan['iva'], $plan_id, $router_id, $cant, $df);
                //guardamos en la base de datos
                if ($CON) {
                    $client->status = 'ac';
                    $client->save();
                    // aumentamos el contador del plan
                    $counter->step_up_plan($plan_id);
                }

                $newExpiries = strtotime('+' . $cant . ' month', strtotime($pay_date[0]->expiration));
                $nuevafecha = date('Y-m-d', $newExpiries);

                $pay_date[0]->expiration = $nuevafecha;
                $pay_date[0]->save();


                if ($CON) {
                    //save log
                    $log->save("Se ha activado el servicio al cliente: ", "info", $nameClient);
                    return $process->show('success');
                } else {
                    # El proceso no fue compledado con exito no se pudo conectar con el router
                    # enviamos a la cola de procesos
                    $queue = new SendQueue(); //send to queue proccess
                    //extra data
                    $extra_data = array(
                        'client_id' => $client_id,
                        'detail' => 'activar servicio al cliente: ' . $nameClient
                    );

                    $queue->unlock_client_mikrotik($extra_data);
                    //save log
                    $log->save("No se pudo activar el servicio al cliente, no se tiene acceso al router cliente enviado a la cola de procesos: ", "info", $nameClient);
                    return $process->show('success');
                }

            }//end if dhcp leases control


			//for PPPoe simple queue with tree
			if($typeconf=='pt'){

				if ($API->connect($con['ip'], $con['login'], $con['password'])) {

						//significa que esta bloqueado en ese caso desbloqueamos
						$STATUS = $rocket->block_ppp($API,$data,$target,$debug);

						if ($debug==1) {
							if($process->check($STATUS)){
								$API->disconnect();
								return $process->check($STATUS);
							}
						}

						$client->status = 'ac';
						$client->save();
						// aumentamos el contador del plan
						$counter->step_up_plan($plan_id);

						$STATUS = $rocket->block_simple_queue_with_tree($API,$data,$target,$debug);

						if ($debug==1) {
							if($process->check($STATUS)){
								$API->disconnect();
								return $process->check($STATUS);
							}
						}


						$API->disconnect();

						$CON = true;

				} //end connect api mikrotik
				else
					$CON = false;


						$df = $dp->get_date($cant,0);

						//actualizamos pagos y nueva fecha de pago
						$pay_date = SuspendClient::where('client_id','=',$client_id)->get();

						$regp->add($client_id,$pay_date[0]->expiration,$plan['cost'],$plan['iva'],$plan_id,$router_id,$cant,$df);

						$newExpiries = strtotime ( '+'.$cant.' month' , strtotime ($pay_date[0]->expiration));
						$nuevafecha = date ( 'Y-m-d' , $newExpiries );

						$pay_date[0]->expiration = $nuevafecha;
						$pay_date[0]->save();

						if ($CON) {
							//save log
							$log->save("Se ha activado el servicio al cliente: ","info",$nameClient);
							return $process->show('success');
						}else{
							# El proceso no fue compledado con exito no se pudo conectar con el router
							# enviamos a la cola de procesos
							$queue = new SendQueue(); //send to queue proccess
							//extra data
							$extra_data = array(
								'client_id' => $client_id,
								'detail' => 'activar servicio al cliente: '.$nameClient
							);

							$queue->unlock_client_mikrotik($extra_data);
							//save log
							$log->save("No se pudo activar el servicio al cliente, no se tiene acceso al router cliente enviado a la cola de procesos: ","info",$nameClient);
							return $process->show('success');
						}

			}//end if pppoe sectres control



			//for PPPoe
			if($typeconf=='pp' || $typeconf=='ps'){

				if ($API->connect($con['ip'], $con['login'], $con['password'])) {

						//significa que esta bloqueado en ese caso desbloqueamos
						$STATUS = $rocket->block_ppp($API,$data,$target,$debug);

                    if ($debug == 1) {
                        if ($process->check($STATUS)) {
                            $API->disconnect();
                            return $process->check($STATUS);
                        }
                    }


                    $API->disconnect();
                    $CON = true;
                } //end connect api mikrotik
                else
                    $CON = false;

                $df = $dp->get_date($cant, 0);

                //actualizamos pagos y nueva fecha de pago
                $pay_date = SuspendClient::where('client_id', '=', $client_id)->get();

                $regp->add($client_id, $pay_date[0]->expiration, $plan['cost'], $plan['iva'], $plan_id, $router_id, $cant, $df);
                //guardamos en la base de datos
                if ($CON) {
                    $client->status = 'ac';
                    $client->save();
                    // aumentamos el contador del plan
                    $counter->step_up_plan($plan_id);
                }

                $newExpiries = strtotime('+' . $cant . ' month', strtotime($pay_date[0]->expiration));
                $nuevafecha = date('Y-m-d', $newExpiries);

                $pay_date[0]->expiration = $nuevafecha;
                $pay_date[0]->save();

                if ($CON) {
                    //save log
                    $log->save("Se ha activado el servicio al cliente: ", "info", $nameClient);
                    return $process->show('success');
                } else {
                    # El proceso no fue compledado con exito no se pudo conectar con el router
                    # enviamos a la cola de procesos
                    $queue = new SendQueue(); //send to queue proccess
                    //extra data
                    $extra_data = array(
                        'client_id' => $client_id,
                        'detail' => 'activar servicio al cliente: ' . $nameClient
                    );

                    $queue->unlock_client_mikrotik($extra_data);
                    //save log
                    $log->save("No se pudo activar el servicio al cliente, no se tiene acceso al router cliente enviado a la cola de procesos: ", "info", $nameClient);
                    return $process->show('success');
                }

            }//end if pppoe sectres control

            //for PCQ Address list
            if ($typeconf == 'pc' || $typeconf == 'ha' || $typeconf == 'pa') {

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                    $STATUS = $rocket->block_pcq($API, $data, $target, $debug);

                    if ($debug == 1) {
                        if ($process->check($STATUS)) {
                            $API->disconnect();
                            return $process->check($STATUS);
                        }
                    }


                    $API->disconnect();
                    $CON = true;
                }//end connect api mikrotik
                else
                    $CON = false;

                $df = $dp->get_date($cant, 0);
                //actualizamos pagos y nueva fecha de pago
                $pay_date = SuspendClient::where('client_id', '=', $client_id)->get();

                $regp->add($client_id, $pay_date[0]->expiration, $plan['cost'], $plan['iva'], $plan_id, $router_id, $cant, $df);
                //guardamos en la base de datos
                if ($CON) {
                    $client->status = 'ac';
                    $client->save();
                    // aumentamos el contador del plan
                    $counter->step_up_plan($plan_id);
                }

                $newExpiries = strtotime('+' . $cant . ' month', strtotime($pay_date[0]->expiration));
                $nuevafecha = date('Y-m-d', $newExpiries);

                $pay_date[0]->expiration = $nuevafecha;
                $pay_date[0]->save();

                if ($CON) {
                    //save log
                    $log->save("Se ha activado el servicio al cliente: ", "info", $nameClient);
                    return $process->show('success');
                } else {
                    # El proceso no fue compledado con exito no se pudo conectar con el router
                    # enviamos a la cola de procesos
                    $queue = new SendQueue(); //send to queue proccess
                    //extra data
                    $extra_data = array(
                        'client_id' => $client_id,
                        'detail' => 'activar servicio al cliente: ' . $nameClient
                    );

                    $queue->unlock_client_mikrotik($extra_data);
                    //save log
                    $log->save("No se pudo activar el servicio al cliente, no se tiene acceso al router cliente enviado a la cola de procesos: ", "info", $nameClient);
                    return $process->show('success');
                }

            }//en pcq control

        }// end status check

        if ($client->status == 'ac') // cliente no esta vencido
        {

            $pay_date = SuspendClient::where('client_id', '=', $client_id)->get();

            $df = $dp->get_date($cant, 0);

            $regp->add($client_id, $pay_date[0]->expiration, $plan['cost'], $plan['iva'], $plan_id, $router_id, $cant, $df);

            $newExpiries = strtotime('+' . $cant . ' month', strtotime($pay_date[0]->expiration));
            $nuevafecha = date('Y-m-d', $newExpiries);

            $pay_date[0]->expiration = $nuevafecha;
            $pay_date[0]->save();
            //save log
            $log->save("Se ha registrado un pago del cliente: ", "success", $nameClient);
            return $process->show('success');

        }//fin de activo

    }

    //metodo para eliminar un pago
    public function postDelete(Request $request)
    {

        $pay_id = $request->get('id');
        $pay = Payment::find($pay_id);
        $nameClient = $pay->name;
        PaymentRecord::where('payment_id', '=', $pay_id)->delete();
        $pay->delete();

        $log = new Slog();

        $log->save("Se ha eliminado un pago del cliente: ", "danger", $nameClient);

        return Response::json(array('msg' => 'success'));
    }

    //metodo para imprimir la factura
    public function postPrint(Request $request)
    {
        //recuperamos los datos del cliente
        $client = Client::find($request->get('client_id'));
        $nameClient = $client->name;
        $emailClient = $client->email;
        $dni = $client->dni;
        $fd = strtotime($request->get('expiring_date'));
        $Expiring = date('d/m/Y', $fd);
        $global = GlobalSetting::all()->first();
        $company = $global->company;
        $general_email = $global->email;
        $num = new Numbill();
        $numBill = $request->get('nbill');
        $plan = Plan::find($client->plan_id);
        $planConst = $plan->cost;
        $numpays = $request->get('numpays'); //mese pagados
        $Totalcost = ($numpays * $planConst);
        $iva = $plan->iva;
        $costIva = $iva * ($Totalcost / 100);
        $costIva = round($costIva, 2);
        $Totalcost = $Totalcost + ($iva * ($Totalcost / 100));
        $Totalcost = round($Totalcost, 2);
        $dp = new GetDate();
        $df = $dp->get_date($numpays, 1);

        //numero de pagos
        $npays = strtotime('+' . $numpays . ' month', strtotime($request->get('expiring_date')));
        $npays = date('d/m/Y', $npays);

        //enviamos todos los datos a la plantilla
        //variables globales
        $data = array(
            "cliente" => $nameClient,
            "direccionCliente" => $client->address,
            "telefonoCliente" => $client->phone,
            "emailCliente" => $emailClient,
            "dniCliente" => $dni,
            "fechaPago" => date('d/m/Y'),
            "vencimiento" => $Expiring,
            "numFactura" => $numBill,
            "vatNumber" => $client->dni,
            "plan" => $plan->name,
            "costo" => $planConst,
            "total" => $Totalcost,
            "Smoneda" => $global->smoney,
            "subida" => $plan->upload,
            "descarga" => $plan->download,
            "hastafecha" => $npays,
            "moneda" => $global->nmoney,
            "numpagos" => $df,
            "empresa" => $company,
            "iva" => $costIva,
            "paid" => true,
            "gen" => false
        );

        $html = View::make("templates.Factura_cliente", $data);
        return $html;
    }

    //metodo para enviar comprobante de pago al email cliente
    public function postSendmail(Request $request)
    {

        if (empty($request->get('id'))) {
            return "Factura no encontrada";
        }

        $id = Payment::find($request->get('id'));
        $client = Client::find($id->client_id);

        //buscamos todos los datos
        $print = DB::table('payments')
        ->join('clients', 'clients.id', '=', 'payments.client_id')
        ->join('plans', 'plans.id', '=', 'payments.plan_id')
        ->select('payments.num_bill', 'payments.expiries_date', 'payments.total_amount',
            'payments.pay_date', 'payments.month_pay', 'payments.after_date',
            'plans.name As planname', 'payments.amount', 'payments.iva', 'plans.upload', 'plans.download', 'clients.name As client', 'clients.address', 'clients.dni',
            'clients.phone', 'clients.email')->where('payments.id', $request->get('id'))->get();

        if (count($print) > 0) {

            $global = GlobalSetting::all()->first();

            $fd = strtotime($print[0]->pay_date);
            $df = strtotime($print[0]->expiries_date);
            $afd = strtotime($print[0]->after_date);

            $num = new Numbill();
            $costIva = $print[0]->iva * ($print[0]->amount / 100);
            $costIva = round($costIva, 2);

            $data = array(
                "cliente" => $print[0]->client,
                "direccionCliente" => $print[0]->address,
                "telefonoCliente" => $print[0]->phone,
                "emailCliente" => $print[0]->email,
                "dniCliente" => $print[0]->dni,
                "fechaPago" => date('d/m/Y', $fd),
                "vencimiento" => date('d/m/Y', $df),
                "numFactura" => $num->get_format($print[0]->num_bill),
                "vatNumber" => $client->dni,
                "subida" => $print[0]->upload,
                "descarga" => $print[0]->download,
                "hastafecha" => date('d/m/Y', $afd),
                "plan" => $print[0]->planname,
                "costo" => $print[0]->amount,
                "total" => $print[0]->total_amount,
                "Smoneda" => $global->smoney,
                "moneda" => $global->nmoney,
                "numpagos" => $print[0]->month_pay,
                "empresa" => $global->company,
                "iva" => $costIva,
                "paid" => true,
                "gen" => false
            );

            $html = View::make("templates.Factura_cliente", $data);

            $email = $client->email;
            $subject = $client->name . " - Comprobante de pago";

            Mail::send('templates.Factura_cliente', $data, function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);

            });

            return Response::json(array("msg" => "success"));
        }


    }

}
