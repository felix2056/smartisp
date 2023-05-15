<?php
namespace App\libraries;
use App\libraries\SimpleQueuesTree;
use App\models\AddressRouter;
use App\models\Client;
use App\models\ClientService;
use App\models\Network;
/**
* Mikrotik - Migrations for type controls
*/
class MkMigrate
{
	//Metodo para migrar al nuevo tipo de control
	function migrate_up($API,$rocket,$data,$address,$control,$debug){

		$process = new Chkerr();

		switch ($control) {
			case 'sq':
				# Pasamos todos los clientes mas los planes a simple queues

			$SQUEUES = $rocket->add_simple_queues($API,$data,$address,$debug);

			if ($debug==1) {
					//control de y procesamiento de errores
				if($process->check($SQUEUES)){
					return $SQUEUES;
				}
			}


			return 'ok';

			break;

			case 'st':
				# Pasamos todos los clientes mas los planes a simple queues with tree

				$SQUEUES = $rocket->add_simple_queue_with_tree($API,$data,$address,'add',$debug);

				if ($debug==1) {
					//control de y procesamiento de errores
					if($process->check($SQUEUES))
						return $process->check($SQUEUES);
				}


			return 'ok';

			break;

			case 'dl':
				# Pasamos todos los clientes mas los planes a DHCP Leases
			$DHCP = $rocket->add_dhcp_leases($API,$data,$address,$debug);

			if ($debug==1) {
					//control y procesamiento de errores
				if($process->check($DHCP)){
					return $DHCP;
				}
			}


			return 'ok';

			break;

			case 'pc':

			$PCQ = $rocket->add_pcq_list($API,$data,$address,$debug);

			if ($debug==1) {
					//control y procesamiento de errores
				if($process->check($PCQ)){
					return $PCQ;
				}
			}


			return 'ok';

			break;
			case 'ho':
				# Pasamos todos los clientes mas los planes a hotspot
			$HOTSPOT = $rocket->add_user_hotspot($API,$data,$address,$debug);

			if ($debug==1) {
					//control y procesamiento de errores
				if($process->check($HOTSPOT)){
					return $HOTSPOT;
				}
			}


			return 'ok';


			break;
			case 'ha':

			$PCQ = $rocket->add_user_hotspot_pcq($API,$data,$address,$debug);

			if ($debug==1) {
					//control y procesamiento de errores
				if($process->check($PCQ)){
					return $PCQ;
				}
			}


			break;

			case 'pt':
				# Pasamos al cliente mas los planes a pppoe y simplequeues with tree
				//get gateway for addres
				$network = Network::where('ip',$address)->get();
				$gat = AddressRouter::find($network[0]->address_id);

				$SQUEUES = $rocket->add_ppp_simple_queue_with_tree($API,$data,$address,$gat->gateway,'add',$debug);

				if ($debug==1) {
					//control y procesamiento de errores
					if($process->check($SQUEUES)){
						return $SQUEUES;
					}
				}


			break;

			case 'ps': # Pasamos al cliente mas los planes a pppoe y simplequeues

				//get gateway for addres
				$network = Network::where('ip',$address)->get();
				$gat = AddressRouter::find($network[0]->address_id);

				$SQUEUES = $rocket->add_ppp_simple($API,$data,$address,$gat->gateway,$debug);

				if ($debug==1) {
					//control y procesamiento de errores
					if($process->check($SQUEUES)){
						return $SQUEUES;
					}
				}


			break;

			case 'pp':
				# Pasamos todos los clientes mas los planes a pppoe
			//get gateway for addres
			$network = Network::where('ip',$address)->get();
			$gat = AddressRouter::find($network[0]->address_id);

			$PPP = $rocket->add_ppp_secrets($API,$data,$address,$gat->gateway,$debug);

			if ($debug==1) {
					//control y procesamiento de errores
				if($process->check($PPP)){
					return $PPP;
				}
			}


			return 'ok';


			break;

			case 'pa':

				//get gateway for addres
			$network = Network::where('ip',$address)->get();
			$gat = AddressRouter::find($network[0]->address_id);

			$PCQ = $rocket->add_ppp_secrets_pcq($API,$data,$address,$gat->gateway,$debug);

			if ($debug==1) {
					//control y procesamiento de errores
				if($process->check($PCQ)){
					return $PCQ;
				}
			}


			return 'ok';

			break;
		}

	}

	//metodo para remover la configuracion inicial
	function remove_previous($API,$rocket,$data,$address,$control,$debug){

		$process = new Chkerr();

		switch ($control) {
			case 'sq':
				# Quitamos de simple queues
			$DELETE = $rocket->delete_simple_queues($API,$data,$address,$debug);

			if ($debug==1) {
					//control y procesamiento de errores
				if($process->check($DELETE)){
					return $DELETE;
				}
			}


			return 'ok';

			break;

			case 'st':
				# Quitamos de simple queues with tree

				$DELETE = $rocket->delete_simple_queue_with_tree($API,$data,$address,'delete',$debug);

				if ($debug==1) {
					//control y procesamiento de errores
					if($process->check($DELETE)){
						return $DELETE;
					}
				}

			break;

			case 'dl':

				# Quitamos de DHCP Leases
			$DELETE = $rocket->delete_dhcp_leases($API,$data,$address,$debug);

			if ($debug==1) {
					//control y procesamiento de errores
				if($process->check($DELETE)){
					return $DELETE;
				}
			}


			return 'ok';

			break;
			case 'pc':

				# Quitamos de PCQ-Address list
			$DELETE = $rocket->delete_pcq_list($API,$data,$address,'delete',$debug);

			if ($debug==1) {
					//control y procesamiento de errores
				if($process->check($DELETE)){
					return $DELETE;
				}
			}


			return 'ok';

			break;
			case 'ho':
				# Quitamos de hotspot
			$DELETE = $rocket->delete_hotspot_user($API,$data,$address,$debug);

			if ($debug==1) {
					//control y procesamiento de errores
				if($process->check($DELETE)){
					return $DELETE;
				}
			}


			return 'ok';

			break;

			case 'ha':
			$DELETE = $rocket->delete_hotspot_user_pcq($API,$data,$address,$debug);

			if ($debug==1) {
					//control y procesamiento de errores
				if($process->check($DELETE)){
					return $DELETE;
				}
			}


			return 'ok';

			break;

			case 'pt':

				$DELETE = $rocket->delete_ppp_simple_queue_with_tree($API,$data,$address,'delete',$debug);

				if ($debug==1) {
					//control y procesamiento de errores
					if($process->check($DELETE)){
						$API->disconnect();
						return $process->check($DELETE);
					}
				}

			break;

			case 'ps':
				# Quitamos de pppoe
				$DELETE = $rocket->delete_ppp_simple($API,$data,$address,$debug);

				if ($debug==1) {
					//control y procesamiento de errores
					if($process->check($DELETE)){
						return $DELETE;
					}
				}


			break;

			case 'pp':
				# Quitamos de pppoe
			$DELETE = $rocket->delete_ppp_user($API,$data,$address,$debug);

			if ($debug==1) {
					//control y procesamiento de errores
				if($process->check($DELETE)){
					return $DELETE;
				}
			}


			return 'ok';

			break;

			case 'pa':
				# Quitamos de pppoe pcq-address list
			$DELETE = $rocket->delete_ppp_secrets_pcq($API,$data,$address,$debug);

			if ($debug==1) {
					//control y procesamiento de errores
				if($process->check($DELETE)){
					return $DELETE;
				}
			}


			return 'ok';

			break;
		}

	}

