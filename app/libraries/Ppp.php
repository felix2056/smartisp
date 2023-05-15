<?php
namespace App\libraries;
/**
* PPP FUNCTIONS
*/
class Ppp
{
	//metodo para añadir ppp secrets
	public static function ppp_add($API,$user,$pass,$Address,$local,$mac,$profile,$comment){

		$API->write("/ppp/secret/add",false);
		$API->write("=name=".$user,false);
		$API->write("=service=pppoe",false);
		$API->write("=password=".$pass,false);
		$API->write('=remote-address='.$Address,false); // IP
		$API->write('=local-address='.$local,false);// Gateway
		if ($mac!='00:00:00:00:00:00') {
			$API->write("=caller-id=".$mac,false);
		}

		$API->write('=profile='.$profile,false);
		$API->write('=comment='.$comment,true); // comentario
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;

	}


	//metodo para listar perfiles ppp desde mikrotik
	public static function get_ppp_profile_list($API){

		$API->write("/ppp/profile/print",true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;

	}

	//metodo para listar todos los users secrets
	public static function ppp_list_secrets($API){

		$API->write("/ppp/secret/print",true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	//metodo para buscar perfil ppp
	public static function ppp_find_profile($API,$name){

		$API->write("/ppp/profile/print",false);
		$API->write("?name=".$name,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}

	//metodo para crear profile ppp
	public static function ppp_add_profile($API,$profile,$maxlimit,$bl,$bth,$bt,$priority,$limit_at){

		$API->write("/ppp/profile/add",false);
		$API->write("=name=".$profile,false);
		$API->write("=comment=".'Smartisp-'.$profile,false);
		$API->write("=rate-limit=".$maxlimit.' '.$bl.' '.$bth.' '.$bt.' '.$priority.' '.$limit_at,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}

	//metodo para verficar duplicidad de registro users en ppp
	public static function ppp_check_user($API,$name){

		$API->write("/ppp/secret/print",false);
		$API->write('?name='.$name,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		if(count($ARRAY)>0)
			return true;

		return false;
	}

	//metod para buscar user en active connections ppp
	public static function get_active_connetion($API,$Address){
		$API->write("/ppp/active/print",false);
		$API->write("?address=".$Address,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		if(count($ARRAY)>0)
			return $ARRAY;

		return false;

	}

	//metodo para quirar el usuario del active connections ppp
	public static function remove_active_connection($API,$id){
		$API->write("/ppp/active/remove",false);
		$API->write("=.id=".$id,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	//metodo para habilitar o desabilitar secret ppp
	public static function enable_disable_secret($API,$id,$status,$comment){

		$API->write("/ppp/secret/set",false);
		$API->write("=.id=".$id,false);
		$API->write("=comment=".$comment,false);
		$API->write("=disabled=".$status,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;

	}

	//metodo para editar registro en ppp
	public static function ppp_set($API,$id,$name,$pass,$Address,$local,$mac,$profile,$comment){

		$API->write("/ppp/secret/set",false);
		$API->write("=.id=".$id,false);
		$API->write("=name=".$name,false);
		if (!empty($pass)) {
			$API->write("=password=".$pass,false);
		}
		$API->write("=remote-address=".$Address,false);
		$API->write('=local-address='.$local,false);// Gateway

		if ($mac!='00:00:00:00:00:00') {
			$API->write("=caller-id=".$mac,false);
		}

		$API->write("=profile=".$profile,false);
		$API->write("=comment=".$comment,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;

	}

	//metodo para eliminar registro en ppp
	public static function ppp_remove($API,$id){

		$API->write("/ppp/secret/remove",false);
		$API->write("=.id=".$id,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	//metodo para eliminar registro de active connections
	public static function active_ppp_remove($API,$id){

		$API->write("/ppp/active/remove",false);
		$API->write("=.id=".$id,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	//metodo para buscar un active connections
	public static function ppp_active_get_id($API,$Address){

		$ID = $API->comm('/ppp/active/print', array(
			".proplist" => ".id",
			"?address" => $Address
		));

		if(count($ID)>0)
			return $ID;
		else
			return "notFound";
	}

	//metodo para editar perfil secret
	public static function ppp_set_profile($API,$id,$profile,$maxlimit,$bl,$bth,$bt,$priority,$limit_at){

		$API->write("/ppp/profile/set",false);
		$API->write("=.id=".$id,false);
		$API->write("=name=".$profile,false);
		$API->write("=rate-limit=".$maxlimit.' '.$bl.' '.$bth.' '.$bt.' '.$priority.' '.$limit_at,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}


	//metodo para buscar y recuperar la ID de un user en ppp usando
	public static function ppp_get_id($API,$name){

		$ID = $API->comm('/ppp/secret/print', array(
			".proplist" => ".id",
			"?name" => $name
		));


		if(count($ID)>0)
			return $ID;
		else
			return "notFound";

	}



}
