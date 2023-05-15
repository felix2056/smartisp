<?php
namespace App\libraries;
use App\models\Client;
use App\models\ClientService;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\Plan;
use App\models\radius\Radreply;
use App\models\Router;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Response;

/**
* Mikrotik - Tratamiento de velocidad
*/
class MultiplySpeed
{


	function multiply_process($option,$plan_id,$bandwidth){

		///////////////////// ACTUALIZAMOS A TODOS LOS CLIENTES EN ROUTERS ASOCIADOS AL PLAN //////////////////////////////



				//Buscamos a todos los clientes quienes estes asociados a este plan
        $clients = ClientService::with('client')->where('plan_id',$plan_id)->get();

        //recuperamos los routers

		$dts = array();
		foreach ($clients as $fg) {
			$dts[] = $fg->router_id;
		}

		$rou = array_unique($dts);
		$rou = array_values($rou);

		 		//verificamos si hay clientes
		if(count($clients)>0) {

			$global = GlobalSetting::all()->first();
			$debug = $global->debug;

	   				//GET all data for API
			$conf = Helpers::get_api_options('mikrotik');
			$error = new Mkerror();
			$rocket = new RocketCore();

					//iteramos la cantidad de routers
			for ($i=0; $i < count($rou); $i++) {

				$router = new RouterConnect();
				$con = $router->get_connect($rou[$i]);
						// creamos conexion con el router
				$API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
				$API->debug = $conf['d'];

				if ($con['connect']==0) {

							//establecemos la conexion
					if ($API->connect($con['ip'], $con['login'], $con['password'])) {

						$clients = ClientService::with('client')->where('plan_id',$plan_id)->where('router_id',$rou[$i])->get();

								//obtenemos los datos del plan actual
						$pl = new GetPlan();
						$plan = $pl->get($plan_id);

								//preparamos las datos del nuevo plan
						$newPlanName = $plan['name'];

								//comprobamos las opciones
						if ($option=='multiply') {
									//multiplicamos la velocidad por el porcentaje
							$speedx = Burst::get_percent_kb($plan['upload'],$plan['download'],$bandwidth);
							$upload = ($plan['upload'] + round($speedx['upload']));
							$download = ($plan['download'] + round($speedx['download']));
						}

						if ($option=='restore') {
									//restauramos la velocidad real
							$upload = $plan['upload'];
							$download = $plan['download'];
						}



						$comment = 'SmartISP - '.$newPlanName;
						$maxlimit = $upload.'k/'.$download.'k';
						        //obtenemos la configuracion burst del nuevo plan
						$burst = Burst::get_all_burst($upload,$download,$plan['burst_limit'],$plan['burst_threshold'],$plan['limitat']);
						$bt = $plan['burst_time'];
						        //burst limit
						$bl = $burst['blu'].'/'.$burst['bld'];
						        //burst threshold
						$bth = $burst['btu'].'/'.$burst['btd'];
								//burst time
						$bt = $bt.'/'.$bt;
								//priority
						$priority = $plan['priority'].'/'.$plan['priority'];
								//limit At
						$limit_at = $burst['lim_at_up'].'/'.$burst['lim_at_down'];

							//verificamos tipo de control
						$control = ControlRouter::where('router_id',$rou[$i])->get();
						$control = $control[0]->type_control;

						$num_cli = Helpers::getnumcl($rou[$i],$control,$plan_id);
							//switch de control

						switch ($control) {

							case 'sq':
							case 'ps':
									# simple queues
							foreach ($clients as $client) {

										//SIMPLE Q
								$SQUEUES = SimpleQueues::simple_get_id($API,$client->ip,$client->client->name.'_'.$client->id);
								if($SQUEUES != 'notFound'){
									$SQUEUES = SimpleQueues::simple_set($API,$SQUEUES[0]['.id'],$client->client->name.'_'.$client->id,$maxlimit,$client->ip,$bl,$bth,$bt,$limit_at,$priority,$comment);

									if ($debug==1) {
										$msg = $error->process_error($SQUEUES);
										if($msg)
											return $msg;
									}

								}
								else{

									$SQUEUES = SimpleQueues::simple_add($API,$client->client->name.'_'.$client->id,$client->ip,$maxlimit,$bl,$bth,$bt,$limit_at,$priority,$comment);

									if ($debug==1) {
										$msg = $error->process_error($SQUEUES);
										if($msg)
											return $msg;
									}

								}

									}//end foreach

									break;

							case 'st':
							case 'pt':

                            $clients = ClientService::with('client', 'plan')->where('plan_id', $plan_id)->where('router_id', $rou[$i])->where('status', 'ac')->get();

                            foreach ($clients as $client) {

								# simple queues with treee
                                # add or update clients to parents
                                $P_DATA = $rocket->data_simple_queue_with_tree_parent($plan_id,$rou[$i], $download, $upload, $plan['aggregation'], $plan['limitat'], $plan['burst_limit'], $plan['burst_threshold'], $client->tree_priority);

                                $dataNamePlan = Helper::replace_word($client->plan->name);
                                $comment = 'SmartISP - '.$client->plan->name;

                                if($client->tree_priority != 0) {
                                    $dataNamePlan = $dataNamePlan.'_virtual_'.$client->tree_priority;
                                    $comment = 'SmartISP - '.$client->plan->name.'_virtual_'.$client->tree_priority;
                                }

                                //buscamos regla parent segun el plan
                                $parent = SimpleQueuesTree::simple_parent_get_id($API, $dataNamePlan);

                                if ($P_DATA['ncl'] > 0) { // aplicamos los cambios si existen clientes activos en el plan

                                    if ($parent == 'notFound') {
                                        # Creamos parent
                                        $PARENT = SimpleQueuesTree::add_simple_parent($API, $dataNamePlan, $P_DATA['ips'], $P_DATA['maxlimit'], $P_DATA['bl'], $P_DATA['bth'], $bt, $P_DATA['limitat'], $priority, $dataNamePlan);

                                    } else {
                                        # Actualizamos parent
                                        $PARENT = SimpleQueuesTree::set_simple_parent($API, $parent[0]['.id'], $dataNamePlan, $P_DATA['maxlimit'], $P_DATA['ips'], $P_DATA['bl'], $P_DATA['bth'], $bt, $P_DATA['limitat'], $priority, $dataNamePlan);
                                    }



                                    $limitat = $P_DATA['limitat_up_cl'] . 'k/' . $P_DATA['limitat_down_cl'] . 'k';



                                        //SIMPLE SQWT
                                        $SQUEUES = SimpleQueuesTree::simple_child_get_id($API, $client->client->name . '_' . $client->id);

                                        if ($SQUEUES != 'notFound') {

                                            SimpleQueuesTree::set_simple_child($API, $SQUEUES[0]['.id'], $client->client->name . '_' . $client->id, $maxlimit, $client->ip, $dataNamePlan, $bl, $bth, $bt, $limitat, $priority, $comment);

                                        } else {

                                            SimpleQueuesTree::add_simple_child($API, $client->client->name . '_' . $client->id, $client->ip, $dataNamePlan, $maxlimit, $bl, $bth, $bt, $limitat, $priority, $comment);

                                        }//end else


                                    }else { //no hay clientes en el plan intentamos eliminar el plan si existe en el router
                                        if ($parent != 'notFound') {

                                            $SQUEUES = SimpleQueuesTree::simple_parent_remove($API, $parent[0]['.id']);

                                        }

                                    }

                                }//end foreach

							break;

									case 'ho':
									# hotspot

										//buscamos un user profile segun nombre del plan
									$PROFILE = Hotspot::hotspot_find_profile($API,$plan['name']);
									if ($debug==1) {
										$msg = $error->process_error($PROFILE);
										if($msg)
											return $msg;
									}


										if(count($PROFILE)>0){ // verificamos si el user profile existe si es asi añadimos los registros

											$HOTSPOT = Hotspot::hotspot_set_profile($API,$PROFILE[0]['.id'],$newPlanName,$maxlimit,$bl,$bth,$bt,$plan['priority'],$limit_at);

											if ($debug==1) {
												$msg = $error->process_error($HOTSPOT);
												if($msg)
													return $msg;
											}

										}
										else{ //creamos el perfil

											$HOTSPOT = Hotspot::hotspot_add_profile($API,$newPlanName,$maxlimit,$bl,$bth,$bt,$plan['priority'],$limit_at);

											if ($debug==1) {
												$msg = $error->process_error($HOTSPOT);
												if($msg)
													return $msg;
											}

										}


										break;

										case 'dl':
									# dhcp leases


										foreach ($clients as $client) {

										//DHCP
											$DHCP = Dhcp::dhcp_get_id($API,$client->ip,$client->mac);
											if($DHCP != 'notFound'){
												$DHCP = Dhcp::dhcp_rate_set($API,$DHCP[0]['.id'],$client->mac,$client->ip,$maxlimit,$bl,$bth,$bt,$client->client->name.' - '.$comment);

												if ($debug==1) {
													$msg = $error->process_error($DHCP);
													if($msg)
														return $msg;
												}

											}
											else{

												$DHCP = Dhcp::dhcp_add_rate($API,$client->ip,$client->mac,$maxlimit,$bl,$bth,$bt,$client->client->name.' - '.$comment);

												if ($debug==1) {
													$msg = $error->process_error($DHCP);
													if($msg)
														return $msg;
												}

											}

									}//end foreach

									break;

									case 'pp':
									# ppp secret

										//buscamos un profile segun nombre del plan
									$PROFILE = Ppp::ppp_find_profile($API,$plan['name']);

									if ($debug==1) {
										$msg = $error->process_error($PROFILE);
										if($msg)
											return $msg;
									}


										if(count($PROFILE)>0){ // verificamos si el profile existe si es asi actualizamos los registros

											$PPP = Ppp::ppp_set_profile($API,$PROFILE[0]['.id'],$newPlanName,$maxlimit,$bl,$bth,$bt,$plan['priority'],$limit_at);

											if ($debug==1) {
												$msg = $error->process_error($PPP);
												if($msg)
													return $msg;
											}

											//quitamos del user active a todos los clientes asociados al perfil para que este tenga efecto

											foreach ($clients as $client) {

												//quitamos del user active para que los cambios tengan efecto en mikrotik
												$active = Ppp::ppp_active_get_id($API,$client->ip);

												if ($active != 'notFound') {
													//eliminamos
													$remove = Ppp::active_ppp_remove($API,$active[0]['.id']);

													if ($debug==1) {
														$msg = $error->process_error($remove);
														if($msg)
															return $msg;
													}

												}
												//end user active

											}//end foreach



										}
										else{ //creamos el perfil


											$PPP = Ppp::ppp_add_profile($API,$newPlanName,$maxlimit,$bl,$bth,$bt,$plan['priority'],$limit_at);

											if ($debug==1) {
												$msg = $error->process_error($PPP);
												if($msg)
													return $msg;
											}

										}

										break;

										case ($control=='pc' || $control=='ha' || $control=='pa'):

									# pcq address list

										//Aditional Data for PCQ
										$data = array(
											//general data
											'namePlan' => $plan['name'], //old plan
											'newPlan' => $newPlanName,
											//advanced for pcq for new plan
											'speed_down' => $download,
											'speed_up' => $upload,
											'num_cl' => $num_cli,
											'rate_down' => $download.'k',
											'rate_up' => $upload.'k',
											'burst_rate_down' => $burst['bld'],
											'burst_rate_up' => $burst['blu'],
											'burst_threshold_down' => $burst['btd'],
											'burst_threshold_up' => $burst['btu'],
											'limit_at_down' => $burst['lim_at_down'],
											'limit_at_up' => $burst['lim_at_up'],
											'burst_time' => $plan['burst_time'],
											'priority_a' => $plan['priority'],
											'no_rules' => $plan['no_rules'],
										);//end data array

										$Migrate = new MkMigrate();
										$UPDATE = $Migrate->plan_migrate_pcq($API,$rocket,$data,$clients,$debug);

										if ($debug==1) {
											if (!empty($UPDATE)) {
												return $UPDATE;
											}
										}


										break;

                            case 'rr':

                                $clients = ClientService::with('client', 'plan')->where('plan_id', $plan_id)->where('router_id', $rou[$i])->where('status', 'ac')->get();
                                $router_buscado = Router::find($rou[$i]);
                                $velocidad = $upload.'k/'.$download.'k';

                                foreach ($clients as $client) {
                                    if(!$plan['no_rules']){
                                        /**si hay que aplicar control sobre el mkt, actualizamos la cola**/
                                        Radreply::where('username',$client->user_hot)->where('attribute','Mikrotik-Rate-Limit')->update(['value' => $velocidad]);
                                        $ejecucion = shell_exec('echo User-Name="'.$client->user_hot.'",Mikrotik-Rate-Limit:="'.$velocidad.'" | /usr/bin/radclient -c 1 -n 3 -r 3 -t 3 -x '.$router_buscado->ip.':3799 coa '.$router_buscado->radius->secret.' 2>&1');
                                    }
                                }


                                break;

								}//end switch

							}else //guardamos en log el error
							return Response::json(array('msg'=>'errorConnect'));


						}//end if connect


   					}//end for routers


   				}//end if count client


	}//end function

}
