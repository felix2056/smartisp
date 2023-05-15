<?php
/**
* Mikrotik Core Process - by Smartisp
*/
namespace App\libraries;
use App\libraries\SimpleQueuesTree;
use App\models\AdvSetting;
use App\models\Client;
use App\models\ClientService;
use App\models\ControlRouter;
use App\models\Plan;
use Carbon\Carbon;

class RocketCore
{

	///////////////////// AÑADIR SIMPLE QUEUES ////////////////////////

	//metodo para añadir colas simples + arp + address list + web proxy
	function add_simple_queues($API,$data,$Address,$debug){

		$error = new Mkerror();

		$SBC = $this->set_basic_config($API,$error,$data,$Address,null,'add',$debug);

		if ($debug==1) {
			if ($SBC!=false) {
				return $SBC;
			}
		}

		$SQUEUES = SimpleQueues::simple_get_id($API,$Address,$data['name']);

		if($SQUEUES!='notFound'){

			$SQUEUES = SimpleQueues::simple_set($API,$SQUEUES[0]['.id'],$data['name'],$data['maxlimit'],$Address,$data['bl'],$data['bth'],$data['bt'],$data['limit_at'],$data['priority'],$data['comment']);

			if ($debug==1) {
				$msg = $error->process_error($SQUEUES);
				if($msg)
					return $msg;
			}


		}else{ // no existe creamos

			$SQUEUES = SimpleQueues::simple_add($API,$data['name'],$Address,$data['maxlimit'],$data['bl'],$data['bth'],$data['bt'],$data['limit_at'],$data['priority'],$data['comment']);

			if ($debug==1) {
				$msg = $error->process_error($SQUEUES);
				if($msg)
					return $msg;
			}

		}

	}

	////////////////// EDITAR SIMPLE QUEUES /////////////////////////////

	function update_simple_queues($API,$data,$Address,$newAddress,$debug)
	{
		$error = new Mkerror();

		$SBC = $this->set_basic_config($API,$error,$data,$Address,$newAddress,'update',$debug);

		if ($debug==1) {
			if ($SBC!=false) {
				return $SBC;
			}
		}

		//SIMPLE Q
		$SQUEUES = SimpleQueues::simple_get_id($API,$Address,$data['name']);

		if($SQUEUES != 'notFound'){

			$SQUEUES = SimpleQueues::simple_set($API,$SQUEUES[0]['.id'],$data['name'],$data['maxlimit'],$newAddress,$data['bl'],$data['bth'],$data['bt'],$data['limit_at'],$data['priority'],$data['comment']);

			if ($debug==1) {
				$msg = $error->process_error($SQUEUES);
				if($msg)
					return $msg;
			}

		}
		else{

			$SQUEUES = SimpleQueues::simple_add($API,$data['name'],$newAddress,$data['maxlimit'],$data['bl'],$data['bth'],$data['bt'],$data['limit_at'],$data['priority'],$data['comment']);

			if ($debug==1) {
				$msg = $error->process_error($SQUEUES);
				if($msg)
					return $msg;
			}

		}

	}

	////////////////// ELIMINAR SIMPLE QUEUES ///////////////////////////

	function delete_simple_queues($API,$data,$Address,$debug)
	{
		$error = new Mkerror();

		$SBC = $this->set_basic_config($API,$error,$data,$Address,null,'delete',$debug);

		if ($debug==1) {
			if ($SBC!=false)
				return $SBC;
		}


		//eliminamos de colas simple
		$SQUEUES = SimpleQueues::simple_get_id($API,$Address,$data['name']);
		//verificamos si se encontro al cliente, si no encontro no eliminamos del router solo de la BD
		if($SQUEUES != 'notFound'){
			$SQUEUES = SimpleQueues::simple_remove($API,$SQUEUES[0]['.id']);

			if ($debug==1) {
				$msg = $error->process_error($SQUEUES);
				if($msg)
					return $msg;
			}
		}

	}


	////////////////// BLOQUEAR SIMPLE QUEUES ///////////////////////////

	function block_simple_queues($API,$data,$Address,$debug){

		$error = new Mkerror();


		if($data['status'] == 'ac'){ //significa que esta activo en ese caso bloqueamos

			$block = $this->set_basic_config($API,$error,$data,$Address,null,'block',$debug);

			if ($block)
				return $block;
			else
				return 'true';

		}

		if($data['status'] == 'de'){ //significa que esta bloqueado en ese caso desbloqueamos

			$block = $this->set_basic_config($API,$error,$data,$Address,null,'unblock',$debug);

			if ($block)
				return $block;
			else
				return 'false';
		}

	}

	////////////////// AÑADIR HOTSPOT USING USERS PROFILES //////////////

	//metodo para añadir user + arp + user profile + burst
	function add_user_hotspot($API,$data,$Address,$debug){

		$error = new Mkerror();

			//agregamos a IP bindings bypassed si es requerido
		if ($data['typeauth']=='binding') {

				//buscamos la ipbinding
			$BINDING = Hotspot::get_id_ipbinding($API,$Address);

			if ($BINDING != 'notFound') {
					//seteamos
				$BINDING = Hotspot::set_ipbinding($API,$BINDING[0]['.id'],$Address,$data['mac'],'bypassed',$data['name']);

				if ($debug==1) {
					$msg = $error->process_error($BINDING);
					if($msg)
						return $msg;
				}

			}else{
					//agregamos
				$BINDING = Hotspot::add_ipbinding($API,$Address,$data['mac'],$data['name']);

				if ($debug==1) {
					$msg = $error->process_error($BINDING);
					if($msg)
						return $msg;
				}

			}

				/////////Agregamos control de ancho de banda mediante dhcp leases debido a que ipbinding anula el control queues ////////////
			$DHCP = $this->add_dhcp_leases($API,$data,$Address,$debug);

			if ($debug==1) {
				if ($DHCP!='ok')
					return $DHCP;
			}


		}else{

			$SBC = $this->set_basic_config($API,$error,$data,$Address,null,'add',$debug);

			if ($debug==1) {
				if ($SBC!=false)
					return $SBC;
			}

		}
			//end if ipbinding

			//buscamos un user profile segun nombre del plan
		$PROFILE = Hotspot::hotspot_find_profile($API,$data['namePlan']);

		if ($debug==1) {
			$msg = $error->process_error($PROFILE);
			if($msg)
				return $msg;
		}


			if(count($PROFILE)>0){ // verificamos si el user profile existe si es asi añadimos los registros

				//buscamos si existe el usuario
				$HOTSPOT = Hotspot::hotspot_get_id($API,$Address);

				if ($HOTSPOT=='notFound') {

					$HOTSPOT = Hotspot::hotspot_add($API,$data['user'],$data['pass'],$Address,$data['mac'],$data['profile'],$data['name'],$data['typeauth']);

					if ($debug==1) {
						$msg = $error->process_error($HOTSPOT);
						if($msg)
							return $msg;
					}


				}else{
					//set user
					$HOTSPOT = Hotspot::hotspot_set($API,$HOTSPOT[0]['.id'],$data['user'],$data['pass'],$Address,$data['mac'],$data['profile'],$data['name'],$data['typeauth']);

					if ($debug==1) {
						$msg = $error->process_error($HOTSPOT);
						if($msg)
							return $msg;
					}
				}

			}
			else{ // sigifica que no hay un user profile creamos y vinculamos ademas añadimos los registros



				$PROFILE = Hotspot::hotspot_add_profile($API,$data['namePlan'],$data['maxlimit'],$data['bl'],$data['bth'],$data['bt'],$data['priority_a'],$data['limit_at']);

				if ($debug==1) {
					$msg = $error->process_error($PROFILE);
					if($msg)
						return $msg;
				}


				//buscamos si existe el usuario
				$HOTSPOT = Hotspot::hotspot_get_id($API,$Address);

				if ($HOTSPOT=='notFound') {

					$HOTSPOT = Hotspot::hotspot_add($API,$data['user'],$data['pass'],$Address,$data['mac'],$data['profile'],$data['name'],$data['typeauth']);

					if ($debug==1) {
						//control error
						$msg = $error->process_error($HOTSPOT);
						if($msg)
							return $msg;
					}

				}else{
					//set user
					$HOTSPOT = Hotspot::hotspot_set($API,$HOTSPOT[0]['.id'],$data['user'],$data['pass'],$Address,$data['mac'],$data['profile'],$data['name'],$data['typeauth']);

					if ($debug==1) {
						$msg = $error->process_error($HOTSPOT);
						if($msg)
							return $msg;
					}

				}
			}

	} // fin del metodo hotspot

	////////////////// EDITAR HOTSPOT USER ////////////////////////////

	public function update_hotspot_user($API,$data,$Address,$newAddress,$debug){

		$error = new Mkerror();

		//agregamos a IP bindings bypassed si es requerido
		if ($data['typeauth']=='binding') {

			//buscamos la ipbinding
			$BINDING = Hotspot::get_id_ipbinding($API,$Address);

			if ($BINDING != 'notFound') {
				//seteamos
				$BINDING = Hotspot::set_ipbinding($API,$BINDING[0]['.id'],$newAddress,$data['mac'],'bypassed',$data['name']);

				if ($debug==1) {
					$msg = $error->process_error($BINDING);
					if($msg)
						return $msg;
				}


			}else{
				//agregamos
				$BINDING = Hotspot::add_ipbinding($API,$newAddress,$data['mac'],$data['name']);

				if ($debug==1) {
					$msg = $error->process_error($BINDING);
					if($msg)
						return $msg;
				}

			}

			////////////Actualizamos dhcp leases//////////////////////
			$DHCP = $this->update_dhcp_leases_user($API,$data,$Address,$newAddress,$debug);

			if ($debug==1) {
				if (!empty($DHCP))
					return $DHCP;
			}



		}else{ //intentamos eliminar de ipbiunding

			//buscamos la ipbinding
			$BINDING = Hotspot::get_id_ipbinding($API,$Address);

			if ($BINDING != 'notFound') {
				//eliminamos
				$BINDING = Hotspot::remove_ipbinding($API,$BINDING[0]['.id']);
				if ($debug==1) {
					$msg = $error->process_error($BINDING);
					if($msg)
						return $msg;
				}
			}

			//comprobamos si esta cambiando de plan
			if ($data['changePlan']==true || $data['typeauth']=='userpass') {
				#Reseteamos colas simples dinámicas quitando del avtive user
				$ACTIVE = Hotspot::hotspot_useractive_get_id($API,$Address);

				if ($ACTIVE!='notFound') {
					# quitamos
					$ACTIVE = Hotspot::hotspot_remove_active($API,$ACTIVE[0]['.id']);

					if ($debug==1) {
						$msg = $error->process_error($ACTIVE);
						if($msg)
							return $msg;
					}

				}

			}

			//ponemos las velocidades a cero en dhcpleases

			$DHCP = Dhcp::dhcp_get_id($API,$Address,$data['mac']);

			if ($DHCP!='notFound') {
				#seteamos a cero
				$DHCP = Dhcp::dhcp_reset_rate($API,$DHCP[0]['.id']);

				if ($debug==1) {
					$msg = $error->process_error($DHCP);
					if($msg)
						return $msg;
				}
			}


			$SBC = $this->set_basic_config($API,$error,$data,$Address,$newAddress,'update',$debug);

			if ($debug==1) {
				if ($SBC!=false)
					return $SBC;
			}

		}

		//buscamos un user profile segun nombre del plan
		$PROFILE = Hotspot::hotspot_find_profile($API,$data['namePlan']);

		if ($debug==1) {
			$msg = $error->process_error($PROFILE);
			if($msg)
				return $msg;
		}


		if(count($PROFILE)>0){ // verificamos si el user profile existe si es asi añadimos los registros

			$HOTSPOT = Hotspot::hotspot_get_id($API,$Address);

			if($HOTSPOT!='notFound'){

				$HOTSPOT = Hotspot::hotspot_set($API,$HOTSPOT[0]['.id'],$data['user'],$data['pass'],$newAddress,$data['mac'],$data['profile'],$data['name'],$data['typeauth']);

				if ($debug==1) {
					$msg = $error->process_error($HOTSPOT);
					if($msg)
						return $msg;
				}

			}
			else{

				$HOTSPOT = Hotspot::hotspot_add($API,$data['user'],$data['pass'],$Address,$data['mac'],$data['profile'],$data['name'],$data['typeauth']);

				if ($debug==1) {
					$msg = $error->process_error($HOTSPOT);
					if($msg)
						return $msg;
				}

			}

			return 'ok';
		}
		else{//no encontro perfil

			if ($data['profile']=='*0') {

				$PROFILE = Hotspot::hotspot_add_profile($API,$data['namePlan'],$data['maxlimit'],$data['bl'],$data['bth'],$data['bt'],$data['priority_a'],$data['limit_at']);

				if ($debug==1) {
					$msg = $error->process_error($PROFILE);
					if($msg)
						return $msg;
				}

				$data['profile'] = $data['namePlan'];

			}//end if create a new profile hotspot

			$HOTSPOT = Hotspot::hotspot_get_id($API,$Address);

			if($HOTSPOT!='notFound'){

				$HOTSPOT = Hotspot::hotspot_set($API,$HOTSPOT[0]['.id'],$data['user'],$data['pass'],$newAddress,$data['mac'],$data['profile'],$data['name'],$data['typeauth']);

				if ($debug==1) {
					$msg = $error->process_error($HOTSPOT);
					if($msg)
						return $msg;
				}

			}
			else{

				$HOTSPOT = Hotspot::hotspot_add($API,$data['user'],$data['pass'],$newAddress,$data['mac'],$data['profile'],$data['name'],$data['typeauth']);

				if ($debug==1) {
					$msg = $error->process_error($HOTSPOT);
					if($msg)
						return $msg;
				}

			}

			return 'ok';
		}

	}

	////////////////// ELIMINAR HOTSPOT USER ////////////////////////////

	public function delete_hotspot_user($API,$data,$Address,$debug){

		$error = new Mkerror();

		$SBC = $this->set_basic_config($API,$error,$data,$Address,null,'delete',$debug);

		if ($debug==1) {
			if ($SBC!=false)
				return $SBC;
		}


		//eliminamos ipbinding si es requerido

		if ($data['typeauth']=='binding') {

			//buscamos la ipbinding
			$BINDING = Hotspot::get_id_ipbinding($API,$Address);

			if ($BINDING != 'notFound') {
				//seteamos
				$BINDING = Hotspot::remove_ipbinding($API,$BINDING[0]['.id']);

				if ($debug==1) {
					$msg = $error->process_error($BINDING);
					if($msg)
						return $msg;
				}

			}

			//eliminamos control de ancho de banda de dhcp leases
			$DHCP=Dhcp::dhcp_get_id($API,$Address,$data['mac']);

			if ($DHCP!='notFound') {
				$DHCP=Dhcp::dhcp_remove($API,$DHCP[0]['.id']);

				if ($debug==1) {
					$msg = $error->process_error($DHCP);
					if($msg)
						return $msg;
				}

			}

		}else{
			//intentamos eliminar de dhcp leases
			$DHCP=Dhcp::dhcp_get_id($API,$Address,$data['mac']);

			if ($DHCP!='notFound') {
				$DHCP=Dhcp::dhcp_remove($API,$DHCP[0]['.id']);

				if ($debug==1) {
					$msg = $error->process_error($DHCP);
					if($msg)
						return $msg;
				}

			}
		}


		$HOTSPOT = Hotspot::hotspot_get_id($API,$Address);
		if($HOTSPOT != 'notFound'){
			$HOTSPOT = Hotspot::hotspot_remove($API,$HOTSPOT[0]['.id']);

			if ($debug==1) {
				$msg = $error->process_error($HOTSPOT);
				if($msg)
					return $msg;
			}

		}

		$HOTSPOT = Hotspot::hotspot_useractive_get_id($API,$Address);

		if($HOTSPOT!='notFound'){

			$HOTSPOT = Hotspot::hotspot_remove_active($API,$HOTSPOT[0]['.id']);

			if ($debug==1) {
				$msg = $error->process_error($HOTSPOT);
				if($msg)
					return $msg;
			}

		}

		$FILTER = Firewall::get_id_filter_block($API,$Address);

		if ($FILTER!='notFound') {
			//encontro la regla quitamos la regla
			$BLOCK = Firewall::remove_filter_block($API,$FILTER[0]['.id']);

			if ($debug==1) {
				$msg = $error->process_error($BLOCK);
				if($msg)
					return $msg;
			}

		}

	}

	////////////////// BLOQUEAR HOTSPOT ///////////////////////////

	public function block_hotspot($API,$data,$Address,$debug){

		$error = new Mkerror();

		if($data['status'] == 'ac'){ //significa que esta activo en ese caso bloqueamos

			$block = $this->set_basic_config($API,$error,$data,$Address,null,'block',$debug);

			if ($block)
				return $block;
			else
				return 'true';

			// bloqueamos ip binding
			//$BLOCK = Hotspot::block($API,$Address,$data['mac'],$data['name'].' - Cortado');
			//$msg = $error->process_error($BLOCK);
			//if($msg)
				//return $msg;
		}

		if($data['status'] == 'de'){ //significa que esta bloqueado en ese caso desbloqueamos


			$block = $this->set_basic_config($API,$error,$data,$Address,null,'unblock',$debug);

			if ($block)
				return $block;
			else
				return 'false';

			//$HOTSPOT = Hotspot::get_id_ipbinding($API,$Address);
			//$HOTSPOT = Hotspot::remove_ipbinding($API,$HOTSPOT[0]['.id']);
			//$msg = $error->process_error($HOTSPOT);
			//if($msg)
				//return $msg;

		}

	}



	//////////////////// AÑADIR HOTSPOT - PCQ ADDRESS LIST //////