	//metodo para migrar al nuevo tipo de control dentro del mismo router
	function control_migrate_process($API,$rocket,$router_id,$options,$oldcontrol,$control,$debug){

		//verificamos si esta activo el portal adv
		if ($options['adv']==1) {
			//verificamos el tipo de control
			if ($control=='pp' || $control=='pa' || $control=='ps' || $control=='pt') { //control pppoe y pppoe pcq + address list
			   	//Quitamos el web proxy standar anterior si existe
				$ADV = $rocket->remove_advs($API,$debug);

				if ($debug==1) {
					if(!empty($ADV)){
						return $ADV;
					}
				}


				//creamos reglas de bloqueo avisos solo para pppoe segun las ip redes creadas
				$networks = AddressRouter::where('router_id',$router_id)->get();
				if (count($networks)>0) {
					foreach ($networks as $net) {
				    	//iteramos las ip redes
						$STATUS = $rocket->enabled_pppoe_advs($API,$net->network,$debug);

						if ($debug==1) {
							if(!empty($STATUS)){
								return $STATUS;
							}
						}


				   }//end foreach
				}//end if count networks


			}else{

				//quitamos reglas de bloqueo pppoe si existen
				$networks = AddressRouter::where('router_id',$router_id)->get();
				if (count($networks)>0) {
					foreach ($networks as $net) {
					    //iteramos las ip redes
						$STATUS = $rocket->remove_advs_ppp($API,$net->network,$debug);
						if ($debug==1) {
							if(!empty($STATUS)){
								return $STATUS;
							}
						}

					}//end foreach
				}//end if count networks

				//activamos las reglas de bloqueo standar
				$STATUS = $rocket->enabled_advs($API,$options['lan'],$control,$debug);

				if ($debug==1) {
					if(!empty($STATUS)){
						return $STATUS;
					}
				}


			}//end else tipo control pppoe y pppoe pcq + address list



			//activamos web proxy
			$PRO = $rocket->enable_proxy($API,$debug);

			if ($debug==1) {
				if(!empty($PRO)){
					return $PRO;
				}
			}


		}//end if adv
		else{ //quitamos las reglas

			if ($control=='pp' || $control=='pa' || $control=='ps' || $control=='pt') { //control pppoe y pppoe pcq + address list
				//eliminamos solo pppoe
				$STATUS = $rocket->remove_proxy_ppp($API,$debug);
				//activamos reglas de bloequeo avisos solo para pppoe segun las ip redes creadas
				$networks = AddressRouter::where('router_id',$router_id)->get();
				if (count($networks)>0) {
					foreach ($networks as $net) {
						$STATUS = $rocket->remove_advs_ppp($API,$net->network,$debug);
						if ($debug==1) {
							if(!empty($STATUS)){
								return $STATUS;
							}
						}

				    }//end foreach
				}//end if

			}else{
				//eliminamos reglas para otros tipos de control

				//quitamos las reglas del portal cliente si existen y del web proxy
				$STATUS = $rocket->remove_advs($API,$debug);

				if ($debug==1) {
					if(!empty($STATUS)){
						return $STATUS;
					}
				}


			}//end else control pppoe y pppoe pcq + address list

		}//en else adv

		//////////////MIGRAMOS/////////////////

		//iteramos todos los clientes
		$error = new Mkerror();
		//contador generar usuarios
		$user_count = 0;
		$i=0; //count for plan

		$clients = ClientService::with('client')->where('router_id',$router_id)->get();


		foreach ($clients as $client) { //iteramos los clientes

			$user_count ++;
			//$adv2 = $adv ? 0 : 1; //negamos adv para no eliminar del web proxy durante la migración
			//obtenemos el plan del cliente
			$pl = new GetPlan();
			$plan = $pl->get($client->plan_id);
			//Obtenemos opciones avanzada burst
			$burst = Burst::get_all_burst($plan['upload'],$plan['download'],$plan['burst_limit'],$plan['burst_threshold'],$plan['limitat']);
			//preparamos la data del cliente
			$en = new Pencrypt();

			$data = array(
				//general data
				'name' => $client->client->name.'_'.$client->id,
				'typeauth' => $client->typeauth, //default login
				'profile' => $plan['name'],
				'user' => empty($client->user_hot) ? 'User-'.$user_count : $client->user_hot,
				'pass' => $client->pass_hot == '0' ? '0': $en->decode($client->pass_hot),
				'mac' => $client->mac,
				//options set default
				'arp' => $options['old_arp'],
				'adv' => $options['old_adv'],
				'dhcp' => $options['old_dhcp'],
				'drop' => 0,
				'lan' => $options['lan'],
				'namePlan' => $plan['name'],
				'maxlimit' => $plan['maxlimit'],
				'comment' => 'SmartISP - '.$plan['name'],
				//advanced
				'bl' => $burst['blu'].'/'.$burst['bld'],
				'bth' => $burst['btu'].'/'.$burst['btd'],
				'bt' => $plan['burst_time'].'/'.$plan['burst_time'],
				'priority' => $plan['priority'].'/'.$plan['priority'],
				'priority_a' => $plan['priority'],
				'limit_at' => $burst['lim_at_up'].'/'.$burst['lim_at_down'],
				//advanced for pcq
				'speed_down' => $plan['download'],
				'speed_up' => $plan['upload'],
				//'num_cl' => $plan['num_clients']-1,
				'rate_down' => $plan['download'].'k',
				'rate_up' => $plan['upload'].'k',
				'burst_rate_down' => $burst['bld'],
				'burst_rate_up' => $burst['blu'],
				'burst_threshold_down' => $burst['btd'],
				'burst_threshold_up' => $burst['btu'],
				'limit_at_down' => $burst['lim_at_down'],
				'limit_at_up' => $burst['lim_at_up'],
				'burst_time' => $plan['burst_time'],
				//for simple queue with tree
				'plan_id' => $client->plan_id,
				'router_id' => $client->router_id,
				'download' => $plan['download'],
				'upload' => $plan['upload'],
				'aggregation' => $plan['aggregation'],
				'limitat' => $plan['limitat'],
				'burst_limit' => $plan['burst_limit'],
				'burst_threshold' => $plan['burst_threshold'],
                'tree_priority' => $client->tree_priority,
                'ip' => $client->ip,

			);//end data array

		//////////start delete previous configuration ///////////

			//set data for delete items

			if ($client->status=='de') {
				if ($options['adv']==1) {
					$data['drop']=1;
				}else{
					$data['drop']=0;
				}
			}

			switch ($oldcontrol) {
				case 'sq':
					# delete simple queues
				$DELETE = $rocket->delete_simple_queues($API,$data,$client->ip,$debug);

				if ($debug==1) {
						//control y procesamiento de errores
					if(!empty($DELETE)){
						return $DELETE;
					}
				}


				break;

				case 'st':
					# delete simple queues with tree

				$rocket->set_basic_config($API,$error,$data,$client->ip,null,'delete',$debug);

				$DELETE = SimpleQueuesTree::simple_parent_get_id($API,$data['name']);
				//verificamos si se encontro al cliente, si no encontro no eliminamos del router solo de la BD
				if($DELETE != 'notFound'){
					SimpleQueues::simple_remove($API,$DELETE[0]['.id']);
				}

				//Eliminamos los parents
				//buscamos regla parent segun el plan
				$parent = SimpleQueuesTree::simple_parent_get_id($API,Helper::replace_word($data['namePlan']));

				if ($parent!='notFound') {

					$DELETE = SimpleQueuesTree::simple_parent_remove($API,$parent[0]['.id']);

					if ($debug==1) {
						//control y procesamiento de errores
						if(!empty($DELETE)){
							return $DELETE;
						}
					}
				}

				break;

				case 'ho':
					# delete hotspot
				$DELETE = $rocket->delete_hotspot_user($API,$data,$client->ip,$debug);

				if ($debug==1) {
						//control y procesamiento de errores
					if(!empty($DELETE)){
						return $DELETE;
					}
				}


				break;

				case 'dl':
					# delete dhcp leases
				$DELETE = $rocket->delete_dhcp_leases($API,$data,$client->ip,$debug);

				if ($debug==1) {
						//control y procesamiento de errores
					if(!empty($DELETE)){
						return $DELETE;
					}
				}

				break;

				case 'pt':

					$rocket->set_basic_config($API,$error,$data,$client->ip,null,'delete',$debug);

					$DELETE = SimpleQueuesTree::simple_parent_get_id($API,$data['name']);
					//verificamos si se encontro al cliente, si no encontro no eliminamos del router solo de la BD
					if($DELETE != 'notFound'){
						SimpleQueues::simple_remove($API,$DELETE[0]['.id']);
					}

					//Eliminamos los parents
					//buscamos regla parent segun el plan
					$parent = SimpleQueuesTree::simple_parent_get_id($API,Helper::replace_word($data['namePlan']));

					if ($parent!='notFound') {

						$DELETE = SimpleQueuesTree::simple_parent_remove($API,$parent[0]['.id']);

						if ($debug==1) {
							//control y procesamiento de errores
							if(!empty($DELETE)){
								return $DELETE;
							}
						}
					}

					//eliminamos del active client
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

					$PPP = Ppp::ppp_get_id($API,$data['user']);

					if($PPP != 'notFound'){

						$PPP = Ppp::ppp_remove($API,$PPP[0]['.id']);

						if ($debug==1) {
							$msg = $error->process_error($PPP);
							if($msg)
								return $msg;
						}

					}

				break;

				case 'ps':

					$DELETE = $rocket->delete_ppp_simple($API,$data,$client->ip,$debug);

					if ($debug==1) {
						//control y procesamiento de errores
						if(!empty($DELETE)){
							return $DELETE;
						}
					}

				break;

				case 'pp':
					# delete pppoe
				$DELETE = $rocket->delete_ppp_user($API,$data,$client->ip,$debug);
				if ($debug==1) {
						//control y procesamiento de errores
					if(!empty($DELETE)){
						return $DELETE;
					}
				}


				break;

				case 'pc':

						//eliminamos del address list
				if ($client->status=='ac') {

							//eliminamos del address list
					$ADDLIST = Firewall::get_id_address_list_pcq($API,$client->ip,Helper::replace_word($data['name']));

					if ($ADDLIST!='notFound') {

						$ADDLIST = Firewall::remove_address_list($API,$ADDLIST[0]['.id']);

						if ($debug==1) {
							$msg = $error->process_error($ADDLIST);
							if($msg){
								return $msg;
							}
						}

					}

							//buscamos el plan en QueueTree y eliminamos el plan DOWN
					$QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-DOWN'));

					if ($QUEUETREE!='notFound') {

						$QUEUETREE = QueueTree::delete_parent($API,$QUEUETREE[0]['.id']);

						if ($debug==1) {
							$msg = $error->process_error($QUEUETREE);
							if($msg){
								return $msg;
							}
						}

					}

							//buscamos el plan en QueueTree y eliminamos el plan UP
					$QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-UP'));

					if ($QUEUETREE != 'notFound') {

						$QUEUETREE = QueueTree::delete_parent($API,$QUEUETREE[0]['.id']);

						if ($debug==1) {
							$msg = $error->process_error($QUEUETREE);
							if($msg){
								return $msg;
							}
						}


					}

							// Buscamos y eliminamos el queue type asociado al plan DOWN

					$QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_DOWN'));

					if($QUEUETYPE != 'notFound'){

						$QUEUETYPE = QueueType::delete_queuetype($API,$QUEUETYPE[0]['.id']);

						if ($debug==1) {
							$msg = $error->process_error($QUEUETYPE);
							if($msg){
								return $msg;
							}
						}


					}

							// Buscamos y eliminamos el queue type asociado al plan UP

					$QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_UP'));

					if($QUEUETYPE != 'notFound'){

						$QUEUETYPE = QueueType::delete_queuetype($API,$QUEUETYPE[0]['.id']);
						if ($debug==1) {
							$msg = $error->process_error($QUEUETYPE);
							if($msg){
								return $msg;
							}
						}


					}


							// Buscamos y eliminamos las reglas mangle
					$MANGLE = Firewall::find_mangle($API,Helper::replace_word($data['namePlan'].'_in'));

					if ($MANGLE!='notFound') {

						$MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);

						if ($debug==1) {
							$msg = $error->process_error($MANGLE);
							if($msg){
								return $msg;
							}
						}

					}

							// Buscamos y eliminamos las reglas mangle
					$MANGLE = Firewall::find_mangle($API,Helper::replace_word($data['namePlan'].'_out'));

					if ($MANGLE!='notFound') {

						$MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);

						if ($debug==1) {
							$msg = $error->process_error($MANGLE);
							if($msg){
								return $msg;
							}
						}


					}


						}//end if status client


						//eliminamos del web proxy o del filter
						$DEL = $rocket->set_basic_config($API,$error,$data,$client->ip,null,'delete',$debug);

						if ($debug==1) {
							if ($DEL!=false) {
								return $DEL;
							}
						}



						break;

						case 'pa':

						//eliminamos el resto de secrets
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

						//eliminamos ppp secret
						$PPP = Ppp::ppp_get_id($API,$data['user']);

						if($PPP != 'notFound'){
							$PPP = Ppp::ppp_remove($API,$PPP[0]['.id']);

							if ($debug==1) {
								$msg = $error->process_error($PPP);
								if($msg)
									return $msg;
							}

						}


						//eliminamos del address list
						if ($client->status=='ac') {

							//eliminamos del address list
							$ADDLIST = Firewall::get_id_address_list_pcq($API,$client->ip,Helper::replace_word($data['name']));

							if ($ADDLIST!='notFound') {

								$ADDLIST = Firewall::remove_address_list($API,$ADDLIST[0]['.id']);

								if ($debug==1) {
									$msg = $error->process_error($ADDLIST);
									if($msg)
										return $msg;
								}

							}

							//buscamos el plan en QueueTree y eliminamos el plan DOWN
							$QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-DOWN'));

							if ($QUEUETREE!='notFound') {

								$QUEUETREE = QueueTree::delete_parent($API,$QUEUETREE[0]['.id']);

								if ($debug==1) {
									$msg = $error->process_error($QUEUETREE);
									if($msg)
										return $msg;
								}

							}

							//buscamos el plan en QueueTree y eliminamos el plan UP
							$QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-UP'));

							if ($QUEUETREE != 'notFound') {

								$QUEUETREE = QueueTree::delete_parent($API,$QUEUETREE[0]['.id']);

								if ($debug==1) {
									$msg = $error->process_error($QUEUETREE);
									if($msg)
										return $msg;
								}


							}

							// Buscamos y eliminamos el queue type asociado al plan DOWN

							$QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_DOWN'));

							if($QUEUETYPE != 'notFound'){

								$QUEUETYPE = QueueType::delete_queuetype($API,$QUEUETYPE[0]['.id']);

								if ($debug==1) {
									$msg = $error->process_error($QUEUETYPE);
									if($msg)
										return $msg;
								}


							}

							// Buscamos y eliminamos el queue type asociado al plan UP

							$QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_UP'));

							if($QUEUETYPE != 'notFound'){

								$QUEUETYPE = QueueType::delete_queuetype($API,$QUEUETYPE[0]['.id']);

								if ($debug==1) {
									$msg = $error->process_error($QUEUETYPE);
									if($msg)
										return $msg;
								}


							}


							// Buscamos y eliminamos las reglas mangle
							$MANGLE = Firewall::find_mangle($API,Helper::replace_word($data['namePlan'].'_in'));

							if ($MANGLE!='notFound') {

								$MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);

								if ($debug==1) {
									$msg = $error->process_error($MANGLE);
									if($msg)
										return $msg;
								}


							}


							// Buscamos y eliminamos las reglas mangle
							$MANGLE = Firewall::find_mangle($API,Helper::replace_word($data['namePlan'].'_out'));

							if ($MANGLE!='notFound') {

								$MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);

								if ($debug==1) {
									$msg = $error->process_error($MANGLE);
									if($msg)
										return $msg;
								}


							}


						}//end if status client


						//eliminamos del web proxy o del filter
						$DEL = $rocket->set_basic_config($API,$error,$data,$client->ip,null,'delete',$debug);

						if ($debug==1) {
							if ($DEL!=false) {
								return $DEL;
							}
						}


						break;

						case 'ha':

						//itentamos eliminamos el resto de usuarios de ip binding
						if ($data['typeauth']=='binding') {

							//buscamos la ipbinding
							$BINDING = Hotspot::get_id_ipbinding($API,$client->ip);

							if ($BINDING != 'notFound') {
								//seteamos
								$BINDING = Hotspot::remove_ipbinding($API,$BINDING[0]['.id']);

								if ($debug==1) {
									$msg = $error->process_error($BINDING);
									if($msg)
										return $msg;
								}

							}

						}

						//itentamos eliminamos el resto de usuarios del hotspot
						$HOTSPOT = Hotspot::hotspot_get_id($API,$client->ip);
						if($HOTSPOT != 'notFound'){
							$HOTSPOT = Hotspot::hotspot_remove($API,$HOTSPOT[0]['.id']);

							if ($debug==1) {
								$msg = $error->process_error($HOTSPOT);
								if($msg)
									return $msg;
							}

						}

						$HOTSPOT = Hotspot::hotspot_useractive_get_id($API,$client->ip);

						if($HOTSPOT!='notFound'){
							$HOTSPOT = Hotspot::hotspot_remove_active($API,$HOTSPOT[0]['.id']);

							if ($debug==1) {
								$msg = $error->process_error($HOTSPOT);
								if($msg)
									return $msg;
							}

						}


						//eliminamos del address list
						if ($client->status=='ac') {

							//eliminamos del address list
							$ADDLIST = Firewall::get_id_address_list_pcq($API,$client->ip,Helper::replace_word($data['name']));

							if ($ADDLIST!='notFound') {

								$ADDLIST = Firewall::remove_address_list($API,$ADDLIST[0]['.id']);

								if ($debug==1) {
									$msg = $error->process_error($ADDLIST);
									if($msg)
										return $msg;
								}

							}

							//buscamos el plan en QueueTree y eliminamos el plan DOWN
							$QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-DOWN'));

							if ($QUEUETREE!='notFound') {

								$QUEUETREE = QueueTree::delete_parent($API,$QUEUETREE[0]['.id']);

								if ($debug==1) {
									$msg = $error->process_error($QUEUETREE);
									if($msg)
										return $msg;
								}

							}

							//buscamos el plan en QueueTree y eliminamos el plan UP
							$QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-UP'));

							if ($QUEUETREE != 'notFound') {

								$QUEUETREE = QueueTree::delete_parent($API,$QUEUETREE[0]['.id']);

								if ($debug==1) {
									$msg = $error->process_error($QUEUETREE);
									if($msg)
										return $msg;
								}

							}

							// Buscamos y eliminamos el queue type asociado al plan DOWN

							$QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_DOWN'));

							if($QUEUETYPE != 'notFound'){

								$QUEUETYPE = QueueType::delete_queuetype($API,$QUEUETYPE[0]['.id']);

								if ($debug==1) {
									$msg = $error->process_error($QUEUETYPE);
									if($msg)
										return $msg;
								}

							}

							// Buscamos y eliminamos el queue type asociado al plan UP

							$QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_UP'));

							if($QUEUETYPE != 'notFound'){

								$QUEUETYPE = QueueType::delete_queuetype($API,$QUEUETYPE[0]['.id']);

								if ($debug==1) {
									$msg = $error->process_error($QUEUETYPE);
									if($msg)
										return $msg;
								}

							}


							// Buscamos y eliminamos las reglas mangle
							$MANGLE = Firewall::find_mangle($API,Helper::replace_word($data['namePlan'].'_in'));

							if ($MANGLE!='notFound') {

								$MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);

								if ($debug==1) {
									$msg = $error->process_error($MANGLE);
									if($msg)
										return $msg;
								}


							}


							// Buscamos y eliminamos las reglas mangle
							$MANGLE = Firewall::find_mangle($API,Helper::replace_word($data['namePlan'].'_out'));

							if ($MANGLE!='notFound') {

								$MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);

								if ($debug==1) {
									$msg = $error->process_error($MANGLE);
									if($msg)
										return $msg;
								}


							}


						}//end if status client


					//eliminamos del web proxy o del filter
						$DEL = $rocket->set_basic_config($API,$error,$data,$client->ip,null,'delete',$debug);

						if ($debug==1) {
							if ($DEL!=false) {
								return $DEL;
							}
						}


						break;

			}//end switch

		//////////End delete previous configuration ///////////

		/////////Start add new configuration //////////////////

			$data['adv'] = $options['adv'];
			$data['arp'] = $options['arp'];
			$data['dhcp'] = $options['dhcp'];

			if ($client->status=='de') {
				if ($options['adv']==1) {
					$data['drop']=0;
				}else{
					$data['drop']=1;
				}
			}

			switch ($control) {
				case 'sq':
					# add simplequeues
				$ADD = $rocket->add_simple_queues($API,$data,$client->ip,$debug);

				if ($debug==1) {
						//control y procesamiento de errores
					if(!empty($ADD)){
						return $ADD;
					}
				}


				break;

				case 'st':
					# add simplequeues with tree
					$ADD = $rocket->add_simple_queue_with_tree($API,$data,$client->ip,'add',$debug);

					if ($debug==1) {
							//control y procesamiento de errores
						if(!empty($ADD)){
							return $ADD;
						}
					}

				break;

				case 'ho':
					# add hotspot user profiles
				$ADD = $rocket->add_user_hotspot($API,$data,$client->ip,$debug);

				if ($debug==1) {
						//control y procesamiento de errores
					if(!empty($ADD)){
						return $ADD;
					}
				}


					//verificamos si tiene un nombre de usuario para hotspot caso contrario asignamos
				if (empty($client->user_hot)){
					$client->user_hot = $data['user'];
					$client->save();
				}

				break;

				case 'dl':
					# add dhcp leases
				$ADD = $rocket->add_dhcp_leases($API,$data,$client->ip,$debug);
				if ($debug==1) {
						//control y procesamiento de errores
					if(!empty($ADD)){
						return $ADD;
					}
				}


				break;

				case 'ps':

					$drop = $data['drop'];
					$data['drop']=0;

					$network = Network::where('ip',$client->ip)->get();
					$gat = AddressRouter::find($network[0]->address_id);

					$ADD = $rocket->add_ppp_simple($API,$data,$client->ip,$gat->gateway,$debug);

					if ($debug==1) {
						//control y procesamiento de errores
						if(!empty($ADD)){
							return $ADD;
						}
					}

					if ($drop==1) {
						//bloqueamos definitivo si esta activo el drop
						$PPP = Ppp::ppp_get_id($API,$data['user']);

						if($PPP != 'notFound'){
								//desactivamos el secret del usuario
							$PPP = Ppp::enable_disable_secret($API,$PPP[0]['.id'],'true','Servicio cortado - '.$data['name']);

							if ($debug==1) {
								$msg = $error->process_error($PPP);
								if($msg)
									return $msg;
							}
						}
					}

					//verificamos si tiene un nombre de usuario para secret caso contrario asignamos
					if (empty($client->user_hot)){
						$client->user_hot = $data['user'];
						$client->save();
					}

				break;

				case 'pt':
					# add pppoe simple queue with tree
					$drop = $data['drop'];
					$data['drop']=0;

					$network = Network::where('ip',$client->ip)->get();
					$gat = AddressRouter::find($network[0]->address_id);

					$ADD = $rocket->add_ppp_simple_queue_with_tree($API,$data,$client->ip,$gat->gateway,'add',$debug);

					if ($debug==1) {
						//control y procesamiento de errores
						if(!empty($ADD)){
							return $ADD;
						}
					}

					if ($drop==1) {
						//bloqueamos definitivo si esta activo el drop
						$PPP = Ppp::ppp_get_id($API,$data['user']);

						if($PPP != 'notFound'){
								//desactivamos el secret del usuario
							$PPP = Ppp::enable_disable_secret($API,$PPP[0]['.id'],'true','Servicio cortado - '.$data['name']);

							if ($debug==1) {
								$msg = $error->process_error($PPP);
								if($msg)
									return $msg;
							}
						}
					}

					if (empty($client->user_hot)){
						$client->user_hot = $data['user'];
						$client->save();
					}


					break;

				case 'pp':
					# add pppoe
					//get gateway for addres
				$network = Network::where('ip',$client->ip)->get();
				$gat = AddressRouter::find($network[0]->address_id);
				$drop = $data['drop'];
				$data['drop']=0;


				$ADD = $rocket->add_ppp_secrets($API,$data,$client->ip,$gat->gateway,$debug);

				if ($debug==1) {
						//control y procesamiento de errores
					if(!empty($ADD)){
						return $ADD;
					}
				}


				if ($drop==1) {
						//bloqueamos definitivo si esta activo el drop
					$PPP = Ppp::ppp_get_id($API,$data['user']);

					if($PPP != 'notFound'){
							//desactivamos el secret del usuario
						$PPP = Ppp::enable_disable_secret($API,$PPP[0]['.id'],'true','Servicio cortado - '.$data['name']);

						if ($debug==1) {
							$msg = $error->process_error($PPP);
							if($msg)
								return $msg;
						}
					}
				}


					//verificamos si tiene un nombre de usuario para secret caso contrario asignamos
				if (empty($client->user_hot)){
					$client->user_hot = $data['user'];
					$client->save();
				}

				break;

				case 'pc':


						//guardamos todos los planes asociados al router
				$PLANS[$i]=$client->plan_id;

				$ADD = $rocket->set_basic_config($API,$error,$data,$client->ip,null,'add',$debug);

				if ($debug==1) {
					if ($ADD!=false){
						return $ADD;
					}
				}



				$i++;

				break;

				case 'ha':


				if ($data['typeauth']=='binding') {

						//buscamos la ipbinding
					$BINDING = Hotspot::get_id_ipbinding($API,$client->ip);

					if ($BINDING != 'notFound') {
							//seteamos
						$BINDING = Hotspot::set_ipbinding($API,$BINDING[0]['.id'],$client->ip,$data['mac'],'bypassed',$data['name']);

						if ($debug==1) {
							$msg = $error->process_error($BINDING);
							if($msg)
								return $msg;
						}

					}else{
							//agregamos
						$BINDING = Hotspot::add_ipbinding($API,$client->ip,$data['mac'],$data['name']);

						if ($debug==1) {
							$msg = $error->process_error($BINDING);
							if($msg)
								return $msg;
						}

					}

				}

					//añadimos usuarios al hotspot
				$HOTSPOT = Hotspot::hotspot_get_id($API,$client->ip);

				if($HOTSPOT != 'notFound'){
					$HOTSPOT = Hotspot::hotspot_set($API,$HOTSPOT[0]['.id'],$data['user'],$data['pass'],$client->ip,$data['mac'],'default',$data['name'],$data['typeauth']);
					if ($debug==1) {
						$msg = $error->process_error($HOTSPOT);
						if($msg){
							return $msg;
						}
					}

				}
				else{

					$HOTSPOT = Hotspot::hotspot_add($API,$data['user'],$data['pass'],$client->ip,$data['mac'],'default',$data['name'],$data['typeauth']);
					if ($debug==1) {
						$msg = $error->process_error($HOTSPOT);
						if($msg){
							return $msg;
						}
					}

				}

					# agregamos al filter para que sean bloqueados
				$ADD = $rocket->set_basic_config($API,$error,$data,$client->ip,null,'add',$debug);

				if ($debug==1) {
					if ($ADD!=false){
						return $ADD;
					}
				}


				$PLANS[$i]=$client->plan_id;

					//verificamos si tiene un nombre de usuario para hotspot caso contrario asignamos por defecto uno
				if (empty($client->user_hot)){
					$client->user_hot = $data['user'];
					$client->save();
				}

				$i++;

				break;

				case 'pa':
					//añadimos clientes al ppp secrets

					//get gateway for addres
				$network = Network::where('ip',$client->ip)->get();
				$gat = AddressRouter::find($network[0]->address_id);


				$PPP = Ppp::ppp_get_id($API,$data['user']);

				if($PPP!='notFound'){
					$PPP = Ppp::ppp_set($API,$PPP[0]['.id'],$data['user'],$data['pass'],$client->ip,$gat->gateway,$data['mac'],'default',$data['name']);

					if ($debug==1) {
						$msg = $error->process_error($PPP);
						if($msg){
							return $msg;
						}
					}

					}else{ // no existe el usuario creamos el secret

						$PPP = Ppp::ppp_add($API,$data['user'],$data['pass'],$client->ip,$gat->gateway,$data['mac'],'default',$data['name']);
						if ($debug==1) {
							$msg = $error->process_error($PPP);
							if($msg){
								return $msg;
							}
						}

					}

					$drop = $data['drop'];
					$data['drop']=0;

					if ($drop==1) {
						//bloqueamos definitivo si esta activo el drop
						$PPP = Ppp::ppp_get_id($API,$data['user']);

						if($PPP != 'notFound'){
							//desactivamos el secret del usuario
							$PPP = Ppp::enable_disable_secret($API,$PPP[0]['.id'],'true','Servicio cortado - '.$data['name']);
							if ($debug==1) {
								$msg = $error->process_error($PPP);
								if($msg){
									return $msg;
								}
							}

						}
					}


					# agregamos al filter para que sean bloqueados
					$ADD = $rocket->set_basic_config($API,$error,$data,$client->ip,null,'add',$debug);

					if ($debug==1) {
						if ($ADD!=false){
							return $ADD;
						}
					}


					$PLANS[$i]=$client->plan_id;

					//verificamos si tiene un nombre de usuario para pppoe caso contrario asignamos por defecto uno
					if (empty($client->user_hot)){
						$client->user_hot = $data['user'];
						$client->save();
					}

					$i++;

					break;

			}//end switch

			//agregamos los clientes suspendidos
			if ($options['adv']!=0) {
				//los avisos estan activos agregamos a los clientes estado bloqueado del address list
				if ($client->status=='de') {
					//añadimos el cliente al address list
					$ADDLIST = Firewall::get_id_address_list_name($API,$client->ip,'avisos');
					if($ADDLIST != 'notFound'){
						//editamos a address list activamos
						$ADDLIST = Firewall::set_address_list($API,$ADDLIST[0]['.id'],$client->ip,'avisos','false',$client->client->name.'_'.$client->id);

						if ($debug==1) {
							$msg = $error->process_error($ADDLIST);
							if($msg){
								return $msg;
							}
						}

					}
					else{
						//no encontro la ip del usuario address list creamos con los nuevos datos enviados
						$ADDLIST = Firewall::add_address_list($API,$client->ip,'avisos','false',$client->client->name.'_'.$client->id);
						if ($debug==1) {
							$msg = $error->process_error($ADDLIST);
							if($msg){
								return $msg;
							}
						}

					}//end if
				}//end if
			}else{
				//intentamos eliminar del address list
				if ($client->status=='de') {
					//intentamos eliminar los clientes suspendidos a de addres list
					$ADDLIST = Firewall::get_id_address_list_name($API,$client->ip,'avisos');
					if($ADDLIST != 'notFound'){
						//eliminamos a address list activamos
						$ADDLIST = Firewall::remove_address_list($API,$ADDLIST[0]['.id']);
						if ($debug==1) {
							$msg = $error->process_error($ADDLIST);
							if($msg){
								return $msg;
							}
						}

					}//end if
				}//endif
			}//end else adv

		}//end foreach

		///////////// BLOCK PCQ FOR CREATE PLANS ///////////////////

		if ($control=='pc' || $control=='ha' || $control=='pa') {

			$plans = array_unique($PLANS);
			$plans = array_values($plans);

			$pl = new GetPlan();



			for ($i=0; $i < count($plans); $i++) {

				$num_cli = Helpers::getnumcl($router_id,$oldcontrol,$plans[$i]);

				$clients = ClientService::where('plan_id',$plans[$i])->where('router_id',$router_id)->get();



				//creamos los planes QUEUE TREE
				$plan = $pl->get($plans[$i]);
				//Obtenemos opciones avanzada burst
				$burst = Burst::get_all_burst($plan['upload'],$plan['download'],$plan['burst_limit'],$plan['burst_threshold'],$plan['limitat']);
				//preparamos la data del cliente

				//Aditional Data for PCQ
				$data = array(
					//general data
					'namePlan' => $plan['name'],
					'newPlan' => $plan['name'],
					//advanced for pcq plan
					'speed_down' => $plan['download'],
					'speed_up' => $plan['upload'],
					'num_cl' => $num_cli,
					'rate_down' => $plan['download'].'k',
					'rate_up' => $plan['upload'].'k',
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
			//////////////////////////////////////////////////////////////////


				$MIGRATE = $this->plan_migrate_pcq($API,$rocket,$data,$clients,$debug);

				if ($debug==1) {
					if (!empty($MIGRATE)) {
						return $MIGRATE;
					}
				}


			///////////////////////////////////////////////////////////////////////////
			}//end for

		}//end if

		/////////////END BLOCK PCQ CREATE PLANS //////////////////

		/////////////END MIGRATION ///////////

		return true;

	}//end method

	//metodo para actualizar datos plan pcq
	function plan_migrate_pcq($API,$rocket,$data,$clients,$debug){

		$error = new Mkerror();

		//creamos reglas parent si no existen
		
		if($data['no_rules'] == 0) {
			$PARENTS = $rocket->create_queuetree_parent($API,$debug);
			
			if ($debug==1) {
				$msg = $error->process_error($PARENTS);
				if($msg){
					return $msg;
				}
			}
		}

		if ($data['num_cl']>0) {

			////////////////////////////////////////////////////////////////////
			//Buscamos el plan aterior en queue types
			//QUEUE TYPE DOWN
			$QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_DOWN'));

			if($QUEUETYPE=='notFound'){
				//agregamos la regla a Queuetype
				$QUEUETYPE = QueueType::add_queuetype($API,Helper::replace_word($data['newPlan'].'_DOWN'),$data['rate_down'],$data['burst_rate_down'],$data['burst_threshold_down'],$data['burst_time'],'DOWN');

				if ($debug==1) {
					$msg = $error->process_error($QUEUETYPE);
					if($msg){
						return $msg;
					}
				}


			}else{
				//Seteamos la regla QueueType DOWN
				$QUEUETYPE = Queuetype::set_queuetype($API,$QUEUETYPE[0]['.id'],Helper::replace_word($data['newPlan'].'_DOWN'),$data['rate_down'],$data['burst_rate_down'],$data['burst_threshold_down'],$data['burst_time'],'DOWN');
				if ($debug==1) {
					$msg = $error->process_error($QUEUETYPE);
					if($msg){
						return $msg;
					}
				}

			}

			//QUEUE TYPE UP
			$QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_UP'));

			if($QUEUETYPE=='notFound'){
				//agregamos la regla a Queuetype
				$QUEUETYPE = QueueType::add_queuetype($API,Helper::replace_word($data['newPlan'].'_UP'),$data['rate_up'],$data['burst_rate_up'],$data['burst_threshold_up'],$data['burst_time'],'UP');
				if ($debug==1) {
					$msg = $error->process_error($QUEUETYPE);
					if($msg){
						return $msg;
					}
				}


			}else{
				//Seteamos la regla QueueType UP
				$QUEUETYPE = Queuetype::set_queuetype($API,$QUEUETYPE[0]['.id'],Helper::replace_word($data['newPlan'].'_UP'),$data['rate_up'],$data['burst_rate_up'],$data['burst_threshold_up'],$data['burst_time'],'UP');
				if ($debug==1) {
					$msg = $error->process_error($QUEUETYPE);
					if($msg){
						return $msg;
					}
				}


			}

			//////// Buscamos si existe la regla mangle in postrouting //////

			//recomvertimos nombres
			$dt = Helper::replace_word_mangle($data['newPlan'],"in","out");

			if($data['no_rules'] == 0) {
				$MANGLE = Firewall::find_mangle($API,Helper::replace_word($data['namePlan'].'_in'));

				if ($MANGLE=='notFound') {
					# no esta la regla agregamos nueva regla
					$MANGLE = Firewall::add_mangle_postrouting($API,$dt['plan_in'],$dt['srcaddress'],Helper::replace_word($data['newPlan'].'_in'));
					if ($debug==1) {
						$msg = $error->process_error($MANGLE);
						if($msg){
							return $msg;
						}
					}
	
				}else{
					//seteamos regla mangle
					$MANGLE = Firewall::set_mangle_postrouting($API,$MANGLE[0]['.id'],$dt['plan_in'],$dt['srcaddress'],Helper::replace_word($data['newPlan'].'_in'));
					if ($debug==1) {
						$msg = $error->process_error($MANGLE);
						if($msg){
							return $msg;
						}
					}
	
				}
	
				/////// Buscamos si existe la regla mangle out forward //////
				$MANGLE = Firewall::find_mangle($API,Helper::replace_word($data['namePlan'].'_out'));
	
				if ($MANGLE=='notFound') {
					# no esta la regla agregamos nueva regla
					$MANGLE = Firewall::add_mangle_forward($API,$dt['plan_out'],$dt['srcaddress'],Helper::replace_word($data['newPlan'].'_out'));
					if ($debug==1) {
						$msg = $error->process_error($MANGLE);
						if($msg){
							return $msg;
						}
					}
	
				}else{
					//seteamos regla mangle
	
					$MANGLE = Firewall::set_mangle_forward($API,$MANGLE[0]['.id'],$dt['plan_out'],$dt['srcaddress'],Helper::replace_word($data['newPlan'].'_out'));
					if ($debug==1) {
						$msg = $error->process_error($MANGLE);
						if($msg){
							return $msg;
						}
					}
	
	
				}
	
	
				//buscamos si existe el Queue Tree en el grupo Download
				$QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-DOWN'));
	
				if ($QUEUETREE=='notFound') {
					// creamos regla DOWN recalculando las velocidades
					$ncl = $data['num_cl'];
	
					$QUEUETREE = QueueTree::create_child($API,
						Helper::replace_word($data['newPlan'].'-DOWN'),
						'SmartISP_DOWN',
						$dt['plan_in'],
						Helper::replace_word($data['newPlan'].'_DOWN'),
						$data['priority_a'],
						RecalculateSpeed::speed($data['limit_at_down'],$ncl,true),
						RecalculateSpeed::speed($data['rate_down'],$ncl,true),
						RecalculateSpeed::speed($data['burst_rate_down'],$ncl,true),
						RecalculateSpeed::speed($data['burst_threshold_down'],$ncl,true),
						$data['burst_time']
					);
					if ($debug==1) {
						$msg = $error->process_error($QUEUETREE);
						if($msg){
							return $msg;
						}
					}
	
				}else{
	
					// encontro el plan sumamos la velocidad DOWN
					$ncl = $data['num_cl'];
	
					$QUEUETREE = QueueTree::set_child($API,
						$QUEUETREE[0]['.id'],
						Helper::replace_word($data['newPlan'].'-DOWN'),
						$dt['plan_in'],
						Helper::replace_word($data['newPlan'].'_DOWN'),
						RecalculateSpeed::speed($data['limit_at_down'],$ncl,true),
						RecalculateSpeed::speed($data['rate_down'],$ncl,true),
						RecalculateSpeed::speed($data['burst_rate_down'],$ncl,true),
						RecalculateSpeed::speed($data['burst_threshold_down'],$ncl,true),
						$data['burst_time'],
						$data['priority_a']
					);
					if ($debug==1) {
						$msg = $error->process_error($QUEUETREE);
						if($msg){
							return $msg;
						}
					}
	
	
				}
	
	
				//buscamos si existe el Queue Tree en el grupo Upload
				$QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-UP'));
	
				if ($QUEUETREE=='notFound') {
					// creamos regla DOWN recalculando las velocidades
					$ncl = $data['num_cl'];
	
					$QUEUETREE = QueueTree::create_child($API,
						Helper::replace_word($data['newPlan'].'-UP'),
						'SmartISP_UP',
						$dt['plan_out'],
						Helper::replace_word($data['newPlan'].'_UP'),
						$data['priority_a'],
						RecalculateSpeed::speed($data['limit_at_up'],$ncl,true),
						RecalculateSpeed::speed($data['rate_up'],$ncl,true),
						RecalculateSpeed::speed($data['burst_rate_up'],$ncl,true),
						RecalculateSpeed::speed($data['burst_threshold_up'],$ncl,true),
						$data['burst_time']
					);
					if ($debug==1) {
						$msg = $error->process_error($QUEUETREE);
						if($msg){
							return $msg;
						}
					}
	
				}else{
	
					// encontro el plan sumamos la velocidad DOWN
					$ncl = $data['num_cl'];
	
					$QUEUETREE = QueueTree::set_child($API,
						$QUEUETREE[0]['.id'],
						Helper::replace_word($data['newPlan'].'-UP'),
						$dt['plan_out'],
						Helper::replace_word($data['newPlan'].'_UP'),
						RecalculateSpeed::speed($data['limit_at_up'],$ncl,true),
						RecalculateSpeed::speed($data['rate_up'],$ncl,true),
						RecalculateSpeed::speed($data['burst_rate_up'],$ncl,true),
						RecalculateSpeed::speed($data['burst_threshold_up'],$ncl,true),
						$data['burst_time'],
						$data['priority_a']
					);
					if ($debug==1) {
						$msg = $error->process_error($QUEUETREE);
						if($msg){
							return $msg;
						}
					}
	
	
				}
			}
			else {
				
				// Buscamos y eliminamos las reglas mangle
				$MANGLE = Firewall::find_mangle($API,Helper::replace_word($data['namePlan'].'_in'));
				
				if ($MANGLE!='notFound') {
					
					$MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);
					
					if ($debug==1) {
						$msg = $error->process_error($MANGLE);
						if($msg)
							return $msg;
					}
					
					
				}
				
				
				// Buscamos y eliminamos las reglas mangle
				$MANGLE = Firewall::find_mangle($API,Helper::replace_word($data['namePlan'].'_out'));
				
				if ($MANGLE!='notFound') {
					
					$MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);
					
					if ($debug==1) {
						$msg = $error->process_error($MANGLE);
						if($msg)
							return $msg;
					}
					
					
				}
				
				
				//buscamos el plan en QueueTree y eliminamos el plan
				$QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-DOWN'));
				
				if ($QUEUETREE != 'notFound') {
					
					$QUEUETREE = QueueTree::delete_parent($API,$QUEUETREE[0]['.id']);
					
					if ($debug==1) {
						$msg = $error->process_error($QUEUETREE);
						if($msg)
							return $msg;
					}
					
					
				}
				
				//buscamos el plan en QueueTree y eliminamos el plan
				$QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-UP'));
				
				if ($QUEUETREE != 'notFound') {
					
					$QUEUETREE = QueueTree::delete_parent($API,$QUEUETREE[0]['.id']);
					
					if ($debug==1) {
						$msg = $error->process_error($QUEUETREE);
						if($msg)
							return $msg;
					}
					
					
				}
				
			}

		}//end if count clients

		foreach ($clients as $client) {

			if ($client->status=='ac') {
					//buscamos si existe el cliente en address list
				$ADDRESSLIST = Firewall::get_id_address_list_pcq($API,$client->ip,Helper::replace_word($client->client->name.'_'.$client->id));

				if ($ADDRESSLIST=='notFound') {
							# No esta el cliente agregamos nueva regla
					$ADDRESSLIST = Firewall::add_address_list($API,$client->ip,$dt['srcaddress'],'false',Helper::replace_word($client->client->name.'_'.$client->id));
//					$ADDRESSLIST = Firewall::add_address_list($API,$client->ip,$data['address_list_name'],'false',Helper::replace_word($client->client->name.'_'.$client->id));
					if ($debug==1) {
						$msg = $error->process_error($ADDRESSLIST);
						if($msg){
							return $msg;
						}
					}

				}else{
							//seteamos Addresslist
					$ADDRESSLIST = Firewall::set_address_list($API,$ADDRESSLIST[0]['.id'],$client->ip,$dt['srcaddress'],'false',Helper::replace_word($client->client->name.'_'.$client->id));
//					$ADDRESSLIST = Firewall::set_address_list($API,$ADDRESSLIST[0]['.id'],$client->ip,$data['address_list_name'],'false',Helper::replace_word($client->client->name.'_'.$client->id));
					if ($debug==1) {
						$msg = $error->process_error($ADDRESSLIST);
						if($msg){
							return $msg;
						}
					}

				}

				}//en if status clients

			}//end foreach

			////////////////////////////////////////////////////////////////////


	}//end method



}
