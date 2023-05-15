<?php
namespace App\libraries;
/**
* HOTSPOT FUNCTIONS
*/
class Hotspot
{
	//metodo para añadir registros en hotspot
	public static function hotspot_add($API,$user,$pass,$Address,$mac,$profile,$comment,$macauth){

		$API->write("/ip/hotspot/user/add",false);

		if ($macauth=='mac') {
			//login automatico por mac
			$API->write("=name=".$mac,false);

		}
		elseif ($macauth=='binding') {
			//establecemos solo nombre de usuario
			$API->write("=name=".$user,false);
			$API->write("=password=",false);
		}
		else{

			$API->write("=name=".$user,false);
			$API->write("=password=".$pass,false);
		}

		$API->write('=address='.$Address,false); // IP
		$API->write('=mac-address='.$mac,false);
		$API->write('=profile='.$profile,false);
		$API->write('=comment='.$comment,true); // comentario
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}

	//metodo para crear user profile
	public static function hotspot_add_profile($API,$profile,$maxlimit,$bl,$bth,$bt,$priority,$limit_at){

		$API->write("/ip/hotspot/user/profile/add",false);
		$API->write("=name=".$profile,false);
		$API->write("=rate-limit=".$maxlimit.' '.$bl.' '.$bth.' '.$bt.' '.$priority.' '.$limit_at,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}

	//metodo para editar perfil hotspot
	public static function hotspot_set_profile($API,$id,$profile,$maxlimit,$bl,$bth,$bt,$priority,$limit_at){

		$API->write("/ip/hotspot/user/profile/set",false);
		$API->write("=.id=".$id,false);
		$API->write("=name=".$profile,false);
		$API->write("=rate-limit=".$maxlimit.' '.$bl.' '.$bth.' '.$bt.' '.$priority.' '.$limit_at,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}

	//metodo para eliminar registro en hotspot
	public static function hotspot_remove($API,$id){

		$API->write("/ip/hotspot/user/remove",false);
		$API->write("=.id=".$id,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	//metodo para eliminar usuario activo
	public static function hotspot_remove_active($API,$id){
		$API->write("/ip/hotspot/active/remove",false);
		$API->write("=.id=".$id,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	//metodo para editar registro en hotspot
	public static function hotspot_set($API,$id,$user,$pass,$Address,$mac,$profile,$comment,$macauth){

		$API->write("/ip/hotspot/user/set",false);
		$API->write("=.id=".$id,false);
		if ($macauth=='mac') {
			//login automatico por mac
			$API->write("=name=".$mac,false);
			$API->write("=password=",false);

		}elseif ($macauth=='binding') {
			//establecemos solo nombre de usuario
			$API->write("=name=".$user,false);
			$API->write("=password=",false);
		}
		else{
			//login con usuario y contraseña
			$API->write("=name=".$user,false);
			$API->write("=password=".$pass,false);
		}

		$API->write("=address=".$Address,false);
		$API->write("=mac-address=".$mac,false);
		$API->write("=profile=".$profile,false);
		$API->write("=comment=".$comment,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

	}

	//metodo para buscar registro en hotspot
	public static function hotspot_find_profile($API,$name){

		$API->write("/ip/hotspot/user/profile/print",false);
		$API->write("?name=".$name,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}

	//metodo para buscar perfil por id
	public static function hotspot_find_profile_pcq($API){

		$API->write("/ip/hotspot/user/profile/print",false);
		$API->write("?name=default",true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		if(count($ARRAY)>0){

			return $ARRAY;
		}
		else{
			//buscamos por id unica
			$API->write('/ip/hotspot/profile/print',false);
			$API->write('?.id=*0',true);
			$READ = $API->read(false);
			$ARRAY = $API->parseResponse($READ);
			return $ARRAY;
		}

	}

	//metodo para buscar y recuperar la ID de un user en hotspot
	public static function hotspot_get_id($API,$Address){

		$ID = $API->comm('/ip/hotspot/user/print', array(
		".proplist" => ".id",
		"?address" => $Address
		));

		if(count($ID)>0)
			return $ID;
		else
			return "notFound";

	}

	//metodo para buscar y recuperar la ID de un user active en hotspot
	public static function hotspot_useractive_get_id($API,$Address){
		$ID = $API->comm('/ip/hotspot/active/print', array(
		".proplist" => ".id",
		"?address" => $Address
		));

		if(count($ID)>0)
			return $ID;
		else
			return "notFound";

	}

	//metodo para verficar duplicidad de registro users en hotspot
	public static function hotspot_check_user($API,$Address){

		$API->write("/ip/hotspot/user/print",false);
		$API->write('?address='.$Address,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		if(count($ARRAY)>0)
			return true;

		return false;
	}

	//metodo para agregar o bloquear usuario en con ipbinding
	public static function block_ipbinding($API,$Address,$mac,$comment){

		$API->write("/ip/hotspot/ip-binding/add",false);
		$API->write("=mac-address=".$mac,false);
		$API->write("=address=".$Address,false);
		$API->write("=type=blocked",false);
		$API->write("=comment=".$comment,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}

	//metodo para agregar ip binding bypassed
	public static function add_ipbinding($API,$Address,$mac,$comment){

		$API->write("/ip/hotspot/ip-binding/add",false);
		$API->write("=mac-address=".$mac,false);
		$API->write("=address=".$Address,false);
		$API->write("=type=bypassed",false);
		$API->write("=comment=".$comment,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;

	}

	//metodo para setear ip binding
	public static function set_ipbinding($API,$id,$Address,$mac,$type,$comment){

		$API->write("/ip/hotspot/ip-binding/set",false);
		$API->write("=.id=".$id,false);
		$API->write("=mac-address=".$mac,false);
		$API->write("=address=".$Address,false);
		$API->write("=type=".$type,false);
		$API->write("=comment=".$comment,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;

	}

	//metodo para obtener el id de ip binding
	public static function get_id_ipbinding($API,$Address){

		$ID = $API->comm('/ip/hotspot/ip-binding/print', array(
		".proplist" => ".id",
		"?address" => $Address
		));

		if(count($ID)>0)
			return $ID;
		else
			return "notFound";
	}

	//metodo para eliminar usuario en ip-binding
	public static function remove_ipbinding($API,$id){

		$API->write("/ip/hotspot/ip-binding/remove",false);
		$API->write("=.id=".$id,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	//////////////////////// FUNCIONES PARA IMPORTAR ///////////////////////////

	//metodo para listar todos los user
	public static function list_users($API){

		$API->write("/ip/hotspot/user/print",true);
		$READ = $API->read(false);
        $ARRAY = $API->parseResponse($READ);

        return $ARRAY;
	}

	//metodo para buscar un perfil segun su nombre
	public static function list_profiles($API,$name){
		$API->write("/ip/hotspot/user/profile/print",false);
		$API->write("?name=".$name,true);
		$READ = $API->read(false);
        $ARRAY = $API->parseResponse($READ);

        return $ARRAY;
	}

	//metodo para listar todos los users perfiles
	public static function get_user_profiles_list($API){
		$API->write("/ip/hotspot/user/profile/print",true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}



}