	public function add_user_hotspot_pcq($API,$data,$Address,$debug){

		$error = new Mkerror();

		$HOTSPOT = Hotspot::hotspot_get_id($API,$Address);

		if($HOTSPOT != 'notFound'){
			$HOTSPOT = Hotspot::hotspot_set($API,$HOTSPOT[0]['.id'],$data['user'],$data['pass'],$Address,$data['mac'],'default',$data['name'],$data['typeauth']);

			if ($debug==1) {
				$msg = $error->process_error($HOTSPOT);
				if($msg)
					return $msg;
			}

		}
		else{

			$HOTSPOT = Hotspot::hotspot_add($API,$data['user'],$data['pass'],$Address,$data['mac'],'default',$data['name'],$data['typeauth']);

			if ($debug==1) {
				$msg = $error->process_error($HOTSPOT);
				if($msg)
					return $msg;
			}

		}

		//agregamos a IP bindings bypassed si es requerido
		if ($data['typeauth']=='binding') {

			//buscamos la ipbinding
			$BINDING = Hotspot::get_id_ipbinding($API,$Address);

			if ($BINDING != 'notFound') {
				//seteamos
				$BINDING = Hotspot::set_ipbinding($API,$BINDING[0]['.id'],$Address,$data['mac'],'bypassed',$data['name']);

				if ($debug==1) {
					$msg = $error->process_error($BINDING);
					if($msg)
						return $msg;
				}


			}else{
				//agregamos
				$BINDING = Hotspot::add_ipbinding($API,$Address,$data['mac'],$data['name']);

				if ($debug==1) {
					$msg = $error->process_error($BINDING);
					if($msg)
						return $msg;
				}

			}
		}

		//agregamos a PCQ Address list
		$PCQ = $this->add_pcq_list($API,$data,$Address,$debug);

		if ($debug==1) {
			if (!empty($PCQ))
				return $PCQ;
		}


	}

	//////////////////// ACTUALIZAR HOTSPOT - PCQ ADDRESS LIST //////

	public function update_hotspot_user_pcq($API,$data,$Address,$newAddress,$auth,$debug){

		$error = new Mkerror();

		//buscamos un user profile segun nombre del plan
		$PROFILE = Hotspot::hotspot_find_profile_pcq($API);

		if ($debug==1) {
			$msg = $error->process_error($PROFILE);
			if($msg)
				return $msg;
		}


		$HOTSPOT = Hotspot::hotspot_get_id($API,$Address);

		if($HOTSPOT != 'notFound'){
			$HOTSPOT = Hotspot::hotspot_set($API,$HOTSPOT[0]['.id'],$data['user'],$data['pass'],$newAddress,$data['mac'],$PROFILE[0]['name'],$data['name'],$auth);

			if ($debug==1) {
				$msg = $error->process_error($HOTSPOT);
				if($msg)
					return $msg;
			}

		}
		else{

			$HOTSPOT = Hotspot::hotspot_add($API,$data['user'],$data['pass'],$Address,$data['mac'],$PROFILE[0]['name'],$data['name'],$auth);

			if ($debug==1) {
				$msg = $error->process_error($HOTSPOT);
				if($msg)
					return $msg;
			}

		}

		//agregamos a IP bindings bypassed si es requerido
		if ($auth=='binding') {
			//buscamos la ipbinding
			$BINDING = Hotspot::get_id_ipbinding($API,$Address);

			if ($BINDING != 'notFound') {
				//seteamos
				$BINDING = Hotspot::set_ipbinding($API,$BINDING[0]['.id'],$newAddress,$data['mac'],'bypassed',$data['name']);

				if ($debug==1) {
					$msg = $error->process_error($BINDING);
					if($msg)
						return $msg;
				}


			}else{
				//agregamos
				$BINDING = Hotspot::add_ipbinding($API,$newAddress,$data['mac'],$data['name']);

				if ($debug==1) {
					$msg = $error->process_error($BINDING);
					if($msg)
						return $msg;
				}

			}
		}else{
			//intentamos eliminar el ipbinding
			//buscamos la ipbinding
			$BINDING = Hotspot::get_id_ipbinding($API,$Address);

			if ($BINDING != 'notFound') {
				//eliminamos
				$BINDING = Hotspot::remove_ipbinding($API,$BINDING[0]['.id']);

				if ($debug==1) {
					$msg = $error->process_error($BINDING);
					if($msg)
						return $msg;
				}

			}
		}

		//Actualizamod PCQ
		$UPDATE = $this->update_pcq_list($API,$data,$Address,$newAddress,$debug);

		if ($debug==1) {
			if (!empty($UPDATE))
				return $UPDATE;

		}


	}

	//////////////////// ELIMINAR HOTSPOT - PCQ ADDRESS LIST //////

	public function delete_hotspot_user_pcq($API,$data,$Address,$debug){


		$error = new Mkerror();

		$HOTSPOT = Hotspot::hotspot_get_id($API,$Address);
		if($HOTSPOT != 'notFound'){
			$HOTSPOT = Hotspot::hotspot_remove($API,$HOTSPOT[0]['.id']);

			if ($debug==1) {
				$msg = $error->process_error($HOTSPOT);
				if($msg)
					return $msg;
			}

		}

		$HOTSPOT = Hotspot::hotspot_useractive_get_id($API,$Address);

		if($HOTSPOT!='notFound'){
			$HOTSPOT = Hotspot::hotspot_remove_active($API,$HOTSPOT[0]['.id']);

			if ($debug==1) {
				$msg = $error->process_error($HOTSPOT);
				if($msg)
					return $msg;
			}

		}

		//eliminamos IP bindings bypassed si es requerido
		if ($data['typeauth']=='binding') {

			//buscamos la ipbinding
			$BINDING = Hotspot::get_id_ipbinding($API,$Address);

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


		//eliminamos pcq
		$DELETE = $this->delete_pcq_list($API,$data,$Address,'delete',$debug);

		if ($debug==1) {
			if (!empty($DELETE))
				return $DELETE;
		}



	}

	//////////////////// BLOQUEAR HOTSPOT - PCQ ADDRESS LIST //////
	public function block_hotspot_pcq($API,$data,$Address,$debug){

		$error = new Mkerror();

		if($data['status'] == 'ac'){ //significa que esta activo en ese caso bloqueamos

			//Cortamos el servicio habilitando en address list y redireccionando a web proxy
			$block = $this->set_basic_config($API,$error,$data,$Address,null,'block',$debug);

			if ($block)
				return $block;

			$block = $this->delete_pcq_list($API,$data,$Address,'block',$debug);

			if ($block)
				return $block;
			else
				return 'true';

		}


		if($data['status'] == 'de'){ //significa que esta bloqueado en ese caso desbloqueamos


			//Cortamos el servicio habilitando en address list y redireccionando a web proxy
			$block = $this->set_basic_config($API,$error,$data,$Address,null,'unblock',$debug);

			if ($block)
				return $block;


			$BLOCK = $this->add_pcq_list($API,$data,$Address,$debug);

			if (empty($BLOCK)) {

				return 'false';

			}else
			return $BLOCK;


		}


	}

	//////////////////// AÑADIR DHCP LEASES ////////////////////////

	//metodo para añadir colas simples atravez de DHCP leases
	public function add_dhcp_leases($API,$data,$Address,$debug){

		$error = new Mkerror();
		$SBC = $this->set_basic_config($API,$error,$data,$Address,null,'add',$debug);

		if ($debug==1) {
			if ($SBC!=false)
				return $SBC;
		}

		$DHCP = Dhcp::dhcp_get_id($API,$Address,$data['mac']);

		if($DHCP!='notFound'){

			$DHCP = Dhcp::dhcp_rate_set($API,$DHCP[0]['.id'],$data['mac'],$Address,$data['maxlimit'],$data['bl'],$data['bth'],$data['bt'],$data['name'].' - '.$data['comment']);

			if ($debug==1) {
				$msg = $error->process_error($DHCP);
				if($msg)
					return $msg;
			}

		}
		else{ // no existe el registro agregamos

			$DHCP = Dhcp::dhcp_add_rate($API,$Address,$data['mac'],$data['maxlimit'],$data['bl'],$data['bth'],$data['bt'],$data['name'].' - '.$data['comment']);

			if ($debug==1) {
				$msg = $error->process_error($DHCP);
				if($msg)
					return $msg;
			}

		}
	}


	//////////////////// ACTUALIZAR DHCP LEASES ////////////////////////

	public function update_dhcp_leases_user($API,$data,$Address,$newAddress,$debug){

		$error = new Mkerror();

		$SBC = $this->set_basic_config($API,$error,$data,$Address,$newAddress,'update',$debug);

		if ($debug==1) {
			if ($SBC!=false)
				return $SBC;
		}

		$DHCP = Dhcp::dhcp_get_id($API,$Address,$data['mac']);

		if($DHCP != 'notFound'){

			$DHCP = Dhcp::dhcp_rate_set($API,$DHCP[0]['.id'],$data['mac'],$newAddress,$data['maxlimit'],$data['bl'],$data['bth'],$data['bt'],$data['name'].' - '.$data['comment']);

			if ($debug==1) {
				$msg = $error->process_error($DHCP);
				if($msg)
					return $msg;
			}

		}else{

			$DHCP = Dhcp::dhcp_add_rate($API,$newAddress,$data['mac'],$data['maxlimit'],$data['bl'],$data['bth'],$data['bt'],$data['name'].' - '.$data['comment']);

			if ($debug==1) {
				$msg = $error->process_error($DHCP);
				if($msg)
					return $msg;
			}

		}

	}


	//////////////////// ELIMINAR DHCP LEASES ////////////////////////

	public function delete_dhcp_leases($API,$data,$Address,$debug){

		$error = new Mkerror();

		$delete = $this->set_basic_config($API,$error,$data,$Address,null,'delete',$debug);

		if ($debug==1) {
			if ($delete)
				return $delete;
		}

	}

	//////////////////// Bloqueo DHCP LEASES ////////////////////////

	public function block_dhcp_lease($API,$data,$Address,$debug){

		$error = new Mkerror();

		if($data['status'] == 'ac'){ //significa que esta activo en ese caso bloqueamos
			//habilitamos address list

			$block = $this->set_basic_config($API,$error,$data,$Address,null,'block',$debug);

			if ($debug==1) {
				if ($block)
					return $block;
			}


			return 'true';
		}

		if($data['status'] == 'de'){ //significa que esta bloqueado en ese caso desbloqueamos
			//deshabilitamos address list
			$block = $this->set_basic_config($API,$error,$data,$Address,null,'unblock',$debug);

			if ($debug==1) {
				if ($block)
					return $block;
			}

			return 'false';
		}
	}

	//////////////////// AÑADIR PPP SECRETS - PCQ ADDRESS LIST //////


	public function add_ppp_secrets_pcq($API,$data,$Address,$gateway,$debug){

		$error = new Mkerror();
		//agregamos el secret

		$PPP = Ppp::ppp_get_id($API,$data['user']);


		if($PPP!='notFound'){

			$PPP = Ppp::ppp_set($API,$PPP[0]['.id'],$data['user'],$data['pass'],$Address,$gateway,$data['mac'],'default',$data['name']);

			if ($debug==1) {
				$msg = $error->process_error($PPP);
				if($msg)
					return $msg;
			}


		}else{ // no existe el usuario creamos el secret

			$PPP = Ppp::ppp_add($API,$data['user'],$data['pass'],$Address,$gateway,$data['mac'],'default',$data['name']);

			if ($debug==1) {
				$msg = $error->process_error($PPP);
				if($msg)
					return $msg;
			}
		}

		//añadimos a queuetree
		$QUEUETREE = $this->add_pcq_list($API,$data,$Address,$debug);

		if ($debug==1) {
			if (!empty($QUEUETREE))
				return $QUEUETREE;
		}


	}

	//////////////////// ACTUALIZAR PPP SECRETS - PCQ ADDRESS LIST //////

	public function update_ppp_secrets_pcq($API,$data,$Address,$newAddress,$gateway,$debug){
		$error = new Mkerror();
		//actualizamos ppp secret
		//$PPP = Ppp::ppp_get_id($API,$data['old_user']);
		$PPP = Ppp::ppp_get_id($API,$data['name']);/**fix 01/06/2021**/
		if($PPP != 'notFound'){ //actualizamos
//			$PPP = Ppp::ppp_set($API,$PPP[0]['.id'],$data['user'],$data['pass'],$newAddress,$gateway,$data['mac'],'default',$data['name']);
			$PPP = Ppp::ppp_set($API,$PPP[0]['.id'],$data['name'],$data['pass'],$newAddress,$gateway,$data['mac'],'default',$data['name']); /**fix 01/06/2021**/
			if ($debug==1) {
				$msg = $error->process_error($PPP);
                if($msg)
					return $msg;
			}

		}
		else{ //creamos
			//$PPP = Ppp::ppp_add($API,$data['user'],$data['pass'],$Address,$gateway,$data['mac'],'default',$data['name']);
			$PPP = Ppp::ppp_add($API,$data['name'],$data['pass'],$Address,$gateway,$data['mac'],'default',$data['name']); /**fix 01/06/2021**/
			if ($debug==1) {
				$msg = $error->process_error($PPP);
				if($msg)
					return $msg;
			}
		}
        //actualizamos pcq
		$UPDATE = $this->update_pcq_list($API,$data,$Address,$newAddress,$debug);
		if ($debug==1) {
			if (!empty($UPDATE))
				return $UPDATE;
		}

		return 'ok';

	}

	//////////////////// ELIMINAR PPP SECRETS - PCQ ADDRESS LIST //////

	public function delete_ppp_secrets_pcq($API,$data,$Address,$debug){

		$error = new Mkerror();

		//eliminamos del active client
		$active = Ppp::ppp_active_get_id($API,$Address);

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


		$DELETE = $this->delete_pcq_list($API,$data,$Address,'delete',$debug);

		if ($debug==1) {
			if (!empty($DELETE))
				return $DELETE;
		}


	}

	//////////////////// BLOQUEAR PPP SECRETS - PCQ ADDRESS LIST //////

	public function block_ppp_secrets_pcq($API,$data,$Address,$debug){

		$error = new Mkerror();

		if($data['status'] == 'ac'){ //significa que esta activo en ese caso bloqueamos

			$drop = $data['drop'];
			$data['drop']=0;

				//Cortamos el servicio habilitando en address list y redireccionando a web proxy
			$block = $this->set_basic_config($API,$error,$data,$Address,null,'block',$debug);

			if ($debug==1) {
				if ($block!=false)
					return $block;
			}


				//intentamos elimar de active user si existe
			$PPP = Ppp::get_active_connetion($API,$Address);

			if ($PPP) {
					# existe el usuario eliminamos
				$PPP = Ppp::remove_active_connection($API,$PPP[0]['.id']);

				if ($debug==1) {
					$msg = $error->process_error($PPP);
					if($msg)
						return $msg;
				}
			}


			if ($drop==1) {
					//buscamos la id del usuario en secrets
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

			$BLOCK = $this->delete_pcq_list($API,$data,$Address,'block',$debug);

			if (empty($BLOCK)) {
				return 'true';
			}else{

				if ($debug==1)
					return $BLOCK;
			}

		}

		if($data['status'] == 'de'){ //significa que esta bloqueado en ese caso desbloqueamos

			//deshabilitamos address list
			$block = $this->set_basic_config($API,$error,$data,$Address,null,'unblock',$debug);

			if ($debug==1) {
				if ($block!=false)
					return $block;
			}


			if ($data['drop']==1) {

				$PPP = Ppp::ppp_get_id($API,$data['user']);
				if($PPP != 'notFound'){
					//desactivamos el secret del usuario
					$PPP = Ppp::enable_disable_secret($API,$PPP[0]['.id'],'false',$data['name']);

					if ($debug==1) {
						$msg = $error->process_error($PPP);
						if($msg)
							return $msg;
					}
				}

			}
			//negamos el drop para evitar volver a bloquear al cliente
			$data['drop']=0;

			$BLOCK = $this->add_pcq_list($API,$data,$Address,$debug);

			if (empty($BLOCK)) {
				return 'false';
			}else{
				if ($debug==1)
					return $BLOCK;
			}

		}


	}


	//////////////////// AÑADIR PPP SECRETS SIMPLE QUEUES ////////////////////////
	public function add_ppp_simple($API,$data,$Address,$gateway,$debug,$radius = false){

		//Add to simplequeues if not is radius. If is Radius, simple queue is created automatically for database radius.
        if(!$radius)
		    $this->add_simple_queues($API,$data,$Address,$debug);

        // si no es para radius, agrego el secret al mkt
		if(!$radius){
            $PPP = Ppp::ppp_get_id($API,$data['user']);
            //Add to ppp secret
            if($PPP!='notFound'){

                Ppp::ppp_set($API,$PPP[0]['.id'],$data['user'],$data['pass'],$Address,$gateway,$data['mac'],"*0",$data['name']);

            }else{ // no existe el usuario creamos

                Ppp::ppp_add($API,$data['user'],$data['pass'],$Address,$gateway,$data['mac'],"*0",$data['name']);
            }
        }

	}

	////////////////// EDITAR PPP SECRET SIMPLE QUEUES ////////////////////////////
	public function update_ppp_simple($API,$data,$Address,$newAddress,$gateway,$debug, $radius = false){

		$error = new Mkerror();

		if(!$radius) // si no es radius, actualizamos. Si es radius, lo hacemos por coa
		    $this->update_simple_queues($API,$data,$Address,$newAddress,$debug);

		/*** si el control es por radius, entonces no metemos el secret**/
        if(!$radius) {
            $PPP = Ppp::ppp_get_id($API,$data['old_user']);

            if($PPP != 'notFound'){ //actualizamos

                $PPP = Ppp::ppp_set($API,$PPP[0]['.id'],$data['user'],$data['pass'],$newAddress,$gateway,$data['mac'],"*0",$data['name']);

                if ($debug==1) {
                    $msg = $error->process_error($PPP);
                    if($msg)
                        return $msg;
                }

                /////////////Comentar si no se desea quitar del active user
                //quitamos del user active para que los cambios tengan efecto en mikrotik
                $active = Ppp::ppp_active_get_id($API,$Address);

                if ($active != 'notFound') {
                    //eliminamos
                    $remove = Ppp::active_ppp_remove($API,$active[0]['.id']);

                    if ($debug==1) {
                        $msg = $error->process_error($remove);
                        if($msg)
                            return $msg;
                    }

                }

            }
            else{ //creamos
                $PPP = Ppp::ppp_add($API,$data['user'],$data['pass'],$Address,$gateway,$data['mac'],"*0",$data['name']);
                if ($debug==1) {
                    $msg = $error->process_error($PPP);
                    if($msg)
                        return $msg;
                }

            }
        }


	}

	////////////////// ELIMINAR PPP SECRET SIMPLE QUEUES //////////////////////////
	public function delete_ppp_simple($API,$data,$Address,$debug,$radius = false){

		$error = new Mkerror();
        if(!$radius) // si no es radius, eliminamos. Si es radius, lo hacemos con la base de datos y coa directamente
		    $this->delete_simple_queues($API,$data,$Address,$debug);

		/**Por Jerson, si no es para radius, eliminamos el secret del mkt*/
		if(!$radius){
            //eliminamos del active client
            $active = Ppp::ppp_active_get_id($API,$Address);

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
        }

	}

	//////////////////// AÑADIR PPP SECRETS ////////////////////////

	public function add_ppp_secrets($API,$data,$Address,$gateway,$debug){

		$error = new Mkerror();
		$SBC = $this->set_basic_config($API,$error,$data,$Address,null,'add',$debug);

		if ($debug==1) {
			if ($SBC!=false)
				return $SBC;
		}


		//verificamos si existe el perfil
		$PROFILE = Ppp::ppp_find_profile($API,$data['namePlan']);

		if ($debug==1) {
			$msg = $error->process_error($PROFILE);
			if($msg)
				return $msg;
		}


		if(count($PROFILE)>0){ //el perfil existe asignamos

			$PPP = Ppp::ppp_get_id($API,$data['user']);

			if($PPP!='notFound'){

				$PPP = Ppp::ppp_set($API,$PPP[0]['.id'],$data['user'],$data['pass'],$Address,$gateway,$data['mac'],$data['namePlan'],$data['name']);

				if ($debug==1) {
					$msg = $error->process_error($PPP);
					if($msg)
						return $msg;
				}


			}else{ // no existe el usuario creamos

				$PPP = Ppp::ppp_add($API,$data['user'],$data['pass'],$Address,$gateway,$data['mac'],$data['namePlan'],$data['name']);

				if ($debug==1) {
					$msg = $error->process_error($PPP);
					if($msg)
						return $msg;
				}

			}

		}else{ // no existe el perfil creamos y asociamos



			$PROFILE = Ppp::ppp_add_profile($API,$data['namePlan'],$data['maxlimit'],$data['bl'],$data['bth'],$data['bt'],$data['priority_a'],$data['limit_at']);

			if ($debug==1) {
				$msg = $error->process_error($PROFILE);
				if($msg)
					return $msg;
			}



			$PPP = Ppp::ppp_get_id($API,$data['user']);

			if($PPP!='notFound'){

                $PPP = Ppp::ppp_set($API,$PPP[0]['.id'],$data['user'],$data['pass'],$Address,$gateway,$data['mac'],$data['namePlan'],$data['name']);

				if ($debug==1) {
					$msg = $error->process_error($PPP);
					if($msg)
						return $msg;
				}


			}else{ // no existe el usuario creamos

                $PPP = Ppp::ppp_add($API,$data['user'],$data['pass'],$Address,$gateway,$data['mac'],$data['namePlan'],$data['name']);

				if ($debug==1) {
					$msg = $error->process_error($PPP);
					if($msg)
						return $msg;
				}

			}

		}

	}


	////////////////// EDITAR PPP SECRET ////////////////////////////

	public function update_ppp_user($API,$data,$Address,$newAddress,$gateway,$debug){

		$error = new Mkerror();

		$SBC = $this->set_basic_config($API,$error,$data,$Address,$newAddress,'update',$debug);

		if ($debug==1) {
			if ($SBC!=false)
				return $SBC;
		}


		//verificamos si existe el perfil
		$PROFILE = Ppp::ppp_find_profile($API,$data['namePlan']);

		if ($debug==1) {
			$msg = $error->process_error($PROFILE);
			if($msg)
				return $msg;
		}


		if(count($PROFILE)>0){ //el perfil existe asignamos

			$PPP = Ppp::ppp_get_id($API,$data['old_user']);

			if($PPP != 'notFound'){ //actualizamos
                $PPP = Ppp::ppp_set($API,$PPP[0]['.id'],$data['user'],$data['pass'],$newAddress,$gateway,$data['mac'],$data['namePlan'],$data['name']);

				if ($debug==1) {
					$msg = $error->process_error($PPP);
					if($msg)
						return $msg;
				}

				//quitamos del user active para que los cambios tengan efecto en mikrotik
				$active = Ppp::ppp_active_get_id($API,$Address);

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


			}
			else{ //creamos

                $PPP = Ppp::ppp_add($API,$data['user'],$data['pass'],$Address,$gateway,$data['mac'],$data['namePlan'],$data['name']);

				if ($debug==1) {
					$msg = $error->process_error($PPP);
					if($msg)
						return $msg;
				}

			}

			return 'ok';

		}else{ //no existe el perfil creamos y asignamos


				$PROFILE = Ppp::ppp_add_profile($API,$data['namePlan'],$data['maxlimit'],$data['bl'],$data['bth'],$data['bt'],$data['priority_a'],$data['limit_at']);

				if ($debug==1) {
					$msg = $error->process_error($PROFILE);
					if($msg)
						return $msg;
				}



			$PPP = Ppp::ppp_get_id($API,$data['old_user']);

			if($PPP!='notFound'){ //actualizamos

                $PPP = Ppp::ppp_set($API,$PPP[0]['.id'],$data['user'],$data['pass'],$newAddress,$gateway,$data['mac'],$data['namePlan'],$data['name']);

				if ($debug==1) {
					$msg = $error->process_error($PPP);
					if($msg)
						return $msg;
				}

				//quitamos del user active para que los cambios tengan efecto en mikrotik
				$active = Ppp::ppp_active_get_id($API,$Address);

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


			}
			else{ //creamos

                $PPP = Ppp::ppp_add($API,$data['user'],$data['pass'],$Address,$gateway,$data['mac'],$data['namePlan'],$data['name']);

				if ($debug==1) {
					$msg = $error->process_error($PPP);
					if($msg)
						return $msg;
				}

			}

			return 'ok';

		}

	}


	////////////////// ELIMINAR PPP USER ////////////////////////////

	public function delete_ppp_user($API,$data,$Address,$debug){

		$error = new Mkerror();

		$delete = $this->set_basic_config($API,$error,$data,$Address,null,'delete',$debug);

		if ($debug==1) {
			if ($delete!=false)
				return $delete;
		}


		//eliminamos del active client
		$active = Ppp::ppp_active_get_id($API,$Address);

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

	}


	////////////////// BLOQUEAR PPP ///////////////////////////

	public function block_ppp($API,$data,$Address,$debug){

		$error = new Mkerror();

		if($data['status'] == 'ac'){ //significa que esta activo en ese caso bloqueamos

			$drop = $data['drop'];
			$data['drop']=0;

				//Cortamos el servicio habilitando en address list, redireccionando a web proxy o agregamos al filter
			$block = $this->set_basic_config($API,$error,$data,$Address,null,'block',$debug);

			if ($debug==1) {
				if ($block!=false)
					return $block;
			}

				//intentamos elimar de active user si existe
			$PPP = Ppp::get_active_connetion($API,$Address);

			if ($PPP) {
					# existe el usuario eliminamos
				$PPP = Ppp::remove_active_connection($API,$PPP[0]['.id']);

				if ($debug==1) {
					$msg = $error->process_error($PPP);
					if($msg)
						return $msg;
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

			return 'true';

		}

		if($data['status'] == 'de'){ //significa que esta bloqueado en ese caso desbloqueamos

			//deshabilitamos address list
			$block = $this->set_basic_config($API,$error,$data,$Address,null,'unblock',$debug);

			if ($debug==1) {
				if ($block)
					return $block;
			}

				//activamos el secret en caso de drop
			if ($data['drop']==1) {

					//buscamos la id del usuario en secrets
				$PPP = Ppp::ppp_get_id($API,$data['user']);
				if($PPP != 'notFound'){
						//desactivamos el secret del usuario
					$PPP = Ppp::enable_disable_secret($API,$PPP[0]['.id'],'false',$data['name']);

					if ($debug==1) {
						$msg = $error->process_error($PPP);
						if($msg)
							return $msg;
					}

				}
			}


			return 'false';
		}

	}

	//////////////////// AÑADIR PPP SIMPLE QUEUE WITH TREE ///////////////////
	function add_ppp_simple_queue_with_tree($API,$data,$Address,$gateway,$operation,$debug){

		$error = new Mkerror();

		$PPP = Ppp::ppp_get_id($API,$data['user']);
		//Add to ppp secret
		if($PPP!='notFound'){

			$PPP = Ppp::ppp_set($API,$PPP[0]['.id'],$data['user'],$data['pass'],$Address,$gateway,$data['mac'],"*0",$data['name']);

			if ($debug==1) {
				$msg = $error->process_error($PPP);
				if($msg)
					return $msg;
			}


		}else{ // no existe el usuario creamos

			$PPP = Ppp::ppp_add($API,$data['user'],$data['pass'],$Address,$gateway,$data['mac'],"*0",$data['name']);

			if ($debug==1) {
				$msg = $error->process_error($PPP);
				if($msg)
					return $msg;
			}
		}

		//Add to simplequeue with tree
		$this->add_simple_queue_with_tree($API,$data,$Address,$operation,$debug);


	}

    //////////////////// ACTUALIZAR PPP RADOUS AND SIMPLE QUEUE ////////////////
    function update_ppp_radius_simple_queue($API,$data,$Address,$newAddress,$gateway,$debug){
        $error = new Mkerror();
        $PPP = Ppp::ppp_get_id($API,$data['old_user']);

        if($PPP != 'notFound'){ //actualizamos

            $PPP = Ppp::ppp_set($API,$PPP[0]['.id'],$data['user'],$data['pass'],$newAddress,$gateway,$data['mac'],"*0",$data['name']);

            if ($debug==1) {
                $msg = $error->process_error($PPP);
                if($msg)
                    return $msg;
            }

            /////////////Comentar si no se desea quitar del active user
            //quitamos del user active para que los cambios tengan efecto en mikrotik
            $active = Ppp::ppp_active_get_id($API,$Address);

            if ($active != 'notFound') {
                //eliminamos
                $remove = Ppp::active_ppp_remove($API,$active[0]['.id']);

                if ($debug==1) {
                    $msg = $error->process_error($remove);
                    if($msg)
                        return $msg;
                }

            }
            ///////////////////////////////////////////

        }
        else{ //creamos


            $PPP = Ppp::ppp_add($API,$data['user'],$data['pass'],$Address,$gateway,$data['mac'],"*0",$data['name']);

            if ($debug==1) {
                $msg = $error->process_error($PPP);
                if($msg)
                    return $msg;
            }

        }


        // Update to simple queue with tree
        $this->update_radius_simple_queue($API,$data,$Address,$newAddress,$debug);
    }

    function update_radius_simple_queue($API,$data,$Address,$newAddress,$debug){
        $error = new Mkerror();

        $this->set_basic_config($API,$error,$data,$Address,$newAddress,'update',$debug);

        /////////////////////////////////////////UPDATE ACTUAL PLAN AND CLIENTS /////////////////////////////////////////////////

        $dataNamePlan = Helper::replace_word($data['namePlan']);
        $P_DATA = $this->data_simple_queue_with_tree_parent($data['plan_id'],$data['router_id'],$data['speed_down'],$data['speed_up'],$data['aggregation'],$data['limitat'],$data['burst_limit'],$data['burst_threshold'],$data['tree_priority']);

        $limitat = $P_DATA['limitat_up_cl'].'k/'.$P_DATA['limitat_down_cl'].'k';

        $UPSQUEUES = SimpleQueuesTree::simple_child_get_id($API,$data['old_name']);

        if ($UPSQUEUES != 'notFound') {

            $UPSQUEUES = SimpleQueuesTree::set_simple_child($API,$UPSQUEUES[0]['.id'],$data['name'],$data['maxlimit'],$data['ip'],$dataNamePlan,$data['bl'],$data['bth'],$data['bt'],$limitat,$data['priority'],$data['comment']);

            if ($debug==1) {
                $msg = $error->process_error($UPSQUEUES);
                if($msg)
                    return $msg;
            }
        }
    }

	//////////////////// ACTUALIZAR PPP SIMPLE QUEUE WITH TREE ////////////////
	function update_ppp_simple_queue_with_tree($API,$data,$Address,$newAddress,$gateway,$debug){


		$error = new Mkerror();

		$PPP = Ppp::ppp_get_id($API,$data['old_user']);

		if($PPP != 'notFound'){ //actualizamos

			$PPP = Ppp::ppp_set($API,$PPP[0]['.id'],$data['user'],$data['pass'],$newAddress,$gateway,$data['mac'],"*0",$data['name']);

			if ($debug==1) {
				$msg = $error->process_error($PPP);
				if($msg)
					return $msg;
			}

			/////////////Comentar si no se desea quitar del active user
			//quitamos del user active para que los cambios tengan efecto en mikrotik
			$active = Ppp::ppp_active_get_id($API,$Address);

			if ($active != 'notFound') {
				//eliminamos
				$remove = Ppp::active_ppp_remove($API,$active[0]['.id']);

				if ($debug==1) {
					$msg = $error->process_error($remove);
					if($msg)
						return $msg;
				}

			}
			///////////////////////////////////////////

		}
		else{ //creamos


			$PPP = Ppp::ppp_add($API,$data['user'],$data['pass'],$Address,$gateway,$data['mac'],"*0",$data['name']);

			if ($debug==1) {
				$msg = $error->process_error($PPP);
				if($msg)
					return $msg;
			}

		}


		// Update to simple queue with tree
		$this->update_simple_queue_with_tree($API,$data,$Address,$newAddress,$debug);


	}

	//////////////////// ELIMINAR PPP SIMPLE QUEUE WITH TREE ///////////////////////////
	function delete_ppp_simple_queue_with_tree($API,$data,$Address,$operation,$debug){

		$error = new Mkerror();

		$this->delete_simple_queues($API,$data,$Address,$debug);

		//eliminamos del active client
		$active = Ppp::ppp_active_get_id($API,$Address);

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

		//Delete simple queue with tree
		$this->delete_simple_queue_with_tree($API,$data,$Address,$operation,$debug);


	}




	//////////////////// AÑADIR QUEUE WITH TREE /////////////////////////

	function add_simple_queue_with_tree($API,$data,$Address,$operation,$debug){

			$error = new Mkerror();

			$this->set_basic_config($API,$error,$data,$Address,null,$operation,$debug);

            $dataNamePlan = Helper::replace_word($data['namePlan']);

            if($data['tree_priority'] != 0) {
                $dataNamePlan = $dataNamePlan.'_virtual_'.$data['tree_priority'];
                $data['comment'] = $data['comment'].'_virtual_'.$data['tree_priority'];
            }

			# add or update clients to parents
			$P_DATA = $this->data_simple_queue_with_tree_parent($data['plan_id'],$data['router_id'],$data['download'],$data['upload'],$data['aggregation'],$data['limitat'],$data['burst_limit'],$data['burst_threshold'], $data['tree_priority']);

			//buscamos regla parent segun el plan
			$parent = SimpleQueuesTree::simple_parent_get_id($API,$dataNamePlan);

                /**fix cuando corta el servicio de un cliente, o lo agrega, no tiene en cuenta si esta en periodo de duplicidad 27/05/2021 -->*/
                $planStudy = Plan::with('smart_bandwidth')->find($data['plan_id']);
                $now = Carbon::now();
                $start = Carbon::create($planStudy->smart_bandwidth->start_time); // comienza
                $end = Carbon::create($planStudy->smart_bandwidth->end_time);
                if($start > $end) // si termina al otro dia, le agregamos un dia
                    $end = $end->addDay();

                $multiplicador = $planStudy->smart_bandwidth->bandwidth;
                if ($now->between($start, $end)) {

                    $maxlimit_down_parent = $P_DATA['maxlimit_down_parent']+(($multiplicador*$P_DATA['maxlimit_down_parent'])/100);
                    $maxlimit_up_parent = $P_DATA['maxlimit_up_parent']+(($multiplicador*$P_DATA['maxlimit_up_parent'])/100);

                    $limitat_down_parent = $P_DATA['limit_at_down_parent']+(($multiplicador*$P_DATA['limit_at_down_parent'])/100);
                    $limitat_up_parent = $P_DATA['limit_at_up_parent']+(($multiplicador*$P_DATA['limit_at_up_parent'])/100);


                    $P_DATA['maxlimit'] = $maxlimit_up_parent.'k/'.$maxlimit_down_parent.'k';
                    $P_DATA['limitat'] = $limitat_up_parent.'k/'.$limitat_down_parent.'k';
                }
                /**fin fix*/

			if ($parent=='notFound') {
				# Creamos parent
				SimpleQueuesTree::add_simple_parent($API,$dataNamePlan,$P_DATA['ips'],$P_DATA['maxlimit'],$P_DATA['bl'],$P_DATA['bth'],$data['bt'],$P_DATA['limitat'],$data['priority'],$dataNamePlan);
			}else{
				# Actualizamos parent
				SimpleQueuesTree::set_simple_parent($API,$parent[0]['.id'],$dataNamePlan,$P_DATA['maxlimit'],$P_DATA['ips'],$P_DATA['bl'],$P_DATA['bth'],$data['bt'],$P_DATA['limitat'],$data['priority'],$dataNamePlan);
			}

			$clients = ClientService::with('client', 'plan')->where('plan_id',$data['plan_id'])->where('router_id', $data['router_id'])->where('status','ac')->get();

			$limitat = $P_DATA['limitat_up_cl'].'k/'.$P_DATA['limitat_down_cl'].'k';

                /**fix cuando corta el servicio de un cliente, o lo agrega, no tiene en cuenta si esta en periodo de duplicidad 27/05/2021 -->*/
            if ($now->between($start, $end)) {
                $P_DATA['limitat_up_cl'] = $P_DATA['limitat_up_cl']+(($multiplicador*$P_DATA['limitat_up_cl'])/100);
                $P_DATA['limitat_down_cl'] = $P_DATA['limitat_down_cl']+(($multiplicador*$P_DATA['limitat_down_cl'])/100);

                $limitat = $P_DATA['limitat_up_cl'].'k/'.$P_DATA['limitat_down_cl'].'k';

                $down_plan_mult = $planStudy['download']+(($multiplicador*$planStudy['download'])/100);
                $up_plan_mult = $planStudy['upload']+(($multiplicador*$planStudy['upload'])/100);
                $data['maxlimit'] = $up_plan_mult.'k/'.$down_plan_mult.'k';
            }
        /**fin fix*/


//
//			foreach ($clients as $client) {
//
                $dataNamePlan = Helper::replace_word($data['namePlan']);
                $comment = 'SmartISP - '.$data['namePlan'];
                if($data['tree_priority'] != 0) {
                    $dataNamePlan = $dataNamePlan.'_virtual_'.$data['tree_priority'];
                    $comment = 'SmartISP - '.$data['namePlan'].'_virtual_'.$data['tree_priority'];
                }
//					//SIMPLE Q
//                if($client->client) {
                    $SQUEUES = SimpleQueuesTree::simple_parent_get_id($API,$data['name']);
                    if($SQUEUES != 'notFound'){

                        $QUEUES = SimpleQueuesTree::set_simple_child($API,$SQUEUES[0]['.id'],$data['name'],$data['maxlimit'],$data['ip'],$dataNamePlan,$data['bl'],$data['bth'],$data['bt'],$limitat,$data['priority'],$comment);
                        if ($debug==1) {
                            $msg = $error->process_error($SQUEUES);
                            if($msg)
                                return $msg;
                        }

                    }
                    else{

                        $SQUEUES = SimpleQueuesTree::add_simple_child($API,$data['name'],$data['ip'],$dataNamePlan,$data['maxlimit'],$data['bl'],$data['bth'],$data['bt'],$limitat,$data['priority'],$comment);

                        if ($debug==1) {
                            $msg = $error->process_error($SQUEUES);
                            if($msg)
                                return $msg;
                        }

                    }
//                }
//
//
//			}//end foreach

        /**Fix 16/06 no calcula el limit at del resto de los clientes de un plan teniendo en cuenta la agregacion**/
        $plan = Plan::find($data['plan_id']);
        $type_control = ControlRouter::where('router_id',$data['router_id'])->first();
        if($clients->count() <= $plan->aggregation && ($type_control == 'st' || $type_control == 'pt')){
            // si son mas clientes de los agregation, no hace falta que hagamos nada con los anteriores ya que se hizo antes
            foreach ($clients as $client) {
                if($data['name'] != $client->client->name.'_'.$client->id){
                    $SQUEUES = SimpleQueuesTree::simple_parent_get_id($API,$client->client->name.'_'.$client->id);
                    if($SQUEUES != 'notFound'){
                        SimpleQueuesTree::set_simple_child(
                            $API,$SQUEUES[0]['.id'],$client->client->name.'_'.$client->id,$data['maxlimit'],$client->ip,$dataNamePlan,$data['bl'],$data['bth'],$data['bt'],$limitat,$data['priority'],$comment);
                    }
                }
            }
        }


	}




	//////////////////// ACTUALIZAR QUEUE WITH TREE /////////////////////////

	function update_simple_queue_with_tree($API,$data,$Address,$newAddress,$debug){

		$error = new Mkerror();

		$this->set_basic_config($API,$error,$data,$Address,$newAddress,'update',$debug);

		/////////////////////////////////////////UPDATE ACTUAL PLAN AND CLIENTS /////////////////////////////////////////////////

        $dataNamePlan = Helper::replace_word($data['namePlan']);

        if($data['tree_priority'] != 0) {
            $dataNamePlan = $dataNamePlan.'_virtual_'.$data['tree_priority'];
            $data['comment'] = $data['comment'].'_virtual_'.$data['tree_priority'];
        }

        # add or update clients to parents
		$P_DATA = $this->data_simple_queue_with_tree_parent($data['plan_id'],$data['router_id'],$data['speed_down'],$data['speed_up'],$data['aggregation'],$data['limitat'],$data['burst_limit'],$data['burst_threshold'],$data['tree_priority']);

		//buscamos regla parent segun el plan actual
		$parent = SimpleQueuesTree::simple_parent_get_id($API,$dataNamePlan);

		if ($parent=='notFound') {
			# Creamos parent
			 SimpleQueuesTree::add_simple_parent($API,$dataNamePlan,$P_DATA['ips'],$P_DATA['maxlimit'],$P_DATA['bl'],$P_DATA['bth'],$data['bt'],$P_DATA['limitat'],$data['priority'],$dataNamePlan);
		}else{
			# Actualizamos parent
			 SimpleQueuesTree::set_simple_parent($API,$parent[0]['.id'],$dataNamePlan,$P_DATA['maxlimit'],$P_DATA['ips'],$P_DATA['bl'],$P_DATA['bth'],$data['bt'],$P_DATA['limitat'],$data['priority'],$dataNamePlan);
		}

		//Actualizamos las velocidades de todos los clientes asociados al plan actual
//		$clients = ClientService::with('client', 'plan')->where('plan_id',$data['plan_id'])->where('router_id',$data['router_id'])->where('status','ac')->get();

		$limitat = $P_DATA['limitat_up_cl'].'k/'.$P_DATA['limitat_down_cl'].'k';

        $UPSQUEUES = SimpleQueuesTree::simple_child_get_id($API,$data['old_name']);

        if ($UPSQUEUES != 'notFound') {

            $UPSQUEUES = SimpleQueuesTree::set_simple_child($API,$UPSQUEUES[0]['.id'],$data['name'],$data['maxlimit'],$data['ip'],$dataNamePlan,$data['bl'],$data['bth'],$data['bt'],$limitat,$data['priority'],$data['comment']);

            if ($debug==1) {
                $msg = $error->process_error($UPSQUEUES);
                if($msg)
                    return $msg;
            }
        }

        $dataNamePlan = Helper::replace_word($data['namePlan']);
        $comment = 'SmartISP - '.$data['namePlan'];
        if($data['tree_priority'] != 0) {
            $dataNamePlan = $dataNamePlan.'_virtual_'.$data['tree_priority'];
            $comment = 'SmartISP - '.$data['namePlan'].'_virtual_'.$data['tree_priority'];
        }

        $SQUEUES = SimpleQueuesTree::simple_parent_get_id($API,$data['name']);


        if($SQUEUES != 'notFound'){

            $QUEUES = SimpleQueuesTree::set_simple_child($API,$SQUEUES[0]['.id'],$data['name'],$data['maxlimit'],$data['ip'],$dataNamePlan,$data['bl'],$data['bth'],$data['bt'],$limitat,$data['priority'],$comment);

            if ($debug==1) {
                $msg = $error->process_error($SQUEUES);
                if($msg)
                    return $msg;
            }

        }
        else{

            $SQUEUES = SimpleQueuesTree::add_simple_child($API,$data['name'],$data['ip'],$dataNamePlan,$data['maxlimit'],$data['bl'],$data['bth'],$data['bt'],$limitat,$data['priority'],$comment);

            if ($debug==1) {
                $msg = $error->process_error($SQUEUES);
                if($msg)
                    return $msg;
            }

        }

//		foreach ($clients as $client) {
//            if($client->client) {
//                $SQUEUES = SimpleQueuesTree::simple_child_get_id($API,$data['name']);
//
//
//                $dataNamePlan = Helper::replace_word($data['namePlan']);
//                $comment = 'SmartISP - '.$data['namePlan'];
//                if($data['tree_priority'] != 0) {
//                    $dataNamePlan = $dataNamePlan.'_virtual_'.$data['tree_priority'];
//                    $comment = 'SmartISP - '.$data['namePlan'].'_virtual_'.$data['tree_priority'];
//                }
//
//
//                if($SQUEUES != 'notFound'){
//
//                    $SQUEUES = SimpleQueuesTree::set_simple_child($API,$SQUEUES[0]['.id'],$data['name'],$data['maxlimit'],$data['ip'],$dataNamePlan,$data['bl'],$data['bth'],$data['bt'],$limitat,$data['priority'],$comment);
//
//                    if ($debug==1) {
//                        $msg = $error->process_error($SQUEUES);
//                        if($msg)
//                            return $msg;
//                    }
//
//                }
//                else{
//
//                    $SQUEUES = SimpleQueuesTree::add_simple_child($API,$data['name'],$data['ip'],$dataNamePlan,$data['maxlimit'],$data['bl'],$data['bth'],$data['bt'],$limitat,$data['priority'],$comment);
//
//                    if ($debug==1) {
//                        $msg = $error->process_error($SQUEUES);
//                        if($msg)
//                            return $msg;
//                    }
//
//                }
//            }


//		}//end foreach



		/////////////////////////////////////////UPDATE OLD PLAN AND CLIENTS /////////////////////////////////////////////////
		//verificamos si ha cambiado de plan
		if ($data['changePlan']) {
			# Actualizamos o eliminamos su plan anterior
			$plan = new GetPlan();
			$plan = $plan->get($data['oldplan']);

            $dataNamePlan = Helper::replace_word($plan['name']);

            if($data['tree_priority'] != 0) {
                $dataNamePlan = $dataNamePlan.'_virtual_'.$data['tree_priority'];
                $data['comment'] = $data['comment'].'_virtual_'.$data['tree_priority'];
            }

			#actualizamos
			$PO_DATA = $this->data_simple_queue_with_tree_parent($data['oldplan'],$data['router_id'],$plan['download'],$plan['upload'],$plan['aggregation'],$plan['limitat'],$plan['burst_limit'],$plan['burst_threshold'],$plan['tree_priority']);

			//buscamos regla parent del plan anterior
			$parent = SimpleQueuesTree::simple_parent_get_id($API,$dataNamePlan);

			//verificamos si el plan anterior tiene clientes
			if ($PO_DATA['ncl']>0) {

				if ($parent!='notFound') {
					SimpleQueuesTree::set_simple_parent($API,$parent[0]['.id'],$dataNamePlan,$PO_DATA['maxlimit'],$PO_DATA['ips'],$PO_DATA['bl'],$PO_DATA['bth'],$plan['burst_time'],$PO_DATA['limitat'],$plan['priority'],$dataNamePlan);
				}
				else{
					SimpleQueuesTree::add_simple_parent($API,$dataNamePlan,$PO_DATA['ips'],$PO_DATA['maxlimit'],$PO_DATA['bl'],$PO_DATA['bth'],$plan['burst_time'],$PO_DATA['limitat'],$plan['priority'],$dataNamePlan);
				}

				//obtenemos datos del plan anterior
				$burst = Burst::get_all_burst($plan['upload'],$plan['download'],$plan['burst_limit'],$plan['burst_threshold'],$plan['limitat']);

				$oldata = array(
					'maxlimit' => $plan['maxlimit'],
					'namePlan' => $dataNamePlan,
					'bl' => $burst['blu'].'/'.$burst['bld'],
					'bth' => $burst['btu'].'/'.$burst['btd'],
					'bt' => $plan['burst_time'].'/'.$plan['burst_time'],
					'priority' => $plan['priority'].'/'.$plan['priority'],
					'comment' => 'SmartISP - '.$dataNamePlan
				);

				//Actualizamos todos los clientes del plan anterior
//				$clients = ClientService::with('client', 'plan')->where('plan_id',$data['oldplan'])->where('router_id',$data['router_id'])->where('status','ac')->get();
				//Obtenemos velocidad limit at del plan anterior
				$limitat = $PO_DATA['limitat_up_cl'].'k/'.$PO_DATA['limitat_down_cl'].'k';

//				foreach ($clients as $client) {
//                    if($client->client) {

//                        $comment = 'SmartISP - '.$newPlan['name'];
//                        $dataNamePlan = Helper::replace_word($newPlan['name']);
//                        if($data['tree_priority'] != 0) {
//                            $dataNamePlan = $dataNamePlan.'_virtual_'.$data['tree_priority'];
//                            $comment = 'SmartISP - '.$newPlan['name'].'_virtual_'.$data['tree_priority'];
//                        }

                        $SQUEUES = SimpleQueuesTree::simple_child_get_id($API,$data['name']);

                        if($SQUEUES != 'notFound'){

                            $SQUEUES = SimpleQueuesTree::set_simple_child($API,$SQUEUES[0]['.id'],$data['name'],$oldata['maxlimit'],$data['ip'],$oldata['namePlan'],$oldata['bl'],$oldata['bth'],$oldata['bt'],$limitat,$oldata['priority'],$comment);

                            if ($debug==1) {
                                $msg = $error->process_error($SQUEUES);
                                if($msg)
                                    return $msg;
                            }

                        }
                        else{

                            $SQUEUES = SimpleQueuesTree::add_simple_child($API,$data['name'],$data['ip'],$oldata['namePlan'],$oldata['maxlimit'],$oldata['bl'],$oldata['bth'],$oldata['bt'],$limitat,$oldata['priority'],$comment);

                            if ($debug==1) {
                                $msg = $error->process_error($SQUEUES);
                                if($msg)
                                    return $msg;
                            }

                        }
//                    }
//
//
//				}//end foreach

			}else{

				if ($parent!='notFound') {

					$SQUEUES = SimpleQueuesTree::simple_parent_remove($API,$parent[0]['.id']);

					if ($debug==1) {
							$msg = $error->process_error($SQUEUES);
							if($msg)
								return $msg;
					}

				}
			}


		}//end if change plan




	}


	//////////////////// ELIMINAR QUEUE WITH TREE /////////////////////////
	public function delete_simple_queue_with_tree($API,$data,$Address,$operation,$debug){

		$error = new Mkerror();

		$SBC = $this->set_basic_config($API,$error,$data,$Address,null,$operation,$debug);

		if ($debug==1) {
			if ($SBC!=false)
				return $SBC;
		}

		//recalculamos parent
		# add or update clients to parents
		$P_DATA = $this->data_simple_queue_with_tree_parent($data['plan_id'],$data['router_id'],$data['download'],$data['upload'],$data['aggregation'],$data['limitat'],$data['burst_limit'],$data['burst_threshold'],$data['tree_priority']);


        $dataNamePlan = Helper::replace_word($data['namePlan']);

        if($data['tree_priority'] != 0) {
            $dataNamePlan = $dataNamePlan.'_virtual_'.$data['tree_priority'];
            $data['comment'] = $data['comment'].'_virtual_'.$data['tree_priority'];
        }


		//buscamos regla parent segun el plan
		$parent = SimpleQueuesTree::simple_parent_get_id($API,$dataNamePlan);

        /**fix cuando corta el servicio de un cliente, o lo agrega, no tiene en cuenta si esta en periodo de duplicidad 27/05/2021 -->*/
            $planStudy = Plan::with('smart_bandwidth')->find($data['plan_id']);
            $now = Carbon::now();
            $start = Carbon::create($planStudy->smart_bandwidth->start_time); // comienza
            $start_finish = (clone $start)->addMinutes(15); // hasta pasados los 15 minutos desde que comenzo
            $end = Carbon::create($planStudy->smart_bandwidth->end_time);
            if($start > $end) // si termina al otro dia, le agregamos un dia
                $end = $end->addDay();

            $multiplicador = $planStudy->smart_bandwidth->bandwidth;
            if ($now->between($start, $end)) {

                $maxlimit_down_parent = $P_DATA['maxlimit_down_parent']+(($multiplicador*$P_DATA['maxlimit_down_parent'])/100);
                $maxlimit_up_parent = $P_DATA['maxlimit_up_parent']+(($multiplicador*$P_DATA['maxlimit_up_parent'])/100);

                $limitat_down_parent = $P_DATA['limit_at_down_parent']+(($multiplicador*$P_DATA['limit_at_down_parent'])/100);
                $limitat_up_parent = $P_DATA['limit_at_up_parent']+(($multiplicador*$P_DATA['limit_at_up_parent'])/100);


                $P_DATA['maxlimit'] = $maxlimit_up_parent.'k/'.$maxlimit_down_parent.'k';
                $P_DATA['limitat'] = $limitat_up_parent.'k/'.$limitat_down_parent.'k';
            }
        /**fin fix*/

        if ($P_DATA['ncl']>0) {

			if ($parent=='notFound') {
				# Creamos parent
				 SimpleQueuesTree::add_simple_parent($API,$dataNamePlan,$P_DATA['ips'],$P_DATA['maxlimit'],$P_DATA['bl'],$P_DATA['bth'],$data['bt'],$P_DATA['limitat'],$data['priority'],$dataNamePlan);
			}else{
				# Actualizamos parent
				 SimpleQueuesTree::set_simple_parent($API,$parent[0]['.id'],$dataNamePlan,$P_DATA['maxlimit'],$P_DATA['ips'],$P_DATA['bl'],$P_DATA['bth'],$data['bt'],$P_DATA['limitat'],$data['priority'],$dataNamePlan);
			}

		}else{
                //No hay mas clientes en el plan eliminamos el parent
	        if ($parent !='notFound') {
		        SimpleQueuesTree::simple_parent_remove($API,$parent[0]['.id']);
	        }

        }

        $clients = ClientService::with('client', 'plan')->where('plan_id',$data['plan_id'])->where('router_id',$data['router_id'])->where('status','ac')->get();


		//eliminamos de colas simple
		$SQUEUES = SimpleQueuesTree::simple_parent_get_id($API,$data['name']);
		//verificamos si se encontro al cliente, si no encontro no eliminamos del router solo de la BD
		if($SQUEUES != 'notFound'){
			$SQUEUES = SimpleQueues::simple_remove($API,$SQUEUES[0]['.id']);

			if ($debug==1) {
				$msg = $error->process_error($SQUEUES);
				if($msg)
					return $msg;
			}
		}


		$limitat = $P_DATA['limitat_up_cl'].'k/'.$P_DATA['limitat_down_cl'].'k';
//		//Actualizamos las velocidades de todos los clientes asociados al plan
//		$cls = ClientService::with('client', 'plan')->where('plan_id',$data['plan_id'])->where('router_id',$data['router_id'])->where('status','ac')->get();
//
//
//		foreach ($cls as $client) {
//
//		    if($client->client) {
//
                $dataNamePlan = Helper::replace_word($data['namePlan']);
                $comment = 'SmartISP - '.$data['namePlan'];
                if($data['tree_priority'] != 0) {
                    $dataNamePlan = $dataNamePlan.'_virtual_'.$data['tree_priority'];
                    $comment = 'SmartISP - '.$data['namePlan'].'_virtual_'.$data['tree_priority'];
                }
//                //SIMPLE Q
                $IDSQUEUES = SimpleQueuesTree::simple_child_get_id($API,$dataNamePlan);
//
                if($IDSQUEUES != 'notFound'){
//
                    $QUEUES = SimpleQueuesTree::set_simple_child($API,$IDSQUEUES[0]['.id'],$data['name'],$data['maxlimit'],$data['ip'],$dataNamePlan,$data['bl'],$data['bth'],$data['bt'],$limitat,$data['priority'],$comment);

                    if ($debug==1) {
                        $msg = $error->process_error($SQUEUES);
                        if($msg)
                            return $msg;
                    }

                }
                else{
//
                    $SQUEUES = SimpleQueuesTree::add_simple_child($API,$data['name'],$data['ip'],$dataNamePlan,$data['maxlimit'],$data['bl'],$data['bth'],$data['bt'],$limitat,$data['priority'],$comment);

                    if ($debug==1) {
                        $msg = $error->process_error($SQUEUES);
                        if($msg)
                            return $msg;
                    }
                }
//            }
//
//
//		}//end foreach


        /**Fix 16/06 no calcula el limit at del resto de los clientes de un plan teniendo en cuenta la agregacion**/

        $plan = Plan::find($data['plan_id']);
        if($clients->count() <= $plan->aggregation){
            foreach ($clients as $client) {
                if($data['name'] != $client->client->name.'_'.$client->id){
                    $SQUEUES = SimpleQueuesTree::simple_parent_get_id($API,$client->client->name.'_'.$client->id);
                    if($SQUEUES != 'notFound'){
                        SimpleQueuesTree::set_simple_child(
                            $API,$SQUEUES[0]['.id'],$client->client->name.'_'.$client->id,$data['maxlimit'],$client->ip,$dataNamePlan,$data['bl'],$data['bth'],$data['bt'],$limitat,$data['priority'],$comment);
                    }
                }
            }
        }

	}


	//////////////////// BLOQUEAR QUEUE WITH TREE /////////////////////////
	function block_simple_queue_with_tree($API,$data,$Address,$debug){

		$error = new Mkerror();

		if($data['status'] == 'ac'){ //significa que esta activo en ese caso bloqueamos

			//Eliminamos del arbol de colas
			$this->delete_simple_queue_with_tree($API,$data,$Address,'block',$debug);

			return 'true';

		}

		if($data['status'] == 'de'){ //significa que esta bloqueado en ese caso desbloqueamos

			//Añadimos al arbol de colas
			$this->add_simple_queue_with_tree($API,$data,$Address,'unblock',$debug);

			return 'false';
		}

	}


	//////////////////// AÑADIR PCQ-ADDRESS-LIST ////////////////////////

	function add_pcq_list($API,$data,$Address,$debug){

		$error = new Mkerror();

		if($data['no_rules'] == 0) {
			//creamos reglas parent si no existen
			$PARENTS = $this->create_queuetree_parent($API,$debug);

			if ($debug==1) {
				$msg = $error->process_error($PARENTS);
				if($msg)
					return $msg;
			}


			$SBC = $this->set_basic_config($API,$error,$data,$Address,null,'add',$debug);

			if ($debug==1) {
				if ($SBC!=false)
					return $SBC;
			}
		}




		//// Creamos el plan en queuetypes y en queuetree ////
		///////// buscamos si existe el queuetype DOWN //////

		$QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_DOWN'));

		if($QUEUETYPE == 'notFound'){
			//agregamos la regla a Queuetype
			$QUEUETYPE = QueueType::add_queuetype($API,Helper::replace_word($data['namePlan'].'_DOWN'),$data['rate_down'],$data['burst_rate_down'],$data['burst_threshold_down'],$data['burst_time'],'DOWN');

			if ($debug==1) {
				$msg = $error->process_error($QUEUETYPE);
				if($msg)
					return $msg;
			}


		}else{
			//Seteamos la regla QueueType DOWN
			$QUEUETYPE = Queuetype::set_queuetype($API,$QUEUETYPE[0]['.id'],null,$data['rate_down'],$data['burst_rate_down'],$data['burst_threshold_down'],$data['burst_time'],'DOWN');

			if ($debug==1) {
				$msg = $error->process_error($QUEUETYPE);
				if($msg)
					return $msg;
			}

		}

		/////// buscamos si existe el queuetype UP ///////
		$QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_UP'));

		if($QUEUETYPE == 'notFound'){
			//agregamos la regla a Queuetype
			$QUEUETYPE = QueueType::add_queuetype($API,Helper::replace_word($data['namePlan'].'_UP'),$data['rate_up'],$data['burst_rate_up'],$data['burst_threshold_up'],$data['burst_time'],'UP');

			if ($debug==1) {
				$msg = $error->process_error($QUEUETYPE);
				if($msg)
					return $msg;
			}

		}else{
			//Seteamos la regla QueueType UP
			$QUEUETYPE = Queuetype::set_queuetype($API,$QUEUETYPE[0]['.id'],null,$data['rate_up'],$data['burst_rate_up'],$data['burst_threshold_up'],$data['burst_time'],'UP');

			if ($debug==1) {
				$msg = $error->process_error($QUEUETYPE);
				if($msg)
					return $msg;
			}
		}

		//// creamos reglas en mangle para el plan ////

		//////// Buscamos si existe la regla mangle in postrouting //////
		//recomvertimos nombres
		$dt = Helper::replace_word_mangle($data['namePlan'],"in","out");

		if($data['no_rules'] == 0) {
			$MANGLE = Firewall::find_mangle($API,Helper::replace_word($data["namePlan"].'_in'));

			if ($MANGLE=='notFound') {
				# no esta la regla agregamos nueva regla
				$MANGLE = Firewall::add_mangle_postrouting($API,$dt['plan_in'],$dt['srcaddress'],Helper::replace_word($data['namePlan'].'_in'));

				if ($debug==1) {
					$msg = $error->process_error($MANGLE);
					if($msg)
						return $msg;
				}

			}else{
				//seteamos regla mangle
				$MANGLE = Firewall::set_mangle_postrouting($API,$MANGLE[0]['.id'],$dt['plan_in'],$dt['srcaddress'],Helper::replace_word($data['namePlan'].'_in'));

				if ($debug==1) {
					$msg = $error->process_error($MANGLE);
					if($msg)
						return $msg;
				}

			}

			/////// Buscamos si existe la regla mangle out forward //////
			$MANGLE = Firewall::find_mangle($API,Helper::replace_word($data["namePlan"].'_out'));

			if ($MANGLE=='notFound') {
				# no esta la regla agregamos nueva regla
				$MANGLE = Firewall::add_mangle_forward($API,$dt['plan_out'],$dt['srcaddress'],Helper::replace_word($data['namePlan'].'_out'));

				if ($debug==1) {
					$msg = $error->process_error($MANGLE);
					if($msg)
						return $msg;
				}

			}else{
				//seteamos regla mangle

				$MANGLE = Firewall::set_mangle_forward($API,$MANGLE[0]['.id'],$dt['plan_out'],$dt['srcaddress'],Helper::replace_word($data['namePlan'].'_out'));

				if ($debug==1) {
					$msg = $error->process_error($MANGLE);
					if($msg)
						return $msg;
				}

			}

			///// creamos reglas en address list para el cliente /////

			//buscamos si existe el cliente en address list

			$ADDRESSLIST = Firewall::get_id_address_list_pcq($API,$Address,Helper::replace_word($data['name']));

			if ($ADDRESSLIST=='notFound') {
				# No esta el cliente agregamos nueva regla
				$ADDRESSLIST = Firewall::add_address_list($API,$Address,$dt['srcaddress'],'false',Helper::replace_word($data['name']));
//				$ADDRESSLIST = Firewall::add_address_list($API,$Address,$data['address_list_name'],'false',Helper::replace_word($data['name']));

				if ($debug==1) {
					$msg = $error->process_error($ADDRESSLIST);
					if($msg)
						return $msg;
				}

			}else{
				//seteamos Addresslist
				$ADDRESSLIST = firewall::set_address_list($API,$ADDRESSLIST[0]['.id'],$Address,$dt['srcaddress'],'false',Helper::replace_word($data['name']));
//				$ADDRESSLIST = firewall::set_address_list($API,$ADDRESSLIST[0]['.id'],$Address,$data['address_list_name'],'false',Helper::replace_word($data['name']));

				if ($debug==1) {
					$msg = $error->process_error($ADDRESSLIST);
					if($msg)
						return $msg;
				}

			}

			///// creamos reglas en Queue Tree para el grupo segun el plan /////

			//buscamos si existe el Queue Tree en el grupo Download
			$QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-DOWN'));

			if ($QUEUETREE=='notFound') {

				// creamos regla DOWN
				$QUEUETREE = QueueTree::create_child($API,Helper::replace_word($data['namePlan'].'-DOWN'),'SmartISP_DOWN',$dt['plan_in'],Helper::replace_word($data['namePlan'].'_DOWN'),$data['priority_a'],$data['limit_at_down'],$data['rate_down'],$data['burst_rate_down'],$data['burst_threshold_down'],$data['burst_time']);

				if ($debug==1) {
					$msg = $error->process_error($QUEUETREE);
					if($msg)
						return $msg;
				}

			}else{

				// encontro el plan sumamos la velocidad DOWN
				$ncl = $data['num_cl'] + 1;

				$QUEUETREE = QueueTree::set_child($API,
					$QUEUETREE[0]['.id'],
					null,
					null,
					null,
					RecalculateSpeed::speed($data['limit_at_down'],$ncl,true),
					RecalculateSpeed::speed($data['rate_down'],$ncl,true),
					RecalculateSpeed::speed($data['burst_rate_down'],$ncl,true),
					RecalculateSpeed::speed($data['burst_threshold_down'],$ncl,true),
					$data['burst_time'],
					$data['priority_a']
				);
				if ($debug==1) {
					$msg = $error->process_error($QUEUETREE);
					if($msg)
						return $msg;
				}

			}

			//buscamos si existe el Queue Tree en el grupo Upload
			$QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-UP'));

			if ($QUEUETREE=='notFound') {

				// creamos regla DOWN
				$QUEUETREE = QueueTree::create_child($API,Helper::replace_word($data['namePlan'].'-UP'),'SmartISP_UP',$dt['plan_out'],Helper::replace_word($data['namePlan'].'_UP'),$data['priority_a'],$data['limit_at_up'],$data['rate_up'],$data['burst_rate_up'],$data['burst_threshold_up'],$data['burst_time']);

				if ($debug==1) {
					$msg = $error->process_error($QUEUETREE);
					if($msg)
						return $msg;
				}

			}else{

				// encontro el plan sumamos la velocidad UP
				$ncl = $data['num_cl'] + 1;

				$QUEUETREE = QueueTree::set_child($API,
					$QUEUETREE[0]['.id'],
					null,
					null,
					null,
					RecalculateSpeed::speed($data['limit_at_up'],$ncl,true),
					RecalculateSpeed::speed($data['rate_up'],$ncl,true),
					RecalculateSpeed::speed($data['burst_rate_up'],$ncl,true),
					RecalculateSpeed::speed($data['burst_threshold_up'],$ncl,true),
					$data['burst_time'],
					$data['priority_a']
				);
				if ($debug==1) {
					$msg = $error->process_error($QUEUETREE);
					if($msg)
						return $msg;
				}

			}
		} else {

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


			//eliminamos del address list
			$ADDLIST = Firewall::get_id_address_list($API,$Address);

			if($ADDLIST != 'notFound'){

				$ADDLIST = Firewall::set_address_list($API,$ADDLIST[0]['.id'],$Address,Helper::replace_word($data['namePlan']),'false',Helper::replace_word($data['name']));

				if ($debug==1) {
					$msg = $error->process_error($ADDLIST);
					if($msg)
						return $msg;
				}

			} else {
				$ADDLIST = Firewall::add_address_list($API,$Address,Helper::replace_word($data['namePlan']),'false',Helper::replace_word($data['name']));

				if ($debug==1) {
					$msg = $error->process_error($ADDLIST);
					if($msg)
						return $msg;
				}
			}


		}



	}

	//////////////////// ACTUALIZAR PCQ-ADDRESS-LIST ///////////////////////////////

	function update_pcq_list($API,$data,$Address,$newAddress,$debug){

		$error = new Mkerror();

		//creamos reglas parent si no existen
		$PARENTS = $this->create_queuetree_parent($API,$debug);

		if ($debug==1) {
			$msg = $error->process_error($PARENTS);
			if($msg)
				return $msg;
		}

		$SBC = $this->set_basic_config($API,$error,$data,$Address,$newAddress,'update',$debug);

		if ($debug==1) {
			if ($SBC!=false)
				return $SBC;
		}



			//recuperamos la info del plan anterior del cliente
		$plan = Plan::find($data['oldplan']);

//		$oldnumcl = Client::where('plan_id',$data['oldplan'])->where('router_id',$data['old_router'])->count();
        $oldnumcl = ClientService::where('plan_id',$data['oldplan'])->where('router_id',$data['old_router'])->count(); /*** fix 01/06/2021 */

			//Buscamos el si existe el nuevo plan en queuetype DONW
		$QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_DOWN'));

		if($QUEUETYPE=='notFound'){
				//creamos el nuevo plan agregamos la regla a Queuetype
			$QUEUETYPE = QueueType::add_queuetype($API,Helper::replace_word($data['namePlan'].'_DOWN'),$data['rate_down'].'k',$data['burst_rate_down'],$data['burst_threshold_down'],$data['burst_time'],'DOWN');

			if ($debug==1) {
				$msg = $error->process_error($QUEUETYPE);
				if($msg)
					return $msg;
			}


				if ($data['changePlan']==true) { //Esta cambiando de plan

                    //$old_numcl = ($oldnumcl-1); /*** fix 01/06/2021 */
                    $old_numcl = ($oldnumcl);

					if ($old_numcl==0) {
						//el plan no tiene clientes eliminamos
						//buscamos el anterior plan
						$QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($plan->name.'_DOWN'));

						if ($QUEUETYPE!='notFound') {

							$QUEUETYPE = QueueType::delete_queuetype($API,$QUEUETYPE[0]['.id']);

							if ($debug==1) {
								$msg = $error->process_error($QUEUETYPE);
								if($msg)
									return $msg;
							}

						}
					}
				}

			}else{ //Significa que encontro el plan

				//verificamos si esta cambiando de plan
				if ($data['changePlan']==true) {

					//$old_numcl = ($oldnumcl-1); /*** fix 01/06/2021 */
					$old_numcl = ($oldnumcl);

					if ($old_numcl==0) {
						//el plan no tiene clientes eliminamos
						//buscamos el anterior plan
						$QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($plan->name.'_DOWN'));

						if ($QUEUETYPE!='notFound') {

							$QUEUETYPE = QueueType::delete_queuetype($API,$QUEUETYPE[0]['.id']);

							if ($debug==1) {
								$msg = $error->process_error($QUEUETYPE);
								if($msg)
									return $msg;
							}
						}

					}

				}else{ //no esta cmabiando de plan seteamos

					//Seteamos la regla QueueType DOWN
					$QUEUETYPE = Queuetype::set_queuetype($API,$QUEUETYPE[0]['.id'],null,$data['rate_down'].'k',$data['burst_rate_down'],$data['burst_threshold_down'],$data['burst_time'],'DOWN');

					if ($debug==1) {
						$msg = $error->process_error($QUEUETYPE);
						if($msg)
							return $msg;
					}


				}

			}


		/////// buscamos si existe el queuetype UP ///////

			$QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_UP'));

			if($QUEUETYPE == 'notFound'){
			//agregamos la regla a Queuetype
				$QUEUETYPE = QueueType::add_queuetype($API,Helper::replace_word($data['namePlan'].'_UP'),$data['rate_up'].'k',$data['burst_rate_up'],$data['burst_threshold_up'],$data['burst_time'],'UP');

				if ($debug==1) {
					$msg = $error->process_error($QUEUETYPE);
					if($msg)
						return $msg;
				}


			if ($data['changePlan']==true) { //Esta cambiando de plan
				//verificamos si el anterior plan tiene clientes caso contrario eliminamos el plan
                //$old_numcl = ($oldnumcl-1); /*** fix 01/06/2021 */
                $old_numcl = ($oldnumcl);

				if ($old_numcl==0) {
					//el plan no tiene clientes eliminamos
					//buscamos el anterior plan
					$QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($plan->name.'_UP'));

					if ($QUEUETYPE!='notFound') {

						$QUEUETYPE = QueueType::delete_queuetype($API,$QUEUETYPE[0]['.id']);

						if ($debug==1) {
							$msg = $error->process_error($QUEUETYPE);
							if($msg)
								return $msg;
						}

					}
				}
			}

		}else{ //Significa que no esta cambiando de plan actulizamos

			//verificamos si esta cambiando de plan
			if ($data['changePlan']==true) {
                //$old_numcl = ($oldnumcl-1); /*** fix 01/06/2021 */
                $old_numcl = ($oldnumcl);
				if ($old_numcl==0) {
						//el plan no tiene clientes eliminamos
						//buscamos el anterior plan
					$QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($plan->name.'_UP'));

					if ($QUEUETYPE!='notFound') {

						$QUEUETYPE = QueueType::delete_queuetype($API,$QUEUETYPE[0]['.id']);

						if ($debug==1) {
							$msg = $error->process_error($QUEUETYPE);
							if($msg)
								return $msg;
						}

					}

				}

				}else{ //no esta cmabiando de plan seteamos

					//Seteamos la regla QueueType DOWN
					$QUEUETYPE = Queuetype::set_queuetype($API,$QUEUETYPE[0]['.id'],null,$data['rate_up'].'k',$data['burst_rate_up'],$data['burst_threshold_up'],$data['burst_time'],'UP');

					if ($debug==1) {
						$msg = $error->process_error($QUEUETYPE);
						if($msg)
							return $msg;
					}


				}


			}


		//////// Buscamos si existe la regla mangle in postrouting //////
		//recomvertimos nombres
			$dt = Helper::replace_word_mangle($data['namePlan'],"in","out");

			if($data['no_rules'] == 0) {
				$MANGLE = Firewall::find_mangle($API,Helper::replace_word($data["namePlan"].'_in'));

				if ($MANGLE=='notFound') {
					# no esta la regla agregamos nueva regla
					$MANGLE = Firewall::add_mangle_postrouting($API,$dt['plan_in'],$dt['srcaddress'],Helper::replace_word($data['namePlan'].'_in'));

					if ($debug==1) {
						$msg = $error->process_error($MANGLE);
						if($msg)
							return $msg;
					}


					if ($data['changePlan']==true) {
						//$old_numcl = ($oldnumcl-1); /*** fix 01/06/2021 */
						$old_numcl = ($oldnumcl);
						if ($old_numcl==0) {
							# significa que el anterior plan no tiene clientes eliminamos la regla mangle in postrouting
							// Buscamos y eliminamos las reglas mangle
							$MANGLE = Firewall::find_mangle($API,Helper::replace_word($plan->name.'_in'));
							if ($MANGLE!='notFound') {
								$MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);

								if ($debug==1) {
									$msg = $error->process_error($MANGLE);
									if($msg)
										return $msg;
								}

							}
						}
					}

				}else{

					if ($data['changePlan']==true) {
						//$old_numcl = ($oldnumcl-1); /*** fix 01/06/2021 */
						$old_numcl = ($oldnumcl);
						if ($old_numcl==0) {
							//eliminamos la regla postrouting
							// Buscamos y eliminamos las reglas mangle
							$MANGLE = Firewall::find_mangle($API,Helper::replace_word($plan->name.'_in'));

							if ($MANGLE!='notFound') {
								$MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);

								if ($debug==1) {
									$msg = $error->process_error($MANGLE);
									if($msg)
										return $msg;
								}

							}
						}

					}else{

						//seteamos regla mangle
						$MANGLE = Firewall::set_mangle_postrouting($API,$MANGLE[0]['.id'],$dt['plan_in'],$dt['srcaddress'],Helper::replace_word($data['namePlan'].'_in'));

						if ($debug==1) {
							$msg = $error->process_error($MANGLE);
							if($msg)
								return $msg;
						}

					}

				}

				/////// Buscamos si existe la regla mangle out forward //////
				$MANGLE = Firewall::find_mangle($API,Helper::replace_word($data["namePlan"].'_out'));

				if ($MANGLE=='notFound') {
					# no esta la regla agregamos nueva regla
					$MANGLE = Firewall::add_mangle_forward($API,$dt['plan_out'],$dt['srcaddress'],Helper::replace_word($data['namePlan'].'_out'));

					if ($debug==1) {
						$msg = $error->process_error($MANGLE);
						if($msg)
							return $msg;
					}

					//verificamos si el anterior plan tiene clientes
					if ($data['changePlan']==true) {
						//$old_numcl = ($oldnumcl-1); /*** fix 01/06/2021 */
						$old_numcl = ($oldnumcl);
						if ($old_numcl==0) {
							# significa que el anterior plan no tiene clientes eliminamos la regla mangle in postrouting
							// Buscamos y eliminamos las reglas mangle
							$MANGLE = Firewall::find_mangle($API,Helper::replace_word($plan->name.'_out'));

							if ($MANGLE!='notFound') {
								$MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);

								if ($debug==1) {
									$msg = $error->process_error($MANGLE);
									if($msg)
										return $msg;
								}

							}
						}
					}

				}else{
					//seteamos regla mangle
					if ($data['changePlan']==true) {
						//$old_numcl = ($oldnumcl-1); /*** fix 01/06/2021 */
						$old_numcl = ($oldnumcl);
						if ($old_numcl==0) {
							// Buscamos y eliminamos las reglas mangle
							$MANGLE = Firewall::find_mangle($API,Helper::replace_word($plan->name.'_out'));

							if ($MANGLE!='notFound') {
								$MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);

								if ($debug==1) {
									$msg = $error->process_error($MANGLE);
									if($msg)
										return $msg;
								}

							}

						}

					}else{

						$MANGLE = Firewall::set_mangle_forward($API,$MANGLE[0]['.id'],$dt['plan_out'],$dt['srcaddress'],Helper::replace_word($data['namePlan'].'_out'));

						if ($debug==1) {
							$msg = $error->process_error($MANGLE);
							if($msg)
								return $msg;
						}

					}
				}

			}


		///// creamos reglas en address list para el cliente /////

		//buscamos si existe el cliente en address list
		if ($data['changePlan']==true) {
			$ADDRESSLIST = Firewall::get_id_address_list_by_name($API,$Address,Helper::replace_word($plan->name));


//			dd('hii', $ADDRESSLIST, $plan->name);
			if ($ADDRESSLIST=='notFound') {
				# No esta el cliente agregamos nueva regla
				$ADDRESSLIST = Firewall::add_address_list($API,$newAddress,$dt['srcaddress'],'false',Helper::replace_word($data['name']));

				if ($debug==1) {
					$msg = $error->process_error($ADDRESSLIST);
					if($msg)
						return $msg;
				}

			} else {
				//seteamos Addresslist
				$ADDRESSLIST = firewall::set_address_list($API,$ADDRESSLIST[0]['.id'],$newAddress,$dt['srcaddress'],'false',Helper::replace_word($data['name']));

				if ($debug==1) {
					$msg = $error->process_error($ADDRESSLIST);
					if($msg)
						return $msg;
				}

			}
		} else {
			$ADDRESSLIST = Firewall::get_id_address_list_by_name($API,$Address,Helper::replace_word($data['namePlan']));

			if ($ADDRESSLIST=='notFound') {
				# No esta el cliente agregamos nueva regla
				$ADDRESSLIST = Firewall::add_address_list($API,$newAddress,$dt['srcaddress'],'false',Helper::replace_word($data['name']));

				if ($debug==1) {
					$msg = $error->process_error($ADDRESSLIST);
					if($msg)
						return $msg;
				}

			} else {
				//seteamos Addresslist
				$ADDRESSLIST = firewall::set_address_list($API,$ADDRESSLIST[0]['.id'],$newAddress,$dt['srcaddress'],'false',Helper::replace_word($data['name']));

				if ($debug==1) {
					$msg = $error->process_error($ADDRESSLIST);
					if($msg)
						return $msg;
				}

			}
		}


//			if(is_array($ADDRESSLIST) && count($ADDRESSLIST) > 1) {
//
//			}

//			$ADDRESSLIST = Firewall::get_id_address_list_by_name($API,$Address,$data['address_list_name']);
//
//			if ($ADDRESSLIST=='notFound') {
//			# No esta el cliente agregamos nueva regla
//				if($data['no_rules'] == 1) {
//					$ADDRESSLIST = Firewall::add_address_list($API,$newAddress,$data['address_list_name'],'false',Helper::replace_word($data['name']));
//
//				}
//
//				if ($debug==1) {
//					$msg = $error->process_error($ADDRESSLIST);
//					if($msg)
//						return $msg;
//				}
//
//			} else {
//			//seteamos Addresslist
//				if($data['no_rules'] == 1) {
//					$ADDRESSLIST = firewall::set_address_list($API,$ADDRESSLIST[0]['.id'],$newAddress,$data['address_list_name'],'false',Helper::replace_word($data['name']));
//
//				}
//
//				if ($debug==1) {
//					$msg = $error->process_error($ADDRESSLIST);
//					if($msg)
//						return $msg;
//				}
//
//			}

		///// creamos reglas en Queue Tree para el grupo segun el plan /////

		if($data['no_rules'] == 0) {


			//buscamos si existe el Queue Tree en el grupo Download
			$QUEUETREE = QueueTree::get_parent($API, Helper::replace_word($data['namePlan'] . '-DOWN'));

			if ($QUEUETREE == 'notFound') {
				// creamos regla DOWN recalculando la velocidad

				$QUEUETREE = QueueTree::create_child($API,
					Helper::replace_word($data['namePlan'] . '-DOWN'),
					'SmartISP_DOWN', $dt['plan_in'],
					Helper::replace_word($data['namePlan'] . '_DOWN'),
					$data['priority_a'],
					RecalculateSpeed::speed($data['limit_at_down'], $data['changePlan'] == true ? ($data['num_cl'] + 1) : $data['num_cl'], true),
					RecalculateSpeed::speed($data['rate_down'], $data['changePlan'] == true ? ($data['num_cl'] + 1) : $data['num_cl'], true),
					RecalculateSpeed::speed($data['burst_rate_down'], $data['changePlan'] == true ? ($data['num_cl'] + 1) : $data['num_cl'], true),
					RecalculateSpeed::speed($data['burst_threshold_down'], $data['changePlan'] == true ? ($data['num_cl'] + 1) : $data['num_cl'], true),
					$data['burst_time']
				);
				if ($debug == 1) {
					$msg = $error->process_error($QUEUETREE);
					if ($msg)
						return $msg;
				}


				if ($data['changePlan'] == true) {

					//$old_numcl = ($oldnumcl-1); /*** fix 01/06/2021 */
					$old_numcl = ($oldnumcl);

					if ($old_numcl == 0) { //no tiene clientes eliminamos el plan

						$QUEUETREE = QueueTree::get_parent($API, Helper::replace_word($plan->name . '-DOWN'));

						if ($QUEUETREE != 'notFound') {

							$QUEUETREE = QueueTree::delete_parent($API, $QUEUETREE[0]['.id']);

							if ($debug == 1) {
								$msg = $error->process_error($QUEUETREE);
								if ($msg)
									return $msg;
							}

						}
					}

					if ($old_numcl > 0) { //tiene clientes el plan anterior recalculamos las velocidades

						$QUEUETREE = QueueTree::get_parent($API, Helper::replace_word($plan->name . '-DOWN'));

						if ($QUEUETREE != 'notFound') { //encontro el plan recalculamos

							//recuperamos advanced burst del plan anterior
							$burst = Burst::get_all_burst($plan->upload, $plan->download, $plan->burst_limit, $plan->burst_threshold, $plan->limitat);

							$QUEUETREE = QueueTree::set_child($API,
								$QUEUETREE[0]['.id'],
								null,
								null,
								null,
								RecalculateSpeed::speed($burst['lim_at_down'], $old_numcl, true),
								RecalculateSpeed::speed($plan->download, $old_numcl, true),
								RecalculateSpeed::speed($burst['bld'], $old_numcl, true),
								RecalculateSpeed::speed($burst['btd'], $old_numcl, true),
								$plan->burst_time,
								$plan->priority
							);
							if ($debug == 1) {
								$msg = $error->process_error($QUEUETREE);
								if ($msg)
									return $msg;
							}

						}

					}

				}

			} else { // encontro el plan sumamos la velocidad DOWN y restamos al plan anterior


				if ($data['changePlan'] == true) { //esta cambiando de plan

					//$old_numcl = ($oldnumcl-1); /*** fix 01/06/2021 */
					$old_numcl = ($oldnumcl);

					if ($old_numcl == 0) { //eliminamos el plan anterior

						$QUEUETREE = QueueTree::get_parent($API, Helper::replace_word($plan->name . '-DOWN'));
						if ($QUEUETREE != 'notFound') {

							$QUEUETREE = QueueTree::delete_parent($API, $QUEUETREE[0]['.id']);

							if ($debug == 1) {
								$msg = $error->process_error($QUEUETREE);
								if ($msg)
									return $msg;
							}
						}
					}

					//recalculamos el plan anterior

					if ($old_numcl > 0) { //tiene clientes el plan anterior recalculamos sus las velocidades

						$QUEUETREE = QueueTree::get_parent($API, Helper::replace_word($plan->name . '-DOWN'));

						if ($QUEUETREE != 'notFound') { //encontro el plan recalculamos

							//recuperamos advanced burst del plan anterior
							$burst = Burst::get_all_burst($plan->upload, $plan->download, $plan->burst_limit, $plan->burst_threshold, $plan->limitat);

							$QUEUETREE = QueueTree::set_child($API,
								$QUEUETREE[0]['.id'],
								null,
								null,
								null,
								RecalculateSpeed::speed($burst['lim_at_down'], $old_numcl, true),
								RecalculateSpeed::speed($plan->download, $old_numcl, true),
								RecalculateSpeed::speed($burst['bld'], $old_numcl, true),
								RecalculateSpeed::speed($burst['btd'], $old_numcl, true),
								$plan->burst_time,
								$plan->priority
							);
							if ($debug == 1) {
								$msg = $error->process_error($QUEUETREE);
								if ($msg)
									return $msg;
							}

						}

					}

					//recalculamos el plan actual
					$QUEUETREE = QueueTree::get_parent($API, Helper::replace_word($data['namePlan'] . '-DOWN'));

					if ($QUEUETREE != 'notFound') {

						$QUEUETREE = QueueTree::set_child($API,
							$QUEUETREE[0]['.id'],
							null,
							null,
							null,
							RecalculateSpeed::speed($data['limit_at_down'], ($data['num_cl'] + 1), true),
							RecalculateSpeed::speed($data['rate_down'], ($data['num_cl'] + 1), true),
							RecalculateSpeed::speed($data['burst_rate_down'], ($data['num_cl'] + 1), true),
							RecalculateSpeed::speed($data['burst_threshold_down'], ($data['num_cl'] + 1), true),
							$data['burst_time'],
							$data['priority_a']
						);
						if ($debug == 1) {
							$msg = $error->process_error($QUEUETREE);
							if ($msg)
								return $msg;
						}


					}


				} else {// no esta cambiando de plan


					$QUEUETREE = QueueTree::set_child($API,
						$QUEUETREE[0]['.id'],
						null,
						null,
						null,
						RecalculateSpeed::speed($data['limit_at_down'], $data['num_cl'], true),
						RecalculateSpeed::speed($data['rate_down'], $data['num_cl'], true),
						RecalculateSpeed::speed($data['burst_rate_down'], $data['num_cl'], true),
						RecalculateSpeed::speed($data['burst_threshold_down'], $data['num_cl'], true),
						$data['burst_time'],
						$data['priority_a']
					);
					if ($debug == 1) {
						$msg = $error->process_error($QUEUETREE);
						if ($msg)
							return $msg;
					}


				}

			}


			//buscamos si existe el Queue Tree en el grupo Upload
			$QUEUETREE = QueueTree::get_parent($API, Helper::replace_word($data['namePlan'] . '-UP'));

			if ($QUEUETREE == 'notFound') {

				// creamos regla DOWN
				$QUEUETREE = QueueTree::create_child($API,
					Helper::replace_word($data['namePlan'] . '-UP'),
					'SmartISP_UP',
					$dt['plan_out'],
					Helper::replace_word($data['namePlan'] . '_UP'),
					$data['priority_a'],
					RecalculateSpeed::speed($data['limit_at_up'], $data['changePlan'] == true ? ($data['num_cl'] + 1) : $data['num_cl'], true),
					RecalculateSpeed::speed($data['rate_up'], $data['changePlan'] == true ? ($data['num_cl'] + 1) : $data['num_cl'], true),
					RecalculateSpeed::speed($data['burst_rate_up'], $data['changePlan'] == true ? ($data['num_cl'] + 1) : $data['num_cl'], true),
					RecalculateSpeed::speed($data['burst_threshold_up'], $data['changePlan'] == true ? ($data['num_cl'] + 1) : $data['num_cl'], true),
					$data['burst_time']
				);
				if ($debug == 1) {
					$msg = $error->process_error($QUEUETREE);
					if ($msg)
						return $msg;
				}


				if ($data['changePlan'] == true) {

					//$old_numcl = ($oldnumcl-1); /*** fix 01/06/2021 */
					$old_numcl = ($oldnumcl);

					if ($old_numcl == 0) { //eliminamos

						$QUEUETREE = QueueTree::get_parent($API, Helper::replace_word($plan->name . '-UP'));

						if ($QUEUETREE != 'notFound') {

							$QUEUETREE = QueueTree::delete_parent($API, $QUEUETREE[0]['.id']);

							if ($debug == 1) {
								$msg = $error->process_error($QUEUETREE);
								if ($msg)
									return $msg;
							}
						}

					}

					if ($old_numcl > 0) {

						$QUEUETREE = QueueTree::get_parent($API, Helper::replace_word($plan->name . '-UP'));

						if ($QUEUETREE != 'notFound') { //encontro el plan recalculamos

							//recuperamos advanced burst del plan anterior
							$burst = Burst::get_all_burst($plan->upload, $plan->download, $plan->burst_limit, $plan->burst_threshold, $plan->limitat);

							$QUEUETREE = QueueTree::set_child($API,
								$QUEUETREE[0]['.id'],
								null,
								null,
								null,
								RecalculateSpeed::speed($burst['lim_at_up'], $old_numcl, true),
								RecalculateSpeed::speed($plan->upload, $old_numcl, true),
								RecalculateSpeed::speed($burst['blu'], $old_numcl, true),
								RecalculateSpeed::speed($burst['btu'], $old_numcl, true),
								$plan->burst_time,
								$plan->priority
							);
							if ($debug == 1) {
								$msg = $error->process_error($QUEUETREE);
								if ($msg)
									return $msg;
							}


						}

					}

				}

			} else {

				// encontro el plan sumamos la velocidad UP

				if ($data['changePlan'] == true) {

					//$old_numcl = ($oldnumcl-1); /*** fix 01/06/2021 */
					$old_numcl = ($oldnumcl);

					if ($old_numcl == 0) { //eliminamos

						$QUEUETREE = QueueTree::get_parent($API, Helper::replace_word($plan->name . '-UP'));

						if ($QUEUETREE != 'notFound') {

							$QUEUETREE = QueueTree::delete_parent($API, $QUEUETREE[0]['.id']);

							if ($debug == 1) {
								$msg = $error->process_error($QUEUETREE);
								if ($msg)
									return $msg;
							}

						}
					}

					if ($old_numcl > 0) { //tiene clientes el plan anterior recalculamos las velocidades

						$QUEUETREE = QueueTree::get_parent($API, Helper::replace_word($plan->name . '-UP'));

						if ($QUEUETREE != 'notFound') { //encontro el plan recalculamos

							//recuperamos advanced burst del plan anterior
							$burst = Burst::get_all_burst($plan->upload, $plan->download, $plan->burst_limit, $plan->burst_threshold, $plan->limitat);

							$QUEUETREE = QueueTree::set_child($API,
								$QUEUETREE[0]['.id'],
								null,
								null,
								null,
								RecalculateSpeed::speed($burst['lim_at_up'], $old_numcl, true),
								RecalculateSpeed::speed($plan->upload, $old_numcl, true),
								RecalculateSpeed::speed($burst['blu'], $old_numcl, true),
								RecalculateSpeed::speed($burst['btu'], $old_numcl, true),
								$plan->burst_time,
								$plan->priority
							);
							if ($debug == 1) {
								$msg = $error->process_error($QUEUETREE);
								if ($msg)
									return $msg;
							}

						}
					}


					//recalculamos el plan actual
					$QUEUETREE = QueueTree::get_parent($API, Helper::replace_word($data['namePlan'] . '-UP'));

					if ($QUEUETREE != 'notFound') {

						$QUEUETREE = QueueTree::set_child($API,
							$QUEUETREE[0]['.id'],
							null,
							null,
							null,
							RecalculateSpeed::speed($data['limit_at_up'], ($data['num_cl'] + 1), true),
							RecalculateSpeed::speed($data['rate_up'], ($data['num_cl'] + 1), true),
							RecalculateSpeed::speed($data['burst_rate_up'], ($data['num_cl'] + 1), true),
							RecalculateSpeed::speed($data['burst_threshold_up'], ($data['num_cl'] + 1), true),
							$data['burst_time'],
							$data['priority_a']
						);
						if ($debug == 1) {
							$msg = $error->process_error($QUEUETREE);
							if ($msg)
								return $msg;
						}


					}


				} else { //encontro el plan


					if ($data['changePlan'] == true) {

						//$old_numcl = ($oldnumcl-1); /*** fix 01/06/2021 */
						$old_numcl = ($oldnumcl);


						if ($old_numcl == 0) { //eliminamos

							$QUEUETREE = QueueTree::get_parent($API, Helper::replace_word($plan->name . '-UP'));

							if ($QUEUETREE != 'notFound') {

								$QUEUETREE = QueueTree::delete_parent($API, $QUEUETREE[0]['.id']);

								if ($debug == 1) {
									$msg = $error->process_error($QUEUETREE);
									if ($msg)
										return $msg;
								}

							}
						}


						if ($old_numcl > 0) { //tiene clientes el plan anterior recalculamos las velocidades

							$QUEUETREE = QueueTree::get_parent($API, Helper::replace_word($plan->name . '-UP'));

							if ($QUEUETREE != 'notFound') { //encontro el plan recalculamos

								//recuperamos advanced burst del plan anterior
								$burst = Burst::get_all_burst($plan->upload, $plan->download, $plan->burst_limit, $plan->burst_threshold, $plan->limitat);

								$QUEUETREE = QueueTree::set_child($API,
									$QUEUETREE[0]['.id'],
									null,
									null,
									null,
									RecalculateSpeed::speed($burst['lim_at_up'], $old_numcl, true),
									RecalculateSpeed::speed($plan->upload, $old_numcl, true),
									RecalculateSpeed::speed($burst['blu'], $old_numcl, true),
									RecalculateSpeed::speed($burst['btu'], $old_numcl, true),
									$plan->burst_time,
									$plan->priority
								);
								if ($debug == 1) {
									$msg = $error->process_error($QUEUETREE);
									if ($msg)
										return $msg;
								}

							}

						}


					} else { //no esta cambiando el plan

						$QUEUETREE = QueueTree::set_child($API,
							$QUEUETREE[0]['.id'],
							null,
							null,
							null,
							RecalculateSpeed::speed($data['limit_at_up'], $data['num_cl'], true),
							RecalculateSpeed::speed($data['rate_up'], $data['num_cl'], true),
							RecalculateSpeed::speed($data['burst_rate_up'], $data['num_cl'], true),
							RecalculateSpeed::speed($data['burst_threshold_up'], $data['num_cl'], true),
							$data['burst_time'],
							$data['priority_a']
						);
						if ($debug == 1) {
							$msg = $error->process_error($QUEUETREE);
							if ($msg)
								return $msg;
						}


					}

				}

			}
		}

	}

	////////////////// ELIMINAR PCQ ADDRESS LIST ////////////////////////////

	function delete_pcq_list($API,$data,$Address,$option,$debug){

		$error = new Mkerror();

		if($option=='delete' && $data['no_rules'] == 0){

			$SBC = $this->set_basic_config($API,$error,$data,$Address,null,'delete',$debug);
			if ($debug==1) {
				if ($SBC!=false) {
					return $SBC;
				}
			}

		}



		if($data['no_rules'] == 0) {
			//recuperamos la cantidad de clientes
			$ncl = ($data['num_cl']-1);

			if ($ncl>0) { //significa que encontro el plan, descontamos un cliente

				//eliminamos del address list
				$ADDLIST = Firewall::get_id_address_list($API,$Address);

				if($ADDLIST != 'notFound'){

					$ADDLIST = Firewall::remove_address_list($API,$ADDLIST[0]['.id']);

					if ($debug==1) {
						$msg = $error->process_error($ADDLIST);
						if($msg)
							return $msg;
					}

				}


				//buscamos el plan en QueueTree y descontamos si no hay usuarios eliminamos el plan
				$QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-DOWN'));


				if ($QUEUETREE!='notFound') { //no se encontro el parent creamos nuevo parent

					//se encontro el parent seteamos los nuevos datos descontando
					$QUEUETREE = QueueTree::set_child($API,
						$QUEUETREE[0]['.id'],
						null,
						null,
						null,
						RecalculateSpeed::speed($data['limit_at_down'],$ncl,true),
						RecalculateSpeed::speed($data['speed_down'],$ncl,true),
						RecalculateSpeed::speed($data['burst_rate_down'],$ncl,true),
						RecalculateSpeed::speed($data['burst_threshold_down'],$ncl,true),
						$data['burst_time'],
						$data['priority_a']);
					if ($debug==1) {
						$msg = $error->process_error($QUEUETREE);
						if($msg)
							return $msg;
					}

				}

				//buscamos el plan en QueueTree y descontamos si no hay usuarios eliminamos el plan
				$QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-UP'));

				if ($QUEUETREE!='notFound') { //no se encontro el parent creamos nuevo parent

					//se encontro el parent seteamos los nuevos datos descontando
					$QUEUETREE = QueueTree::set_child($API,
						$QUEUETREE[0]['.id'],
						null,
						null,
						null,
						RecalculateSpeed::speed($data['limit_at_up'],$ncl,true),
						RecalculateSpeed::speed($data['speed_up'],$ncl,true),
						RecalculateSpeed::speed($data['burst_rate_up'],$ncl,true),
						RecalculateSpeed::speed($data['burst_threshold_up'],$ncl,true),
						$data['burst_time'],
						$data['priority_a']);
					if ($debug==1) {
						$msg = $error->process_error($QUEUETREE);
						if($msg)
							return $msg;
					}

				}

			}else{ //significa que es el ultimo cliente dentro del plan eliminamos los planes del router


				//eliminamos del address list
				$ADDLIST = Firewall::get_id_address_list($API,$Address);

				if($ADDLIST != 'notFound'){

					$ADDLIST = Firewall::remove_address_list($API,$ADDLIST[0]['.id']);

					if ($debug==1) {
						$msg = $error->process_error($ADDLIST);
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

			}
		}
		else {
			//eliminamos del address list
			$ADDLIST = Firewall::get_id_address_list($API,$Address);

			if($ADDLIST != 'notFound'){
				$ADDLIST = Firewall::set_address_list($API,$ADDLIST[0]['.id'],$Address,'avisos','false',Helper::replace_word($data['name']));

				if ($debug==1) {
					$msg = $error->process_error($ADDLIST);
					if($msg)
						return $msg;
				}

			}


			if($option == 'delete') {
				//eliminamos del address list
				$ADDLIST = Firewall::get_id_address_list($API,$Address);

				if($ADDLIST != 'notFound'){

					$ADDLIST = Firewall::remove_address_list($API,$ADDLIST[0]['.id']);

					if ($debug==1) {
						$msg = $error->process_error($ADDLIST);
						if($msg)
							return $msg;
					}

				}
			}
		}


	}

	//////////////////// BLOQUEAR PCQ-ADDRESS-LIST ///////////////////////////////
	public function block_pcq($API,$data,$Address,$debug){

		$error = new Mkerror();

		if($data['no_rules'] == 0) {
			//creamos reglas parent si no existen
			$PARENTS = $this->create_queuetree_parent($API,$debug);

			if ($debug==1) {
				$msg = $error->process_error($PARENTS);
				if($msg)
					return $msg;
			}
		}


		if($data['status'] == 'ac'){ //significa que esta activo en ese caso bloqueamos


			if($data['no_rules'] == 0) {
				$SBC = $this->set_basic_config($API, $error, $data, $Address, null, 'block', $debug);

				if ($debug == 1) {
					if ($SBC != false)
						return $SBC;
				}
			}
			$BLOCK = $this->delete_pcq_list($API,$data,$Address,'none',$debug);

			if (empty($BLOCK)) {
				return 'true';
			}else{
				if ($debug==1) {
					return $BLOCK;
				}
			}


		}

		if($data['status'] == 'de'){ //significa que esta bloqueado en ese caso desbloqueamos

			if($data['no_rules'] == 0) {
				$SBC = $this->set_basic_config($API,$error,$data,$Address,null,'unblock',$debug);

				if ($debug==1) {
					if ($SBC!=false)
						return $SBC;
				}
			}



			//negamos el drop para que no vuelva a bloquear al cliente
			$data['drop']=0;

			$BLOCK = $this->add_pcq_list($API,$data,$Address,$debug);

			if (empty($BLOCK)) {
				return 'false';
			}else{
				if ($debug==1) {
					return $BLOCK;
				}
			}


		}

	}

	////////////////// CONFIGURACION DE ARP,DHCP,ADV ///////////////////////////

	public function set_basic_config($API,$error,$data,$Address,$newAddress,$option,$debug){

		switch ($option) {
			case 'add':
				# Añadimos ARP,DHCP,ADV
			if($data['arp'] ==1){

				$ARP = Arp::arp_get_id($API,$Address);

				if($ARP!='notFound'){

					if ($ARP[0]['dynamic']=='true') {

						$ARP = Arp::arp_add($API,$Address,$data['mac'],$data['lan'],$data['name']);

						if ($debug==1) {
							$msg = $error->process_error($ARP);
							if($msg)
								return $msg;
						}

					}else{

						$ARP = Arp::arp_set($API,$ARP[0]['.id'],$data['mac'],$Address,$data['name']);

						if ($debug==1) {
							$msg = $error->process_error($ARP);
							if($msg)
								return $msg;
						}
					}


					}else{ //significa que no hay duplicidad añadimos los registros

						$ARP = Arp::arp_add($API,$Address,$data['mac'],$data['lan'],$data['name']);

						if ($debug==1) {
							$msg = $error->process_error($ARP);
							if($msg)
								return $msg;
						}
					}

				} // Fin de ARP

				if($data['dhcp']==1){

					$DHCP = Dhcp::dhcp_get_id($API,$Address,$data['mac']);

					if($DHCP != 'notFound'){

						if ($DHCP[0]['dynamic']=='true') {

							$DHCP = Dhcp::dhcp_add($API,$Address,$data['mac'],$data['name']);

							if ($debug==1) {
								$msg = $error->process_error($DHCP);
								if($msg)
									return $msg;
							}

						}else{

							$DHCP = Dhcp::dhcp_set($API,$DHCP[0]['.id'],$data['mac'],$Address,$data['name']);

							if ($debug==1) {
								$msg = $error->process_error($DHCP);
								if($msg)
									return $msg;
							}
						}

						}else{//significa que no hay duplicidad añadimos los registros

							$DHCP = Dhcp::dhcp_add($API,$Address,$data['mac'],$data['name']);

							if ($debug==1) {
								$msg = $error->process_error($DHCP);
								if($msg)
									return $msg;
							}
						}

				} // Fin de DHCP

				//ADV
				if($data['adv']==1){

					//recuperamos los datos del aviso de config
					$adv = AdvSetting::all()->first();
					$url = $adv->ip_server.'/'.$adv->server_path;
					//Buscamos en el web proxy
					$PROXY = Proxy::proxy_get_id($API,$Address);

					if($PROXY != 'notFound'){
						$PROXY = Proxy::proxy_set($API,$PROXY[0]['.id'],$Address,$adv->ip_server,$url,$data['name']);

						if ($debug==1) {
							$msg = $error->process_error($PROXY);
							if($msg)
								return $msg;
						}

					}
					else{
						//no encontro la ip del usuario en webproxy creamos con los nuevos datos enviados
						$PROXY = Proxy::proxy_add($API,$Address,$adv->ip_server,$url,$data['name']);

						if ($debug==1) {
							$msg = $error->process_error($PROXY);
							if($msg)
								return $msg;
						}

					}

				} // fin de ADV

				if($data['drop']==1) {
					//agregamos reglas drop a los clientes suspendidos
					$DROP = Firewall::get_id_filter_block($API,$Address);

					if ($DROP=='notFound') {
					 //establecemos el orden de insercion de la regla

						$ORDER = Firewall::count_filter_all($API);
						$DROP = Firewall::filter_add_block($API,$Address,$data['mac'],$data['name'],'Servicio cortado - '.$data['name'],$ORDER);

						if ($debug==1) {
							$msg = $error->process_error($DROP);
							if($msg)
								return $msg;
						}

					}
				}// fin de DROP

				return false;

				break;

				case 'update':
				# Actualizamos ARP,DHCP,ADV

				//ARP LIST
				if($data['arp']==1){

					$ARP = Arp::arp_get_id($API,$Address);

					if($ARP!='notFound'){

						if ($ARP[0]['dynamic'] == 'false') {

							$ARP = Arp::arp_set($API,$ARP[0]['.id'],$data['mac'],$newAddress,$data['name']);

							if ($debug==1) {
								$msg = $error->process_error($ARP);
								if($msg)
									return $msg;
							}

						}else{

							$ARP = Arp::arp_add($API,$newAddress,$data['mac'],$data['lan'],$data['name']);

							if ($debug==1) {
								$msg = $error->process_error($ARP);
								if($msg)
									return $msg;
							}
						}

					}else{

							//significa que no hay duplicidad añadimos los registros
						$ARP = Arp::arp_add($API,$newAddress,$data['mac'],$data['lan'],$data['name']);

						if ($debug==1) {
							$msg = $error->process_error($ARP);
							if($msg)
								return $msg;
						}
					}
				}

				//DHCP
				if($data['dhcp']==1){

					$DHCP = Dhcp::dhcp_get_id($API,$Address,$data['mac']);

					if($DHCP != 'notFound'){

						if ($DHCP[0]['dynamic'] == 'false') {

							$DHCP = Dhcp::dhcp_set($API,$DHCP[0]['.id'],$data['mac'],$newAddress,$data['name']);
							if ($debug==1) {
								$msg = $error->process_error($DHCP);
								if($msg)
									return $msg;
							}

						}else{

							$DHCP = Dhcp::dhcp_add($API,$newAddress,$data['mac'],$data['name']);

							if ($debug==1) {
								$msg = $error->process_error($DHCP);
								if($msg)
									return $msg;
							}
						}

					}else{

						$DHCP = Dhcp::dhcp_add($API,$newAddress,$data['mac'],$data['name']);

						if ($debug==1) {
							$msg = $error->process_error($DHCP);
							if($msg)
								return $msg;
						}

					}
				}

				//ADV
				if($data['adv']==1){
					//recuperamos los datos del aviso de config
					$adv = AdvSetting::all()->first();
					$url = $adv->ip_server.'/'.$adv->server_path;
					//Buscamos en el web proxy
					$PROXY = Proxy::proxy_get_id($API,$Address);

					if($PROXY != 'notFound'){
						$PROXY = Proxy::proxy_set($API,$PROXY[0]['.id'],$newAddress,$adv->ip_server,$url,$data['name']);

						if ($debug==1) {
							$msg = $error->process_error($PROXY);
							if($msg)
								return $msg;
						}
					}
					else{
						//no encontro la ip del usuario en webproxy creamos con los nuevos datos enviados
						$PROXY = Proxy::proxy_add($API,$newAddress,$adv->ip_server,$url,$data['name']);

						if ($debug==1) {
							$msg = $error->process_error($PROXY);
							if($msg)
								return $msg;
						}
					}

				}// fin ADV


				return false;

				break;

				case 'delete':
				# Eliminamos ARP,DHCP,ADV

				if($data['arp']==1){

					$ARP = Arp::arp_get_id($API,$Address);

					if($ARP!='notFound'){

						$ARP = Arp::arp_remove($API,$ARP[0]['.id']);

						if ($debug==1) {
							$msg = $error->process_error($ARP);
							if($msg)
								return $msg;
						}
					}
				}

				if($data['dhcp']==1){
					$DHCP = Dhcp::dhcp_get_id($API,$Address,$data['mac']);
					if($DHCP != 'notFound'){
						$DHCP = Dhcp::dhcp_remove($API,$DHCP[0]['.id']);

						if ($debug==1) {
							$msg = $error->process_error($DHCP);
							if($msg)
								return $msg;
						}
					}
				}

				if($data['adv']==1){
					$ADDLIST = Firewall::get_id_address_list($API,$Address);
					if($ADDLIST != 'notFound'){
						$ADDLIST = Firewall::remove_address_list($API,$ADDLIST[0]['.id']);

						if ($debug==1) {
							$msg = $error->process_error($ADDLIST);
							if($msg)
								return $msg;
						}
					}
					$PROXY = Proxy::proxy_get_id($API,$Address);
					if($PROXY != 'notFound'){
						$PROXY = Proxy::proxy_remove($API,$PROXY[0]['.id']);

						if ($debug==1) {
							$msg = $error->process_error($PROXY);
							if($msg)
								return $msg;
						}
					}

				}

				if($data['drop']==1){

						//Intentamos eliminamos las reglas drop
					$DROP = Firewall::get_id_filter_block($API,$Address);

					if ($DROP!='notFound') {
							//encontro la regla eliminamos
						$DROP = Firewall::remove_filter_block($API,$DROP[0]['.id']);

						if ($debug==1) {
							$msg = $error->process_error($DROP);
							if($msg)
								return $msg;
						}

						}//end if
					}

					return false;

					break;

					case 'block':

					//ADV
					if($data['adv']==1){ //esta activo el portal cliente redireccionamos al web proxy
						//añadimos el cliente al address list
						$ADDLIST = Firewall::get_id_address_list_name($API,$Address,'avisos');

						if($ADDLIST != 'notFound'){
							//editamos a address list activamos
							$ADDLIST = Firewall::set_address_list($API,$ADDLIST[0]['.id'],$Address,'avisos','false',$data['name']);

							if ($debug==1) {
								$msg = $error->process_error($ADDLIST);
								if($msg)
									return $msg;
							}

						}
						else{
							//no encontro la ip del usuario address list creamos con los nuevos datos enviados
							$ADDLIST = Firewall::add_address_list($API,$Address,'avisos','false',$data['name']);

							if ($debug==1) {
								$msg = $error->process_error($ADDLIST);
								if($msg)
									return $msg;
							}
						}
					}


					if($data['drop']==1){ // no esta activo el portal bloqueamos mediante filter

						$FIND = Firewall::get_id_filter_block($API,$Address);

						if ($FIND!='notFound') {
							//encontro la regla solo actualizamos
							$BLOCK = Firewall::filter_set_block($API,$FIND[0]['.id'],$Address,$data['mac']);

							if ($debug==1) {
								$msg = $error->process_error($BLOCK);
								if($msg)
									return $msg;
							}

						}else{

							//establecemos el orden de insercion de la regla
							$ORDER = Firewall::count_filter_all($API);

							if ($debug==1) {
								$msg = $error->process_error($ORDER);
								if($msg)
									return $msg;
							}

							//no encontro la regla agregamos
							$BLOCK = Firewall::filter_add_block($API,$Address,$data['mac'],'Servicio cortado - '.$data['name'],$ORDER);

							if ($debug==1) {
								$msg = $error->process_error($BLOCK);
								if($msg)
									return $msg;
							}

						}
					}

					return false;

					break;

					case 'unblock':

					//ADV
					if($data['adv']=='1'){ //esta activo el portal cliente eliminamos el address list

						//quitamos el cliente al address list
						$ADDLIST = Firewall::get_id_address_list_name($API,$Address,'avisos');

						if($ADDLIST != 'notFound'){
							//eliminamos a address list activamos
							$ADDLIST = Firewall::remove_address_list($API,$ADDLIST[0]['.id']);

							if ($debug==1) {
								$msg = $error->process_error($ADDLIST);
								if($msg)
									return $msg;
							}
						}
					}

					if($data['drop']==1){ // no esta activo el portal eliminamos la regla filter

						$FIND = Firewall::get_id_filter_block($API,$Address);

						if ($FIND!='notFound') {
							//encontro la regla eliminamos
							$BLOCK = Firewall::remove_filter_block($API,$FIND[0]['.id']);

							if ($debug==1) {
								$msg = $error->process_error($BLOCK);
								if($msg)
									return $msg;
							}

						}
					}

					return false;

					break;

					default:
					return false;
					break;
				}


			}

	////////////////// AVISOS DE CORTE SOLO PARA PPPOE ///////////////////////////

			public function enabled_pppoe_advs($API,$network,$debug){

				$error = new Mkerror();

		// Creamos Reglas en firewall Nat para http
				$NAT = Firewall::find_block_nat($API,'Smartisp Avisos http-'.$network);

				if($NAT == 'notFound'){
			// creamos regla nat para redirigir
					$NAT = Firewall::add_nat_adv_ppp($API,$network,'http');

					if ($debug==1) {
						$msg = $error->process_error($NAT);
						if($msg)
							return $msg;
					}

				}
				else{
			//seteamos
					$NAT = Firewall::set_block_nat_ppp($API,$NAT[0]['.id'],$network);

					if ($debug==1) {
						$msg = $error->process_error($NAT);
						if($msg)
							return $msg;
					}

				}

		// Creamos Reglas en firewall Nat para https
				$NAT = Firewall::find_block_nat($API,'Smartisp Avisos https-'.$network);

				if($NAT == 'notFound'){
			// creamos regla nat para redirigir
					$NAT = Firewall::add_nat_adv_ppp($API,$network,'https');

					if ($debug==1) {
						$msg = $error->process_error($NAT);
						if($msg)
							return $msg;
					}

				}
				else{
			//seteamos
					$NAT = Firewall::set_block_nat_ppp($API,$NAT[0]['.id'],$network);

					if ($debug==1) {
						$msg = $error->process_error($NAT);
						if($msg)
							return $msg;
					}

				}

			}



	////////////////// AVISOS DE CORTE STANDAR ///////////////////////////

	//metodo para bloquer y enviar aviso de corte a los clientes SimpleQueues HotSpot DhcpLeases

			public function enabled_advs($API,$lan,$op,$debug){

				$error = new Mkerror();

		// Creamos Reglas en firewall filter

		//buscamos si ya existe la regla filter para udp
				$FILTER = Firewall::find_block_filter($API,'Smartisp avisos Smartisp-dns');

				if($FILTER == 'notFound'){
			//agregamos
					$FILTER = Firewall::filter_block_udp($API,$lan);

					if ($debug==1) {
						$msg = $error->process_error($FILTER);
						if($msg)
							return $msg;
					}

				}
				else{
			//seteamos
					$FILTER = Firewall::filter_set_udp($API,$FILTER[0][".id"],$lan);

					if ($debug==1) {
						$msg = $error->process_error($FILTER);
						if($msg)
							return $msg;
					}

				}

				$FILTER = Firewall::find_block_filter($API,'Smartisp avisos Smartisp-tcp');

				if($FILTER == 'notFound'){
					$FILTER = Firewall::filter_block_tcp($API,$lan);

					if ($debug==1) {
						$msg = $error->process_error($FILTER);
						if($msg)
							return $msg;
					}

				}
				else{
			//seteamos
					$FILTER = Firewall::filter_set_tcp($API,$FILTER[0]['.id'],$lan);

					if ($debug==1) {
						$msg = $error->process_error($FILTER);
						if($msg)
							return $msg;
					}

				}

				$NAT = Firewall::find_block_nat($API,'Smartisp Avisos');

				if($NAT == 'notFound'){
			// creamos regla nat para redirigir
					$NAT = Firewall::add_nat_adv($API,$lan,$op);

					if ($debug==1) {
						$msg = $error->process_error($NAT);
						if($msg)
							return $msg;
					}

					$NAT = Firewall::add_nat_adv_2($API,$lan,$op);
					if ($debug==1) {
						$msg = $error->process_error($NAT);
						if($msg)
							return $msg;
					}

				}
				else{
			//seteamos
					$NAT = Firewall::set_block_nat($API,$NAT[0]['.id'],$lan,$op);

					if ($debug==1) {
						$msg = $error->process_error($NAT);
						if($msg)
							return $msg;
					}

				}


			}

	//metodo para actualizar en forma masiva web proxy access clients
			public function update_all_webproxy($API,$Address,$dsthost,$redirect,$comment,$debug){

				$error = new Mkerror();

				$PROXY = Proxy::proxy_get_id($API,$Address);

				if($PROXY != 'notFound'){

					$ACCESS = Proxy::proxy_set($API,$PROXY[0]['.id'],$Address,$dsthost,$redirect,$comment);

					if ($debug==1) {
						$msg = $error->process_error($ACCESS);
						if($msg)
							return $msg;
					}

				}


			}

	//metodo para agregar proxy

			public function enable_proxy($API,$debug){

				$error = new Mkerror();
		// habilitamos web proxy
				$PROXY = Proxy::enable_proxy($API);

				if ($debug==1) {
					$msg = $error->process_error($PROXY);
					if($msg)
						return $msg;
				}

			}


	//metodo para quitar avisos al cliente


			public function remove_advs($API,$debug){

				$error = new Mkerror();
		// desabilitamos web proxy
				$PROXY = Proxy::disable_proxy($API);

				if ($debug==1) {
					$msg = $error->process_error($PROXY);
					if($msg)
						return $msg;
				}


				$comments[0] = "Smartisp avisos Smartisp-dns";
				$comments[1] = "Smartisp avisos Smartisp-tcp";

				for ($i=0; $i < 2; $i++) {
			//eliminamos los filter rules
					$FILTER = Firewall::find_block_filter($API,$comments[$i]);

					if ($FILTER!='notFound') {
						$FILTER = Firewall::remove_filter_block($API,$FILTER[0]['.id']);

						if ($debug==1) {
							$msg = $error->process_error($FILTER);
							if($msg)
								return $msg;
						}

					}
				}

		//quitamos el nat de redireccionamiento si existe
				$NAT = Firewall::find_block_nat($API,"Smartisp Avisos");

				if ($NAT!='notFound') {
					$NAT = Firewall::remove_block_nat($API,$NAT[0]['.id']);

					if ($debug==1) {
						$msg = $error->process_error($NAT);
						if($msg)
							return $msg;
					}

				}

			}







	//metodo para quitas avisos al cliente pppoe
			public function remove_proxy_ppp($API,$debug){

				$error = new Mkerror();
		// desabilitamos web proxy
				$PROXY = Proxy::disable_proxy($API);

				if ($debug==1) {
					$msg = $error->process_error($PROXY);
					if($msg)
						return $msg;
				}

			}

			public function remove_advs_ppp($API,$network,$debug){

				$error = new Mkerror();
				$NAT = Firewall::find_block_nat($API,"Smartisp Avisos http-".$network);
				if ($NAT!='notFound') {
					$NAT = Firewall::remove_block_nat($API,$NAT[0]['.id']);

					if ($debug==1) {
						$msg = $error->process_error($NAT);
						if($msg)
							return $msg;
					}

				}

				$NAT = Firewall::find_block_nat($API,"Smartisp Avisos https-".$network);
				if ($NAT!='notFound') {
					$NAT = Firewall::remove_block_nat($API,$NAT[0]['.id']);

					if ($debug==1) {
						$msg = $error->process_error($NAT);
						if($msg)
							return $msg;
					}

				}

			}

	////////////////// CONFIGURACION DE INTERFACES MIKROTIK ///////////////////////////

	//metodo para establecer amarre ip mac
			public function update_arp_interface($API,$interface,$op,$debug){

				$error = new Mkerror();

				$ID = Interf::interface_get_id($API,$interface);
		//obtenemos el tipo
				$TYPE = Interf::interface_get_type($API,$interface);
				if($TYPE != 'notFound') {
                    //establecemos
                    if($op)
                        $op = 'reply-only';
                    else
                        $op = 'enabled';

                    if($TYPE[0]['type']=='wlan')
                        $TYPE = 'wireless';
                    else
                        $TYPE = $TYPE[0]['type'];

                    $ARP = Interf::interface_set_arp($API,$TYPE,$ID[0]['.id'],$op);

                    if ($debug==1) {
                        $msg = $error->process_error($ARP);
                        if($msg)
                            return $msg;
                    }
                }

			}//end method*/

	////////////////// DATA PARA ACTUALIZAR REGLAS PARENT SIMPLE QUEUE WITH TREE ////////////////////
			public function data_simple_queue_with_tree_parent($plan_id,$router_id,$plan_down,$plan_up,$plan_aggr,$plan_limitat,$plan_bl,$plan_th, $tree_priority){


                ////////////////////////////// CALCULATE PARAMETRES //////////////////////////////////////////

                //Buscamos los clientes asociados al plan
                $clientsData = ClientService::where('plan_id',$plan_id)->where('router_id',$router_id)->where('status','ac')->where('tree_priority', $tree_priority)->get();

                $clients = ClientService::where('plan_id',$plan_id)->where('router_id',$router_id)->where('status','ac')->get();

                $ips = Helpers::get_ips($clients);
                $ipsCount = Helpers::get_ips($clientsData);

                $download = $plan_down / $plan_aggr;
                $upload = $plan_up / $plan_aggr;

                $speed = Burst::get_percent_kb($upload,$download,$plan_limitat);

                if ($ipsCount['ncl'] > $plan_aggr) {

                    //Parents
                    $maxlimit_down_parent = $download * $ipsCount['ncl'];
                    $maxlimit_up_parent = $upload * $ipsCount['ncl'];

                    $limit_at_down_parent = round($speed['download'] * $ipsCount['ncl'],0,PHP_ROUND_HALF_DOWN);
                    $limit_at_up_parent = round($speed['upload'] * $ipsCount['ncl'],0,PHP_ROUND_HALF_DOWN);
                    //sin redondeo
                    //$limit_at_down_parent = $speed['download'] * $ips['ncl'];
                    //$limit_at_up_parent = $speed['upload'] * $ips['ncl'];

                }else{

                    $maxlimit_down_parent = $plan_down;
                    $maxlimit_up_parent = $plan_up;

                    $limit_at_down_parent = round($speed['download'] * $plan_aggr,0,PHP_ROUND_HALF_DOWN);
                    $limit_at_up_parent = round($speed['upload'] * $plan_aggr,0,PHP_ROUND_HALF_DOWN);
                    //sin redondeo
                    //$limit_at_down_parent = $speed['download'] * $plan_aggr;
                    //$limit_at_up_parent = $speed['upload'] * $plan_aggr;

                }


                $burst_parent = Burst::get_all_burst($maxlimit_up_parent,$maxlimit_down_parent,$plan_bl,$plan_th,100);

                //Prepare data
                $maxlimit = $maxlimit_up_parent.'k/'.$maxlimit_down_parent.'k';
                $limitat = $limit_at_up_parent.'k/'.$limit_at_down_parent.'k';
                $bl = $burst_parent['blu'].'/'.$burst_parent['bld'];
                $bth = $burst_parent['btu'].'/'.$burst_parent['btd'];

                $dt = array(
                    'ips' => $ipsCount['ips'],
                    'ncl' => $ipsCount['ncl'],
                    'maxlimit' => $maxlimit,
                    'bl' => $bl,
                    'bth' => $bth,
                    'limitat' => $limitat,
                    //for clients
                    'limitat_up_cl' => $ipsCount['ncl']==0 ? '0' : round($limit_at_up_parent / $ipsCount['ncl'],0,PHP_ROUND_HALF_DOWN),
                    'limitat_down_cl' => $ipsCount['ncl']==0 ? '0' : round($limit_at_down_parent / $ipsCount['ncl'],0,PHP_ROUND_HALF_DOWN),
                    //sin redondeo
                    //'limitat_up_cl' => $ips['ncl']==0 ? '0' : $limit_at_up_parent / $ips['ncl'],
                    //'limitat_down_cl' => $ips['ncl']==0 ? '0' : $limit_at_down_parent / $ips['ncl'],

                    'maxlimit_down_parent' => $maxlimit_down_parent,
                    'maxlimit_up_parent' => $maxlimit_up_parent,
                    'limit_at_down_parent' => $limit_at_down_parent,
                    'limit_at_up_parent' => $limit_at_up_parent

                );

                return $dt;

			}

	////////////////// CREAR REGLAS PARENT PCQ-ADDRESSLIST ///////////////////////////

			public function create_queuetree_parent($API,$debug){

				$error = new Mkerror();

        $PARENT = QueueTree::get_parent($API,'SmartISP_DOWN');

				if ($PARENT=='notFound') {
				# No existe la regla creamos nueva regla
            $PARENT = QueueTree::create_parent($API,'SmartISP_DOWN',5);

					if ($debug==1) {
						$msg = $error->process_error($PARENT);
						if($msg)
							return $msg;
					}

				}

        $PARENT = QueueTree::get_parent($API,'SmartISP_UP');

				if ($PARENT=='notFound') {
				# No existe la regla creamos nueva regla
            $PARENT = QueueTree::create_parent($API,'SmartISP_UP',5);

					if ($debug==1) {
						$msg = $error->process_error($PARENT);
						if($msg)
							return $msg;
					}

				}

			}

			public function remove_queuetree_parent($API,$debug){

				$error = new Mkerror;
        $PARENT = QueueTree::get_parent($API,'SmartISP_DOWN');
				if ($PARENT!='notFound') {
					$PARENT = QueueTree::delete_parent($API,$PARENT[0]['.id']);

					if ($debug==1) {
						$msg = $error->process_error($PARENT);
						if($msg)
							return $msg;
					}

				}
        $PARENT = QueueTree::get_parent($API,'SmartISP_UP');
				if ($PARENT!='notFound') {
					$PARENT = QueueTree::delete_parent($API,$PARENT[0]['.id']);

					if ($debug==1) {
						$msg = $error->process_error($PARENT);
						if($msg)
							return $msg;
					}

				}

			}

		}
